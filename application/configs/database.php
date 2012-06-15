<?php
$config['master'] = array (
    'dsn'=>'mysql:host=192.168.181.140;dbname=blog',
    'username' => 'zhxia',
    'password' => '123456',
    'init_attributes' => array(),
    'init_statements' => array('SET CHARACTER SET utf8','SET NAMES utf8'),
    'default_fetch_mode' => PDO::FETCH_ASSOC
);
$config['slave'] = array (
    'dsn'=>'mysql:host=192.168.181.140;dbname=blog',
    'username' => 'zhxia',
    'password' => '123456',
    'init_attributes' => array(),
    'init_statements' => array('SET CHARACTER SET utf8','SET NAMES utf8'),
    'default_fetch_mode' => PDO::FETCH_ASSOC
);
