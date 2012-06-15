<?php
/**
 *
 * 控制器抽基类
 * @author zhxia84
 *
 */
abstract class ZHF_Controller{
    /**
     *
     * @var ZHF
     */
    protected $zhf=NULL;
    /**
     *
     * @var ZHF_Request
     */
    protected $request=NULL;
    /**
     *
     * @var ZHF_Response
     */
    protected $response=NULL;
    public function __construct(){
        $this->zhf=ZHF::get_instance();
        $this->request=$this->zhf->get_request();
        $this->response=$this->zhf->get_response();
    }
    abstract public function handle_request();
    public function get_interceptor_index_name(){
        return __CLASS__;
    }
    public function __destruct(){
    }
}