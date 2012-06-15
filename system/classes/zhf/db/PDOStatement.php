<?php
/**
 *
 * PDOStatement重写
 * @author zhxia84
 *
 */
class ZHF_DB_PDOStatement extends PDOStatement{
    /**
     *
     * @var ZHF_Db_PDO
     */
    private $pdo;
    /**
     *
     * 此处不能使用public类型的构造方法
     * @param $pdo
     */
    protected function __construct($pdo){
        $this->pdo=$pdo;
    }
    /**
     * 重写父类的exeute方法，注意一定要兼容父类方法参数前不要加array
     * (non-PHPdoc)
     * @see PDOStatement::execute()
     */
    public function execute($input_parameters=NULL){
        $ret=parent::execute($input_parameters);
        if(ZHF::get_instance()->is_debug_enabled()){
            ZHF::get_instance()->debug("Execute SQL:{$this->queryString}");
        }
        if(!$ret){
            $error_info=parent::errorInfo();
            ZHF_Logger_Factory::get_instance()->get_logger()->error(__CLASS__,$this->queryString);
            $error_info = preg_replace("#[\r\n \t]+#",' ',var_export($error_info,true));
            ZHF_Logger_Factory::get_instance()->get_logger()->error(__CLASS__,$error_info);
            if(parent::errorCode()!=='00000'){
                trigger_error($this->queryString,E_USER_ERROR);
            }
        }
        return $ret;
    }
}