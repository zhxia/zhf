<?php
class Blog_Web_NewsPage extends Blog_Layout_DefaultPage{
    public function get_view(){
        $this->assign_data('data',$this->request->get_attribute('data'));
        return 'News';
    }

    public function get_title(){
        return '新闻列表--博客首页';
    }
    public static function use_boundable_javascripts(){
        $path=zhf_classname_to_path(__CLASS__);
        return array_merge(
            parent::use_boundable_javascripts(),
            array($path.'News.js')
        );
    }
    public static function use_boundable_styles(){
        $path=zhf_classname_to_path(__CLASS__);
        return array_merge(
            parent::use_boundable_styles(),
            array("{$path}News.css")
        );
    }

}