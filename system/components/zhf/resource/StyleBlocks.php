<?php
class ZHF_Resource_StyleBlocksComponent extends ZHF_Resource_JavascriptsAndStylesComponent{
    public function get_view(){
        return 'StyleBlocks';
    }

    public function enabled_inline_styles(){
        return $this->zhf->get_config('enabled_inline_styles','resource');

    }

    public function get_inline_styles(){
        $url=$this->get_boundable_styles_url();
        $cache_key=md5(sprintf('css_%s',$url));
        $memcache=ZHF_Cache_Factory::get_instance()->get_cache();
        $rt=$memcache->get($cache_key);
        if($rt===FALSE){
            $http_client=ZHF_Http_Client_Factory::get_instance()->get_httpClient();
            $http_client->set_option(CURLOPT_TIMEOUT, 2);
            $http_client->set_option(CURLOPT_URL, $url);
            $http_client->set_option(CURLOPT_RETURNTRANSFER, TRUE);
            $rt=$http_client->execute();
            if($rt){
                $memcache->set($cache_key, $rt);
            }
        }
        return $rt;
    }

}