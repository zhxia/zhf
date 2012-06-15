<?php
class ZHF_Cache_Factory{
	private static $instance;
	/**
	 *
	 * Enter description here ...
	 * @var ZHF_Cache_Memcache
	 */
	private $cache=NULL;
	private function __construct(){
		$cache_name=ZHF::get_instance()->get_config('cache_name','cache');
		if($cache_name=='memecache'){
			$this->cache=$this->load_memcache();
		}
	}

	/**
	 *
	 * @return ZHF_Cache_Factory
	 */
	public static function &get_instance(){
		if(self::$instance==NULL){
			self::$instance=new self();
		}
		return self::$instance;
	}

	/**
	 *
	 * @return ZHF_Cache_Memcache
	 */
	public function get_cache(){
		$this->load_memcache();
		return $this->cache;
	}

	private function load_memcache(){
		if(NULL===$this->cache){
			$this->cache=new ZHF_Cache_Memcache();
			$servers=ZHF::get_instance()->get_config('servers','cache');
			foreach ($servers as $server){
				$this->cache->addServer($server['host'],$server['port']);
			}
		}
	}


}