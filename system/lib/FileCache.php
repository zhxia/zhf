<?php
/**
 * 文件缓存类
 *
 * @author zhxia
 */
class FileCache {
    //put your code here
    private $_cache_dir,$_cache_index;

    function  __construct($cache_dir) {
        $this->_cache_dir=$cache_dir; //设置缓存文件存放目录
        $this->_cache_index=$this->_cache_dir.'/cache.index'; //设置缓存索引文件

    }

    /**
     *set拦截器
     * @param <type> $name
     * @param <type> $value
     */
    public function  __set($name, $value) {
        $method="set{$name}";
        if(method_exists($this,$method)){
            $value=htmlspecialchars($value,ENT_QUOTES,'utf-8');
            $this->$method($value);
        }
    }

    /**
     *get拦截器
     * @param <type> $name
     * @return <type>
     */
    public function  __get($name) {
        $method="get{$name}";
        if(method_exists($this,$method)){
            return $this->$method();
        }
    }

   function setCacheDir($path){
       $this->_cache_dir=$path;
   }

   function getCacheDir(){
       return $this->_cache_dir;
   }

   function setCacheIndex($index_file){
       $this->_cache_index=$index_file;
   }

   /**
    *缓存数据
    * @param <type> $key
    * @param <type> $value
    * @param <type> $time
    */
   public function set($key,$value,$time){
        $index_content=file_get_contents($this->_cache_index);
        if(strlen($index_content)>0){
            $index_content=unserialize($index_content);
        }
        else{
            $index_content=array();
        }
        $scalar=is_scalar($value);
        $index_key=md5($key);
        $index_content[$index_key]=array('scalar'=>$scalar,'time'=>$time,'update_time'=>time());
        $cache_content=$scalar?$value:serialize($value);

        //保存缓存文件
        file_put_contents($this->_cache_dir.'/'.$index_key.'.cache',$cache_content);
        //保存缓存索引
        file_put_contents($this->_cache_index, serialize($index_content));
   }

   /**
    *读取缓存数据
    * @param <type> $key
    */
   public function get($key){
        $cache_content='';
        if(!file_exists($this->_cache_index)){
            touch($this->_cache_index);
        }
        $index_content=file_get_contents($this->_cache_index);
        if(strlen($index_content)>0){
            $index_content=unserialize($index_content);
        }
        else{
            $index_content=array();
        }
        $index_key=md5($key);
        $cache_file=$this->_cache_dir.'/'.md5($key).'.cache';
        if(!isset($index_content[$index_key])||!file_exists($cache_file)||filesize($cache_file)==0){
            return FALSE;
        }
        //计算缓存是否过期
        $cache_time=(double)$index_content[$index_key]['time']+(double)$index_content[$index_key]['update_time'];
        if(time()>$cache_time){
            return FALSE;
        }
        else{
            //读取缓存数据
            $cache_content=file_get_contents($cache_file);
            if(!$index_content[$index_key]['scalar']){
                $cache_content=unserialize($cache_content);
            }
            return $cache_content;
        }
   }

}
?>
