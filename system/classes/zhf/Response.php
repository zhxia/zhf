<?php
class ZHF_Response{
    const CONFIG_N_COOKIE_PATH='cookie_path';
    const CONFIG_N_COOKIE_DOMAIN='cookie_domain';
    /**
     *
     * 删除指定的cookie
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param string $secure
     * @param string $httponly
     */
    public function remove_cookie($name,$path=NULL,$domain=NULL,$secure=FALSE,$httponly=FALSE){
        $this->set_cookie($name, NULL,-3600,$path,$domain,$secure,$httponly);
    }

    /**
     *
     * 设置cookie
     * @param unknown_type $name
     * @param unknown_type $value
     * @param unknown_type $expire
     * @param unknown_type $path
     * @param unknown_type $domain
     * @param unknown_type $secure
     * @param unknown_type $httponly
     */
    public function set_cookie($name,$value,$expire=0,$path=NULL,$domain=NULL,$secure=FALSE,$httponly=FALSE){
        if(empty($path)){
            $path=ZHF::get_instance()->get_config(self::CONFIG_N_COOKIE_PATH);
        }
        if(empty($domain)){
            $domain=ZHF::get_instance()->get_config(self::CONFIG_N_COOKIE_DOMAIN);
        }
        $expire=$expire?time()+intval($expire):0;
        setcookie($name,$value,$expire,$path,$domain,$secure,$httponly);
    }

    public function redirect($url,$permanent){
        header("Location:$url",TRUE,$permanent?301:302);
        exit(0);
    }

    public function set_cache_control($value){
        $this->set_header('Cache-Control', $value);
    }
    /**
     *
     * 设置页面header状态码
     * @param unknown_type $name
     * @param unknown_type $value
     * @param unknown_type $http_response_code
     * @param unknown_type $separator
     */
    public function set_header($name,$value,$http_response_code=NULL,$separator =':'){
        header("{$name}{$separator} {$value}",TRUE,$http_response_code);
    }

    public function add_header($name,$value,$http_response_code=NULL,$separator =':'){
        header("{$name}{$separator} {$value}",FALSE,$http_response_code);
    }

    public function set_content_type($content_type,$charset='utf-8'){
        if(preg_match('/^text/i', $content_type)){
            $_charset=ZHF::get_instance()->get_config('charset');
            if($_charset){
                $charset=$_charset;
            }
        }
        if($charset){
            $this->set_header('Content-type', "{$content_type};charset:{$charset}");
        }
        else{
            $this->set_header('Content-type', $content_type);
        }
    }
}
