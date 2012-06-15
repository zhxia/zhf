<?php
abstract class ZHF_DB_DAO {
    const MEMCACHE_KEY_PREFIX='ZHF';
    const NONE_TABLE_PK='nonepk';
    /**
     * @var ZHF_DB_Factory
     */
    private $_dbfactory=NULL;
    private $read_from_master=TRUE;
    protected $remove_tag_cache_when_insert=FALSE; //插入数据立即时整表过期
    protected $remove_tag_cache_when_update=FALSE; //更新数据时立即整表过期
    protected $update_tag_when_update=FALSE; //更新单个表的tag
    protected $cache_expires_time=900; //多条数据的缓存时间
    protected $cache_expires_time_pk=300; //单条数据的缓存时间
    private $tag=NULL;
    private $cache_enabled=TRUE;

    abstract public function get_table_name();
    abstract public function get_read_pdo_name();
    abstract public function get_write_pdo_name();
    abstract public function get_table_primary_key();

    protected function get_cache(){
        return ZHF_Cache_Factory::get_instance()->get_cache();
    }

    public function cache_enabled($flag){
        $this->cache_enabled=$flag;
    }

    public function read_from_master($flag){
        $this->read_from_master=$flag;
    }

    private function build_cache_key($str){
        $tag=$this->get_table_name();
        $key=self::MEMCACHE_KEY_PREFIX.'-'.$tag.'-'.$this->get_tag()->get($tag).'-'.$this->get_table_primary_key().'-'.$str;
        return $key;
    }

    /**
     *
     * @return ZHF_Cache_Tag
     */
    private function get_tag(){
        if (NULL===$this->tag){
            $this->tag=ZHF_Cache_Tag::get_instance();
        }
        return $this->tag;
    }

    /**
     *
     * 通过批量ID实现更新
     * @param array $data
     * @param array $ids
     * @param unknown_type $flag
     */
    public function update_by_ids(array $data,array $ids,$flag=FALSE){
        $rt=$this->_update_by_ids($data, $ids,$flag);
        if($this->update_tag_when_update){
            $this->get_tag()->update_tag($this->get_table_name());
        }
        if($this->remove_tag_cache_when_update){
            $this->get_tag()->update_tags();
        }
        return $rt;
    }
    /**
     *
     * 通过ID实现单条更新
     * @param array $data
     * @param unknown_type $id
     * @param unknown_type $flag
     */
    public function update_by_id(array $data,$id,$flag=FALSE){
        $rt=$this->_update_by_id($data, $id,$flag);
        if($this->update_tag_when_update){
            $this->get_tag()->update_tag($this->get_table_name());
        }
        if($this->remove_tag_cache_when_update){
            $this->get_tag()->update_tags();
        }
        return $rt;
    }

    public function update(array $data,$where=array(),$flag=FALSE){
        $rt=$this->_update($data, $where,$flag);
        if($this->update_tag_when_update){
            $this->get_tag()->update_tag($this->get_table_name());
        }
        if($this->remove_tag_cache_when_update){
            $this->get_tag()->update_tags();
        }
        return $rt;
    }

    public function insert_rows(array $rows){
        $rt=$this->_insert_rows($rows);
        if($this->update_tag_when_update){
            $this->get_tag()->update_tag($this->get_table_name());
        }
        if($this->remove_tag_cache_when_insert){
            $this->get_tag()->update_tags();
        }
        return $rt;
    }

    public function insert_row(array $row){
        $rt=$this->_insert_row($row);
        if($this->update_tag_when_update){
            $this->get_tag()->update_tag($this->get_table_name());
        }
        if($this->remove_tag_cache_when_insert){
            $this->get_tag()->update_tags();
        }
        return $rt;
    }

    public function find_count($where=array()){
        if($this->cache_enabled){
            $str=md5(serialize($where));
            $key=$this->build_cache_key($str);
            $data=$this->get_cache()->get($key);
            if($data!==FALSE){
                return $data;
            }
            $data=$this->_fetch_count($where);
            $this->get_cache()->set($key, $data,NULL,$this->cache_expires_time);
            return $data;
        }
        return $this->_fetch_count($where);
    }

    public function find_by_ids(array $ids,$fields='*'){
        if($this->cache_enabled){
            $this->format_ids($ids);
            $keys=array();
            $keys_mapping=array();
            foreach ($ids as $id){
                $key=$this->build_cache_key($id);
                $keys[]=$key;
                $keys_mapping[$id]=$key;
            }
            $cache=$this->get_cache();
            $data=$cache->get($keys);
            $_ids=array(); //需要进行数据库查询的id
            $rows=array();
            foreach ($keys_mapping as $id=>$key){
                if(!isset($data[$key])){
                    $_ids[]=$id;
                }
                else {
                    $rows[$id]=$data[$key];
                }
            }
            $rs=$this->_fetch_by_ids($_ids,$fields);
            if($rs){
                foreach ($rs as $k=>$r){
                    $key=$keys_mapping[$k];
                    $cache->set($key, $r,NULL,$this->cache_expires_time_pk);
                }
                //合并结果集
                $rows=array_merge($rows,$rs);
            }
            return $rows;
        }
        return $this->_fetch_by_ids($ids,$fields);
    }

