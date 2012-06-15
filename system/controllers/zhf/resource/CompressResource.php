<?php
class ZHF_Resource_CompressResourceController extends ZHF_Resource_ResourceController{
    public function handle_request(){
        $uri=$_SERVER['REQUEST_URI'];
        //可以通过url对资源文件进行缓存
        ob_start();
        parent::handle_request();
        $content=ob_get_contents();
        ob_end_clean();
        if(preg_match('/\.js\??/i', $uri)){
            if($this->zhf->get_config('enable_minjs','resource')){
                $this->compress_resource($content, 'js');
            }
        }
        else if(preg_match('/\.css\??/i',$uri)){
            if($this->zhf->get_config('enable_mincss','resource')){
                $this->compress_resource($content, 'css');
            }
        }
        echo $content;
    }

    /**
     *
     * 压缩页面资源文件
     * @param unknown_type $content
     * @param unknown_type $type
     */
    public function compress_resource(&$content,$type){
        if(defined('SYS_PATH')){
            if($type=='js'){
                require_once SYS_PATH.'lib/JSMin.php';
                $content=JSMin::minify($content);
            }
            else if($type=='css'){
                require_once SYS_PATH.'lib/CssMin.php';
                $content=CssMin::minify($content);
            }
            else{
                trigger_error('This source is unable to compress',E_USER_WARNING);
                return;
            }
        }
    }
}