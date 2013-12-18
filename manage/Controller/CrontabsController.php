<?php

class CrontabsController extends AppController {

    var $name = 'Crontabs';
	var $autoRender = false;
    function admin_index() {
        $this->pageTitle = __('Crontab', true);
       
    }
    
    function admin_sendTransWeibo()
    {
    	// 每两分钟执行一次，发送一分钟以前进入队列的数据。
    	
    	$this->loadModel('TransQueue');
    	$list = $this->TransQueue->find('all',array(
    		'conditions'=>array(
	    		'TransQueue.created <'=>date('Y-m-d H:i:s',time()-60), // 队列处理一分以前的记录 
	    		'TransQueue.published'=>0, 
	    	),
	    	'fields'=>array('TransQueue.*','User.*'),
	    	'joins'=>array(
	    		array(
					'table' => Inflector::tableize('User'),
					'alias' => 'User',  
					'type' => 'inner',
					'conditions' => array("User.sina_uid = TransQueue.creator"),
				),
	    	),
    	));    	
    	
    	foreach($list as $val)
    	{
    		if(!empty($val['User']['sina_token']) && !empty($val['User']['sina_token_secret']))
			{
				$weibo_util = new WeiboUtil($val['User']['sina_token'],$val['User']['sina_token_secret']);
				
				$status = $weibo_util->repost($val['TransQueue']['weibo_id'],$val['TransQueue']['name']);
				
				if(!empty($status['id']))
				{
					echo $val['User']['sina_uid']."--".$val['TransQueue']['weibo_id'].':'.$status['id']."\r\n";
					$this->TransQueue->delete($val['TransQueue']['id']);
				}
				else
				{
					print_r($status);
				}
				unset($weibo_util);
//				print_r($status);
//				sleep(1);此处不sleep防止，造成整个对立发送时间过长，两次cron的超过了时间间隔，没执行完的下次cron会重复执行。
			}
    	}
//    	exit;
    }
    
