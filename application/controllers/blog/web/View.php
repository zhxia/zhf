<?php
class Blog_Web_ViewController extends ZHF_Controller{
    public function handle_request(){
    $matches=$this->request->get_router_matches();
    $id=intval($matches[1]);
    $bll_news=new Blog_BLL_News();
    $row=$bll_news->get_news_by_id($id);
    $this->request->set_attribute('row',$row);
        return 'Blog_Web_View';
    }
}
