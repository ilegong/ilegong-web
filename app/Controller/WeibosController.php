<?php

class WeibosController extends AppController {

	var $name = 'Weibos';

	function admin_recover(){
//		print_r($this->Weibo->_schema);
		$this->Weibo->recover('parent');
		echo "recover over";
		exit;
	}
	
	function publish()
	{
		$modelClass = $this->modelClass;
		if(empty($this->currentUser['User']))
		{
			echo json_encode(array('error'=>'用户信息错误，请重新登录！'));
		    return ;
		}
		if (!empty($this->data)) {
			
			$this->data[$modelClass]['published'] = 0;
			$this->data[$modelClass]['deleted'] = 0;
			
			$this->data[$modelClass]['creator'] = $this->currentUser['User']['nickname'];
			$this->data[$modelClass]['creator_id'] = $this->currentUser['User']['sina_uid'];
			$this->data[$modelClass]['user_img'] = $this->currentUser['User']['image'];
			
			$this->autoRender = false;
			$this->{$modelClass}->create();
			
			$lase_post_weiboid = $history_content = '';
			
			if(!empty($this->data[$modelClass]['parent_id']))
			{
				 // 回答的微博的引用，都应用源问题，不是引用回答。 把之前的回答跟在回答内容的后面。
		        // @xx:一鼓作气 @yy:气绝云天
				$related_weibo = $this->Weibo->getWeiboByWid($this->data[$modelClass]['parent_id'],1,5);
				$related_weibolist = $related_weibo['parentlist'];
//				print_r($related_weibolist);exit;
				$current_weibo =  $related_weibo['current'];
				if($lase_post_weiboid=='') $lase_post_weiboid = $current_weibo['Weibo']['weibo_id'];
				
				foreach($related_weibolist as $val)
				{
					//$val['Weibo']['name'] = preg_replace('/[“|”]/','"',$val['Weibo']['name']);
					$history_content.=' //@'.$val['Weibo']['creator'].': '.usubstr($val['Weibo']['name'],0,15);
					
				}
			}			
			$this->data[$modelClass]['content'] = $this->data[$modelClass]['name'].$history_content;
			
			if ($this->{$modelClass}->save($this->data)) {
				
		        
		        $last_insert_id = $this->{$modelClass}->getLastInsertID();
		        $question_id = $this->data[$modelClass]['data_id'];
		        
		        $successinfo = array('success'=>__('Add success',true),'question_id'=>$question_id,'weibo_id'=>$last_insert_id);
		        
		        $this->loadModel('Question');
		        $question_info = $this->Question->read(null,$question_id);
		        
		        $quote_weiboid = $question_info['Question']['weibo_id'];
		        if($history_content)
		        {
					$weibo_content = usubstr( $this->data[$modelClass]['name'],0,15).$history_content;
		        }
		        else
		        {
		        	$weibo_content = $this->data[$modelClass]['name'];		        
		        }
		        
				$weibo_content .= " =>点击链接参与 ".DIANMING_HOST."/questions/".$question_id."/$last_insert_id.html";
//				echo $weibo_content;
				
				if($quote_weiboid)
				{
					// 发出转发微博
					$repost_status = $this->WeiboUtil->repost($quote_weiboid,$weibo_content);
					// 给问题发出评论
					$comment_status = $this->WeiboUtil->send_comment ($quote_weiboid, $this->data[$modelClass]['name']) ;//, [int $cid = false]
					// 如何发出邀请的人，收到回答的评论,
					
					// 给点过来的回答或邀请发出评论,接口调用错误，接口不能两次发出同样的内容，增加前缀“回答：”。
	//				print_r($comment_status);
					if($lase_post_weiboid)
					{
						$comment1_status = $this->WeiboUtil->send_comment ($lase_post_weiboid, '回答: '.$this->data[$modelClass]['name']) ;//, [int $cid = false]
						//print_r($comment1_status);
					}
					
					if(isset($repost_status['id']))
					{
	//					$this->data[$modelClass]['id'] = $last_insert_id;
	//					$this->data[$modelClass]['weibo_id'] = $repost_status['id'];
	//					$this->{$modelClass}->save($this->data);
						$this->{$modelClass}->update( array('weibo_id'=> $repost_status['id'],'published'=>1), array('id'=>$last_insert_id));
					}
				}
		        $this->StatsDay->addlog($this->data[$modelClass]['model'],$this->data[$modelClass]['data_id'],'comment'); // 统计参与问题次数
					        
			} else {
				echo json_encode($this->{$this->modelClass}->validationErrors);
		        return ;
			}
			echo json_encode($successinfo);
        	return ;
		}
	}


}
?>