<?php

namespace mythink\Log;

use PDO;
use PDOStatement;
use PDOException;
use Throwable;
use RuntimeException;
use think\facade\Log;
use think\facade\Config;

/**
 * SQL 操作记录模块
 * ThinkPHP 原生支持记录 SQL 记录，但如果要将记录存储到数据库中
 * 如果使用 ThinkPHP 原生的数据库类来插入，则会出现循环插入日志导致爆内存
 * 还会导致 getLastSql() 和 getLastInsID() 的结果出错（某些情况下还会导致 count() 方法出错，暂未定位到问题点）
 *
 * 要避免这种情况，最简单的就是日志插入到数据库的操作不要用 ThinkPHP 提供的数据库类
 */
class OperatingRecordLog
{
    protected static $table = 'x_op_record_log';

    protected static $config = [];

    protected static $connection = null;

    private function __construct()
    {
    }

    /**
     * 获取数据库连接
     *
     * @param bool $reconnect 是否重连
     * @return \PDO
     * @throws \PDOException 连接数据库错误时抛出此异常
     */
    protected static function getConnection(bool $reconnect = false): PDO
    {
        if ($reconnect || null === static::$connection) {
            $config = static::getConfig();
            static::$connection = new PDO(sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['hostname'],
                $config['hostport'],
                $config['database'],
                $config['charset']
            ), $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }

        return static::$connection;
    }

    /**
     * 获取数据库连接配置
     *
     * @param string $name 配置名，见 \think\facade\Config 说明
     * @return array
     * @throws \RuntimeException 配置错误的时候抛出此异常
     */
    protected static function getConfig(string $name = 'database.connections.erp_log'): array
    {
        if (empty(static::$config)) {
            $config = Config::get($name);
            if (null === $config) {
                Log::error("{$name} 配置不存在");
                throw new RuntimeException('Missing log database config');
            }

            if (!is_array($config)
                || !isset($config['hostname'], $config['username'], $config['password'], $config['database'], $config['type'])
            ) {
                Log::error("{$name} 缺少必要的数据库连接参数");
                throw new RuntimeException('Invalid log database config');
            }

            if (false === stripos($config['type'], 'mysql')) {
                Log::error("{$name} 不是 mysql 数据库，当前仅支持 mysql");
                throw new RuntimeException('Invalid log database type');
            }

            $config['charset'] = $config['charset'] ?? 'utf8';
            $config['hostport'] = $config['hostport'] ?? 3306;

            $parse = function(string $key, bool $onlyOne = true) use (&$config, $name) {
                if (empty($config[$key])) {
                    Log::error("{$name}.{$key} 不能为空");
                    throw new RuntimeException("Log database {$key} is required");
                }

                $multip = false;
                if (is_array($config[$key])) {
                    if ($onlyOne) {
                        Log::error("hsotname 为单个的情况下 {$name}.{$key} 不应配置多个");
                        throw new RuntimeException("Invalid log database1 {$key}");
                    }

                    $multip = true;
                    $config[$key] = $config[$key][0];
                }

                if (!is_string($config[$key])) {
                    if ('hostport' === $key && is_int($config[$key])) {
                        $config[$key] = (string)$config[$key];
                    } else {
                        Log::error("{$name}.{$key} 参数类型错误");
                        throw new RuntimeException("Invalid log database2 {$key}");
                    }
                }

                $config[$key] = trim($config[$key], ', ');
                if (empty($config[$key])) {
                    Log::error("{$name}.{$key} 参数不能为空");
                    throw new RuntimeException("Log database {$key} is required");
                }

                if (false !== strpos($config[$key], ',')) {
                    if ($onlyOne) {
                        Log::error("hostname 为单个的情况下 {$name}.{$key} 不应配置多个");
                        throw new RuntimeException("Invalid log database3 {$key}");
                    }

                    $multip = true;
                    $config[$key] = explode(',', $config[$key])[0];
                }

                return $multip;
            };

            // 只有在 hostname 有多个的情况下，username 和 password 才有可能多个
            $multip = $parse('hostname', false);
            $parse('username', !$multip);
            $parse('password', !$multip);
            $parse('hostport', !$multip);
            $parse('database', !$multip);
            $parse('charset', !$multip);

            static::$config = $config;
        }

        return static::$config;
    }

    /**
     * 设置记录表名
     *
     * @param string $name
     * @return void
     */
    public static function setTable(string $name): void
    {
        static::$table = $name;
    }

    /**
     * 批量插入数据库
     *
     * @param array $records record 里的数据结构必须保持一致
     * @param int $limit 每批次插入的量，避免量太大导致插入失败，允许的范围在 100-1000
     * @return int 插入的数据行数，理论上应等于 $records 的长度
     */
    public static function insertAll(array $records, int $limit = 500): int
    {
        if (empty($records)) {
            return 0;
        }

        if (!isset($records[0])) {
            $records = [$records];
        }

        if (empty($records[0])) {
            return 0;
        }

        $nums = 0;
        $fields = array_keys($records[0]);
        $fieldsLen = count($fields);
        $limit = min(max($limit, 100), 1000);
        $schema = sprintf('INSERT INTO `%s`(`%s`) VALUES ', static::$table, join('`,`', $fields));

        foreach (array_chunk($records, $limit) as $chunk) {
            $sql = $schema . substr(str_repeat(',(' . substr(str_repeat(',?', $fieldsLen), 1) . ')', count($chunk)), 1);

            $binds = [];
            foreach ($chunk as $row) {
                foreach ($fields as $key) {
                    $binds[] = $row[$key] ?? null;
                }
            }

            if ($binds) {
                $sth = static::execute($sql, $binds);
                $nums += ($sth ? $sth->rowCount() : 0);
            }
        }

        return $nums;
    }

    /**
     * 执行数据库查询
     *
     * @param string $sql 准备执行的 sql 语句
     * @param array $binds 要绑定的 sql 占位符参数
     * @param bool $reconnect 是否重连数据库，主要用于数据库链接超时丢失
     * @return \PDOStatement|false
     */
    protected static function execute(string $sql, array $binds, bool $reconnect = false)
    {
        try {
            if (empty($binds)) {
                return static::getConnection($reconnect)->query($sql);
            }

            $sth = static::getConnection($reconnect)->prepare($sql);
            $sth->execute($binds);
            return $sth;
        } catch (Throwable $t) {
            // 只重连一次，还是连接失败，那数据库大概率是真的挂了
            if (!$reconnect && $t instanceof PDOException && 2006 === $t->errorInfo[1]) {
                return static::execute($sql, $binds, true);
            }

            Log::error(\trace_exception($t));
        }

        return false;
    }

    /**
     * 执行 sql 查询
     *
     * @param string $sql 准备执行的 sql 语句
     * @param mixed ...$binds 要绑定的 sql 占位符参数
     * @return array see PDOStatement->fetchAll(PDO::FETCH_ASSOC)
     */
    public static function query(string $sql, ...$binds): array
    {
        $sth = static::execute($sql, $binds);
        if (!$sth) {
            return [];
        }

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRequestData(): array
    {
        list($m, $c, $a) = \getMCA();
        $addUser = \session('admin') ?? '';
        if ($addUser) {
            if (null !== ($addUser = json_decode($addUser, true))) {
                $addUser = $addUser['username'] ?? '';
            }
        }

        return [
            'm' => $m,
            'c' => $c,
            'a' => $a,
            'user_id' => \session('admin_id') ?? 0,
            'user_name' => $addUser,
            'ip' => \getRealIP(),
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];
    }
}
