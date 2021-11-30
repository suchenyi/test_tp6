<?php

namespace app\controller\auth;

use app\controller\Controller as adminController;

/**
 * app\admin层公共控制器，admin层其余全部控制器继承此控制器
 * Class Controller
 * @package app\controller\admin
 */
class Controller extends adminController
{

    public function __construct()
    {
        // TODO: Implement __call() method.
        parent::__construct();
    }

}