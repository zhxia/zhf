<?php
class ZHF_Resource_YuiCompressResourceController extends ZHF_Resource_ResourceController {
    public function handle_request(){
        $uri=$_SERVER['REQUEST_URI'];
        ob_start();
        parent::handle_request();
        $content=ob_get_contents();
        ob_end_clean();
        $host=$this->zhf->get_config('yui_host','resource');
        $port=$this->zhf->get_config('yui_port','resource');
        if(!$host||!$port){
            echo $content;
        }
        else{
            $handle=@fsockopen($host,$port);
            if($handle){
                fwrite($handle, "$uri\n");
                fwrite($handle, $content);
                fwrite($handle, "\n\0\n");
                while (!feof($handle)){
                    echo fread($handle, 8192);
                }
                fclose($handle);
            }
            else{
                 echo $content;
            }
        }

    }
}
?>