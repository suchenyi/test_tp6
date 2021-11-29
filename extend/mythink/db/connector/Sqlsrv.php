<?php
declare (strict_types = 1);

namespace mythink\db\connector;

class Sqlsrv extends \think\db\connector\Sqlsrv
{
    use ConnectionTrait;
    use PDOConnectionTrait;
}
