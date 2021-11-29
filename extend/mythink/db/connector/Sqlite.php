<?php
declare (strict_types = 1);

namespace mythink\db\connector;

class Sqlite extends \think\db\connector\Sqlite
{
    use ConnectionTrait;
    use PDOConnectionTrait;
}
