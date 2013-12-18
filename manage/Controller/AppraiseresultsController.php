<?php

class AppraiseresultsController extends AppController {
    var $name = 'Appraiseresults';
	
    /**
     * 单一评价问题提交，推荐不推荐，投票的单道题提交。不适合于多道题。
     * $type = one时，只提交一个digg选项，如推荐的digg，只返回一维数组，那个选项的选择次数
     * $type 为multi时，提交了多个选项。返回本问题的所有的选项的投票数。二维数组
     */
    function singlesubmit($type='one')
    {
    	$this->autoRender = false;
    	$modelClass = $this->modelClass;
    	$userid = $this->Auth->user('id');
    	if(!empty($_POST))
    	{
    		$this->loadModel('Appraiselog');
    		$this->loadModel('StatsDay');
    		$this->data[$modelClass]['model'] = $_POST['model'];
    		$this->data[$modelClass]['data_id'] = $_POST['data_id'];
    		
    		foreach($_POST['options'] as $qid => $option)
    		{
    			
    			$hasdata = $this->Appraiselog->find('first',array(
    				'conditions'=>array(
    					'model'=> $_POST['model'],
    					'data_id' => $_POST['data_id'],
    					'q_id' => $qid,
    					'creator' => $userid,
    				),
    			));
    			if(!empty($hasdata))
    			{
    				echo json_encode(array('error'=>__('You have already vote this.',true)));
    				return ;
    			}
    			$this->data[$modelClass]['question_id'] = $qid;
    			
    			foreach($option as $option_id => $val)
    			{
    				$this->{$modelClass}->create();
    				$this->data[$modelClass]['option_id'] = $option_id;
    				$hasdata = $this->{$modelClass}->find('first',array(
    					'conditions'=>array(
    						'model'=> $_POST['model'],
    						'data_id' => $_POST['data_id'],
    						'question_id' => $qid,
    						'option_id' => $option_id,
    					),
    				));
    				if(!empty($hasdata))
    				{
    					$hasdata['Appraiseresult']['value']++;
    					$this->{$modelClass}->save($hasdata);
    					if($type=='one')
    					{
    						echo json_encode($hasdata['Appraiseresult']);
    					}
    				}
    				else
    				{
    					$this->data[$modelClass]['value'] = 1;
    					$this->{$modelClass}->save($this->data);
    					if($type=='one')
    					{
    						echo json_encode($this->data['Appraiseresult']);
    					}
    				}
    			}
    			
    			$this->data['Appraiselog']['creator'] = $userid;
    			$this->data['Appraiselog']['q_id'] = $qid;
    			$this->data['Appraiselog']['q_optid'] = $option_id;
    			$this->data['Appraiselog']['model'] = $_POST['model'];
    			$this->data['Appraiselog']['data_id'] = $_POST['data_id'];
    			$this->Appraiselog->save($this->data);
    			
    			$year = date('Y');
				$month = date('m');
				$day = date('d');
				$date = date('Y-m-d');
				$stats_type = 'digg';
				$this->data['StatsDay']['model'] = $_POST['model'];
				$this->data['StatsDay']['stat_type'] = $stats_type;
				$this->data['StatsDay']['data_id'] = $_POST['data_id'];
				$this->data['StatsDay']['year'] = $year;
				$this->data['StatsDay']['month'] = $month;
				$this->data['StatsDay']['day'] = $day;
				$this->data['StatsDay']['date'] = $date;
				
    			if($type=='one')
    			{
    				// 仅单项digg时，才记录选项。多个投票选项时不记录。
    				$this->data['StatsDay']['related'] = $qid.'-'.$option_id;
    			}
				$hasdata = $this->StatsDay->find('first',array(
					'conditions'=>$this->data['StatsDay'],
					));
		//		print_r($hasdata);
				if(empty($hasdata))
				{
					$this->data['StatsDay']['comment_nums'] = 1;
					$this->StatsDay->save($this->data);
				}
				else
				{
					unset($this->data['StatsDay']);
					
					$this->data['StatsDay']['id'] = $hasdata['StatsDay']['id'];
					$this->data['StatsDay']['comment_nums'] = $hasdata['StatsDay']['comment_nums']+1;
					$this->StatsDay->save($this->data);
				}
				if($type=='multi')
    			{
					$hasdata = $this->{$modelClass}->find('all',array(
	    					'conditions'=>array(
	    						'model'=> $_POST['model'],
	    						'data_id' => $_POST['data_id'],
	    						'question_id' => $qid,
	    					),
	    			));
	    			$joined_num =  $this->Appraiselog->find('count',array(
	    				'conditions'=>array(
	    					'model'=> $_POST['model'],
	    					'data_id' => $_POST['data_id'],
	    					'q_id' => $qid,
	    				),
	    			));
	    			$hasdata['total_join'] = $joined_num;
	    			$hasdata['success'] = __('Add success',true);
	    			echo json_encode($hasdata);
    			}
    			
    			//end foreach,循环只进行一次,处理单道题提交的答案。
    			return ;
    		}
    	}
    }
    function load_vote_result($model,$data_id,$qid)
    {
    	$this->autoRender = false;
    	$modelClass = $this->modelClass;
    	$this->loadModel('Appraiselog');
    	
    	$hasdata = $this->{$modelClass}->find('all',array(
	    		'conditions'=>array(
	    			'model'=> $model,
	    			'data_id' => $data_id,
	    			'question_id' => $qid,
	    		),
	    ));
	    $joined_num =  $this->Appraiselog->find('count',array(
	    	'conditions'=>array(
	    		'model'=> $model,
	    		'data_id' => $data_id,
	    		'q_id' => $qid,
	    	),
	    ));
	    $hasdata['total_join'] = $joined_num;
	    $hasdata['success'] = __('Add success',true);
	    echo json_encode($hasdata);
    }
    
    function getdigdata()
    {
    	$this->autoRender = false;
    	//print_r($_GET);
    	$modelClass = $this->modelClass;
    	$data = $this->{$modelClass}->find('all',array(
    					'conditions'=>array(
    						'model'=> $_GET['model'],
    						'data_id' => $_GET['data_id'],
    						'question_id <'=> 5,
    					),
    				));
//    	print_r($data);
    	echo json_encode($data);
    	
    }
}
?>