<?php

namespace mythink;

/**
 * SQL 监控中添加查询开始时间
 *
 * @author Chopin Ngo <wushaobin@captainbi.com>
 */
trait TriggerSqlTrait
{
    /**
     * 监听SQL
     * @access protected
     * @return void
     */
    public function triggerSql(): void
    {
        // 监听SQL
        $this->listen(function ($sql, $time, $master, $queryStartTime = null) {
            if (0 === strpos($sql, 'CONNECT:')) {
                // $this->log($sql);
                return;
            }

            // 记录SQL
            if (is_bool($master)) {
                // 分布式记录当前操作的主从
                $master = $master ? 'master|' : 'slave|';
            } else {
                $master = '';
            }

            if (null === $queryStartTime) {
                $this->log("{$sql} [ {$master}RunTime:{$time}s ]");
            } else {
                $this->log("{$sql} [ {$master}StartTime:{$queryStartTime}|RunTime:{$time}s ]");
            }
        });
    }
}
