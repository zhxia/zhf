<?php
class ZHF_Resource_JavascriptsAndStylesComponent extends ZHF_Component{
    public function get_view(){
        return 'JavascriptsAndStyles';
    }

    public function get_javascripts($head=false){
        return ZHF::get_instance()->get_javascripts($head);
    }

    public function get_styles(){
        return ZHF::get_instance()->get_styles();
    }

    public function get_boundable_javascripts(){
        return ZHF::get_instance()->get_boundable_javascripts();
    }

    public function get_boundable_styles(){
        return ZHF::get_instance()->get_boundable_styles();
    }

    public function get_javascript_url($resource){
        $uri=$this->build_javascript_uri($resource);
        if(preg_match('/:\/\//', $uri)){
            return $uri;
        }
        $prefix=$this->get_cdn_prefix();
        return "{$prefix}{$uri}";
    }

    public function get_style_url($resource){
        $uri=$this->build_style_uri($resource);
        if(preg_match('/:\/\//', $uri)){
            return $uri;
        }
        $prefix=$this->get_cdn_prefix();
        return "{$prefix}{$uri}";
    }

    public function get_boundable_javascripts_url(){
        $prefix=$this->get_cdn_prefix();
        $uri=$this->build_boundable_javascripts_uri();
        return "{$prefix}{$uri}";
    }

    public function get_boundable_styles_url(){
        $prefix=$this->get_cdn_prefix();
        $uri=$this->build_boundable_styles_uri();
        return "{$prefix}{$uri}";
    }


    public function build_style_uri($resource){
        return ZHF_Resource_ResourceController::build_uri($resource);
    }

    public function build_javascript_uri($resource){
        return ZHF_Resource_ResourceController::build_uri($resource);
    }

    public function build_boundable_styles_uri(){
        return ZHF_Resource_ResourceController::build_boundable_uri($this->get_page_class(), 'css');
    }

    public function build_boundable_javascripts_uri(){
        return ZHF_Resource_ResourceController::build_boundable_uri($this->get_page_class(), 'js');
    }

    private function get_cdn_prefix(){
        $schema='http://';
        $host=ZHF::get_instance()->get_config('cdn_host','resource');
        $path=ZHF::get_instance()->get_config('cdn_path','resource');
        return "{$schema}{$host}{$path}";
    }
    public function get_cdn_boundable_prefix(){
    }

    public function is_boundable_resource_enabled(){
        return ZHF::get_instance()->get_config('enabled_boundable_resource','resource');
    }
    /**
     *
     * 获取页面的类名
     */
    public function get_page_class(){
        $page=$this->get_page();
        $class=get_class($page);
        return substr($class,0,-4);
    }

}