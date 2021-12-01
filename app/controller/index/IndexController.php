<?php

namespace app\controller\index;

use app\logic\auth\AdminLogic;
use app\controller\index\Controller;
use think\App;
use app\middleware\CheckAdmin;
use think\annotation\route\Middleware;
use Xueluo\Library\Util\Result;
use think\annotation\route\Resource;
use think\annotation\route\Group;
use think\annotation\Route;
use hg\apidoc\annotation as Apidoc;

/**
 * @Apidoc\Title("基础示例")
 * @Group("index")
 */
class IndexController extends Controller
{
    public $adminLogic;

    public function __construct()
    {

        $this->adminLogic = new AdminLogic();
        parent::__construct();
    }

    /**
     * @Apidoc\Title("基础的注释方法")
     * @Apidoc\Desc("最基础的接口注释写法")
     * @Apidoc\Url("/index/index")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("测试 基础")
     * @Apidoc\Header("Authorization", require=true, desc="Token")
     * @Apidoc\Param("username", type="string",require=true, desc="用户名" )
     * @Apidoc\Param("password", type="string",require=true, desc="密码" )
     * @Apidoc\Param("phone", type="string",require=true, desc="手机号" )
     * @Apidoc\Param("sex", type="int",default="1",desc="性别" )
     * @Apidoc\Returned("id", type="int", desc="新增用户的id")
     *
     * @Route("index", method="GET")
     */
    public function index()
    {
//        $rand              = rand(0, 9999);
//        $array["password"] = md5(md5("Su123456") . $rand);
//        $array["encrypt"]  = $rand;
//        dump($array);
        $data = $this->adminLogic->get(1);
        return Result::success($data);
    }
}


?>