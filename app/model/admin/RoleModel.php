<?php
namespace app\model\admin;

use app\model\Model;

class RoleModel extends Model
{
    // 当前模型表
    protected $name;

    // 当前模型的数据库连接
    protected $connection;

    /**
     * RoleModel constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->connection = "xueluo";
        $this->name = 'role';
        parent::__construct($data);
    }

    /**
     * 模型的数据字段和对应数据表的字段是对应的，
     * 默认会自动获取（包括字段类型），但自动获取会导致增加一次查询，
     * 因此你可以在模型中明确定义字段信息避免多一次查询的开销。
     * SELECT CONCAT("'",column_name,"' => '",data_type,"',") from information_schema.COLUMNS where table_name='x_role'
     */
    protected $schema = [
        'id' => 'int',//
        'name' => 'varchar',//角色名称
        'parent_id' => 'int',//父级id
        'admin_id' => 'int',//管理员id
        'status' => 'tinyint',//状态 1：有效 2：无效
        'create_time' => 'int',//插入时间
        'modified_time' => 'int',//更新时间
        'deleted_at' => 'int',//软删除时间
    ];
}