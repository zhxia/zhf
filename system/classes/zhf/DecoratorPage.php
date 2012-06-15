<?php
/**
 *
 * 页面装饰类
 * @author zhxia84
 *
 */
abstract class ZHF_DecoratorPage extends ZHF_Page{
    abstract public function get_decorator();

    /**
     * 执行page的入口
     * @see system/classes/zhf/ZHF_Page::execute()
     */
    public function execute(){
        $view=$this->get_decorator();
        $file="pages/{$view}.phtml";
        global $G_LOAD_PATH;
        foreach ($G_LOAD_PATH as $path){
            $fullpath="{$path}{$file}";
            if(file_exists($fullpath)){
                $this->render($fullpath,TRUE);
                break;
            }
        }
    }

    /**
     *
     * 在模板页页面中调用
     */
    public function real_page(){
        $view=$this->get_view();
        if($view){
            $view_path=zhf_classname_to_path(get_class($this));
            $file="pages/{$view_path}{$view}.phtml";
            global $G_LOAD_PATH;
            foreach ($G_LOAD_PATH as $path){
                $full_path="{$path}{$file}";
                if(file_exists($full_path)){
                    $this->render($full_path);
                    break;
                }
            }
        }
    }
}