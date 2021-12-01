<?php
declare (strict_types=1);

namespace app\middleware;

use app\model\admin\AdminModel;

//use app\logic\auth\UserLogic;
use think\facade\Session;
use Xueluo\Library\Util\Result;
use Xueluo\Library\Exception\BusinessException;

/**
 * Class CheckAdmin
 * @package app\middleware
 */
class CheckAdmin
{
    public function handle($request, \Closure $next)
    {
        // 白名单
        $whitePathInfo = [
            "auth/admin/login",
            "auth/admin/reset_password",
            "auth/admin/device_id",
            "apidoc/#/home",
            "apidoc/config",
            "apidoc/apiData",
            "apidoc/mdMenus"
        ];
        if (in_array($request->pathinfo(), $whitePathInfo)) {
            return $next($request);
        }

        // 定时任务脚本
//        if (strpos($request->pathinfo(), 'crontask/') !== false) {
//            return $next($request);
//        }

        // 登录信息
        $admin_json = session('admin');
        $admin_id   = session('admin_id');
        if (empty($admin_json) || empty($admin_id)) {
            throw new BusinessException(-1, "nologin", [
                'message' => '用户未登录',
                'admin_id' => $admin_id ?: '',
                'admin_json' => $admin_json ?: ''
            ]);
        }
        $admin      = json_decode($admin_json, true);
        $adminModel = AdminModel::where('id', $admin_id)->find();
        if (!$adminModel) {
            throw new BusinessException(-1, "nologin", ['message' => '用户不存在']);
        }

        // is_super外，需要验证是否有权限
        if ($admin['is_super'] != 1) {
            $rule   = $request->rule()->getRule();
            $method = $request->rule()->getMethod();

            $route_url = $method . ':/' . $rule;
            if (substr($route_url, -1) == '/') {
                $route_url2 = substr($route_url, 0, strlen($route_url) - 1);
            } else {
                $route_url2 = $route_url . '/';
            }
            if (!in_array($route_url, $admin["resource"]) && !in_array($route_url2, $admin["resource"])) {
                throw new BusinessException(-1, "对不起,您没有该权限,请联系管理员处理", array($route_url));
            }
        }

        return $next($request);
    }
}
