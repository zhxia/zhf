<?php
/**
 *
 * PDO重写
 * @author zhxia84
 *
 */
class ZHF_DB_PDO extends PDO{
    public $config;
    private $default_fetch_mode;
    public function __construct($dsn, $username='', $passwd='', $options=array()){
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('ZHF_DB_PDOStatement',array($this)));
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    }

    public function set_default_mode($mode){
        $this->default_fetch_mode=$mode;
    }
    public function exec($statement){
        if(ZHF::get_instance()->is_debug_enabled()){
            ZHF::get_instance()->debug("PDO execute SQL:{$statement}");
        }
        return parent::exec($statement);
    }

    public function prepare($statement,$driver_options=array()){
        $stmt=parent::prepare($statement,$driver_options);
        if($stmt instanceof PDOStatement){
            $stmt->setFetchMode($this->default_fetch_mode);
        }
        return $stmt;
    }

    public function query($statement,$pdo=NULL,$object=NULL){
        if($pdo!=NULL&&$object!=NULL){
            $stmt=parent::query($statement,$pdo,$object);
        }
        else{
            $stmt=parent::query($statement);
        }

        if($stmt instanceof PDOStatement){
            $stmt->setFetchMode($this->default_fetch_mode);
        }
        return $stmt;
    }
}