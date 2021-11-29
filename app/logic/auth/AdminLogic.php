<?php

namespace app\logic\auth;
use app\logic\BaseLogic;
use app\model\admin\AdminModel;
use Xueluo\Library\Exception\BusinessException;

class AdminLogic extends BaseLogic
{

    private $admin_md;
    public function __construct()
    {
        $this->admin_md = new AdminModel();
        parent::__construct();
    }

    public function getAdminList(){
        $list = $this->admin_md->select();
        return $list;
        //throw new BusinessException(-1, $e->getMessage());
    }
}