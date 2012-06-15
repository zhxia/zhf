<?php
abstract class ZHF_DB_BLL{
    private $dao_list=array();
    private $dao_read_from_master=FALSE;
    private $cache_enabled=TRUE;
    public function cache_enabled($flag){
        $this->cache_enabled=$flag;
    }

    public function dao_read_from_master($boolean){
        $this->dao_read_from_master=$boolean;
    }

    public function get_dao($dao_name){
        if(!isset($this->dao_list[$dao_name])){
			$obj_class=new ReflectionClass($dao_name);
        	$this->dao_list[$dao_name]=$obj_class->newInstance();
        }
        $this->dao_list[$dao_name]->read_from_master($this->dao_read_from_master);
        $this->dao_list[$dao_name]->cache_enabled($this->cache_enabled);
        return $this->dao_list[$dao_name];
    }
}