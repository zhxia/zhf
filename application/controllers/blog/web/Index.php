<?php
class Blog_Web_IndexController extends ZHF_Controller{
    /**
     * @var Blog_BLL_News
     */
    private $bll_news=NULL;
    public function handle_request(){
        $this->bll_news=new Blog_BLL_News();
//        $this->bll_news->cache_enabled(FALSE);
        $rows=$this->get_news_list();
        $this->request->set_attribute('rows', $rows);
        return 'Blog_Web_Index';
    }

    private function get_news_list(){
        return $this->bll_news->get_news_list(array('id>?'=>0));
    }
}