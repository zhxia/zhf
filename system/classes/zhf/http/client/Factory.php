<?php
/**
 *
 * HttpClient Factory
 * @author zhxia84
 *
 */
class ZHF_Http_Client_Factory {
    private static $_instance=null;
    private function __construct(){}

    /**
     *
     * @return ZHF_Http_Client_Factory
     */
    public static function &get_instance(){
        if(self::$_instance==null){
                self::$_instance=new self();
        }
        return self::$_instance;
    }

    /**
     *
     * 获取httpClient对象
     * @return ZHF_Http_Client_IHttpClient
     */
    public function get_httpClient(){
        $class_name='ZHF_Http_Client_Curl';
        return $this->create_object($class_name);
    }

    private function create_object($class_name){
        $obj=new ReflectionClass($class_name);
        return $obj->newInstance();
    }

}