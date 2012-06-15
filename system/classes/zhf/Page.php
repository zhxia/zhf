<?php
abstract class ZHF_Page extends ZHF_Component{
    public function __construct($parent=NULL,$html_id=NULL){
        parent::__construct($parent,$html_id);
    }

    public function get_title(){
        return 'ZHF Framework'.ZHF::VERSION;
    }

    public function get_content_type(){
        return 'text/html';
    }

    public function get_charset(){
        return 'utf-8';
    }

    public function get_head_sections(){
        return array();
    }

    public function execute(){
        $view=$this->get_view();
        if($view){
            $file='pages/'.zhf_classname_to_path(get_class($this))."{$view}.phtml";
            global $G_LOAD_PATH;
            foreach ($G_LOAD_PATH as $path){
                if(file_exists("{$path}{$file}")){
                    $this->render("{$path}{$file}");
                    break;
                }
            }
        }
    }
    public function render($file,$is_send_content_type=false){
        if($is_send_content_type){
            ZHF::get_instance()->get_response()->set_content_type($this->get_content_type(),$this->get_charset());
        }
        parent::render($file);
    }

}