<?php

namespace app\controller\auth;

use app\logic\auth\AdminLogic;
use app\Request;
use think\annotation\route\Group;
use think\annotation\Route;
use Xueluo\Library\Util\Result;

use Xueluo\Library\Exception\BusinessException;
use app\controller\auth\Controller;

/**
 * Class AdminController
 * @package app\controller\auth
 *
 */
class AdminController extends Controller
{
    private $adminLogic;
    public function __construct()
    {
        $this->adminLogic = new AdminLogic();
        parent::__construct();
    }

    /**
     * @param  string $name 登录
     * @return mixed
     * @Route ("/auth/admin/login",method="POST")
     */
    public function login(Request $request)
    {
        $user_name = $request->post("user_name", '', 'trim');
        $password = $request->post("password", '', 'trim');
        $this->adminLogic->login($user_name, $password);
        $result = $this->adminLogic->getAdminInfo();
        return Result::success($result,"");
    }

    /**
     * @param  string $name 获取用户列表
     * @return mixed
     * @Route ("/auth/admin/getList",method="GET")
     */
    public function getList(Request $request)
    {
        $data = $this->adminLogic->getList($request->get());
        $data = [
            'list' => $data['data'],
            'total' => $data['total']
        ];
        return Result::success($data);
    }

    /**
     * @param  string $name 添加、编辑用户
     * @return mixed
     * @Route ("/auth/admin/add_user",method="POST")
     */
    public function insert(Request $request)
    {
        $param = $request->post();
        if (isset($param['id'])) {
            return Result::fail(-1, "新增数据不能提交id属情");
        }
        $data = $this->adminLogic->save($param);
        return Result::success($data, "添加成功");
    }

    /**
     * @param  string $name 退出登录
     * @return mixed
     * @Route ("/auth/admin/login_out",method="GET")
     */
    public function login_out(){
        $data = $this->adminLogic->login_out();
        return Result::success([],'退出成功');
    }
}