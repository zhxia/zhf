<?php
class ZHF_Cache_Memcache extends Memcache implements ZHF_Cache_ICache{

    public function addServer($host,$port=11211){
    	ZHF::get_instance()->debug("add memcached server[host:{$host} port:{$port}]");
        return parent::addServer($host,$port);
    }

    /**
     *
     * 连接memcache服务器
     * @param string $host
     * @param int $port
     * @param int $timeout
     */
    public function connect($host,$port=11211,$timeout=1){
        return parent::connect($host,$port,$timeout);
    }

    /**
     *
     * 在memcache中缓存数据
     * @param string $key
     * @param mixed $value
     * @param inr $flag
     * @param int $expires
     */
    public function set($key,$value,$flag=0,$expires=0){
        ZHF::get_instance()->debug('set data into memcached:[key:'.(is_array($key)?('(array)'.var_export($key,TRUE)):'(string)'.$key).' data:'.var_export($value,TRUE).']');
        return parent::set($key,$value,$flag,$expires);
    }

    /**
     *
     * 向memcache中添加缓存数据，如果不存在指定的key时才进行数据缓存
     * @param string $key
     * @param mixed $value
     * @param int $flag
     * @param int $expires
     */
    public function add($key,$value,$flag=0,$expires=0){
        parent::add($key,$value,$flag,$expires);
    }

    /**
     *
     * 从memcached中删除指定的key
     * @param unknown_type $key
     * @param unknown_type $expires
     */
    public function delete($key,$expires=0){
    	ZHF::get_instance()->debug("delete from memcached[key:{$key}]");
        return parent::delete($key,$expires);
    }

    /**
     *
     * 从memcache中读取数据
     * @param mixed $key array or string
     * @param mixed $flags int or array
     */
    public function get($key,&$flags=0){
        $data= parent::get($key,$flags);
        ZHF::get_instance()->debug('get data from memcached:[key:'.(is_array($key)?('(array)'.var_export($key,TRUE)):'(string)'.$key).' data:'.var_export($data,TRUE).']');
        return $data;
    }

    /**
     *
     * 给指定的key进行值为value自增操作
     * @param string $key
     * @param int $value
     * @return int
     */
    public function increment($key,$value=1){
        return parent::increment($key,$value);
    }

    /**
     *
     * 给指定的key进行值为value自减操作
     * @param string $key
     * @param int $value
     * @return int
     */
    public function decrement ($key,$value = 1){
        return parent::decrement($key,$value);
    }
}