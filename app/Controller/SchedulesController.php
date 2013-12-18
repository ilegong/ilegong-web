<?php
class SchedulesController extends AppController{
	
	var $name = 'Schedules';
	//var $layout = 'schedules_layout';
	
	function add(){
		
		if(!empty($this->data)){
			print_r($this->data);
			exit;
		}
		else{
			
			$this->data['Schedule']['begintime']= date('Y-m-d');	
			$this->data['Schedule']['day_times']=1;		
			$week_name = array('week_sun','week_mon','week_tues','week_wen','week_thur','week_fri','week_sat');
			$this->data['Schedule'][$week_name[date('w')]] = 1;
			$this->data['Schedule']['recurrence_type']='week';
			$this->data['Schedule']['month_recurr_type']='day';
			$this->data['Schedule']['year_recurr_type']='day';
			$this->data['Schedule']['year_calendric']='Solar';	 #Lunar
			$this->data['Schedule']['year_day'] = date('d');
			$this->data['Schedule']['year_week'] = date('w');
			
			$this->set('month_recurr_type',$this->data['Schedule']['month_recurr_type']);
			$this->set('recurrence_type',$this->data['Schedule']['recurrence_type']);
			$this->set('year_recurr_type',$this->data['Schedule']['year_recurr_type']);
		}		
	}
}

