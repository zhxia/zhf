<?php
/**
 *
 * 页面cache-control设置
 * @author zhxia84
 *
 */
class ZHF_CacheControlInterceptor extends ZHF_Interceptor{
    public function before(){
        ob_start();
        return self::STEP_CONTINUE;
    }

    public function after(){
            $cache_control=$this->zhf->get_request()->get_attribute('cache-control');
            if($cache_control=='no-cache'){
                return self::STEP_CONTINUE;
            }
            $cache_key=$this->build_cache_key();
            $etag=substr($cache_key, 0,8);
            //检测etag
            if(isset($_SERVER['HTTP_IF_NONE_MATCH'])){
                    $none_match=$_SERVER['HTTP_IF_NONE_MATCH'];
                    if($none_match&&$none_match==$etag){
                            $this->zhf->get_response()->set_header('HTTP/1.1', '304 Etag Matched','304');
                            ob_end_clean();
                            return self::STEP_CONTINUE;
                    }
            }

            $time=$this->get_last_modified_time($cache_key);
            //检测last_modified
            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
                $last_modified=$_SERVER['HTTP_IF_MODIFIED_SINCE'];
                  if($time==$last_modified){
                      $this->zhf->get_response()->set_header('HTTP/1.1', '304 Not Modified','304');
                      ob_end_clean();
                      return self::STEP_CONTINUE;
                  }
            }
            $configs=$this->zhf->get_config('cache_control','cache');
            $cache_control_conf=$this->get_cache_control($configs);
            $smaxage=isset($cache_control_conf['smaxage'])?$cache_control_conf['smaxage']:0;
            $maxage=isset($cache_control_conf['maxage'])?$cache_control_conf['maxage']:0;
            $this->zhf->get_response()->set_cache_control("public,s-maxage={$smaxage},max-age={$maxage},must-revalidate");
            $this->zhf->get_response()->set_header('ETag',$etag);
            $this->zhf->get_response()->set_header('Last-Modified', gmdate('D,d M Y H:i:s',time()).' GMT');
            return self::STEP_CONTINUE;
    }

    /**
     *
     * 创建缓存key
     */
    private function build_cache_key(){
        $content=ob_get_contents();
        return md5($content);
    }

    /**
     *
     * 设定页面的修改时间
     * @param $key
     */
    private function get_last_modified_time($key){
            $cache=ZHF_Cache_Factory::get_instance()->get_cache();
            $cache_content=$cache->get($key);
            if($cache_content){
                return $cache_content;
            }
            $time=time();
            $cache->set($key, $time,0,0);
            return $time;
    }

    private function get_cache_control($configs){
            if(empty($configs)||!is_array($configs)){
                return array();
            }
            //防止当前的app是配置在虚拟目录
            if(BASE_URI!=''&&strpos($_SERVER['REQUEST_URI'],BASE_URI)===0){
                    $uri=substr($_SERVER['REQUEST_URI'], strlen(BASE_URI));
            }
            else{
                $uri=$_SERVER['REQUEST_URI'];
            }
            //去掉？以及后面的参数
            $pos=strpos($uri,'?');
            if($pos){
                $uri=substr($uri, 0,$pos);
            }
            if(empty($uri)){
                $uri='/';
            }
            foreach ($configs as $item){
                if(preg_match('#'.$item['url'].'#', $uri)){
                    return $item;
                }
            }
            return array();
    }
}