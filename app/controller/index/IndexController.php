<?php

namespace app\controller\index;
use app\logic\auth\AdminLogic;
use Xueluo\Library\Util\Result;
use think\annotation\route\Group;
use think\annotation\Route;
use app\controller\index\Controller;
/**
 * @Group("index")
 */
class IndexController extends Controller
{
    public $admin_logic;
    public function __construct()
    {

        $this->admin_logic = new AdminLogic();
        parent::__construct();
    }

    /**
     * @param  string $name 测试路由
     * @return mixed
     * @Route("index", method="GET")
     */
    public function index()
    {
        $data = $this->admin_logic->getAdminList();
        return Result::success($data);
    }
}


?>