<?php
class ZHF_DB_Factory{
    private static $instance;
    public $pdo_list=array();
    private $pdo_class='ZHF_DB_PDO';
    /**
     * @var ZHF
     */
    private $zhf;
    private function __construct(){
        $this->zhf=ZHF::get_instance();
    }

    /**
     *
     * @return ZHF_Db_Factory
     */
    public static function &get_instance(){
        if(!self::$instance){
            self::$instance=new self();
        }
        return self::$instance;
    }

    /**
     *
     * 设置当前的pdo类
     * @param unknown_type $classname
     */
    public function set_pdo_class($classname='PDO'){
        $this->pdo_class=$classname;
    }

    /**
     *
     * 获取pdo对象
     * @param string $name
     * @return PDO
     */
    public function get_pdo($name='default'){
        if(!isset($this->pdo_list[$name])){
            $this->pdo_list[$name]=$this->load_pdo($name);
        }
        return $this->pdo_list[$name];
    }

    /**
     *
     * 加载PDO对象
     * @param unknown_type $name
     * @return PDO
     */
    public function load_pdo($name='default'){
        if($this->zhf->is_debug_enabled()){
            $this->zhf->benchmark_begin(__CLASS__.":open pdo '{$name}'");
        }
        $db_cfg=$this->zhf->get_config($name,'database');
        zhf_require_class($this->pdo_class);
        $pdo=new $this->pdo_class(
        $db_cfg['dsn'],
        $db_cfg['username'],
        $db_cfg['password'],
        isset($db_cfg['driver_options'])?$db_cfg['driver_options']:array()
        );
        if(isset($db_cfg['default_fetch_mode'])){
            $pdo->set_default_mode($db_cfg['default_fetch_mode']);
        }
        if(isset($db_cfg['init_statements'])){
            foreach ($db_cfg['init_statements'] as $sql){
                $pdo->exec($sql);
            }
        }
        if($this->zhf->is_debug_enabled()){
            $this->zhf->benchmark_end(__CLASS__.":open pdo '{$name}'");
        }
        return $pdo;
    }

    public function close_pdo($name){
        if(isset($this->pdo_list[$name])){
            unset($this->pdo_list[$name]);
        }
    }

}