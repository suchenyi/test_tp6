<?php

declare(strict_types=1);

namespace app\controller\auth;

use app\logic\auth\ResourceLogic;
use app\middleware\Check;
use Xueluo\Library\Util\Result;
use app\Request;
use think\annotation\Route;

class ResourceController extends \app\controller\Controller
{
    private   $resourceLogic;
    protected $middleware = [Check::class];

    public function __construct()
    {
        $this->resourceLogic = new ResourceLogic();
    }

    /**
     * notes ：获取数据--资源列表
     * author：suchenyi
     * @route("auth/resource", method="GET")
     */

    public function getList(Request $request)
    {
        $data = $this->resourceLogic->getList($request->get());
        $data = [
            'list'  => $data['data'],
            'total' => $data['total']
        ];
        return Result::success($data);
    }


    /**
     * @route("/auth/resource/:id", method="get")
     */
    public function get(int $id)
    {
        $data = $this->resourceLogic->get($id);
        $data || $data = array();
        return Result::success($data);
    }

    /**
     * 添加数据
     * @param RequestInterface $request
     * @return mixed
     *
     * @rule("/auth/resource", method="post")
     */
    /**
     * @route("/auth/resource", method="post")
     * OA\Post(path="/auth/resource",summary="添加资源",
     *     description="添加资源",
     *     tags={"auth/resource"},
     *     OA\RequestBody(
     *         required=true,
     *         OA\JsonContent(
     *             OA\Property(property="title",type="varchar",description="权限名称",example="权限名称"),
     *             OA\Property(property="urls",type="varchar",description="urls路径",example="urls路径"),
     *             OA\Property(property="parent_id",type="int",description="父级id",example="父级id"),
     *             OA\Property(property="status",type="tinyint",description="状态 1：有效 2：无效",example="状态 1：有效 2：无效")
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
     *                  OA\Property(property="data",type="objcet",description="返回数据",ref="#components/schemas/system_resource")
     *              )
     *           )
     *        )
     *     )
     * )
     */

    public function insert(Request $request)
    {
        $param = $request->post();
        if (!empty($param['id'])) {
            return Result::fail(-1, "新增数据不能提交id属情");
        }
        unset($param['id']);
        $data = $this->resourceLogic->save($param);
        return Result::success($data);
    }


    /**
     * 修改数据
     * @param RequestInterface $request
     * @param int $id
     * @return mixed
     *
     * @rule("/auth/resource/{id}", method="put")
     */
    /**
     * @route("/auth/resource/:id", method="put")
     * OA\Put(path="/auth/resource/{id}",summary="修改资源",
     *     description="修改资源",
     *     tags={"auth/resource"},
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
     *             OA\Property(property="title",type="varchar",description="权限名称",example="权限名称"),
     *             OA\Property(property="urls",type="varchar",description="urls路径",example="urls路径"),
     *             OA\Property(property="parent_id",type="int",description="父级id",example="父级id"),
     *             OA\Property(property="status",type="tinyint",description="状态 1：有效 2：无效",example="状态 1：有效 2：无效")
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
     *                  OA\Property(property="data",type="objcet",description="返回数据",ref="#components/schemas/system_resource")
     *              )
     *           )
     *        )
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        $param = $request->post();
        if (empty($id)) {
            return Result::fail(-1, "修改数据必须提交id属性");
        }
        $param['id'] = $id;
        $data        = $this->resourceLogic->save($param);
        return Result::success($data);
    }

    /**
     * 删除数据
     * @param RequestInterface $request
     * @return mixed
     *
     * @RequestMapping(path="/auth/resource", methods="delete")
     */
    /**
     *
     * @route("/auth/resource", method="delete")
     * OA\Delete(path="/auth/resource",summary="删除资源",
     *     description="删除资源",
     *     tags={"auth/resource"},
     *     OA\RequestBody(
     *         required=true,
     *         OA\JsonContent(
     *           OA\Property(property="ids",type="array",description="微信消息推送id",OA\Items(type="integer",example="1"))
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
            return $this->failed("删除数据必须提交id属性", 9999);
        }
        $data = $this->resourceLogic->delete($ids);
        return Result::success($data);
    }

}
