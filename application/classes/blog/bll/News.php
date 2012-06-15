<?php
class Blog_BLL_News extends ZHF_DB_BLL{

    public function get_news_by_id($id){
        return $this->get_dao_default()->find_by_id($id);
    }

    public function get_news_list($where){
        return $this->get_dao_default()->find($where);
    }

    /**
     * @return Blog_DAO_News
     */
    public function get_dao_default(){
        return $this->get_dao('Blog_DAO_News');
    }
}