	function admin_updateStats($modelname='Article') {
    	$this->pageTitle = __('Crontab', true);
    	$this->loadModel('StatsWeek');
    	$this->loadModel('StatsDay');
    	$this->loadModel('StatsMonth');
    	
    	$useDbConfig = $this->StatsDay->useDbConfig;
		$dbconfig = new DATABASE_CONFIG();
		$day_table = $dbconfig->{$useDbConfig}['prefix'].'stats_days';
		$week_table = $dbconfig->{$useDbConfig}['prefix'].'stats_weeks';
		$month_table = $dbconfig->{$useDbConfig}['prefix'].'stats_months';
		// 为周一时，更新上周的统计
		if(date('w')==1)
		{
			$timestamp = strtotime('-7 day');
			$week = date('W',$timestamp); 
			$year = date('Y',$timestamp);
			$weekn = date('w',$timestamp); // 星期几，1-7 
			//$weekn +=1; // 从周日开始算一周的第一天。
			if(!$weekn) $weekn =7; //从周一开始算一周的第一天。
			$startdate = date('Y-m-d',strtotime('-'.$weekn.' day',$timestamp)); // 减去几天，上周日的日期
			$enddate = date('Y-m-d',strtotime('+'.(8-$weekn).' day',$timestamp)); // 加上几天，下周一的日期
	    	// 更新本周的统计数据
	    	$sql = "select $year as `year`,$week as `week`,sum(`view_nums`) as `view_nums`,sum(`favor_nums`) as `favor_nums`,sum(`comment_nums`) as `comment_nums`,`model`,`data_id`,`related` from `$day_table` where `model`='$modelname' and `date`>'$startdate' and `date`<'$enddate' group by `data_id`,`related`";
	    	$sqlweek ="replace into `$week_table` (`year`,`week`,`view_nums`,`favor_nums`,`comment_nums`,`model`,`data_id`,`related`) $sql";
	    	$this->StatsWeek->query($sqlweek);
		}
		// 更新本周的统计
		$timestamp = time();
		$week = date('W',$timestamp); // 1年的第几周
		$year = date('Y',$timestamp);
		$weekn = date('w',$timestamp); // 星期几，1-7 
		//$weekn +=1; // 从周日开始算一周的第一天。
		if(!$weekn) $weekn =7; //从周一开始算一周的第一天。
		$startdate = date('Y-m-d',strtotime('-'.$weekn.' day',$timestamp)); // 减去几天，上周日的日期
		$enddate = date('Y-m-d',strtotime('+'.(8-$weekn).' day',$timestamp)); // 加上几天，下周一的日期
    	// 更新本周的统计数据
    	$sql = "select $year as `year`,$week as `week`,sum(`view_nums`) as `view_nums`,sum(`favor_nums`) as `favor_nums`,sum(`comment_nums`) as `comment_nums`,`model`,`data_id`,`related` from `$day_table` where `model`='$modelname' and `date`>'$startdate' and `date`<'$enddate' group by `data_id`,`related`";
    	$sqlweek ="replace into `$week_table` (`year`,`week`,`view_nums`,`favor_nums`,`comment_nums`,`model`,`data_id`,`related`) $sql";
    	$this->StatsWeek->query($sqlweek);
    	echo __('Done',true);
    	
    	// 为1号时，更新上月的统计
		if(date('j')==1)
		{
			$timestamp = strtotime('-30 day');
			$month = date('m',$timestamp);
			$year = date('Y',$timestamp);
			$dayn = date('j'); // 月的第几天
			$year_month = date('Y-m',$timestamp);
			$startdate = date('Y-m-d',strtotime('-'.($dayn).' day',$timestamp)); // 减去几天，上月底
			$enddate = date('Y-m-',strtotime('+1 month',$timestamp)).'01'; // 下月1号
	    	// 更新本月的统计数据
	    	$sql = "select $year as `year`,$month as `month`,'$year_month' as `year_month`,sum(`view_nums`) as `view_nums`,sum(`favor_nums`) as `favor_nums`,sum(`comment_nums`) as `comment_nums`,`model`,`data_id`,`related` from `$day_table` where `model`='$modelname' and `date`>'$startdate' and `date`<'$enddate' group by `data_id`,`related`";
	    	$sqlmonth ="replace into `$month_table` (`year`,`month`,`year_month`,`view_nums`,`favor_nums`,`comment_nums`,`model`,`data_id`,`related`) $sql";
	//    	echo $sqlmonth;exit;
	    	$this->StatsMonth->query($sqlmonth);
		}
		// 更新本月统计
		$timestamp = time();
		$month = date('m',$timestamp);
		$year = date('Y',$timestamp);
		$dayn = date('j'); // 月的第几天
		$year_month = date('Y-m',$timestamp);
		$startdate = date('Y-m-d',strtotime('-'.($dayn).' day',$timestamp)); // 减去几天，上月底
		$enddate = date('Y-m-',strtotime('+1 month',$timestamp)).'01'; // 下月1号
    	// 更新本月的统计数据
    	$sql = "select $year as `year`,$month as `month`,'$year_month' as `year_month`,sum(`view_nums`) as `view_nums`,sum(`favor_nums`) as `favor_nums`,sum(`comment_nums`) as `comment_nums`,`model`,`data_id`,`related` from `$day_table` where `model`='$modelname' and `date`>'$startdate' and `date`<'$enddate' group by `data_id`,`related`";
    	$sqlmonth ="replace into `$month_table` (`year`,`month`,`year_month`,`view_nums`,`favor_nums`,`comment_nums`,`model`,`data_id`,`related`) $sql";
//    	echo $sqlmonth;exit;
    	$this->StatsMonth->query($sqlmonth);
    	echo __('Done',true);
    }
	
	function admin_updateModelNums($modelname='Article') {
    	$this->pageTitle = __('Crontab', true);
    	$this->loadModel('StatsDay');
    	$this->loadModel($modelname);
    	
		$timestamp = strtotime('-1 hour');
		$month = date('m',$timestamp); 
		$year = date('Y',$timestamp);
		$day = date('d',$timestamp); // 星期几，1-7 
		$stats = $this->StatsDay->find('all',array('conditions'=>
			array('year'=>$year,'month'=>$month,'day'=>$day,'model'=>$modelname)
		));
    	foreach($stats as $stat)
    	{
    		if($modelname=='Article')
    		{
    			$updates = array(
    				'views_count'=>'views_count+'.$stat['StatsDay']['view_nums'],
    				'comment_count'=> 'comment_count+'.$stat['StatsDay']['comment_nums'],
    				'favor_nums'=> 'favor_nums+'.$stat['StatsDay']['favor_nums'],
    			);
    		}
    		else
    		{
    			$updates = array(
    				'view_nums'=>'view_nums+'.$stat['StatsDay']['view_nums'],
    				'comment_nums'=> 'comment_nums+'.$stat['StatsDay']['comment_nums'],
    				'favor_nums'=> 'favor_nums+'.$stat['StatsDay']['favor_nums'],
    			);
    		}
    		$this->{$modelname}->update(
    			$updates,
    			array('id'=>$stat['StatsDay']['data_id'])
    		);
    		
    	}
		
    	echo __('Done',true);
    	
    	
    }

}
?>