<?php

class QuestionsController extends AppController {

	var $name = 'Questions';
	
	function beforeFilter()
	{
		$this->set('top_category_id',2);
		parent::beforeFilter();
	}
	function admin_recover(){
		$this->Menu->recover('parent');	
	}
	function index(){
		$this->layout = 'ajax'; // 表情
	}
	
	function publish()
	{
		$modelClass = $this->modelClass;
		$cache_key = 'weibo_faces';
		$faces = Cache::read($cache_key); 
		if ($faces === false) 
		{
			$this->loadModel('Face');
			
			$faces = $this->Face->find('all',array(
				'conditions'=>array(),
				'fields' =>array('Face.name','Face.src','Face.value'),
			));
			Cache::write($cache_key,$faces);
		}
		if (!empty($this->data)) {
			
			$this->data[$modelClass]['published'] = 0;
			$this->data[$modelClass]['deleted'] = 0;
			
			$this->data[$modelClass]['creator'] = $this->currentUser['User']['nickname'];
			$this->data[$modelClass]['creator_id'] = $this->currentUser['User']['sina_uid'];
			$this->data[$modelClass]['user_img'] = $this->currentUser['User']['image'];
			$question_content = $this->data[$modelClass]['name'];
			//<img type="face" title="万圣节" src="http://timg.sjs.sinajs.cn/t3/style/images/common/face/ext/normal/73/nanguatou2_org.gif">
			foreach($faces as $key=>$value)
			{
				$this->data[$modelClass]['name']=str_replace($value['Face']['value'],'<img type="face" title="'.$value['Face']['name'].'" src="'.$value['Face']['src'].'">',$this->data[$modelClass]['name']);
			}
			
			$this->autoRender = false;			
			$this->{$modelClass}->create();
			if ($this->{$modelClass}->save($this->data)) {
				$last_insert_id = $this->{$modelClass}->getLastInsertID();
				$weibo_content = "我发起了一个#点名问题#：“ ";
				$weibo_content .= usubstr($question_content,0,80);
				$weibo_content .= " ”，快来回答吧，点击链接回答".DIANMING_HOST."/questions/$last_insert_id.html";
				
				$send_status = $this->WeiboUtil->new_weibo($weibo_content);
				
				if ($send_status === false || $send_status === null)
				{
					echo json_encode( array('error'=> '微博接口返回失败。'));
					return;
				}
				if(empty($send_status['id']))
				{
					$this->{$modelClass}->deleteAll(array('id'=>$last_insert_id)); // 删除信息，提示错误
					//echo json_encode($send_status['error']);
					echo json_encode( array( 'name'=> __($send_status['error'],true)) );
					return ;
				}
				$this->data[$modelClass]['id'] = $last_insert_id;				
				$this->data[$modelClass]['weibo_id'] = $send_status['id'];	
				// app_model中的update			
				$this->{$modelClass}->update(
					array('weibo_id'=>$send_status['id'],'published'=>1),
					array('id'=>$last_insert_id)
				);
				$successinfo = array('success'=>__('Add success',true),'question_id'=> $last_insert_id);
				echo json_encode($successinfo);
        		return ;
        		
			} else {
				// 保存发生错误，删除对应的微博
				// $this->WeiboUtil->delete_weibo($send_status['id']);
				echo json_encode($this->{$this->modelClass}->validationErrors);
		        return ;
			}
		}
		else
		{
			$this->__loadFormValues($this->modelClass);
		}
	}
	
