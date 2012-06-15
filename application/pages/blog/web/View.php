<?php
class Blog_Web_ViewPage extends Blog_Layout_DefaultPage{
    public function get_view(){
	$this->assign_data('row',$this->request->get_attribute('row'));
        return 'View';
    }

    public function get_title(){
        return '文章单页';
    }
}
