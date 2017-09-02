<?php

class DATABASE_CONFIG {
	var $default = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => '',
		'login' => '',
		'password' => '',		
		'database' => '',
		'prefix' => 'cake_',
		'encoding'=>'utf8mb4', 
	);		
	var $master = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => '',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => 'cake_',
		'encoding'=>'utf8mb4', 
	);

    public $WxOauth = array(
        'datasource' => 'WxOauthSource',
        'apiKey'     => '',
        'api_wx_url' => 'https://api.weixin.qq.com'
    );

    public $test = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host'       => 'dbhost',
        'login'      => 'dblogin',
        'password'   => 'dbpassword',
        'database'   => 'test_database'
    );

	function __construct() {
        $this->default = array(
            'datasource' => 'Database/Mysql',
            'persistent' => false,
            'host' => MYSQL_SERVER_HOST,
            'port' => '3306',
            'login' => 'root',
            'password' => '1qaz@WSX',
            'database' => $_SERVER["dbname"]? $_SERVER["dbname"] : 'database',
            'prefix' => 'cake_',
            'encoding'=>'utf8mb4',
        );
        $this->master = array(
            'datasource' => 'Database/Mysql',
            'persistent' => false,
            'host' => MYSQL_SERVER_HOST,
            'port' => '3306',
            'login' => 'root',
            'password' => '1qaz@WSX',
            'database' => 'database',
            'prefix' => 'cake_',
            'encoding'=>'utf8mb4',
        );
	}
}

