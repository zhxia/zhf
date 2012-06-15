<?php
abstract class Blog_Layout_DefaultPage extends ZHF_DecoratorPage{
    public function get_decorator(){
        $path=zhf_classname_to_path(__CLASS__);
        return "{$path}Default";
    }

    public static function use_boundable_styles(){
        $path=zhf_classname_to_path(__CLASS__);
        return array("{$path}Default.css");
    }

    public static function use_component(){
        $path=zhf_classname_to_path(__CLASS__);
        return array_merge(
            array('Blog_Web_Header'),
            parent::use_component()
        );
    }

    public function get_head_sections(){
         return array_merge(
            array('<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />'),
            array('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'),
            parent::get_head_sections()
        );
    }

    public function execute(){
        if($this->zhf->get_config('enable_minhtml','resource')){
            ob_start();
            parent::execute();
            $html=$this->compress_html(ob_get_contents());
            ob_end_clean();
            echo $html;
        }
        else{
            parent::execute();
        }
    }

    /**
     *
     * 压缩html文件
     * @param unknown_type $html
     */
    public function compress_html($html){
        if(defined('SYS_PATH')){
            require_once SYS_PATH.'lib/Minify_HTML.php';
            $html=Minify_HTML::minify($html);
        }
        return $html;
    }
}