<?php
/**
 * 系统默认配置文件，请勿修改
 */
$config['regex_func']='preg_match';
$config['auto_mapping']=TRUE;
$config['404']='ZHF_Errors_404';
//用于资源文件的引入 使用其它资源进行压缩
$config['mappings']['ZHF_Resource_CompressResource']=array(
    '/[a-z]+/(s|b)/(.*)\.(css|js)$'
);
//用于资源文件的引入 使用yui进行压缩
/*$config['mappings']['ZHF_Resource_YuiCompressResource']=array(
    '/[a-z]+/(s|b)/(.*)\.(css|js)$'
);*/