    public function find_by_id($id,$fields='*'){
        if($this->cache_enabled){
            $str=md5($id.'-'.serialize($fields));
            $key=$this->build_cache_key($str);
            $cache=$this->get_cache();
            $row=$cache->get($key);
            if($row!==FALSE){
                return $row;
            }
            $row=$this->_fetch_by_id($id,$fields);
            $cache->set($key, $row,NULL,$this->cache_expires_time_pk);
            return $row;
        }
        return $this->_fetch_by_id($id,$fields);
    }

    public function find_row($where=array(),$order=NULL,$fields='*'){
        if($this->cache_enabled){
            $str=md5(serialize($where).'-'.serialize($order).'-'.serialize($fields));
            $key=$this->build_cache_key($str);
            $cache=$this->get_cache();
            $row=$cache->get($key);
            if($row!==FALSE){
                return $row;
            }
            $row=$this->_fetch_row($where,$order,$fields);
            $cache->set($key,$row,NULL,$this->cache_expires_time);
            return $row;
        }
        return $this->_fetch_row($where,$order,$fields);
    }

    public function find($where=array(),$order=NULL,$limit=50,$offset=0,$fields='*'){
        if($this->cache_enabled){
            $str=md5(serialize($where).'-'.serialize($order).'-'.$limit.'-'.$offset.'-'.serialize($fields));
            $key=$this->build_cache_key($str);
            $cache=$this->get_cache();
            $rows=$cache->get($key);
            if($rows!==FALSE){
                return $rows;
            }
            $rows=$this->_fetch_rows($where,$order,$limit,$offset,$fields);
            $cache->set($key, $rows,NULL,$this->cache_expires_time);
            return $rows;
        }
        return $this->_fetch_rows($where,$order,$limit,$offset,$fields);
    }

//    =============================================受保护的方法==================================================
    protected function _update_by_ids(array $data,array $ids,$flag=FALSE){
        if(empty($ids)){
            return FALSE;
        }
        $pk=$this->get_table_primary_key();
        if($pk!==self::NONE_TABLE_PK){
            $where="`{$pk}` IN(".implode(',', $ids).")";
            return $this->_update($data, $where,$flag);
        }
        return FALSE;
    }

    protected function _update_by_id(array $data,$id,$flag=FALSE){
        $pk=$this->get_table_primary_key();
        if($pk!==self::NONE_TABLE_PK){
            $where=array($pk=>$id);
            return $this->_update($data, $where,$flag);
        }
        return FALSE;
    }
    /**
     *
     * 通过条件多条更新
     * @param array $data
     * @param unknown_type $where
     * @param unknown_type $flag
     */
    protected function _update(array $data,$where=array(),$flag=FALSE){
        if(empty($data)||empty($where)){
            return FALSE;
        }
        $pdo=$this->get_pdo(TRUE);
        $where=$this->build_where($where);
        $sql="UPDATE `{$this->get_table_name()}` SET ";
        $dt=array();
        foreach ($data as $key=>$val){
            $dt[]=$flag?"`{$key}`={$val}":"`{$key}`='{$val}'";
        }
        $sql.=implode(',', $dt);
        $sql.=@$where['where'];
        $stmt=$pdo->prepare($sql);
        $ret=$stmt->execute(@$where['values']);
        $count=$stmt->rowCount();
        return $count==0?$ret:$count;
    }

    /**
     *
     * 获取记录条数
     * @param unknown_type $where
     */
    protected function _fetch_count($where=array()){
        $pdo=$this->get_pdo();
        $where=$this->build_where($where);
        $sql='SELECT count(*) AS total';
        $sql.="FROM `{$this->get_table_name()}`";
        $sql.=@$where['where'];
        $stmt=$pdo->prepare($sql);
        $stmt->execute(@$where['values']);
        $row=$stmt->fetch();
        return $row['total']?$row['total']:0;
    }

    /**
     *
     * 通过id批量获取
     * @param $ids
     * @param $fields
     */
    protected function _fetch_by_ids(array $ids,$fields='*'){
        $this->format_ids($ids);
        if(empty($ids)){
           return array();
        }
        $pk=$this->get_table_primary_key();
        if($pk!==self::NONE_TABLE_PK){
            $str_ids=implode(',', $ids);
            $where="{$pk} IN({$str_ids})";
            $_rows=$this->_fetch_rows($where,NULL,count($ids),0,$fields);
            $rows=array();
            foreach ($_rows as $_row){
                $rows[$_row[$pk]]=$_row;
            }
            $_rows=NULL;
            return $rows;
        }
        return FALSE;
    }

    /**
     *
     * 通过主键id查找数据行
     * @param $id
     */
    protected function _fetch_by_id($id,$fields='*'){
        $pk=$this->get_table_primary_key();
        if($pk!==self::NONE_TABLE_PK){
            return $this->_fetch_row(array($pk=>$id),NULL,$fields);
        }
        return FALSE;
    }
    /**
     *
     * 获取单行数据
     * @param $where
     * @param $order
     * @param $limit
     * @param $offset
     * @param $fields
     */
    protected function _fetch_row($where=array(),$order=NULL,$fields='*'){
        $rows=$this->_fetch_rows($where,$order,1,0,$fields);
        return array_pop($rows);
    }


