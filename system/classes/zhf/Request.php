<?php
class ZHF_Request{
    protected $parameters=NULL;
    protected $router_matches=array();
    protected $attributes=array();
    protected $client_ip;

    public function set_router_matches($matches){
        $this->router_matches=$matches;
    }

    public function get_router_matches(){
        return $this->router_matches;
    }

    public function remove_attribute($key){
        if(isset($this->attributes[$key])){
            unset($this->attributes[$key]);
        }
    }

    public function get_attribute($key){
        return isset($this->attributes[$key])?$this->attributes[$key]:NULL;
    }

    public function get_attributes(){
        return $this->attributes;
    }
    public function set_attribute($key,$value){
        $this->attributes[$key]=$value;
    }

    public function is_secure(){
        return isset($_SERVER['HTTPS']);
    }

    public function get_cookies(){
        return $_COOKIE;
    }

    public function get_cookie($name){
        return isset($_COOKIE[$name])?$_COOKIE[$name]:NULL;
    }

    public function load_parameters(){
        return array_merge(
        $_GET,
        $_POST
        );
    }
    public function get_parameters(){
        if($this->parameters==NULL){
            $this->parameters=$this->load_parameters();
        }
        return $this->parameters;
    }

    public function get_parameter($name){
        if($this->parameters==NULL){
            $this->parameters=$this->load_parameters();
        }
        if(isset($this->parameters[$name])){
            return $this->parameters[$name];
        }
        return NULL;
    }
    public function get_method(){
        return $_SERVER['REQUEST_METHOD'];
    }
    public function is_get_method(){
        return $this->get_method()=='GET';
    }
    public function is_post_method(){
        return $this->get_method()=='POST';
    }

    public function get_client_ip(){
        if(!isset($this->client_ip)){
            $ip=FALSE;
            if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                $ip=$_SERVER['HTTP_CLIENT_IP'];
            }
            if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $ips=explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
                if($ip){
                    array_unshift($ips, $ip);
                }
//				foreach ($ips as $v){
//					if(!preg_match('/^(10|172\.[16-31]|192\.168)\./', $v)){
//						$ip=$v;
//						break;
//					}
//				}
            }
            if(!$ip){
                $ip=$_SERVER['REMOTE_ADDR'];
            }
            $this->client_ip=$ip;
        }
        return $this->client_ip;
    }
}