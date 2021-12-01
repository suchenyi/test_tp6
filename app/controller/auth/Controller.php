<?php

namespace app\controller\auth;

use app\controller\Controller as adminController;

/**
 * app\auth层公共控制器，auth层其余全部控制器继承此控制器
 * Class Controller
 * @package app\controller\auth
 */
class Controller extends adminController
{

    public function __construct()
    {
        // TODO: Implement __call() method.
        parent::__construct();
    }

}