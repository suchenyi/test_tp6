<?php


namespace app\model;

class Model extends \Xueluo\Library\Base\Model
{
    protected $connection     = '';
    protected $databaseSchema = '';
    protected $updateTime     = 'modified_time';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->table = $this->getTable($this->name);
    }

    public function getTable(string $name = '')
    {

        if (empty($name) && isset($this->options['table'])) {
            return $this->options['table'];
        }

        if ($this->connection) {
            $database_config = config('database')['connections'][$this->connection];
        } else {
            $database_config = config('database')['connections'][config('database')['default']];
        }
        $prefix = $database_config['prefix'];

        $name = $name ?: $this->name;
        if (!empty($this->databaseSchema)) {
            //return $this->databaseSchema . '.' . $prefix . Str::snake($name);   //驼峰转下划线写法废弃，因部分表名带有驼峰写法
            return $this->databaseSchema . '.' . $prefix . $name;
        } else {
            //return $prefix . Str::snake($name); //驼峰转下划线写法废弃，因部分表名带有驼峰写法
            return $prefix . $name;
        }
    }

    static public function getTables($alias)
    {
        $model = new static();
        if ($alias) {
            return $model->getTable($model->name) . ' ' . $alias;
        } else {
            return $model->getTable($model->name);
        }
    }
}