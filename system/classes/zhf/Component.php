<?php
abstract class ZHF_Component extends ArrayObject{
    private $parent=NULL;
    private $html_id=NULL;
    private $params=array();
    private $data=array();
    /**
     *
     * @var ZHF
     */
    protected $zhf=null;
    /**
     *
     * @var ZHF_Request
     */
    protected $request=null;
    /**
     *
     * @var ZHF_Response
     */
    protected $response=null;

    public function __construct($parent=NULL,$html_id=NULL){
        parent::__construct(array(),ArrayObject::ARRAY_AS_PROPS);
        $this->parent=$parent;
        $this->html_id=$html_id;
        $this->zhf=ZHF::get_instance();
        $this->request=$this->zhf->get_request();
        $this->response=$this->zhf->get_response();
    }

    abstract public function get_view();

    public function component($class,$params=array()){
        return ZHF::get_instance()->component($this,$class, $params);
    }
    /**
     *
     * 获取当前component所在的page对象
     */
    public function get_page(){
        $object=$this->get_parent();
        while ($object){
            if($object instanceof ZHF_Page){
                return $object;
            }
            $object=$object->get_parent();
        }
        return NULL;
    }
    public function execute(){
        $view=$this->get_view();
        if($view){
            $f=zhf_classname_to_path(get_class($this)).$view.'.phtml';
            $file="components/{$f}";
            global $G_LOAD_PATH,$G_CACHED_FILES;
            //如果开启文件缓存，则优先从缓存中读取视图文件
            if(defined('CACHE_PATH')){
                $cache_file=zhf_class_to_cache_file($f,'components');
                if(file_exists($cache_file)){
                    $this->render($cache_file);
                    return;
                }
            }

            foreach ($G_LOAD_PATH as $path){
                $full_path="{$path}{$file}";
                if(file_exists($full_path)){
                    $this->render($full_path);
                    if(defined('CACHE_PATH')){
                        //如果开启缓存，则对视图文件进行缓存
                        zhf_file_save_to_cache($f,'components', $full_path);
                    }
                    break;
                }
            }
        }
    }

    public function render($file){
//		foreach ($this->data as $name=>$value){
//			$this->$name=$value;
//		}
        extract($this->data);
        include($file);
    }

    public static function use_javascripts(){
        return array();
    }
    public static function use_boundable_javascripts(){
        return array();
    }

    public static function use_styles(){
        return array();
    }

    public static function use_boundable_styles(){
        return array();
    }

    public static function use_component(){
        return array();
    }

    public function script_block_begin(){
        ob_start();
    }

    public function script_block_end($order=0){
        $js_content=ob_get_contents();
        ZHF::get_instance()->register_script_block($js_content, $order);
        ob_end_clean();
    }

    public function assign_data($name,$value){
        $this->data[$name]=$value;
    }

    public function get_data(){
        return $this->data;
    }

    public function get_params(){
        return $this->params;
    }

    public function set_params($params){
        $this->params=$params;
    }

    public function get_param($name){
        return isset($this->params[$name])?$this->params[$name]:NULL;
    }
    public function get_parent(){
        return $this->parent;
    }

    public function get_html_id(){
        return $this->html_id;
    }


}