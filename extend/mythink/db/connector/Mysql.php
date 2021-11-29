<?php
declare (strict_types = 1);

namespace mythink\db\connector;

class Mysql extends \think\db\connector\Mysql
{
    use ConnectionTrait;
    use PDOConnectionTrait;
}
