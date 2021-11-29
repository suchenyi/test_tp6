<?php
declare (strict_types = 1);

namespace mythink;

use think\Db as TDB;

class Db extends TDB
{
    use TriggerSqlTrait;
}
