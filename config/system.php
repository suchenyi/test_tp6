<?php
// +----------------------------------------------------------------------
// | 系统设置
// +----------------------------------------------------------------------
use think\facade\Env;

return [

    'root_path'                 => dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR, // 系统根目录地址
    'session_pro'               => "xueluo_",  //session前缀
    'login_del_device_id_count' => 3,//失败n次，将删除本机设备号
    "login_max_error_count"     => 5,//超过n次禁用账号
];