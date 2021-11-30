<?php

namespace app\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username'              => 'require|max:25|unique:ocrm_admin',
        'password'              => 'require|min:8|checkPassword',
        'mobile'                => 'require|mobile|min:6|unique:ocrm_admin',  //手机号
        'email'                 => 'email',                 //邮箱
        'role_id'               => 'require|number',        //角色id
        'province_id'           => 'require|number|gt:0',   // 所属省份id
        'city_id'               => 'require|number|gt:0',   // 所属城市id
        'structure_id'          => 'require|number',        //部门id
        'pid'                   => 'require|number',        //上级id
        'expire_time'           => 'require',               //到期时间
        'is_service'            => 'require|number',        //是否销售
        'is_service_assistant'  => 'require|number',        //是否客服
        'sex'                   => 'require|number',        //是否客服
    ];

    protected $message = [
        'username.require' => '账号不能为空',
        'username.max' => '账号不能超过25个字符',
        'password.require' => '密码不能为空',
        'password.min' => '密码不能少于6个字符',
        'password.checkPassword' => '密码必须包含有大小写字母和数字',
        'mobile.require' => '手机号不能为空',
        'email.email' => '邮箱不正确',
        'mobile.min' => '手机号不能少于6个字符',
        'role_id.require' => '角色不能为空',
        'role_id.number' => '角色必须为数字',
        'pid.require' => '上级不能为空',
        'pid.number' => '上级必须为数字',
        'sex.require' => '性别不能为空',
        'sex.number' => '性别必须为数字',
    ];

    protected $scene = [
        'checkPassword'  =>  ['password'],
        'resetPassword'  =>  ['password'],
    ];

    /**
     * notes ：编辑时验证场景定义
     * author：suchenyi
     * dateTime 2021-11-30 11:18
     * @return admin
     */
    public function sceneEdit()
    {
        return $this->remove('mobile', ['require', 'mobile', 'unique'])
                ->remove('username', ['require', 'max:25', 'unique']);
    }

    /**
     * 密码验证
     *
     * auther suchenyi
     * dateTime 2021-11-30 11:18
     * @param string $value
     * @param string $rule
     * @return void
     */
    protected function checkPassword($value, $rule)
    {
        $matchResult = preg_match('/(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])/', $value);
        return $matchResult ? true : false;
    }
}
