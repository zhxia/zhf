<?php
/**
 *
 * Http Curl
 * @author zhxia84
 *
 */
class ZHF_Http_Client_Curl implements ZHF_Http_Client_IHttpClient{
    private	$curl=null;

    public function __construct(){
        $this->curl=curl_init();
    }

    public function set_option($option, $value){
        curl_setopt($this->curl, $option, $value);
    }
    public function execute(){
        $rt=curl_exec($this->curl);
        $this->close();
        return $rt;
    }

    protected function close(){
        curl_close($this->curl);
    }
}