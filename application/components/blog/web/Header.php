<?php
zhf_require_class('ZHF_Component');
class Blog_Web_HeaderComponent extends ZHF_Component{
	public function get_view(){
		return 'Header';
	}

	public static function use_boundable_styles(){
		$path=zhf_classname_to_path(__CLASS__);
		return array_merge(
			array("{$path}Header.css")
		);
	}
}