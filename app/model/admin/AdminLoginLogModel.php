<?php
namespace app\model\admin;

use app\model\Model;

class AdminLoginLogModel extends Model
{
    // 分类
    public const TYPE_USERNAME_PASSWORD_EMPTY = 1; // 用户名和密码为空
    public const TYPE_USERNAME_PASSWORD_ERROR = 2; //  2 用户名或密码错误
    public const TYPE_USER_DENY = 3; // 账号被禁用
    public const TYPE_USER_EXPIRED = 4; // 账号已过期
    public const TYPE_LOGIN_OK = 5; // 正常登录
    public const TYPE_FIRST_LOGIN = 6; // 首次登录
    public const TYPE_PASSWORD_SIMPLE = 7; // 密码太简单
    public const TYPE_DEVICE_ID_ERROR = 8; // 设备号不存在或已删除
    public const TYPE_LOGIN_AUTH_CODE_ERROR = 9; // 授权码错误
    public const TYPE_SUPER_IP_ERROR = 10; // 超管IP限制
    public const TYPE_VIRTUAL_DENY = 11; // 虚拟销售不允许登录
    public const TYPE_USERNAME_NOT_FOUND = 12; // 用户名不存在

    // 当前模型表
    protected $name;
    protected $autoWriteTimestamp = false;
    // 当前模型的数据库连接
    protected $connection;
    /**
     * UserModel constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->connection = "xueluo";
        $this->name = 'admin_login_log';
        parent::__construct($data);
    }



    /**
     * 模型的数据字段和对应数据表的字段是对应的，
     * 默认会自动获取（包括字段类型），但自动获取会导致增加一次查询，
     * 因此你可以在模型中明确定义字段信息避免多一次查询的开销。
     * SELECT CONCAT("'",column_name,"' => '",data_type,"',") from information_schema.COLUMNS where table_name='x_admin_login_log'
     */
    protected $schema = [
        'id' => 'int',
        'username' => 'varchar',
        'ip' => 'varchar',
        'create_time' => 'bigint',
        'type' => 'tinyint',
    ];
}