<?php

namespace app\logic\auth;
use app\constants\ErrorCode;
use app\logic\BaseLogic;
use app\model\admin\AdminModel;
use app\model\admin\AdminRoleModel;
use app\model\admin\ResourceModel;
use app\model\admin\RoleResourceModel;
use app\model\admin\AdminDeviceModel;
use app\model\admin\AdminLoginLogModel;

//use app\util\AppEnv;
use think\facade\Event;
use Xueluo\Library\Exception\BusinessException;

class AdminLogic extends BaseLogic
{
    /**
     * @var AdminModel 登录用户
     */
    private $admin_md;
    /**
     * @var string 设备号
     */
    protected $deviceId;
    public function __construct()
    {
        $this->admin_md = new AdminModel();
        parent::__construct();
    }

    /**
     * 用户登陆
     *
     * auther suchenyi
     * dateTime 2021-04-26 15:06
     * @param string $admin_name
     * @param string $password
     * @return void
     */
    public function login($admin_name, $password)
    {
        // 登录日志记录
        $arr = [
            'create_time' => time(),
            'username'    => $admin_name ?: '',
            'ip'          => getRealIP(),
        ];
        try {
            if (empty($admin_name) || empty($password)) {
                $arr['type'] = AdminLoginLogModel::TYPE_USERNAME_PASSWORD_EMPTY;
                throw new BusinessException(-1, "用户名和密码不能为空");
            }

            $admin = AdminModel::where("username", $admin_name)->find();
            // 对象
            $this->admin_md = $admin;
            if (empty($admin)) {
                $arr['type'] = AdminLoginLogModel::TYPE_USERNAME_NOT_FOUND;
                throw new BusinessException(-1, "用户名或密码错误");
            }
            $logModel = new AdminLoginLogModel();
            // 取登录时间、密码更新时间、状态更新时间最大值
            $times = [$admin->lastlogintime, $admin->last_status_modified_time, $admin->last_password_modified_time];
            rsort($times);
            $startTime = current($times);

            // 是否超级管理员

            if ($admin->is_super != 1) {
                $admin->resource = [];
                $superRole       = AdminRoleModel::where([
                    "admin_id" => $admin->id,
                    "role_id"  => 1,
                ])->find();
            } else {
                $admin->resource = ["admin"];
            }
            // 超级管理员限制
//            $superAdminAuthFunc = function () use ($isSuper) {
//                if ($isSuper ) {
//                    if (!in_array(getRealIP(), config('system')['super_admin_allow_ips'])) {
//                        $arr['type'] = AdminLoginLogModel::TYPE_SUPER_IP_ERROR;
//                        throw new BusinessException(-1, "公司内部IP才能登录访问");
//                    }
//                }
//            };

            if ($admin->password != md5(md5($password) . $admin->encrypt)) {
                // 账号密码失败次数
                $failCount   = $logModel->where([
                    'username' => $admin_name,
                    'type'     => AdminLoginLogModel::TYPE_USERNAME_PASSWORD_ERROR,
                ])->where('create_time', '>', $startTime)->count();
                $arr['type'] = AdminLoginLogModel::TYPE_USERNAME_PASSWORD_ERROR;
                // 失败n次，将删除本机设备号
                $deleteDeviceIdCount = config('system')['login_del_device_id_count'];
                // 错误次数超过5次，禁用账号
                $denyUserCount = config('system')['login_max_error_count'];
                $failCount++;
                if ($failCount >= $deleteDeviceIdCount) {
                    if (!empty($this->deviceId)) {
                        // 删除本机设备号
                        AdminDeviceModel::update([
                            'is_delete' => 1
                        ], [
                            'device_id' => $this->deviceId,
                            'is_delete' => 0
                        ]);
                    }
                }
                if ($failCount >= $denyUserCount) {
                    // 禁用用户
                    $this->denyAdmin();
                    throw new BusinessException(-1, "用户名或密码错误,账号被禁用");
                } elseif ($failCount >= $deleteDeviceIdCount) {
                    // 设备号返回信息
                    $deviceData = $this->getDeviceData();
                    throw new BusinessException(
                        ErrorCode::LOGIN_DEVICE_ID_ERROR,
                        "用户名或密码错误,剩余输入次数" . ($denyUserCount - $failCount) . "次",
                        $deviceData
                    );
                }
                throw new BusinessException(-1, "用户名或密码错误,剩余输入次数" . ($denyUserCount - $failCount) . "次");
            }


            if ($admin->status != 1) {
                $arr['type'] = AdminLoginLogModel::TYPE_USER_DENY;
                throw new BusinessException(-1, "账号被禁用");
            }
            if (strtotime(date('Y-m-d', $admin->expire_time) . ' 23:59:59') < time()) {
                $arr['type'] = AdminLoginLogModel::TYPE_USER_EXPIRED;
                throw new BusinessException(-1, "账号已过期");
            }
            // 首次登录验证
            if ($admin->lastlogintime < $admin->create_time) {
                $arr['type'] = AdminLoginLogModel::TYPE_FIRST_LOGIN;
                throw new BusinessException(
                    ErrorCode::FIRST_LOGIN_RESET_PASSWORD,
                    ErrorCode::$messages[ErrorCode::FIRST_LOGIN_RESET_PASSWORD]
                );
            }
            // 密码检测
            try {
                AdminCheckPasswordLogic::needResetPassword($password);
            } catch (BusinessException $e) {
                $arr['type'] = AdminLoginLogModel::TYPE_PASSWORD_SIMPLE;
                throw $e;
            }
            // 设备号检测
//            try {
//                $this->checkDeviceId();
//            } catch (BusinessException $e) {
//                $arr['type'] = AdminLoginLogModel::TYPE_DEVICE_ID_ERROR;
//                // 设备号返回信息
//                $deviceData            = $this->getDeviceData();
//                $deviceData['message'] = $e->getMessage();
//                throw new BusinessException(ErrorCode::LOGIN_DEVICE_ID_ERROR, "请授权登录", $deviceData);
//            }

            // 授权码检测
//            if (!$this->adminDeviceModel->isStatusOk()) {
//                try {
//                    $this->checkLoginAuthCode();
//                } catch (BusinessException $e) {
//                    $arr['type'] = AdminLoginLogModel::TYPE_LOGIN_AUTH_CODE_ERROR;
//                    $deviceData  = $this->getDeviceData();
//                    throw new BusinessException(ErrorCode::LOGIN_AUTH_CODE_ERROR, $e->getMessage(), $deviceData);
//                }
//                // 监听，如果登录成功，则处理
//                Event::listen('adminLoginSuccess', function () use ($admin_name) {
//                    // 授权码更新
//                    $this->adminDeviceModel->save([
//                        'status' => AdminDeviceModel::STATUS_IS_VALIDATED
//                    ]);
//                    $this->clearSmsCode($admin_name);
//                    // 清空授权错误次数
//                    $this->clearAuthErrorCount($admin_name);
//                    // 清空授权码发送次数，暂不清空
//                    // $this->clearSmsCheckCount($admin_name);
//                });
//            }
        } catch (BusinessException $e) {
            $this->addLoginLog($arr);
            throw new BusinessException($e->getCode(), $e->getMessage(), $e->getData());
        }

        $admin->lastloginip   = getRealIP();
        $admin->lastlogintime = time();
        $admin->save();

        $arr['type'] = AdminLoginLogModel::TYPE_LOGIN_OK;
        $this->addLoginLog($arr);

        // 执行事件
        Event::trigger('adminLoginSuccess');

        $resource = AdminRoleModel::alias('admin_role')
            ->join(RoleResourceModel::getTables("role_resource"), "admin_role.role_id=role_resource.role_id")
            ->join(ResourceModel::getTables("resource"), "resource.id=role_resource.resource_id")
            ->where("admin_role.admin_id", $admin->id)->field('urls')
            ->select();
        if (count($resource) > 0) {
            foreach ($resource as $key => $value) {
                $admin->resource = array_merge($admin->resource, explode("\n", $value->urls));
            }
        }

        session('admin_id', $admin['id']);
        session('admin', json_encode($admin));
    }
    public function getAdminInfo()
    {
        $admin = session('admin');
        if (empty($admin)) {
            throw new BusinessException(-1, 'nologin');
        }
        return json_decode($admin);
    }
    /**
     * 登录设备号检测
     *
     * auther guoruidian
     * dateTime 2021-04-26 16:17
     * @return void
     */
    protected function checkDeviceId()
    {
        if (empty($this->deviceId)) {
            throw new BusinessException(ErrorCode::LOGIN_DEVICE_ID_ERROR, "设备号为空");
        }
        $this->adminDeviceModel = AdminDeviceModel::where('device_id', $this->deviceId)
            ->where('admin_id', $this->admin_md->id)
            ->where('is_delete', 0)
            ->find();
        if (empty($this->adminDeviceModel)) {
            throw new BusinessException(ErrorCode::LOGIN_DEVICE_ID_ERROR, "账号没有该设备号");
        }
    }
    /**
     * Notes：新增登录日志
     * User: suchenyi
     * DateTime: 2021年11月30日 09:44:45
     */
    public function addLoginLog($data)
    {
        $crm_admin_login_log_md = new AdminLoginLogModel;
        $crm_admin_login_log_md->insert($data);
        return 1;
    }

