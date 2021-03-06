<?php

namespace app\controller;

use think\App;
use think\annotation\route\Middleware;

use Xueluo\Library\Util\Result;
use Xueluo\Library\Exception\BusinessException;
use hg\apidoc\annotation\Title;
use hg\apidoc\annotation\Group;
use hg\apidoc\annotation\Desc;
use hg\apidoc\annotation\Author;
use hg\apidoc\annotation\Url;
use hg\apidoc\annotation\Tag;
use hg\apidoc\annotation\Param;
use hg\apidoc\annotation\Returned;

/**
 * app应用层公共控制器，app其余层公用控制器继承此控制器
 * Class Controller
 * @package app\controller
 * @Middleware({CheckAdmin::class})
 */
class Controller extends \Xueluo\Library\Base\Controller
{
    public $_admin_id;
    public $_admin_info; // 登录用户的缓存信息

    public function __construct()
    {
        // TODO: Implement __call() method.
        // 当前登录用户成员id
        $this->_admin_id = session('admin_id');
        // 当前登录用户缓存信息
        $this->_admin_info = json_decode(session("admin"), true);
    }
}