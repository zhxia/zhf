<?php
/**
 *
 * 页面调试类
 * @author zhxia84
 *
 */
class ZHF_Debugger{
	const DEFAULT_BENCHMARK='ZHF';

	const MESSAGE_TIME='t';
	const MESSAGE_CONTENT='c';
	const MESSAGE_MEMORY='m';

	const BENCHMARK_BEGIN='b';
	const BENCHMARK_END='e';
	const BENCHMARK_BEGIN_MEMORY='bm';
	const BENCHMARK_END_MEMORY='em';

	private $benchmarks=array();
	private $messages=array();
	public function __construct(){
		$this->benchmark_begin(self::DEFAULT_BENCHMARK);
		ZHF::get_instance()->register_shutdown_function(array($this,'shutdown'));
	}

	public function shutdown(){
		$this->benchmark_end(self::DEFAULT_BENCHMARK);
		//页面执行完毕，加载显示debug信息的component
		ZHF::get_instance()->component(NULL,'ZHF_Debugger_Debug');
	}
	public function debug($message){
		$this->messages[]=array(
			self::MESSAGE_TIME=>microtime(TRUE)-$this->benchmarks[self::DEFAULT_BENCHMARK][self::BENCHMARK_BEGIN],
			self::MESSAGE_CONTENT=>$message,
			self::MESSAGE_MEMORY=>$this->get_memory_usage()
		);
	}
	public function benchmark_begin($name){
		$this->benchmarks[$name][self::BENCHMARK_BEGIN]=microtime(TRUE);
		$this->benchmarks[$name][self::BENCHMARK_BEGIN_MEMORY]=$this->get_memory_usage();
	}

	public function benchmark_end($name){
		$this->benchmarks[$name][self::BENCHMARK_END]=microtime(TRUE);
		$this->benchmarks[$name][self::BENCHMARK_END_MEMORY]=$this->get_memory_usage();
	}

	protected function get_memory_usage(){
		return function_exists('memory_get_usage')?memory_get_usage():0;
	}

	public function get_benchmarks(){
		return $this->benchmarks;
	}
	public function get_messages(){
		return $this->messages;
	}
}