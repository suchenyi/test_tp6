<?php

namespace app\model\admin;

use app\model\Model;

class AdminDeviceModel extends Model
{
    public const STATUS_NOT_VALIDATE = 0; // 未验证
    public const STATUS_IS_VALIDATED = 1; // 已验证
    public const STATUS_CHANGED_PASSWORD = 2; // 修改密码
    public const STATUS_OTHER_LOGIN = 3; // 其他用户登录
    // 设置当前模型的数据库连接
    protected $connection;

    protected $autoWriteTimestamp = true;
    // 当前模型表
    protected $name;

    /**
     * AdminDeviceModel constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->connection = "xueluo";
        $this->name = 'admin_device';
        parent::__construct($data);
    }
    /*
     * 模型的数据字段和对应数据表的字段是对应的，
     * 默认会自动获取（包括字段类型），但自动获取会导致增加一次查询，
     * 因此你可以在模型中明确定义字段信息避免多一次查询的开销。
     * SELECT CONCAT("'",column_name,"' => '",data_type,"',") from information_schema.COLUMNS where table_name='x_admin_device'
     */
    protected $schema = [
        'id' => 'bigint',
        'admin_id' => 'int',
        'device_id' => 'varchar',
        'status' => 'tinyint',
        'create_time' => 'bigint',
        'modified_time' => 'bigint',
        'is_delete' => 'tinyint',
    ];

    /**
     * 状态是否正常
     *
     * auther suchenyi
     * dateTime 2021年11月30日 10:10:35
     * @return boolean
     */
    public function isStatusOk()
    {
        if ($this->status == static::STATUS_IS_VALIDATED) {
            return true;
        }
        return false;
    }
}
