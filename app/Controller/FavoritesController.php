<?php

class FavoritesController extends AppController {

	var $name = 'Favorites';
	
	function add()
	{
		$this->autoRender = false;
		if(empty($this->currentUser) || empty($this->currentUser['id'])){
			$errorinfo = array(
				'error'=>__('Need_Login'),
				'tasks'=>array(
					array(
						'dotype'=> 'callback',
						'callback'=> 'publishController.open_dialog',//'showErrorMessage',//publishController.open_dialog',
						'thisArg'=> 'publishController',
						'callback_args' => array('url'=>Router::url('/users/login'),'options'=>array('title'=>__('Need_Login'))),
					)
				)
			);
			echo json_encode($errorinfo);
			return false;
		}
		
		$modelClass = $this->modelClass;
		$this->data[$modelClass]['published'] = 1;
		$this->data[$modelClass]['deleted'] = 0;
		$hasfavored = $this->{$modelClass}->find('first',array('conditions'=>array(
			'model'=> $this->data[$modelClass]['model'],
			'data_id'=> $this->data[$modelClass]['data_id']
		)));
		if(!empty($hasfavored)){
			$errorinfo = array('error'=>__('Already favored'));
			echo json_encode($errorinfo);
			return false;
		}
		// 无微博id时，直接发起一个带链接的微博，即分享到微博。
		$this->loadModel($this->data[$modelClass]['model']);
		
		$data = $this->{$this->data[$modelClass]['model']}->findById($this->data[$modelClass]['data_id']);
		//$weibo_content = usubstr($data[$this->data[$modelClass]['model']]['title'].'——'.$data[$this->data[$modelClass]['model']]['summary'],0,100);
		//$url = DIANMING_HOST."/".Inflector::tableize($this->data[$modelClass]['model'])."/".date('Ym',strtotime($data[$this->data[$modelClass]['model']]['created']))."/".$this->data[$modelClass]['data_id'].".html";
		//$weibo_content = $weibo_content.$url;
		//$send_status = $this->WeiboUtil->new_weibo($weibo_content);
		$this->{$modelClass}->create();
		$this->data[$modelClass]['creator'] = $this->currentUser['User']['nickname'];
		$this->data[$modelClass]['creator_id'] = $this->currentUser['User']['id'];				
		
		if ($this->{$modelClass}->save($this->data)) {
			$successinfo = array('success'=>__('Favorite success',true),
				'data'=>array(
					'model'=> $this->data[$modelClass]['model'],
					'data_id'=> $this->data[$modelClass]['data_id']
			));			
			$this->StatsDay->addlog($this->data[$modelClass]['model'],$this->data[$modelClass]['data_id'],'favor'); // 统计参与问题次数
			echo json_encode($successinfo);
        	return ;
		}else {
			// 保存发生错误，删除对应的收藏
			echo json_encode($this->{$this->modelClass}->validationErrors);
	        return ;
		}
	}
	
	function delete($id)
	{
		//$msg = $c->remove_from_favorites( $sid );
		$this->read(null,$id);
		$this->WeiboUtil->remove_from_favorites($weibo_id);
	}
}
?>