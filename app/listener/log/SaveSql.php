<?php
declare (strict_types=1);

namespace app\listener\log;

use mythink\log\driver\Sql;

class SaveSql
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        Sql::realySave();
    }
}
