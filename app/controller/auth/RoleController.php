<?php

declare(strict_types=1);

namespace app\controller\auth;

use app\logic\auth\RoleLogic;
use app\Request;
use Xueluo\Library\Util\Result;
use think\annotation\Route;

class RoleController extends \app\controller\Controller
{
    private $roleLogic;

    public function __construct()
    {
        $this->roleLogic = new RoleLogic();
    }

    /**
     * 查询列表数据
     * @param RequestInterface $request
     * @return array
     * Created by suchenyi at 2020/9/25 15:27
     *
     * @route("/auth/role", method="get")
     */

    /**
     * @route("/auth/role", method="get")
     * OA\Get(path="/auth/role",summary="获取角色列表",
     *     description="获取角色列表",
     *     tags={"auth/role"},
     *     OA\Parameter(
     *       name="name",
     *       in="query",
     *       required=false,
     *       description="角色名称",
     *       OA\Schema(type="string")
     *     ),
     *     OA\Parameter(
     *       name="page",
     *       in="query",
     *       required=false,
     *       description="页数",
     *       OA\Schema(type="string")
     *     ),
     *     OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       description="每页行数",
     *       OA\Schema(type="string")
     *     ),
     *     OA\Response(
     *         response=200,
     *         description="success/成功",
     *         OA\MediaType(
     *             mediaType="application/json",
     *             OA\Schema(
     *                  OA\Property(property="msg", type="string", format="string", description="描述"),
     *                  OA\Property(property="code", type="integer", format="integer", description="状态"),
     *                  OA\Property(property="data",type="array",description="返回数据",
     *                      OA\Items(ref="#components/schemas/role"),
     *                  )
     *              )
     *           )
     *        )
     *     )
     * )
     */
    public function getList(Request $request)
    {
        $data = $this->roleLogic->getList($request->get());
        $data = [
            'list' => $data['data'],
            'total' => $data['total']
        ];
        return Result::success($data);
    }

    /**
     * 查询明细数据
     * @param int $id
     * @return mixed
     *
     * @rule("/auth/role/:id", method="get")
     */
    /**
     * @route("/auth/role/:id", method="get")
     * OA\Get(path="/auth/role/{id}",summary="获取角色详细",
     *     description="获取角色详细",
     *     tags={"auth/role"},
     *     OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       description="服务号名称",
     *       OA\Schema(type="string")
     *     ),
     *     OA\Response(
     *         response=200,
     *         description="success/成功",
     *         OA\MediaType(
     *             mediaType="application/json",
     *             OA\Schema(
     *                  OA\Property(property="msg", type="string", format="string", description="描述"),
     *                  OA\Property(property="code", type="integer", format="integer", description="状态"),
     *                  OA\Property(property="data",type="object",description="返回数据",ref="#components/schemas/role_with_resource")
     *              )
     *           )
     *        )
     *     )
     * )
     */
    public function get(int $id)
    {
        $data = $this->roleLogic->get($id);
        return Result::success($data);
    }

    /**
     * 添加数据
     * @param RequestInterface $request
     * @return mixed
     *
     * @rule("/auth/role", method="post")
     */
    /**
     *  @route("/auth/role", method="post")
     * OA\Post(path="/auth/role",summary="新增角色",
     *     description="新增角色",
     *     tags={"auth/role"},
     *     OA\RequestBody(
     *         required=true,
     *         OA\JsonContent(
     *             OA\Property(property="name",type="varchar",description="角色名称",example="角色名称"),
     *             OA\Property(property="resource_id",type="arrty",description="资源id",example="资源id",OA\Items(ref="int")),
     *             OA\Property(property="status",type="tinyint",description="状态 1：有效 2：无效",example="状态 1：有效 2：无效"),
     *         )
     *     ),
     *     OA\Response(
     *         response=200,
     *         description="success/成功",
     *         OA\MediaType(
     *             mediaType="application/json",
     *             OA\Schema(
     *                  OA\Property(property="msg", type="string", format="string", description="描述"),
     *                  OA\Property(property="code", type="integer", format="integer", description="状态"),
     *                  OA\Property(property="data",type="objcet",description="返回数据",ref="#components/schemas/role_with_resource")
     *              )
     *           )
     *        )
     *     )
     * )
     */

    public function insert(Request $request)
    {
        $param = $request->post();
        if (isset($param['id'])) {
            return Result::fail(-1, "新增数据不能提交id属情");
        }
        $data = $this->roleLogic->save($param);
        return Result::success($data, "添加成功");
    }


    /**
     * 修改数据
     * @param RequestInterface $request
     * @param int $id
     * @return mixed
     *
     * @route("/auth/role/{id}", method="put")
     */
    /**
     * @route("/auth/role/:id", method="put")
     * OA\Put(path="/auth/role/{id}",summary="修改角色",
     *     description="修改角色",
     *     tags={"auth/role"},
     *     OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="微信消息推送id",
     *         OA\Schema(type="string")
     *     ),
     *     OA\RequestBody(
     *         required=true,
     *         OA\JsonContent(
     *             OA\Property(property="name",type="varchar",description="角色名称",example="角色名称"),
     *             OA\Property(property="resource_id",type="arrty",description="资源id",example="资源id",OA\Items(ref="int")),
     *             OA\Property(property="status",type="tinyint",description="状态 1：有效 2：无效",example="状态 1：有效 2：无效"),
     *         )
     *     ),
     *     OA\Response(
     *         response=200,
     *         description="success/成功",
     *         OA\MediaType(
     *             mediaType="application/json",
     *             OA\Schema(
     *                  OA\Property(property="msg", type="string", format="string", description="描述"),
     *                  OA\Property(property="code", type="integer", format="integer", description="状态"),
     *                  OA\Property(property="data",type="objcet",description="返回数据",ref="#components/schemas/role_with_resource")
     *              )
     *           )
     *        )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $param = $request->post();
        if (empty($id)) {
            return Result::fail(-1, "修改数据必须提交id属性");
        }
        $param['id'] = $id;
        $data = $this->roleLogic->save($param);
        return Result::success($data, "修改成功");
    }

    /**
     * 删除数据
     * @param Request $request
     * @return mixed
     *
     *
     */
    /**
     * @route("/auth/role", method="delete")
     * OA\Delete(path="/auth/role",summary="删除角色",
     *     description="删除角色",
     *     tags={"auth/role"},
     *     OA\RequestBody(
     *         required=true,
     *         OA\JsonContent(
     *           OA\Property(property="ids",type="array",description="角色id",OA\Items(type="integer",example="1"))
     *         )
     *     ),
     *     OA\Response(
     *         response=200,
     *         description="success/成功",
     *         OA\MediaType(
     *             mediaType="application/json",
     *             OA\Schema(
     *                  OA\Property(property="msg", type="string", format="string", description="描述"),
     *                  OA\Property(property="code", type="integer", format="integer", description="状态"),
     *                  OA\Property(property="data",type="integer",description="影响条数")
     *              )
     *           )
     *        )
     *     )
     * )
     */
    public function delete(Request $request)
    {
        $ids = $request->post('ids');
        if (empty($ids)) {
            return Result::fail(-1,"删除数据必须提交id属性");
        }
        $data = $this->roleLogic->delete($ids);
        return Result::success($data);
    }

    /**
     * 角色下拉框
     * @Route("/auth/role_arr", method="PUT")
     * @return void
     */
    public function role_arr(){
        $this->roleLogic->getRoleArr();
    }
}
