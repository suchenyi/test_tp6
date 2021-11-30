<?php
namespace app\model\admin;

use app\model\Model;
use think\model\Pivot;

class AdminRoleModel extends Pivot
{
    // 当前模型表
    protected $name;

    // 当前模型的数据库连接
    protected $connection;

    /**
     * AdminRoleModel constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->connection = "xueluo";
        $this->name = 'admin_role';
        parent::__construct($data);
    }

    /**
     * 模型的数据字段和对应数据表的字段是对应的，
     * 默认会自动获取（包括字段类型），但自动获取会导致增加一次查询，
     * 因此你可以在模型中明确定义字段信息避免多一次查询的开销。
     * SELECT CONCAT("'",column_name,"' => '",data_type,"',") from information_schema.COLUMNS where table_name='x_admin_role'
     */
    protected $schema = [
        'id' => 'int',
        'admin_id' => 'int',
        'role_id' => 'int',
        'status' => 'tinyint',
        'create_time' => 'bigint',
        'modified_time' => 'bigint',
        'deleted_at' => 'bigint',
    ];
}