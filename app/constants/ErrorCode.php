<?php

declare (strict_types=1);

namespace app\constants;

/**
 * 错误码
 */
class ErrorCode
{
    const BUSINESS_ERROR = -1;
    const NEED_RESET_PASSWORD = -400403;
    const FIRST_LOGIN_RESET_PASSWORD = -400410;
    const LOGIN_DEVICE_ID_ERROR = -400412;
    const LOGIN_AUTH_CODE_ERROR = -400413;

    public static $messages = [
        self::BUSINESS_ERROR => '系统错误',
        self::NEED_RESET_PASSWORD => '密码过于简单，请修改密码',
        self::FIRST_LOGIN_RESET_PASSWORD => '首次登录，请修改密码',
        self::LOGIN_DEVICE_ID_ERROR => '设备号错误',
        self::LOGIN_AUTH_CODE_ERROR => '授权码错误',
    ];
}
