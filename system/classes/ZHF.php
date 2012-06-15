<?php
/**
 *
 * ZHF前端控制器
 * @author zhxia84
 *
 */
class ZHF{
    const VERSION='1.0.0';
    const HTML_ID_PREFIX='zhf_';
    private static $instance=null;
    /**
     *
     * @var ZHF_Request
     */
    private $request;
    /**
     *
     * @var ZHF_Response
     */
    private $response;
    private $html_id=0;
    /**
     *
     * 路由对象
     * @var ZHF_Router
     */
    private $router;
    private $request_class='ZHF_Request';
    private $response_class='ZHF_Response';
    private $router_class='ZHF_Router';
    private $configures=array();
    private $controllers=array();
    private $current_controller=NULL;
    public $script_blocks=array();
    private $javascripts=array();
    private $boundable_javascripts=array();
    private $styles=array();
    private $boundable_styles=array();
    private $resource_index=0;
    private $script_blocks_processed=FALSE;
    private $styles_processed=FALSE;
    private $javascripts_processed=FALSE;
    private $boundable_styles_processed=FALSE;
    private $boundable_javascripts_processed=FALSE;
    private $shutdown_functions=array();
    /**
     *
     * @var ZHF_Debugger
     */
    private $debugger;
    private function __construct(){
        $error_handler=$this->get_config('error_handler');
        if($error_handler){
            set_error_handler($error_handler);
        }
        $exception_handler=$this->get_config('exception_handler');
        if($exception_handler){
            set_exception_handler($exception_handler);
        }
        register_shutdown_function(array($this,'shutdown'));
    }

    public function debug($message){
        if(!isset($this->debugger)){
            return;
        }
        $this->debugger->debug($message);
    }
    /**
     *
     * @return ZHF_Debugger
     */
    public function get_debugger(){
        return $this->debugger;
    }

    public function set_debugger($debugger){
        $this->debugger=$debugger;
    }

    public function is_debug_enabled(){
        return isset($this->debugger);
    }

    /**
     *
     * 给当前的控制器加载所配置的拦截器
     * @param string $class
     */
    public function get_interceptor_classes($class){
        $interceptors=array();
        //		读取全局拦截器
        $global_interceptors=$this->get_config('global','interceptor');
        if($global_interceptors&&!is_array($global_interceptors)){
            $global_interceptors=array($global_interceptors);
        }
        //		需要特殊处理global interceptor
        $tmp_global_interceptors=$global_interceptors;
        $global_interceptors=array();
        foreach ($tmp_global_interceptors as $its){
            if(is_array($its)){
                foreach ($its as $interceptor){
                    $global_interceptors[]=$interceptor;
                }
            }
            else{
                $global_interceptors[]=$its;
            }
        }

        //		读取当前controller所配置的拦截器
        $class_interceptors=$this->get_config($class,'interceptor');
        if(empty($class_interceptors)){
            //			如果当前的controller没有配置拦截器时，则读取默认拦截器
            $class_interceptors=$this->get_config('default','interceptor');
        }
        if($class_interceptors&&!is_array($class_interceptors)){
            $class_interceptors=array($class_interceptors);
        }
        if($global_interceptors){
            foreach ($global_interceptors as $interceptor){
                $interceptors[$interceptor]=$interceptor;
            }
        }
        if($class_interceptors){
            foreach ($class_interceptors as $interceptor){
                if(preg_match('/^!/', $interceptor)){
                    $interceptor=substr($interceptor,1);
                    unset($interceptors[$interceptor]); //排除不需要加载的拦截器
                    continue;
                }
                $interceptors[$interceptor]=$interceptor;
            }
        }
        return $interceptors;
    }

    public function register_shutdown_function($function){
        $this->shutdown_functions[]=$function;
    }

