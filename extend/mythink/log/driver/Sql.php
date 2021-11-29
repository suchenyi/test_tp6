<?php
declare (strict_types = 1);

namespace mythink\log\driver;

use think\App;
use think\facade\Log;
use think\contract\LogHandlerInterface;

use mythink\log\OperatingRecordLog;

/**
 * 记录 SQL 操作的日志类
 *
 * @author Chopin Ngo <wushaobin@captainbi.com>
 */
class Sql implements LogHandlerInterface
{
    // @var array
    protected $config = [
        // 保存的查询类型，insert 包含 replace
        'query_type' => ['insert', 'update', 'delete'],
    ];

    protected $dbData = [];

    protected static $sqls = [];

    protected static $sqlLength = 0;

    protected static $maxAllowedPacket = null;

    // 实例化并传入参数
    public function __construct(App $app, $config = [])
    {
        if (is_array($config)) {
            $this->config = $config + $this->config;
        }

        $this->dbData = OperatingRecordLog::getRequestData();
    }

    /** {@inheritdoc} */
    public function save(array $log): bool
    {
        $logs = $log['sql'] ?? [];
        foreach ($logs as $log) {
            $table = '';
            $sql = trim($log);
            $isMaster = true;
            $runTime = -1;
            $startTime = time();
            $index = strrpos($log, '[');

            if (false !== $index) {
                $sql = trim(substr($log, 0, $index));
                // todo 增加日志格式用于解析，当前定死格式为 [master|slave]|startTime:%d.%d|RunTime:%0.6fs
                // e.g. master|StartTime:1611222009.8937|RunTime:0.000128s
                $ext = trim(trim(substr($log, $index), '[]'));
                if (1 === preg_match('/((master|slave)\|)?(StartTime:(\d+\.\d+)s\|)?RunTime:(\d+\.\d{1,6})s/', $ext, $match)) {
                    $isMaster = $match[2] !== 'slave';
                    $runTime = floatval($match[5] ?: -1);
                    $startTime = intval($match[4] ?: $startTime);
                }
            }

            // 如果 sql 包含注释，替换掉
            if (false !== strpos($sql, '/*')) {
                $sql = trim(preg_replace('/\/\*([\s\S]*?)\*\//', '', $sql));
            }

            if (0 === stripos($sql, 'CONNECT:')) {
                continue;
            }

            $type = 'select';
            if (0 === stripos($sql, 'update ')) {
                $type = 'update';
                if (1 === preg_match('/^update\s+(low_priority\s+)?(ignore\s+)?`?([^\s\(`]+)`?\s+/i', $sql, $match)) {
                    // 暂时只兼容 mysql
                    $table = trim($match[3], ',');
                }
            } elseif (0 === stripos($sql, 'delete ')) {
                $type = 'delete';
            } elseif (0 === stripos($sql, 'insert ') || 0 === stripos($sql, 'replace ')) {
                $type = 'insert';

                if (1 === preg_match('/\s+into\s+`?([^\s`]+)`?\s+/i', $sql, $match)) {
                    $table = $match[1];
                }
            }

            if ('select' === $type || 'delete' === $type) {
                if (1 === preg_match('/\s+from\s+`?([^\s\(`]+)`?\s+/i', $sql, $match)) {
                    $table = trim($match[1], ',');
                }
            }

            if (!in_array($type, $this->config['query_type'])) {
                continue;
            }

            self::$sqls[] = $this->dbData + [
                'sql_str' => $sql,
                'sql_act' => $type,
                'sql_table' => $table,
                'create_time' => $startTime,
                'total_time' => $runTime,
            ];

            self::$sqlLength += mb_strlen($sql);

            // 插入数据数超过 500 行，或者占用的内存超过阈值，马上执行写入操作
            if (sizeof(self::$sqls) >= 500 || self::$sqlLength >= static::getMaxAllowedPacket()) {
                static::realySave();
            }
        }

        return true;
    }

    protected static function getMaxAllowedPacket(): int
    {
        if (null === self::$maxAllowedPacket) {
            $result = OperatingRecordLog::query('select @@max_allowed_packet');
            if ($result) {
                self::$maxAllowedPacket = intval(current($result[0]) * 0.9);
            } else {
                // 获取不到的情况下默认使用 0.9MB
                self::$maxAllowedPacket = intval(0.9 * 1024 * 1024);
            }
        }

        return self::$maxAllowedPacket;
    }

    public static function realySave(): bool
    {
        $sqls = self::$sqls;
        self::$sqls = [];
        self::$sqlLength = 0;
        if ($sqls && sizeof($sqls) !== OperatingRecordLog::insertAll($sqls)) {
            Log::sql_log('插入到SQL日志表失败: ' . var_export($sqls, true));
            return false;
        }

        return true;
    }
}
