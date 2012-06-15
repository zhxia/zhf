<?php
class Blog_Dao_News extends ZHF_DB_DAO{
    public function get_table_name(){
        return 'news';
    }

    public function get_read_pdo_name(){
        return 'slave';
    }
    public function get_write_pdo_name(){
        return 'master';
    }
    public function get_table_primary_key(){
        return 'id';
    }
}