    public function shutdown(){
        if(is_array($this->shutdown_functions)){
            $functions=array_reverse($this->shutdown_functions);
            foreach ($functions as $function){
                call_user_func($function);
            }
        }
        restore_error_handler();
        restore_exception_handler();
    }
    /**
     *
     * 注册资源文件
     * @param unknown_type $class
     * @param unknown_type $is_page
     */
    public function register_resource($class,$is_page=FALSE){
        $this->debug('register resource:'.$class);
        if($is_page){
            zhf_require_page($class);
            $path='pages/';
            $class="{$class}Page";
        }
        else{
            zhf_require_component($class);
            $path='components/';
            $class="{$class}Component";
        }
        eval("\$list={$class}::use_component();");
        foreach ($list as $item){
            $this->register_resource($item);
        }

        eval("\$list={$class}::use_boundable_javascripts();");
        foreach ($list as $item){
            $this->process_resource_url($path, $item, $this->boundable_javascripts);
        }

        eval("\$list={$class}::use_boundable_styles();");
        foreach ($list as $item){
            $this->process_resource_url($path, $item, $this->boundable_styles);
        }

        eval("\$list={$class}::use_styles();");
        foreach ($list as $item){
            $this->process_resource_url($path, $item, $this->styles);
        }

        eval("\$list={$class}::use_javascripts();");
        foreach ($list as $item){
            $this->process_resource_url($path, $item, $this->javascripts);
        }

        //如果当前注册的资源的对象是页面，,最后需要注册debug Component所需要的资源
        if($is_page&&$this->is_debug_enabled()){
            $this->register_resource('ZHF_Debugger_Debug');
        }
    }

    /**
     *
     * 处理资源文件
     * @param string $path
     * @param mixed $item
     * @param mixed $items
     */
    public function process_resource_url($path,&$item,&$items){
        if(is_array($item)){ //处理资源文件有优先级设置
            $url=$item[0];
        }
        else{
            $url=$item;
            $item=array($url,0);
        }
        //不是外部资源时，添加文件路径
        if(!preg_match('/:\/\//', $url)){
            $url=$path.$url;
        }
        if(is_array($items)&&array_key_exists($url, $items)){
            return;
        }
        $item[0]=$url;
        if(!isset($item[1])){
            $item[1]=0;
        }
        $item[3]=$this->resource_index++;
        $items[$url]=$item;
    }

    public function register_script_block($content,$order){
        $this->script_blocks[]=array($content,$order,3=>$this->resource_index++);
    }
    /**
     *
     * 获取页面js块资源
     */
    public function get_script_blocks(){
        if(!$this->script_blocks_processed){
            $values=$this->script_blocks;
            usort($values, array($this,'resource_order_comparator'));
            $this->script_blocks=array();
            foreach ($values as $value){
                $this->script_blocks[]=$value[0];
            }
            $this->script_blocks_processed=TRUE;
        }
        return $this->script_blocks;
    }

    /**
     *
     * 获取外链的css文件列表
     */
    public function get_styles(){
        if(!$this->styles_processed){
            $values=$this->styles;
            usort($values, array($this,'resource_order_comparator'));
            $this->styles=array();
            foreach ($values as $value){
                $this->styles[]=$value[0];
            }
            $this->styles_processed=TRUE;
        }
        return $this->styles;
    }

    /**
     *
     * 获取可以绑定的css文件列表
     */
    public function get_boundable_styles(){
        if(!$this->boundable_styles_processed){
            $values=$this->boundable_styles;
            usort($values, array($this,'resource_order_comparator'));
            $this->boundable_styles=array();
            foreach ($values as $value){
                $this->boundable_styles[]=$value[0];
            }
            $this->boundable_styles_processed=TRUE;
        }
        return $this->boundable_styles;
    }

    /**
     *
     * 获取可以绑定的js文件列表
     */
    public function get_boundable_javascripts(){
        if(!$this->boundable_javascripts_processed){
            $values=$this->boundable_javascripts;
            usort($values, array($this,'resource_order_comparator'));
            $this->boundable_javascripts=array();
            foreach ($values as $value){
                $this->boundable_javascripts[]=$value[0];
            }
            $this->boundable_javascripts_processed=TRUE;
        }
        return $this->boundable_javascripts;
    }