    public function denyAdmin()
    {
        return AdminModel::save([
            'status' => 0
        ]);
    }

    protected function getDeviceData()
    {
        // 设备号返回信息
        $deviceData = [];
        $deviceData['device_id'] = '';
        if ($this->deviceId) {
            // 设备号是否有效，如果有效，则沿用
            $adminDeviceModel = AdminDeviceModel::where('device_id', $this->deviceId)->where('is_delete', 0)->find();
            if ($adminDeviceModel) {
                $deviceData['device_id'] = $this->deviceId;
            }
        }
        if (empty($this->adminDeviceModel)) {
            $adminDeviceData = [
                'status' => AdminDeviceModel::STATUS_NOT_VALIDATE,
                'admin_id' => $this->admin_md->id,
                'device_id' => $deviceData['device_id'] ?: md5(uniqid(). rand(0, 9999)),
                'create_time' => time()
            ];
            (new AdminDeviceModel())->save($adminDeviceData);
            $deviceData['device_id'] = $adminDeviceData['device_id'];
        }
        // 短信接收人
//        $sendSmsToAdmin = $this->getSendAuthCodeAdmin();
//        $deviceData['mobile'] = $sendSmsToAdmin->getMobile();
//        $deviceData['realname'] = $sendSmsToAdmin->realname;
        return $deviceData;
    }
}