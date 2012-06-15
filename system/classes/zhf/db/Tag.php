<?php
/**
 *
 * 用于存储所有的表名
 * @author zhxia84
 *
 */
class ZHF_DB_Tag{
const TAG_TABLE_NAME='cache_tags';
    const DAO_NAME='master';
    public function get_tags(){
        $pdo=ZHF_DB_Factory::get_instance()->get_pdo(self::DAO_NAME);
        $sql='select * from `'.self::TAG_TABLE_NAME.'`';
        $stmt=$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update_tag($tag){
        $pdo=ZHF_DB_Factory::get_instance()->get_pdo(self::DAO_NAME);
        $sql='insert into `'.self::TAG_TABLE_NAME.'`(tag,updated)values(?,?) ON DUPLICATE KEY UPDATE update updated=unix_timestamp()';
        $stmt=$pdo->prepare($sql);
        $stmt->execute(array($tag,time()));
        $rs=$pdo->lastInsertId();
        if($rs){
            return $rs;
        }
        return $stmt->rowCount();
    }
}