<?php
class ZHF_Cache_Tag{
    const TAGS_KEY_EXPIRES=300; //所有tags(所有的表名)的过期时间：300s
    const TAGS_KEY='tags_key'; //缓存所有tags的key
    const DEFAUT_TAG_KEY='default_tag_key'; //单个tag的默认key
    private static $instance=NULL;
    private $tags=NULL;
    private $dao=NULL;
    private function __construct(){
    }

    /**
     *
     * Enter description here ...
     * @return ZHF_Cache_Tag
     */
    public static function &get_instance(){
        if(NULL===self::$instance){
            self::$instance=new self();
        }
        return self::$instance;
    }

    public function update_tag($tag_name){
        return $this->get_dao()->update_tag($tag_name);
    }

    public function update_tags(){
        $cache=ZHF_Cache_Factory::get_instance()->get_cache();
        $cache->delete(self::TAGS_KEY);
        $this->tags=NULL;
    }

    public function get($tag_name){
        if(NULL===$this->tags){
            $this->tags=$this->get_tags();
        }
        return isset($this->tags[$tag_name])?$this->tags[$tag_name]:self::DEFAUT_TAG_KEY;
    }

    private function get_tags(){
        $cache=ZHF_Cache_Factory::get_instance()->get_cache();
        $tags=$cache->get(self::TAGS_KEY);
        if($tags!==FALSE){
            return $tags;
        }
        $tags=array();
        $rows=$this->get_dao()->get_tags();
        foreach ($rows as $row){
            $tags[$row['tag']]=$row['updated'];
        }
        $cache->set(self::TAGS_KEY, $tags,0,self::TAGS_KEY_EXPIRES);
        return $tags;
    }

    /**
     *
     * @return ZHF_DB_Tag
     */
    private function get_dao(){
        if(NULL===$this->dao){
            $this->dao=new ZHF_DB_Tag();
        }
        return $this->dao;
    }

}