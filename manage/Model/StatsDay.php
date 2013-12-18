<?php
class StatsDay extends AppModel { 
       var $name = 'StatsDay';
       
       
       function addlog($model,$data_id,$stats_type='view')
       {
	        $year = date('Y');
			$month = date('m');
			$day = date('d');
			$this->data['StatsDay']['model'] = $model;
//			$this->data['StatsDay']['stat_type'] = $stats_type;
			$this->data['StatsDay']['data_id'] = $data_id;
			$this->data['StatsDay']['date'] = date('Y-m-d');
			$this->data['StatsDay']['year'] = $year;
			$this->data['StatsDay']['month'] = $month;
			$this->data['StatsDay']['day'] = $day;
			
			$hasdata = $this->find('first',array(
				'conditions'=>$this->data['StatsDay'],
				));
			$num_fields = $stats_type.'_nums';
			if(empty($hasdata))
			{
				$this->data['StatsDay'][$num_fields] = 1;
				$this->save($this->data);
			}
			else
			{
				$this->update(
					array($num_fields=>$hasdata['StatsDay'][$num_fields]+1),
					array('id'=>$hasdata['StatsDay']['id'])
				);
			}
       }
}