<?php
declare (strict_types = 1);

namespace mythink\db\connector;

class Pgsql extends \think\db\connector\Pgsql
{
    use ConnectionTrait;
    use PDOConnectionTrait;
}
