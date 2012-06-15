<?php
/**
 *
 * 处理页面的资源文件 css js
 * @author zhxia84
 *
 */
class ZHF_Resource_ResourceController extends ZHF_Controller{
    const CONFIG_F_RESOURCE='resource';
    const CONFIG_N_VERSION='version';
    const CONFIG_N_PREFIX_URI='prefix_uri';
    const CONFIG_N_RESOURCE_TYPE_SINGLE='resource_type_single';
    const CONFIG_N_RESOURCE_TYPE_BOUNDABLE='resource_type_boundable';

    const DEFAULT_PREFIX_URI='res';
    const DEFAULT_RESOURCE_TYPE_SINGLE='s';
    const DEFAULT_RESOURCE_TYPE_BOUNDABLE='b';

    const CONFIG_N_MATCH_IDX_TYPE='match_type';
    const CONFIG_N_MATCH_IDX_FILE='match_file';
    const CONFIG_N_MATCH_IDX_EXT='match_ext';

    const DEFAULT_MATCH_IDX_TYPE=1;
    const DEFAULT_MATCH_IDX_FILE=2;
    const DEFAULT_MATCH_IDX_EXT=3;

    private $boundable_resource=array();

    /**
     *
     * 获取独立资源文件的路径
     * @param $resource
     */
    public static function build_uri($resource){
        //外部资源文件
        if(preg_match('/:\/\//', $resource)){
            return $resource;
        }
        $version=ZHF::get_instance()->get_config(self::CONFIG_N_VERSION,self::CONFIG_F_RESOURCE);
        $prefix=ZHF::get_instance()->get_config(self::CONFIG_N_PREFIX_URI,self::CONFIG_F_RESOURCE);
        if(!isset($prefix)){
            trigger_error('Unable to get config "'.self::CONFIG_N_PREFIX_URI.'" from "'.self::CONFIG_F_RESOURCE.'"',E_USER_NOTICE);
            $prefix=self::DEFAULT_PREFIX_URI;
        }
        $type=ZHF::get_instance()->get_config(self::CONFIG_N_RESOURCE_TYPE_SINGLE,self::CONFIG_F_RESOURCE);
        if(!isset($type)){
            trigger_error('Unable to get config "'.self::CONFIG_N_RESOURCE_TYPE_SINGLE.'" from "'.self::CONFIG_F_RESOURCE.'"',E_USER_NOTICE);
            $type=self::DEFAULT_RESOURCE_TYPE_SINGLE;
        }
        if(isset($version)){
            return "{$prefix}/{$version}/{$type}/{$resource}";
        }
        else{
            return "{$prefix}/{$type}/{$resource}";
        }

    }

    /**
     *
     * 获取可合并的资源文件的路径
     * @param unknown_type $resource
     * @param unknown_type $ext
     */
    public static function build_boundable_uri($resource,$ext){
        $version=ZHF::get_instance()->get_config(self::CONFIG_N_VERSION,self::CONFIG_F_RESOURCE);
        $prefix=ZHF::get_instance()->get_config(self::CONFIG_N_PREFIX_URI,self::CONFIG_F_RESOURCE);
        if(!isset($prefix)){
            trigger_error('Unable to get config "'.self::CONFIG_N_PREFIX_URI.'" from "'.self::CONFIG_F_RESOURCE.'"',E_USER_ERROR);
        }
        $type=ZHF::get_instance()->get_config(self::CONFIG_N_RESOURCE_TYPE_BOUNDABLE,self::CONFIG_F_RESOURCE);
        if(!isset($type)){
            trigger_error('Unable to get config "'.self::CONFIG_N_RESOURCE_TYPE_BOUNDABLE.'" from "'.self::CONFIG_F_RESOURCE.'"',E_USER_ERROR);
        }
        if(isset($version)){
            return "{$prefix}/{$version}/{$type}/{$resource}.{$ext}";
        }
        else{
            return "{$prefix}/{$type}/{$resource}.{$ext}";
        }
    }

