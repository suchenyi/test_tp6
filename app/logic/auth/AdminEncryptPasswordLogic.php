<?php

namespace app\logic\auth;

use app\logic\BaseLogic;

/**
 * 简单密码加密封装，暂未使用
 *
 * auther suchenyi
 * dateTime 2021-11-30 10:12
 */
class AdminEncryptPasswordLogic extends BaseLogic
{
    /**
     * @var string 密码
     */
    public $password;
    /**
     * @var string 加密后的密码
     */
    public $encryptedPassword;
    /**
     * @var string 密码混淆加密
     */
    public $encrypt;

    /**
     * 实例化
     *
     * auther suchenyi
     * dateTime 2021-11-30 10:12
     * @param string $password
     */
    public function __construct($password)
    {
        parent::__construct();
        $this->password = $password;
    }

    /**
     * 密码加密
     *
     * auther suchenyi
     * dateTime 2021-11-30 10:12
     * @return void
     */
    public function encryptionPassword()
    {
        $this->encryptedPassword = md5(md5($this->password) . $this->createEncryptCode());
    }

    /**
     * 生成密码混淆加密串
     *
     * auther suchenyi
     * dateTime 2021-11-30 10:12
     * @return void
     */
    public function createEncryptCode()
    {
        $this->encrypt = rand(0, 9999);
        return $this->encrypt;
    }
}