    public function get_javascripts($head=FALSE){
        if(!$this->javascripts_processed){
            $values=$this->javascripts;
            usort($values,array($this,'resource_order_comparator'));
            $this->javascripts=array(0=>array(),1=>array());
            foreach ($values as $value){
                if(@$value[2]){
                    $this->javascripts[0][]=$value[0];
                }
                else {
                    $this->javascripts[1][]=$value[0];
                }
            }
            $this->javascripts_processed=TRUE;
        }
        if($head){
            return $this->javascripts[0];
        }
        else{
            return $this->javascripts[1];
        }
    }

    /**
     *
     * 对资源文件数组进行排序,
     * @param array $a array(0=>'content',1=>order,3=>index)
     * @param array $b array(0=>'content',1=>order,3=>index)
     *
     */
    public function resource_order_comparator($a,$b){
        if($a[1]==$b[1]){
            if($a[3]==$b[3]){
                return 0;
            }
            return $a[3]>$b[3]?1:-1;
        }
        return $a[1]>$b[1]?-1:1;
    }

    public function get_current_controller(){
        return $this->current_controller;
    }

    public function benchmark_begin($name){
        if(!isset($this->debugger)){
            return;
        }
        $this->debugger->benchmark_begin($name);
    }

    public function benchmark_end($name){
        if(!isset($this->debugger)){
            return;
        }
        $this->debugger->benchmark_end($name);
    }
    protected function dispatch(){
        zhf_require_class($this->router_class);
        $router_class=new ReflectionClass($this->router_class);
        $this->router=$router_class->newInstance();
        $class=$this->router->mapping();
        $controller=$this->get_controller($class);
        if(empty($controller)){
            return FALSE;
        }
        $this->current_controller=$controller;
        $interceptor_classes=$this->get_interceptor_classes($class);
        if(empty($interceptor_classes)){
            //如果当前的控制器没有配置拦截器，就找基类里面的拦截器
            $base_class=$controller->get_interceptor_index_name();
            $interceptor_classes=$this->get_interceptor_classes($base_class);
        }
        $step=ZHF_Interceptor::STEP_CONTINUE; //给初始值，防止没有拦截器
        $interceptors=array();
        foreach ($interceptor_classes as $intercepter_class){
            $interceptor=$this->load_interceptor($intercepter_class);
            if(!$interceptor){
                continue;
            }
            $interceptors[]=$interceptor;
            $step=$interceptor->before();
            $this->debug('interceptor::before():'.get_class($interceptor));
            if($step!=ZHF_Interceptor::STEP_CONTINUE){
                break;
            }
        }
        if($step!=ZHF_Interceptor::STEP_EXIT){
            while(TRUE){
                $this->debug('controller:handle_request:'.get_class($controller));
                $this->benchmark_begin('controller:handle_request():'.get_class($controller));
                $result=$controller->handle_request();
                $this->benchmark_end('controller:handle_request():'.get_class($controller));
                if($result instanceof ZHF_Controller){
                    $controller=$result;
                    continue;
                }
                break;
            }
            //取得view
            if(is_string($result)){
                $this->show_page($result);
            }
        }
        $step=ZHF_Interceptor::STEP_CONTINUE;
        if(isset($interceptors)){
            $interceptors=array_reverse($interceptors);
            foreach ($interceptors as $interceptor){
                $step=$interceptor->after();
                $this->debug('interceptor::after():'.get_class($interceptor));
                if($step!=ZHF_Interceptor::STEP_CONTINUE){
                    break;
                }
            }
        }
        return TRUE;
    }

    public function show_page($class,$params=array()){
        $this->benchmark_begin('page::load():',$class);
        $this->register_resource($class,TRUE);
        $page=$this->load_component(NULL, $class,TRUE);
        $this->benchmark_end('page::load():',$class);

        $this->benchmark_begin('page::execute():'.$class);
        $this->debug('page::execute():'.$class);
        $page->execute();
        $this->benchmark_end('page::execute():'.$class);
        return $page;
    }

