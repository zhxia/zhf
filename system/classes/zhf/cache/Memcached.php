<?php
class ZHF_Cache_Memcached extends Memcached{

	private $key_list;
	private $read_keys=array();
	function __construct($persistent_id=NULL){
		parent::__contruct($persistent_id);
	}

	public function add($key,$value,$expiration=0){
		$this->log($key);
		return parent::add($key,$value,$expiration=0);
	}

	public function get($key,$cache_cb=NULL,&$cas_token=0){
		$this->log_read_keys($key);
		return parent::get($key,$cache_cb,&$cas_token);
	}

	public function getMulti(array $keys,array &$cas_tokens=array()){
		$this->log_read_keys($keys);
		return parent::getMulti($keys,$cas_tokens);
	}

	public function set($key,$value,$expiration=0){
		$this->log($key);
		return parent::set($key,$value,$expiration);
	}

	public function setMulti(array $items,$expiration=0){
		$this->log(array_keys($items));
		return parent::setMulti($items,$expiration);
	}

	public function set_with_collect($key,$value,$flag=0,$expire=0){
		return $this->set($key, $value,$expire);
	}

	private function log_read_keys($key){
		if($key&&is_array($key)){
			foreach ($key as &$k){
				if(!in_array($key, $this->read_keys)){
					$this->read_keys[]=$k;
				}
			}
		}
		else if(is_string($key)){
			$this->read_keys[]=$key;
		}
	}

	public function get_read_keys(){
		return $this->read_keys;
	}

	private function log($key){
		if($key&&is_array($key)){
			foreach ($key as &$k){
				if(!in_array($key, $this->key_list)){
					$this->key_list[]=$k;
				}
			}
		}
		else if(is_string($key)){
			$this->key_list[]=$key;
		}
	}
}
?>