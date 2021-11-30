<?php
namespace app\model\admin;

use app\model\Model;

class RoleResourceModel extends Model
{
    // 当前模型表
    protected $name;

    // 当前模型的数据库连接
    protected $connection;

    /**
     * RoleResourceModel constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->connection = "xueluo";
        $this->name = 'role_resource';
        parent::__construct($data);
    }

    /**
     * 模型的数据字段和对应数据表的字段是对应的，
     * 默认会自动获取（包括字段类型），但自动获取会导致增加一次查询，
     * 因此你可以在模型中明确定义字段信息避免多一次查询的开销。
     * SELECT CONCAT("'",column_name,"' => '",data_type,"',") from information_schema.COLUMNS where table_name='x_role_resource'
     */
    protected $schema = [
        'id' => 'int',//
        'main_admin_id' => 'int',//公司主帐号admin_id
        'role_id' => 'int',//角色id
        'resource_id' => 'int',//权限id
        'status' => 'tinyint',//状态 1：有效 2：无效
        'created_at' => 'int',//插入时间
        'updated_at' => 'int',//更新时间
        'deleted_at' => 'int',//删除时间
    ];
}