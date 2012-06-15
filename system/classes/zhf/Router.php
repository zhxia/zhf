<?php
class ZHF_Router{
	const CONFIG_F_ROUTE='route';
	const CONFIG_N_MAPPINGS='mappings';
	const CONFIG_N_REGEX_FUNC='regex_func';
	const DEFAULT_REGEX_FUNC='preg_match';
	const CONFIG_N_AUTO_MAPPING='auto_mapping';
	const HTTP404_CONTROLLER='404';
	public function mapping(){
		$zhf=ZHF::get_instance();
		$mappings=$zhf->get_config(self::CONFIG_N_MAPPINGS,self::CONFIG_F_ROUTE);
		$regex_func=$zhf->get_config(self::CONFIG_N_REGEX_FUNC,self::CONFIG_F_ROUTE);
		$auto_mapping=$zhf->get_config(self::CONFIG_N_AUTO_MAPPING,self::CONFIG_F_ROUTE);
		if(!function_exists($regex_func)){
			$regex_func=self::DEFAULT_REGEX_FUNC;
		}
		if(BASE_URI!=''&&strpos($_SERVER['REQUEST_URI'],BASE_URI)===0){
			$uri=substr($_SERVER['REQUEST_URI'],strlen(BASE_URI));
		}
		else{
			$uri=$_SERVER['REQUEST_URI'];
		}
		if(strpos($uri,'?')!==FALSE){
			$uri=strstr($uri,'?',TRUE);
		}
		if(empty($uri)){
			$uri='/';
		}
		//根据url进行mapping
		$matches=array();
		foreach ($mappings as $class=>$mapping){
			foreach ($mapping as $pattern){
				$pattern=str_replace('/', '\/', $pattern);
				if($regex_func("/{$pattern}/i",$uri,$matches)){
					$zhf->get_instance()->get_request()->set_router_matches($matches);
					return $class;
				}
			}
		}
		//自动mapping
		if($auto_mapping){
			$class=$this->auto_mapping($uri);
			if($class){
				return $class;
			}
		}

		//如果没有找到匹配的controller，则执行404Controller
		$class=$zhf->get_config(self::HTTP404_CONTROLLER,self::CONFIG_F_ROUTE);
		if($class){
			return $class;
		}
		$zhf->get_response()->set_header('HTTP/1.1', '404 Not Found','404');
		return FALSE;
	}

	protected function auto_mapping($uri){
		$classname=$this->convert_uri_to_controller($uri);
		zhf_require_controller($classname,FALSE);
		if(class_exists("{$classname}Controller")){
			return $classname;
		}
		return FALSE;
	}

	protected function convert_uri_to_controller($uri){
		$uri=trim($uri,'/');
		if($uri){
			$pieces=explode('/',$uri);
			$pieces=array_map('ucfirst', $pieces);
			return implode('_', $pieces);
		}
		return NULL;
	}
}