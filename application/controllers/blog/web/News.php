<?php
class Blog_Web_NewsController extends ZHF_Controller{
	public function handle_request(){
		$request=ZHF::get_instance()->get_request();
		$request->set_attribute('data', array('aaa','bbbbbbbbb'));
		return 'Blog_Web_News';
	}
}