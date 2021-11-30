<?php

namespace app\logic\auth;

use app\constants\ErrorCode;
use app\logic\BaseLogic;
use app\validate\Admin;
use Xueluo\Library\Exception\BusinessException;
use redis\MyRedis;
use think\exception\ValidateException;

/**
 * 密码检测
 *
 * auther suchenyi
 * dateTime 2021-11-30 10:12
 */
class AdminCheckPasswordLogic extends BaseLogic
{
    /**
     * 检测密码是否需要重置
     *
     * auther suchenyi
     * dateTime 2021-11-306 10:19
     * @param string $password
     * @return void
     */
    public static function needResetPassword($password)
    {
        try {
            validate(Admin::class)->scene('checkPassword')->check([
                'password' => $password,
            ]);
        } catch (ValidateException $e) {
            throw new BusinessException(ErrorCode::NEED_RESET_PASSWORD, ErrorCode::$messages[ErrorCode::NEED_RESET_PASSWORD]);
        }
    }
}
