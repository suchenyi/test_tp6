<?php
declare (strict_types = 1);

namespace mythink\db\connector;

/**
 * 扩展 TP 数据库链接库类
 *
 * @author Chopin Ngo <wushaobin@captainbi.com>
 */
trait ConnectionTrait
{
    /**
     * 数据库SQL监控
     * SQL 监控中添加查询开始时间
     *
     * @access protected
     * @param string $sql     执行的SQL语句 留空自动获取
     * @param bool   $master  主从标记
     * @return void
     */
    protected function trigger(string $sql = '', bool $master = false): void
    {
        $listen = $this->db->getListen();

        if (!empty($listen)) {
            $sql     = $sql ?: $this->getLastsql();
            if ($this->queryStartTime) {
                $runtime = number_format((microtime(true) - $this->queryStartTime), 6);
            } else {
                $runtime = '0.000000';
            }

            if (empty($this->config['deploy'])) {
                $master = null;
            }

            foreach ($listen as $callback) {
                if (is_callable($callback)) {
                    $callback($sql, $runtime, $master, $this->queryStartTime);
                }
            }
        }
    }

    /**
     * 获取当前连接器类对应的Builder类
     * 修复未设置 builder 配置的时候获取不到 builder class
     *
     * @access public
     * @return string
     */
    public function getBuilderClass(): string
    {
        $type = $this->getConfig('type');
        if ($type[0] === '\\') {
            $type = substr(strrchr($type, '\\'), 1);
        }

        return $this->getConfig('builder') ?: '\\think\\db\\builder\\' . ucfirst($type);
    }
}
