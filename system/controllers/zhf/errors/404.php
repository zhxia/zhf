<?php
class ZHF_Errors_404Controller extends ZHF_Controller{
    public function handle_request(){
        $this->zhf->get_response()->set_header('HTTP/1.1', '404 Not Found','404');
        return 'ZHF_Errors_404';
    }
}