<?php
declare (strict_types = 1);

namespace mythink\db\connector;

use Exception;
use think\db\BaseQuery;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Command;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\ReadPreference;

class Mongo extends \think\db\connector\Mongo
{
    use ConnectionTrait;

    /** {@inheritdoc} */
    public function getCursor(BaseQuery $query, $mongoQuery, bool $master = false): Cursor
    {
        try {
            return parent::getCursor($query, $mongoQuery, $master);
        } catch (Exception $e) {
            // todo 记录 MongoDB 错误
            throw $e;
        }
    }


    /** {@inheritdoc} */
    protected function mongoExecute(BaseQuery $query, BulkWrite $bulk)
    {
        try {
            return parent::mongoExecute($query, $bulk);
        } catch (Exception $e) {
            // todo 记录 MongoDB 错误
            throw $e;
        }
    }

    /** {@inheritdoc} */
    public function command(
        Command $command,
        string $dbName = '',
        ReadPreference $readPreference = null,
        $typeMap = null,
        bool $master = false
    ): array {
        try {
            return parent::command($command, $dbName, $readPreference, $typeMap, $master);
        } catch (Exception $e) {
            // todo 记录 MongoDB 错误
            throw $e;
        }
    }
}