	function invite($weibo_id,$type='friends',$page=0,$output_type='',$count=18){
		
		if(empty($_POST))
		{
			$uid = $this->currentUser['User']['sina_uid'];
	//		print_r($_POST);
			if($type=='newuser')
			{
				$cache_key = 'user_invite_list_'.$type.'_'.$count.'_'.$page; // 取本站会员时，不加uid到缓存的key中
			}
			else
			{
				$cache_key = 'user_invite_list_'.$uid.'_'.$type.'_'.$count.'_'.$page;
			}
			$friends = Cache::read($cache_key); 
			if ($friends === false) 
			{
			
				$friends = array();
				
				if($type=='friends')
				{
					$friends  = $this->WeiboUtil->friends($uid,$page,$count);
				}
				elseif($type=='followers')
				{
					$friends  = $this->WeiboUtil->followers($uid,$page,$count);
				}
				
				elseif($type=='comments')
				{
					$count=50;
					$tempfriends  = $this->WeiboUtil->comments_timeline($page,$count);
					
					foreach($tempfriends as $f)
					{
						if($f['user']['id']!=$this->currentUser['User']['sina_uid'])
						{
							if(empty($friends[$f['user']['id']]))
							{
								$friends[$f['user']['id']]=$f['user'];
							}
						}
					}
	//				print_r($friends);
				}
				elseif($type=='mentions')
				{
					$tempfriends  = $this->WeiboUtil->mentions($page,$count);
					foreach($tempfriends as $f)
					{
						if($f['user']['id']!=$this->currentUser['User']['sina_uid'])
						{
							if(empty($friends[$f['user']['id']]))
							{
								$friends[$f['user']['id']]=$f['user'];
							}
						}
					}
				}
				elseif($type=='newuser')
				{
					$this->loadModel('User');
					$userlist = $this->User->userlist($page,$count);
					foreach($userlist as $user)
					{
		 				$user['User']['id'] = $user['User']['sina_uid'];
		 				$user['User']['name'] = $user['User']['screen_name'];
		 				$user['User']['profile_image_url'] = $user['User']['image'];
						if($user['User']['sex']==1)
						{
							$user['User']['gender'] = 'm'; //男
						}
						else
						{
							$user['User']['gender'] = 'f'; //女
						}
		 				
						$friends[]= $user['User'];
					}
					//print_r($userlist);exit;
				}
				
				if(isset($friends['users']))
				{
					$friends = $friends['users'] ; 
				}
				Cache::write($cache_key,$friends);
			}
	//		else
	//		{
	//			
	//			echo '==========use cahce';
	//		}
			if($output_type=='json')
			{
				$this->autoRender = false;	
				echo json_encode($friends);
				return false;
			}
			
			$lastpage = $page-1;
			
			if(count($friends)!=$count) $nextpage=0; else $nextpage=$page+1;
			
			$this->set('weibo_id',$weibo_id);
			$this->set('type',$type);
			$this->set('page',$page);
			$this->set('lastpage',$lastpage);
			$this->set('nextpage',$nextpage);
			$this->set('friends',$friends);
			
			if (isset($friends['error_code']) && isset($friends['error'])){
				__($friends['error']);
				return false;
			}
		}
		else
		{
			if(!empty($_POST['data']['inviteusers']))
			{
				//邀请用户参与回答
				$inviteusers = '';
				$i=0;
				foreach($_POST['data']['inviteusers'] as $key=>$value)
				{
					$i++;
					$inviteusers .= '@'.$value;
					if($i==12)
					{
						break; // 限制最多转发12个
					}
				}
				//$inviteusers = substr($inviteusers,0,-1);
				$question = $this->Question->findById($_POST['weibo_id']);
				
				
				// 邀请记录设为未发布状态的微博，在后台隐藏
				$this->loadModel('Weibo');
				$this->Weibo->create();
				$this->data['Weibo']['published'] = 0;
				$this->data['Weibo']['deleted'] = 0;
				$this->data['Weibo']['model'] = 'Question';
				$this->data['Weibo']['data_id'] = $question['Question']['id'];
//				$this->data['Weibo']['name'] = $weibo_content;
				$this->data['Weibo']['creator'] = $this->currentUser['User']['nickname'];
				$this->data['Weibo']['creator_id'] = $this->currentUser['User']['sina_uid'];
				$this->data['Weibo']['user_img'] = $this->currentUser['User']['image'];
				
				if ($this->Weibo->save($this->data)) {
			        $last_insert_id = $this->Weibo->getLastInsertID();
			        $weibo_content = '我#点名#邀请'.$inviteusers.' 来回答问题。 点击链接回答'.DIANMING_HOST.'/questions/'.$question['Question']['id'].'/'.$last_insert_id.'.html';
			        // 转发问题微博
					$repost_status = $this->WeiboUtil->repost($question['Question']['weibo_id'],$weibo_content);
					//print_r($repost_status);
					
					if(empty($repost_status['id']))
					{
						$this->Weibo->deleteAll(array('id'=>$last_insert_id)); // 删除信息，提示错误
						echo json_encode( array( 'name'=> __($repost_status['error'],true)) );
						return ;
					}
					
					$this->data['Weibo']['id'] = $last_insert_id;				
					$this->data['Weibo']['weibo_id'] = $repost_status['id'];	
					// app_model中的update			
					$this->Weibo->update(
						array('weibo_id'=>$repost_status['id'],'name'=>$weibo_content,'content'=>$weibo_content),
						array('id'=>$last_insert_id)
					);
					
					$successinfo = array('success'=>__('Add success',true),'weibo_id'=> $question['Question']['weibo_id']);
					echo json_encode($successinfo);
					
					// 保存用户邀请记录
					$this->loadModel('InviteGroup');
					$this->data['InviteGroup']['name'] = $inviteusers;
					$this->data['InviteGroup']['content'] = $inviteusers;
					$this->data['InviteGroup']['creator'] = $this->currentUser['User']['id'];					
					$this->InviteGroup->save($this->data);
				}
				//连续调用接口发送错误，将邀请微博内容放入队列中，过一会再发送。
				// 邀请的微博内容，不记录缓存					
//				$this->loadModel('TransQueue');
//				$this->data['TransQueue']['name'] = $weibo_content;
//				$this->data['TransQueue']['weibo_id'] = $_POST['weibo_id'];
//				$this->data['TransQueue']['creator'] = $this->currentUser['User']['sina_uid'];
//				$this->TransQueue->save($this->data);
				
				
				
				

				
			}
			exit;
		}
		
	}
	
