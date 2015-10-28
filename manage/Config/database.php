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
		'encoding'=>'utf8', 
	);		
	var $master = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => '',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => 'cake_',
		'encoding'=>'utf8', 
	);

    public $WxOauth = array(
        'datasource' => 'WxOauthSource',
        'apiKey'     => '',
        'api_wx_url' => 'https://api.weixin.qq.com'
    );

	function __construct() {
		if(defined('SAE_MYSQL_DB')){ // on sae.sina.com.cn
			// 变量成员定义时不能写字符串点号连接语句，放在类初始化中
			// 后台时，主从模式取消，全部连向Master
			$this->master['host'] = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
			//$this->default['host'] = SAE_MYSQL_HOST_S.':'.SAE_MYSQL_PORT;
			$this->default['host'] = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
			$this->master['login'] = $this->default['login'] = SAE_MYSQL_USER;
			$this->master['password'] = $this->default['password'] = SAE_MYSQL_PASS;
			$this->master['database'] = $this->default['database'] = SAE_MYSQL_DB;
		}
		elseif(preg_match('/\.aliapp\.com$/',$_SERVER['HTTP_HOST'])){
			// on aliyun.com
			$this->default = array(
					'datasource' => 'Database/Mysql',
					'persistent' => false,
					'host' => 'r3319arlonlov.mysql.aliyun.com:3306',
					'login' => 'r9574arlonlov',
					'password' => 'r0341de2c',
					'database' => 'r9574arlonlov',
					'prefix' => 'cake_',
					'encoding'=>'utf8',
			);
			$this->master = array(
					'datasource' => 'Database/Mysql',
					'persistent' => false,
					'host' => 'r3319arlonlov.mysql.aliyun.com:3306',
					'login' => 'r9574arlonlov',
					'password' => 'r0341de2c',
					'database' => 'r9574arlonlov',
					'prefix' => 'cake_',
					'encoding'=>'utf8',
			);
		}
		else{ // in localhost
			$this->default = array(
				'datasource' => 'Database/Mysql',
				'persistent' => false,
				//'host' => $_SERVER["dbhost"]? $_SERVER["dbhost"] : 'localhost',
				'host' => '127.0.0.1',
				'port' => '3306',
				'login' => '51daifan',	// jieli
				'password' => 'PGdvFePBenE4TtBb',	//zFY8smWUKcaLrUs5	
                'database' => $_SERVER["dbname"]? $_SERVER["dbname"] : '51daifan',
				'prefix' => 'cake_',
				'encoding'=>'utf8', 
			);		
			$this->master = array(
				'datasource' => 'Database/Mysql',
				'persistent' => false,
				'host' => '127.0.0.1',
				'port' => '3306',
				'login' => '51daifan',
				'password' => 'PGdvFePBenE4TtBb',
				'database' => '51daifan',
				'prefix' => 'cake_',
				'encoding'=>'utf8', 
			);
			// used in dbupdate for new version. compare database diffrence
			$this->olddb = array(
				'datasource' => 'Database/Mysql',
				'persistent' => false,
				'host' => '127.0.0.1',
				'login' => '51daifan',
				'password' => 'PGdvFePBenE4TtBb',
				'database' => 'old_saecms',
				'prefix' => 'cake_',
				'encoding'=>'utf8', 
			);
		}
	}
}

