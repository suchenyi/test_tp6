<?php

namespace app\controller\auth;

use app\logic\auth\AdminLogic;
use think\annotation\route\Group;
use app\Request;
use Xueluo\Library\Util\Result;
use think\annotation\Route;
use Xueluo\Library\Exception\BusinessException;
use app\controller\auth\Controller;

/**
 * Class AdminController
 * @package app\controller\auth
 *
 */
class AdminController extends Controller
{
    private $adminLogic;
    public function __construct()
    {
        $this->adminLogic = new AdminLogic();
        parent::__construct();
    }

    /**
     * @param  string $name 登录
     * @return mixed
     * @Route ("/auth/admin/login",method="POST")
     */
    public function login(Request $request)
    {
        $user_name = $request->post("user_name", '', 'trim');
        $password = $request->post("password", '', 'trim');
        $this->adminLogic->login($user_name, $password);
        $result = $this->adminLogic->getAdminInfo();

        return Result::success($result);
    }
}