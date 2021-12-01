<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace session;

use think\helper\Arr;
use think\helper\Str;


/**
 * Session管理类
 * @package think
 * @mixin Store
 */
class Session extends \think\Session
{

    protected function createDriver(string $name)
    {
        $type = $this->resolveType($name);

        $method = 'create' . Str::studly($type) . 'Driver';

        $params = $this->resolveParams($name);

        if (method_exists($this, $method)) {
            return $this->$method(...$params);
        }

        $class = $this->resolveClass($type);

        $handler = $this->app->invokeClass($class, $params);

        //$handler = parent::createDriver($name);

        return new Store($this->getConfig('name') ?: 'PHPSESSID', $handler, $this->getConfig('serialize'));
    }
}
