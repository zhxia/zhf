<?php
/**
 *
 * 拦截器的实现
 * @author zhxia84
 *
 */
abstract class ZHF_Interceptor{
	const STEP_CONTINUE=1;
	const STEP_BREAK=2;
	const STEP_EXIT=3;
	/**
	 *
	 * @var ZHF
	 */
	protected $zhf=null;
	public function __construct(){
		$this->zhf=ZHF::get_instance();
	}

	public function init(){
	}
	public function before(){
		return self::STEP_CONTINUE;
	}

	public function after(){
		return self::STEP_CONTINUE;
	}

	public function destory(){
	}
	public function __destruct(){
	}
}