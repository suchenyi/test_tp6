<?php

namespace app\logic\auth;

use Xueluo\Library\Exception\BusinessException;
use app\logic\BaseLogic;
use app\model\admin\AdminRoleModel;
use app\model\admin\RoleModel;
use app\model\admin\RoleResourceModel;

class RoleLogic extends BaseLogic
{
    private $role_md;

    function __construct()
    {
        parent::__construct();
        $this->role_md = new RoleModel();
    }

    protected function setModel()
    {
        return new RoleModel();
    }

    protected function setValidate()
    {
        return \app\validate\Role::class;
    }

    /**
     * 列表查询
     * @param name 角色的名称
     * @param page 页数
     * @param size 每页条数
     *
     */
    public function getList($param)
    {
        empty($param['rows']) && $param['rows'] = 20;
        empty($param['page']) && $param['page'] = 1;
        $query = RoleModel::where(function ($query) use ($param) {
            if (!empty($param['name'])) {
                $query->where(function ($query) use ($param) {
                    $query->where('id', $param['name']);
                    $query->whereOr('name', 'like', '%' . $param['name'] . '%');
                });
            }
        })->order("id desc");
        return $query->paginate($param['page_size'])->toArray();
    }

    public function get(int $id)
    {
        $role = RoleModel::with(['resource'])->find($id)->hidden(['resource.pivot']);
        return $role;
    }

    public function save($array)
    {
        if (!empty($array['id'])) {//更新
            if ($array['status'] == 2 && $this->isUse($array['id'])) {
                throw new BusinessException(-1, "正在被使用的角色，不能禁用");
            }

            $resource = $array['resource_id'];
            if (empty($resource)) {
                $resource = [];
            }
            unset($array['resource_id']);
            RoleModel::where('id', $array['id'])->update($array);
            $resource_id = RoleResourceModel::where("role_id", $array['id'])->column('resource_id');
            //新的权限-旧的权限获取新增权限然后插入
            $diff = array_diff($resource, $resource_id);
            foreach ($diff as $key => $value) {
                RoleResourceModel::create([
                    "role_id"     => $array['id'],
                    "resource_id" => $value,
                    "status"      => 1
                ]);
            }
            //新的权限-旧的权限获取删除的权限然后删除
            $diff = array_diff($resource_id, $resource);
            if (count($diff) > 0) {
                RoleResourceModel::where("role_id", $array['id'])->whereIn("resource_id", $diff)->delete();
            }
            return $this->get($array['id'])->toArray();
        } else {//插入
            $resource = $array['resource_id'];
            unset($array['resource_id']);
            $role = RoleModel::create($array);

            if (is_array($resource) && count($resource) > 0) {
                foreach ($resource as $key => $value) {
                    RoleResourceModel::create([
                        "role_id"     => $role->id,
                        "resource_id" => $value,
                        "status"      => 1
                    ]);
                }
            }

            return $this->get($role->id)->toArray();
        }
    }

    public function delete($ids)
    {
        if ($this->isUse($ids)) {
            throw new BusinessException(-1, "正在被使用的角色，不能删除");
        }
        return RoleModel::whereIn('id', $ids)->delete();
    }

    function validation(array $data)
    {
        $validator = $this->validationFactory->make(
            $data,
            [
                'foo' => 'required',
                'bar' => 'required',
            ],
            [
                'foo.required' => 'foo is required',
                'bar.required' => 'bar is required',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(9999, $errorMessage);

        }
    }

    function isUse($roleId)
    {
        if (!is_array($roleId)) {
            $roleId = array($roleId);
        }
        $role = AdminRoleModel::whereIn("role_id", $roleId)->field('id')->find();
        if ($role) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Notes：
     * User: suchenyi
     * DateTime: 2020/12/14 11:21
     */
    public function getRoleArr()
    {
        return $this->role_md->where("status", 1)->field("id,name")->select()->toArray();
    }

}