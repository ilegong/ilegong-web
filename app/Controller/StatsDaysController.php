<?php
class StatsDaysController extends AppController {
	
	var $name = 'StatsDays';
	
	function index()
	{
//		echo '====StatsDays===';exit;
	}
	
	function getdata()
    {
    	$this->autoRender = false;
    	//print_r($_GET);
    	$year = date('Y');  $month=date('m');  $day=date('d'); 
    	$modelClass = $this->modelClass;
    	$data = $this->{$modelClass}->find('all',array(
    					'conditions'=>array(
    						'model'=> $_GET['model'],
    						'data_id' => $_GET['data_id'], //data_id为传入的数组
    						'year'=>$year,
    						'month'=>$month,
    						'day'=>$day,
    					),
    				));
//    	print_r($data);
    	echo json_encode($data);    	
    }
    
	function numlog($model,$data_id,$stats_type='view')
	{
		$this->autoRender = false;
		$this->StatsDay->addlog($model,$data_id,$stats_type);		
	}
}