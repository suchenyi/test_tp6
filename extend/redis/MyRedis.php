<?php

namespace redis;

class MyRedis
{

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    protected $redis_config;
    private   $persistent;

    public function __construct($code = "001", $options = array())
    {
        if (!extension_loaded('redis')) {
            throw new Exception(L('_NOT_SUPPORT_') . ':redis');
        }
        $config                  = config("redis_config");
        $this->redis_config      = $config[$code];
        $options                 = array_merge(array(
            'host'       => $this->redis_config['host'],
            'port'       => $this->redis_config['port'],
            'timeout'    => $this->redis_config['timeout'],
            'persistent' => $this->redis_config['persistent'],
            'prefix'     => $this->redis_config['prefix'],
        ), $options);
        $this->options           = $options;
        $this->options['expire'] = isset($options['expire']) ? $options['expire'] : 0;
        $this->options['prefix'] = isset($options['prefix']) ? $options['prefix'] : 0;
        $this->options['length'] = isset($options['length']) ? $options['length'] : 0;
        $this->persistent        = $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler           = new \Redis();
        $options['timeout'] === false ? $this->handler->$func($options['host'], $options['port']) : $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    //连接到从redis
    public function connectToSlave()
    {
        $host = isset($this->redis_config["s_host"]) ? $this->redis_config["s_host"] : FALSE;
        $port = isset($this->redis_config["s_port"]) ? $this->redis_config["s_port"] : FALSE;
        if ($host && $port) {
            $func = $this->persistent;
            $this->redis_config['timeout'] === false ? $this->handler->$func($host, $port) : $this->handler->$func($host, $port, $this->redis_config['timeout']);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        $value = $this->handler->get($this->options['prefix'] . $name);
        return unserialize($value);
    }

    /**
     * 集合添加
     **/
    public function saddEx($name, $value)
    {
        return $this->handler->SADD($name, $value);
    }

    /**
     * 集合大小
     **/
    public function scardEx($name)
    {
        return $this->handler->SCARD($name);
    }

    /**
     * 返回集合所有元素
     **/
    public function smembersEx($name)
    {
        return $this->handler->SMEMBERS($name);
    }

    /**
     * 返回集合中的n个，并删除
     **/
    public function srandmemberEx($name, $size = 100)
    {
        return $this->handler->SRANDMEMBER($name, $size);
    }

    public function spopEx($name)
    {
        return $this->handler->SPOP($name);
    }

    /**
     *
     * @param type $name
     * @return type
     */
    public function getEx($name)
    {
        $value = $this->handler->get($name);
        return $value;
    }

    public function setNxEx($name, $value, $expire = 20)
    {
        $ret = $this->handler->SETNX($name, $value);
        if ($ret && $expire !== 0) {
            $this->handler->EXPIRE($name, $expire);
        }
        return $ret;
    }

    public function expireEx($name, $expire = 20)
    {
        if ($expire !== 0) {
            $this->handler->EXPIRE($name, $expire);
        }
    }

    public function ttlEx($name)
    {
        return $this->handler->TTL($name);
    }

    public function setEx($name, $value, $expire = 86400)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire == 0) {
            $ret = $this->handler->set($name, $value);
        } else {
            $ret = $this->handler->setex($name, $expire, $value);
        }
        return $ret;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = 86400)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $name  = $this->options['prefix'] . $name;
        $value = serialize($value);
        if ($expire == 0) {
            $ret = $this->handler->set($name, $value);
        } else {
            $ret = $this->handler->setex($name, $expire, $value);
        }
        return $ret;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return $this->handler->del($this->options['prefix'] . $name);
    }

    public function rmEx($name)
    {
        return $this->handler->del($name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear()
    {
        return $this->handler->flushDB();
    }

    /**
     * 入队
     * @param type $arr
     * @param type $key
     */
    public function pushArray($key, $arr)
    {
        if (is_array($arr)) {
            $key = $this->options['prefix'] . $key;
            array_map('serialize', $arr);
            foreach (array_chunk($arr, 50) as $subarr) {
                $this->handler->LPUSH($key, ...$subarr);
                // PHP 5.6 以下版本可以使用下面方式
                // array_unshift($subarr, $key);
                // $count += call_user_func_array([$this->handler, 'LPUSH'], $subarr);
            }

            return sizeof($arr);
        }

        return 0;
    }

    public function pushArrayEx($key, $arr)
    {
        if (is_array($arr)) {
            foreach (array_chunk($arr, 50) as $subarr) {
                $this->handler->LPUSH($key, ...$subarr);
                // PHP 5.6 以下版本可以使用下面方式
                // array_unshift($subarr, $key);
                // $count += call_user_func_array([$this->handler, 'LPUSH'], $subarr);
            }

            return sizeof($arr);
        }

        return 0;
    }

    /**
     * 入队
     */
    public function push($key, $name)
    {
        $key = $this->options['prefix'] . $key;
        // $tmpLen = $this->handler->LLEN($key);
        $res = $this->handler->LPUSH($key, serialize($name));
        // $tmpLen2 = $this->handler->LLEN($key);
        //if ($tmpLen == $tmpLen2) {
        //    $res = $this->handler->LPUSH($key, serialize($name));
        //}
        return $res;
    }

    public function pushEx($key, $name)
    {
        $res = $this->handler->LPUSH($key, $name);
        return $res;
    }

    public function pushSEx($key, $name)
    {
        $res = $this->handler->LPUSH($key, serialize($name));
        return $res;
    }

    public function popSEx($name)
    {
        $data = unserialize($this->handler->LPOP($name));
        return $data;
    }

    /**
     * 入队
     * @param type $key
     * @param type $name
     * @return type
     */
    public function rpush($key, $name)
    {
        $key = $this->options['prefix'] . $key;
        // $tmpLen = $this->handler->LLEN($key);
        $res = $this->handler->RPUSH($key, serialize($name));
        // $tmpLen2 = $this->handler->LLEN($key);
        //if ($tmpLen == $tmpLen2) {
        //    $res = $this->handler->RPUSH($key, serialize($name));
        //}
        return $res;
    }

    public function rpop($name)
    {
        $name = $this->options['prefix'] . $name;
        $data = unserialize($this->handler->RPOP($name));
        if (!$data) {
            $this->connectToSlave();
            $data = unserialize($this->handler->RPOP($name));
        }
        return $data;
    }

    /**
     * 出
     */
    public function pop($name)
    {
        $name = $this->options['prefix'] . $name;
        $data = unserialize($this->handler->LPOP($name));
        if (!$data) {
            $this->connectToSlave();
            $data = unserialize($this->handler->LPOP($name));
        }
        return $data;
    }

    public function keysEx($name)
    {
        $data = $this->handler->KEYS($name);
        return $data;
    }

    public function popEx($name)
    {
        $data = $this->handler->LPOP($name);
        return $data;
    }

    /**
     *
     * @param type $key
     * @return type
     */
    public function size($key)
    {
        $key = $this->options['prefix'] . $key;
        return $this->handler->lSize($key);
    }

    /**
     *
     * @param type $key
     * @return type
     */
    public function LLEN($key)
    {
        $key = $this->options['prefix'] . $key;
        return $this->handler->LLEN($key);
    }

    public function LRANGE($key, $start = 0, $end = -1)
    {
        $key   = $this->options['prefix'] . $key;
        $list  = $this->handler->lRange($key, $start, $end);
        $lists = array();
        if (!empty($list)) {
            foreach ($list as $da) {
                $lists[] = unserialize($da);
            }
        }
        return $lists;
    }

    /**
     *
     * @param type $key
     * @return type
     */
    public function LLENEx($key)
    {
        return $this->handler->LLEN($key);
    }

    public function typeEx($key)
    {
        return $this->handler->TYPE($key);
    }

    public function INCR($name)
    {
        $name = $this->options['prefix'] . $name;
        $ret  = $this->handler->INCR($name);
        return $ret;
    }

    public function DECR($name)
    {
        $name = $this->options['prefix'] . $name;
        $ret  = $this->handler->DECR($name);
        return $ret;
    }

    public function stringGet($name)
    {
        $name  = $this->options['prefix'] . $name;
        $value = $this->handler->get($this->options['prefix'] . $name);
        return $value;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public function stringSet($name, $value, $expire = 86400)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $name = $this->options['prefix'] . $name;
        if ($expire == 0) {
            $ret = $this->handler->set($name, $value);
        } else {
            $ret = $this->handler->setex($name, $expire, $value);
        }
        return $ret;
    }

    /**
     * 批量入队
     * 此方法建议配合 arrayPop 使用
     *
     * @param string $key redis key
     * @param array $vals 要插入的 value，如果数组元素是非标量数据类型，记得先序列化
     * @param bool $fifo 队列使用先进先出（true，默认），还是先进后出（false）
     * @return int 返回入队数量
     */
    public function arrayPush(string $key, array $vals, bool $fifo = true): int
    {
        $method = $fifo ? 'rpush' : 'lpush';
        $key    = $this->options['prefix'] . $key;

        foreach (array_chunk($vals, 50) as $sub) {
            $this->handler->$method($key, ...$sub);

            // PHP 5.6 以下版本不支持 ... 操作的，可使用下面的方法
            // array_unshift($sub, $key);
            // $count += call_user_func_array([$this->handler, $method], $sub);
        }

        return sizeof($vals);
    }

    /**
     * 批量出队
     * 此方法建议配合 arrayPush 使用
     *
     * @param string $key redis key
     * @param int $count 出队数量
     * @param array 没有数据时返回空数组
     *              注意！！！此方法返回的是 string[]，如果入队时序列话过，返回后需要自行反序列化
     */
    public function arrayPop(string $key, int $count = 1): array
    {
        if ($count < 1) {
            return [];
        }

        $key = $this->options['prefix'] . $key;
        if (1 === $count) {
            $val = $this->handler->lpop($key);
            return false === $val ? [] : [$val];
        }

        // tips redis 6.2 对 list pop 命令增加了 COUNT 参数，如果 服务器为 6.2 以上（含）版本，可考虑改为 $redis->lpop($key, $count);
        // p.s. 当前 phpredis 还为支持该参数 - by 2021-01-22
        $result = $this->handler->multi(\Redis::PIPELINE)->lrange($key, 0, $count)->ltrim($key, $count, -1)->exec();
        return $result[0];
    }
}
