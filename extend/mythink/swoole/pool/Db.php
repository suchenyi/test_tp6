<?php
declare (strict_types = 1);

namespace mythink\swoole\pool;

use mythink\TriggerSqlTrait;
use think\swoole\pool\Db as SDB;

class Db extends SDB
{
    use TriggerSqlTrait;
}
