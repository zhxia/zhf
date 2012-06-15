<?php
zhf_require_page('Blog_Layout_Default');
class Blog_Web_IndexPage extends Blog_Layout_DefaultPage{
	public function get_view(){
		$this->assign_data('rows',$this->request->get_attribute('rows'));
		return 'Index';
	}

	public function get_title(){
		$title=sprintf('这是%2$s,这是%1$s','首页','测试');
		return $title;
	}

	public static function use_boundable_styles(){
		$path=zhf_classname_to_path(__CLASS__);
		return array_merge(
			parent::use_boundable_styles(),
			array($path.'Index.css')
		);
	}
}