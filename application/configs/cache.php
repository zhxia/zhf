<?php
$config['cache_name']='memcache'; //可以使用的值为：memcache
$config['servers']=array(
        array('host'=>'192.168.181.140','port'=>11211)
);
$config['cache_control'][]=array(
    'url'=>'^/$',
    'smaxage'=>120,
    'maxage'=>100
);
