<?php

namespace app\logic\auth;

use app\model\admin\ResourceModel;
use app\validate\Resource;
use Xueluo\Library\Base\Logic;

class ResourceLogic extends Logic
{

    protected function setModel()
    {
        return new ResourceModel();
    }

    protected function setValidate()
    {
        return Resource::class;
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
        $query = ResourceModel::where(function ($query) use ($param) {
            if (!empty($param['name'])) {
                $query->where('title', 'like', '%' . $param['name'] . '%');
            }
            if (!empty($param["status"])) {
                $query->where('status', $param["status"]);
            }
            if (isset($param["parent_id"])) {
                $query->where('parent_id', (int)$param["parent_id"]);
            }
        })->order('sort,id');
        return $query->paginate($param['rows'])->toArray();
    }

    public function save($array)
    {
        if (!empty($array['parent_id'])) {
            $parent         = ResourceModel::find($array['parent_id']);
            $array['level'] = $parent->level + 1;
        } else {
            $array['level'] = 1;
        }
        if (!empty($array['id'])) {//更新
            ResourceModel::where('id', $array['id'])->update($array);
            return ResourceModel::find($array['id']);
        } else {//插入
            $result = ResourceModel::create($array);
            if ($array['parent_id'] > 0) {
                $result->id_url = $parent->id_url . '-' . $result->id;
            } else {
                $result->id_url = $result->id;
            }
            $result->save();
            return $result;
        }
    }

    public function delete($ids)
    {
        return ResourceModel::whereIn('id', $ids)->delete();
    }

    private function validation(array $data)
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
            throw
            $errorMessage = $validator->errors()->first();
        }
    }

}
