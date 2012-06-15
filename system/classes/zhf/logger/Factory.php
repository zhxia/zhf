<?php
/**
 *
 * 日志工厂
 * @author zhxia84
 *
 */
class ZHF_Logger_Factory {
    private static $instance;
    private $logger_class='ZHF_Logger_Syslogger';
    private function __construct(){
    }

    public static function &get_instance(){
        if(!self::$instance){
            self::$instance=new self();
        }
        return self::$instance;
    }

    /**
     *获取logger
     * @return ZHF_Logger_Logger
     */
    public function get_logger(){
        $logger_class=ZHF::get_instance()->get_config('logger_class','logger');
        if(!$logger_class){
            $logger_class=$this->logger_class;
        }
        return $this->load_logger($logger_class);
    }

    private function load_logger($logger_class){
        return $this->create_object($logger_class);
    }


    private function create_object($class_name){
        $obj=new ReflectionClass($class_name);
        return $obj->newInstance();
    }
}
?>