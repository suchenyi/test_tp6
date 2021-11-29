<?php
declare (strict_types=1);

namespace app\logic;

use Xueluo\Library\Exception\BusinessException;
use Xueluo\Library\Base\Model;

/**
 * 每个logic，都继承这个BaseLogic
 * Class BaseLogic
 * @package app\logic
 * @Middleware({CheckAdmin::class})
 */
class BaseLogic
{
    public function __construct()
    {

    }
}