	function view($question_id,$weibo_id='')
	{
		$question = $this->Question->read(null,$question_id);
		
		$this->pageTitle = $question['Question']['name'];
		
		$this->loadModel('Weibo');
		$related_weibo = array();
		if($weibo_id)
		{
			// 相关回答，指树上的，获取父级类和下级类
			$related_weibo = $this->Weibo->getWeiboByWid($weibo_id);
		}
		
		$weibos_list = $this->Weibo->getWeiboByQid($question_id);
		
		$this->set('related_weibo',$related_weibo);
		$this->set('question',$question);
		$this->set('weibo_id',$weibo_id);
		$this->set('weibos_list',$weibos_list);
		$this->set('data_id',$question_id);
		$this->set('parent_id',$weibo_id);
		
		$cacke_key = 'same_join_question_list_'.$question_id;
		$same_join_question_list = Cache::read($cacke_key); 
		
//		if ($same_join_question_list === false) 
		{
			$users =  $this->Weibo->find('all',array(
				'fields'=>array('User.id','User.sina_uid','User.screen_name','User.image','User.sina_domain'),
				'conditions' => array(
					'Weibo.data_id' => $question_id,
					'Weibo.model'=>'Question',
					'Weibo.published' => 1,
				),
				'joins'=>array(array(
					'table'=> Inflector::tableize('User'),
					'alias'=>'User',
					'type' => 'inner',
					'conditions'=>array('Weibo.creator_id=User.sina_uid'),
				)),
				'limit'=>10,
				'group' => array('User.id'), 
				'order'=>'Weibo.id desc'
			));
			
			// 在显示时，排除当前用户
			$uids = array();
			foreach($users as $user){
				$uids[]= $user['User']['sina_uid'];			
			}
			$same_join_question_list = array();
			$same_join_question_list['User'] = $users;
			
			$same_join_question_list['Question'] = $this->Weibo->find('all',array(
				'fields'=>array('count(Question.id) as qnum','Question.*','User.*'),
				'conditions' => array(
					'Weibo.creator_id' => $uids,
					'Weibo.published' => 1,
				),
				'joins'=>array(array(
					'table' => Inflector::tableize('Question'),
					'alias' =>'Question',
					'type' => 'inner',
					'conditions' => array('Question.id=Weibo.data_id','Question.id !='=>$question_id),
				),
				array(
					'table'=> Inflector::tableize('User'),
					'alias'=>'User',
					'type' => 'inner',
					'conditions'=>array('Question.creator_id =User.sina_uid'),
				)),
				'limit'=>10,
				'order'=>'qnum desc,Question.id desc',
				'group' => array('Question.id'), 
			));
			Cache::write($cacke_key,$same_join_question_list); 
		}
		
		$this->set('same_join_question_list',$same_join_question_list['Question']);
		$this->set('same_join_user_list',$same_join_question_list['User']);
		/*
		
		$weibolist = $this->Weibo->find('all',array(
			'conditions' => array(
				'data_id'=>$id,
				'model'=>'Question',	
			),
		));
		print_r($weibolist);*/
		
	}
}
?>