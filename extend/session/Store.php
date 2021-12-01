<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace session;

use think\contract\SessionHandlerInterface;
use think\helper\Arr;

class Store extends \think\session\Store
{
    /**
     * session_id设置
     * @access public
     * @param string $id session_id
     * @return void
     */
    public function setId($id = null): void
    {
        $this->id = is_string($id) && ctype_alnum($id) ? $id : md5(microtime(true) . session_create_id());
    }
}
