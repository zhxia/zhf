<?php
spl_autoload_register('__autoload');
function __autoload($class_name){
    if(preg_match('/(.*)Controller$/i', $class_name,$matches)){
        //自动加载controller
        if(strtoupper($matches[1])=='ZHF_'){
            zhf_require_class($class_name);
        }
        else{
            zhf_require_controller($matches[1]);
        }
    }
    else if(preg_match('/(.*)Page$/i', $class_name,$matches)){
        //自动加载page
        if(substr(strtoupper($matches[1]),0,4)=='ZHF_'){
            zhf_require_class($class_name);
        }
        else{
            zhf_require_page($matches[1]);
        }
    }
    else if(preg_match('/(.*)Component$/i', $class_name,$matches)){
        //自动加载Component
        if(strtoupper($matches[1])=='ZHF_'){
            zhf_require_class($class_name);
        }
        else{
            zhf_require_component($matches[1]);
        }
    }
    else if(preg_match('/(.*)Interceptor$/i', $class_name,$matches)){
        //自动加载拦截器
        if(strtoupper($matches[1])=='ZHF_'){
            zhf_require_class($class_name);
        }
        else{
            zhf_require_interceptor($matches[1]);
        }
    }
    else{
        //自动加载其他类
        zhf_require_class($class_name);
    }
}

function zhf_error_handler($errno,$errstr,$errfile,$errline){
    $errors='';
    switch ($errno){
        case E_NOTICE:
        case E_USER_NOTICE:
            $errors='Notice';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $errors='Warning';
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $errors='Fatal Error';
            break;
        default:
            $errors='Unknown';
            break;
    }
    $message=sprintf("\n%s : %s in %s on line %d \n",$errors,$errstr,$errfile,$errline);
    echo $message;
    die();
//    ZHF_Logger_Factory::get_instance()->get_logger()->warn($message);
    return TRUE;
}

function zhf_exception_handler(Exception $exception){
    $logger=ZHF_Logger_Factory::get_instance()->get_logger();
    $message=sprintf("\nException:%s with message: %s in %s on line %d \n",get_class($exception),$exception->getMessage(),$exception->getFile(),$exception->getLine());
    $logger->warn($message);
}
/**
 *
 * 加载拦截器
 * @param unknown_type $class
 */
function zhf_require_interceptor($class){
    return zhf_require_class($class,'interceptor');
}
/**
 *
 * 加载组件component
 * @param $class
 */
function zhf_require_component($class){
    return zhf_require_class($class,'components');
}

/**
 *
 * 加载视图page
 * @param $class
 */
function zhf_require_page($class){
    return zhf_require_class($class,'pages');
}

/**
 *
 * 加载控制器controller
 * @param $class
 */
function zhf_require_controller($class){
    return zhf_require_class($class,'controllers');
}
/**
 * 加载指定类
 * Enter description here ...
 * @param $class
 * @param $prefix
 * @param $enable_log
 */
function zhf_require_class($class,$prefix='classes',$enable_log=true){
    $file=zhf_classname_to_filename($class);
    if(!zhf_require_file("$file.php",$prefix)){
        if($enable_log){
            $errmsg="can not find class {$class}";
            ZHF_Logger_Factory::get_instance()->get_logger()->error($errmsg);
        }
        return FALSE;
    }
    return TRUE;
}

/**
 *
 * 包含文件
 * @param $file
 * @param $prefix
 */
function zhf_require_file($file,$prefix='lib'){
    global $G_LOAD_PATH;
    if(defined('CACHE_PATH')&&$prefix!='lib'){
        $f=zhf_class_to_cache_file($file, $prefix);
        if(file_exists($f)){
            if(!zhf_required_files($file, $prefix)){
                require_once $f;
            }
            return TRUE;
        }
    }

    foreach ($G_LOAD_PATH as $path){
        if(file_exists("{$path}{$prefix}/{$file}")){
            if(!defined('CACHE_PATH')||!zhf_required_files($file, $prefix)){
                require_once "{$path}{$prefix}/{$file}";
                if(defined('CACHE_PATH')&&$prefix!='lib'){
                    zhf_file_save_to_cache($file, $prefix, "{$path}{$prefix}/$file");
                }
            }
            return TRUE;
        }
    }
}

/**
 *
 * 将文件存入cache
 * @param $file
 * @param $prefix
 * @param $source
 */
function zhf_file_save_to_cache($file,$prefix,$source){
    $target_file=zhf_class_to_cache_file($file, $prefix);
    if(file_exists($target_file)){
        return TRUE;
    }
    $dir=dirname($target_file);
    if(!is_dir($dir)){
        @mkdir($dir,0755,TRUE);
    }
    return file_put_contents($target_file, php_strip_whitespace($source));
}
/**
 * 检测文件是否被cache
 * @param $file
 * @param $prefix
 */
function zhf_required_files($file,$prefix){
    global $G_CACHED_FILES;
    $f=$prefix.'/'.$file;
    if(in_array($f,$G_CACHED_FILES)){
        return TRUE;
    }
    $G_CACHED_FILES[]=$f;
    return FALSE;
}

/**
 *
 * 将类名转换为文件名
 * @param $class
 */
function zhf_classname_to_filename($class){
    $arr_path=explode('_', $class);
    $filename=array_pop($arr_path);
    $arr_path=array_map('strtolower',$arr_path);
    $path=zhf_classname_to_path($class);
    return $path.$filename;
}
/**
 *
 * 返回类所在的文件夹路径
 * @param unknown_type $class
 */
function zhf_classname_to_path($class){
    $arr_path=explode('_',$class);
    array_pop($arr_path);
    $arr_path=array_map('strtolower',$arr_path);
    $path=implode('/',$arr_path).'/';
    return $path;
}

/**
 *
 * 返回被cache的文件的路径
 * @param $file
 * @param $prefix
 */
function zhf_class_to_cache_file($file,$prefix){
    return CACHE_PATH.$prefix.'/'.$file;
}