    /**
     * Enter description here ...
     * @param unknown_type $parent
     * @param unknown_type $class
     * @param unknown_type $params
     */
    public function component($parent=NULL,$class,$params=array()){
        if(empty($class)){
            return FALSE;
        }
        $component=$this->load_component($parent, $class);
        $component->set_params($params);
        $component->execute();
        return $component;
    }

    /**
     *
     * 加载Component
     * @param unknown_type $parent
     * @param unknown_type $class
     * @param unknown_type $is_page
     * @return ZHF_Component
     */
    public function load_component($parent,$class,$is_page=FALSE){
        if($is_page){
            $this->debug('load page:'.$class);
            zhf_require_page($class);
            $class="{$class}Page";
        }
        else{
            $this->debug('load component:'.$class);
            zhf_require_component($class);
            $class="{$class}Component";
        }
        $this->html_id++;
        $class_obj=new ReflectionClass($class);
        return $class_obj->newInstance($parent,self::HTML_ID_PREFIX.$this->html_id);
    }
    /**
     *
     * Enter description here ...
     * @param unknown_type $class
     * @return ZHF_Controller
     */
    public function get_controller($class){
        if($class){
            if(isset($this->controllers[$class])){
                return $this->controllers[$class];
            }
            $controller=$this->load_controller($class);
            $this->controllers[$class]=$controller;
            return $controller;
        }
    }

    public function load_controller($class){
        $this->debug('load controller:'.$class);
        zhf_require_controller($class);
        $class_controller=new ReflectionClass($class.'Controller');
        return $class_controller->newInstance();
    }

    /**
     *
     * 加载拦截器对象
     * @param string $class
     * @return ZHF_Interceptor
     */
    public function load_interceptor($class){
        zhf_require_class($class,'interceptors');
        $class_interceptor=new ReflectionClass($class.'Interceptor');
        return $class_interceptor->newInstance();
    }

    public function run(){
        //创建request和response对象
        zhf_require_class($this->request_class);
        zhf_require_class($this->response_class);
        $class_request=new ReflectionClass($this->request_class);
        $this->request=$class_request->newInstance();
        $class_response=new ReflectionClass($this->response_class);
        $this->response=$class_response->newInstance();
        if(!$this->dispatch()){
            die('Errors');
        }
    }

    public function set_router_class($class){
        $this->router_class=$class;
    }

    public function get_router(){
        return $this->router;
    }
    public function set_request_class($class){
        $this->request_class=$class;
    }

    public function set_response_class($class){
        $this->response_class=$class;
    }

    /**
     *
     * @return ZHF_Request
     */
    public function get_request(){
        return $this->request;
    }

    /**
     *
     * @return ZHF_Response
     */
    public function get_response(){
        return $this->response;
    }

    /**
     *
     * 读取配置信息
     * @param string $name
     * @param string $file
     */
    public function get_config($name='',$file='common'){
        if(!isset($this->configures[$file])){
            $configs=$this->load_config($file);
            if (!$configs){
                return FALSE;
            }
            $this->configures[$file]=$configs;
        }
        return empty($name)?$this->configures[$file]:(isset($this->configures[$file][$name])?$this->configures[$file][$name]:NULL);
    }

    /**
     *
     * 加载配置信息文件
     * @param string $file
     */
    public function load_config($file='common'){
        $this->debug('load config:'.$file);
        global $G_CONF_PATH;
        foreach ($G_CONF_PATH as $path){
            $full_path="{$path}{$file}.php";
            if(file_exists($full_path)){
                include_once $full_path;
            }
        }
        if(!isset($config)){
            trigger_error('Variable $config not found in file "'.$full_path.'"',E_USER_WARNING);
            return FALSE;
        }
        return $config;
    }

    /**
     * 返回ZHF实例
     * @return ZHF
     */
    public static function &get_instance(){
        if(self::$instance==NULL){
            self::$instance=new self();
        }
        return self::$instance;
    }
}