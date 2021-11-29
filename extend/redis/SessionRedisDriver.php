<?php
declare (strict_types=1);

namespace redis;


/**
 * Redis缓存驱动，适合单机部署、有前端代理实现高可用的场景，性能最好
 * 有需要在业务层实现读写分离、或者使用RedisCluster的需求，请使用Redisd驱动
 *
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 * @author    尘缘 <130775@qq.com>
 */
class SessionRedisDriver extends \think\cache\driver\Redis
{
    public function get($name, $default = null)
    {
        $this->readTimes++;
        $key   = $this->getCacheKey($name);
        $value = $this->handler->get($key);

        if (false === $value || is_null($value)) {
            return $default;
        }

        return $value;
    }

    public function set($name, $value, $expire = null): bool
    {
        $this->writeTimes++;

        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }

        $key    = $this->getCacheKey($name);
        $expire = $this->getExpireTime($expire);
        //$value  = $this->serialize($value);

        if ($expire) {
            $this->handler->setex($key, $expire, $value);
        } else {
            $this->handler->set($key, $value);
        }

        return true;
    }
}