    /**
     *
     * 获取多行数据
     * @param unknown_type $where
     * @param unknown_type $order
     * @param unknown_type $limit
     * @param unknown_type $offset
     * @param unknown_type $fields
     */
    protected function _fetch_rows($where=array(),$order=NULL,$limit=50,$offset=0,$fields='*'){
        $pdo=$this->get_pdo();
        if(is_array($fields)){
            $fields=implode(',', $fields);
        }
        $sql="SELECT {$fields} FROM `{$this->get_table_name()}`";
        $where=$this->build_where($where);
        $sql.=@$where['where'];
        if(is_array($order)){
            $sql.=' ORDER BY '.implode(',', $order);
        }
        else if(is_string($order)){
            $sql.=' ORDER BY '.$order;
        }
        if(is_integer($limit)&&$limit>0){
            $sql.=" LIMIT {$limit} OFFSET {$offset}";
        }
        $stmt=$pdo->prepare($sql);
        if(!$stmt->execute(@$where['values'])){

        }
        return $stmt->fetchAll();
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $where
     * @example array('username=?'=>'aa')
     */
    protected function build_where($where=array()){
        $_values=array();
        $_fields=array();
        if(empty($where)){
            return $where;
        }
        if(is_array($where)){
            foreach ($where as $key=>$val){
                $key=preg_replace('/\s+/', '', $key);
                if(preg_match('/\?/', $key)){
                    $_fields[]="({$key})";
                }
                else{
                    $_fields[]="(`{$key}`=?)";
                }
                $_values[]=mysql_escape_string($val);
            }
            $_where=' WHERE '.implode(' AND ', $_fields);
        }
        else{
            $_where=" WHERE {$where}";
        }
        return array('where'=>$_where,'values'=>$_values);
    }

    /**
     *
     * 批量插入多上数据
     * @param array $rows
     */
    protected function _insert_rows(array $rows){
        $pdo=$this->get_pdo(TRUE);
        $params=array();
        $values=array();
        $row=array_pop($rows);
        foreach ($row as $val){
            $values[]='?';
        }
        $sql="insert into `{$this->get_table_name()}`(`";
        $sql.=implode('`,`',array_keys($row)).'`)';
        $sql.='VALUES('.implode(',', $values).')';
        $stmt=$pdo->prepare($sql);
        $ret=array();
        foreach ($rows as $row){
            $params=array_values($row);
            $stmt->execute($params);
            $ret[]=$pdo->lastInsertId();
        }
        return $ret;
    }

    /**
     *
     * 插入单行数据
     * @param array $row
     */
    protected function _insert_row(array $row){
        $pdo=$this->get_pdo(TRUE);
        $params=array();
        $values=array();
        foreach ($row as $val){
            $values[]='?';
            $params[]=$val;
        }
        $sql="INSERT INTO `{$this->get_table_name()}`(`";
        $sql.=implode('`,`', array_keys($row)).'`)';
        $sql.='VALUES('.implode(',', $values).')';
        $stmt=$pdo->prepare($sql);
        $ret=$stmt->execute($params);
        $id=$pdo->lastInsertId();
        if($id){
            return $id;
        }
        $count=$stmt->rowCount();
        return $count==0?$ret:$count;
    }
    public function execute($sql,array $params=array(),$write=FALSE){
        $pdo=$this->get_pdo($write);
        $stmt=$pdo->prepare($sql);
        if(FALSE===$stmt->execute($params)){
            return FALSE;
        }
        $operator=strtoupper(substr($sql,0,6));
        switch ($operator){
            case 'SELECT':
                $ret=$stmt->fetchAll();
                break;
            case 'INSERT':
                $ret=$pdo->lastInsertId();
                if(!$ret){
                    $ret=$stmt->rowCount();
                }
                break;
            case 'UPDATE':
            case 'DELETE':
                $ret=$stmt->rowCount();
                break;
            default:
                break;
        }
        return $ret;
    }

    /**
     *
     * 获取一个PDO实例
     * @param boolean $write
     * @return ZHF_DB_PDO
     */
    protected function get_pdo($write=FALSE){
        if(NULL===$this->_dbfactory){
            $this->_dbfactory=ZHF_DB_Factory::get_instance();
        }
        if($write||$this->read_from_master){
            ZHF::get_instance()->debug('get write dao');
            return $this->_dbfactory->get_pdo($this->get_write_pdo_name());
        }
        else{
            ZHF::get_instance()->debug('get read dao');
            return $this->_dbfactory->get_pdo($this->get_read_pdo_name());
        }
    }
    //================================一般功能函数===========================
    /**
     *
     * 格式化数字id数组
     * @param array $ids
     */
    protected function format_ids(array &$ids){
        if($ids){
            foreach ($ids as $key=>$id){
                $id=intval($id);
                if($id<1){
                    unset($ids[$key]);
                }
            }
        }
    }
}