    public function handle_request(){
        $request=ZHF::get_instance()->get_request();
        $response=ZHF::get_instance()->get_response();
        $matches=$request->get_router_matches();
        $idx_ext=self::DEFAULT_MATCH_IDX_EXT;
        $type=$matches[self::DEFAULT_MATCH_IDX_TYPE];
        $file=$matches[self::DEFAULT_MATCH_IDX_FILE];
        $ext=$matches[self::DEFAULT_MATCH_IDX_EXT];
        if($ext=='css'){
            $content_type='text/css';
        }
        else if($ext=='js'){
            $content_type='application/x-javascript';
        }
        else{
            trigger_error('Invalid resource extension "'.$ext.'"',E_USER_ERROR);
            return;
        }
        $response->set_content_type($content_type); //发送页面头信息
        if(!$this->is_modified()){
            return FALSE;
        }
        if($type==self::DEFAULT_RESOURCE_TYPE_BOUNDABLE){
            $this->fetch_boundable_resource($file,$ext,TRUE);
        }
        else if($type==self::DEFAULT_RESOURCE_TYPE_SINGLE){
            if(!$this->include_resource_file("{$file}.{$ext}")){
                trigger_error('Unable to include resource file "'."$file.$ext".'"',E_USER_WARNING);
            }
        }
    }

    /**
     *
     * 获取可以绑定的资源文件
     * @param $class
     * @param $ext
     * @param $is_page
     */
    public function fetch_boundable_resource($class,$ext,$is_page=FALSE){
        if($is_page){
            zhf_require_page($class);
            $path='pages/';
            $class="{$class}Page";
        }
        else{
            zhf_require_component($class);
            $path='components/';
            $class="{$class}Component";
        }
        if(!class_exists($class)){
            return;
        }
        eval("\$list={$class}::use_component();");
        foreach ($list as $item){
            $this->fetch_boundable_resource($item, $ext);
        }
        if($ext=='css'){
            eval("\$list={$class}::use_boundable_styles();");
        }
        else if($ext=='js'){
            eval("\$list={$class}::use_boundable_javascripts();");
        }
        else{
            trigger_error('Invalid resource extension "'.$ext.'"',E_USER_WARNING);
            $list=array();
        }
        foreach ($list as $item){
            ZHF::get_instance()->process_resource_url($path, $item, $this->boundable_resource);
        }

        //给资源进行排序
        if($this->boundable_resource){
            usort($this->boundable_resource,array(ZHF::get_instance(),'resource_order_comparator'));
        }
        //开始引入资源
        foreach ($this->boundable_resource as $item){
            if(!$this->include_resource_file($item[0])){
                trigger_error('Unable to include resource "'.$item[0].'"',E_USER_WARNING);
            }
        }
    }

    protected function include_resource_file($file,$path=NULL){
        if(!empty($path)){
            $full_path="{$path}{$file}";
            if(file_exists($full_path)){
                include_once($full_path);
                return TRUE;
            }
        }
        else{
            global $G_LOAD_PATH;
            foreach ($G_LOAD_PATH as $path){
                $full_path="{$path}{$file}";
                if(file_exists($full_path)){
                    include_once($full_path);
                    return TRUE;
                }
            }
            return FALSE;
        }
    }
    public function is_modified(){
        $response=$this->zhf->get_response();
        $last_modified=strtotime('2011-5-5 22:02:38');
        $etag='"'.dechex($last_modified).'"';
//客户端通过HTTP_IF_NONE_MATCH返回服务器端第一次发送给客户端的ETag
        $none_match=isset($_SERVER['HTTP_IF_NONE_MATCH'])?$_SERVER['HTTP_IF_NONE_MATCH']:NULL;
        if($none_match&&$none_match==$etag){
            $response->set_header('HTTP/1.1', '304 ETag Matched','304',' ');
            return FALSE;
        }
//客户端通过HTTP_IF_MODIFIED_SINCE返回服务器端第一次发送给客户端的last_modified信息
        if($last_modified&&isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
            $tmp=explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
            $modified_since=strtotime($tmp[0]);
            if($modified_since&&$modified_since>=$last_modified){
                header('HTTP/1.1 304 Not Modified',TRUE,304);
                return FALSE;
            }
        }
        if(isset($etag)){
            $response->set_header('ETag', $etag);
        }

        if(isset($last_modified)){
            $response->set_header('Last-Modified', gmdate('D,d M Y H:i:s',$last_modified).' GMT');
        }
        return TRUE;
    }

}