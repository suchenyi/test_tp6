<?php
declare (strict_types = 1);

namespace mythink\db\connector;

use Exception;
use PDOStatement;
use think\db\exception\PDOException;
use mythink\log\OperatingRecordLog;

/**
 * 扩展 TP 数据库连接基础类
 * 注意！！！不支持 MongoDB
 *
 * @author Chopin Ngo <wushaobin@captainbi.com>
 */
trait PDOConnectionTrait
{
    /** {@inheritdoc} */
    public function getPDOStatement(
        string $sql,
        array $bind = [],
        bool $master = false,
        bool $procedure = false
    ): PDOStatement {
        try {
            return parent::getPDOStatement($sql, $bind, $master, $procedure);
        } catch (PDOException $e) {
            // 当前仅记录数据库执行错误，其他错误不记录
            $data = $e->getData();
            $lastSql = $data['Database Status']['Error SQL'] ?? $sql;
            OperatingRecordLog::insertAll([OperatingRecordLog::getRequestData() + [
                'sql_str' => $e->getMessage() . ". SQL: $lastSql",
                'sql_act' => 'error',
                'sql_table' => '',
                'create_time' => time(),
                'total_time' => -1,
            ]]);

            throw $e;
        }
    }
}
