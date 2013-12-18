<?php

class SinaweibosController extends AppController {

	var $name = 'Sinaweibos';
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->autoRender = false;
	}
	/**
	 * 发新微博
	 */
	function new_weibo(){
		
		$send_status = $this->WeiboUtil->new_weibo($weibo_content);
		//转发
	}
	
	/**
	 * 转发
	 */
	function repost()
	{
		//print_r($_POST);exit;
		if($_POST['weibocontent']=='顺便说点什么吧...') $_POST['weibocontent']='';
		$repost_status = $this->WeiboUtil->repost($_POST['weiboid'],$_POST['weibocontent']);
		echo json_encode($repost_status);
	}
	
	/**
	 * 发评论
	 */
	function send_comment($weiboid)
	{
		$comment_status = $this->WeiboUtil->send_comment ($weiboid,$weibo_content) ;//, [int $cid = false]
	}
	
/**
	 * 发评论
	 */
	function glist($listid='')
	{
		print_r($this->currentUser);
		$lists = $this->WeiboUtil->group_list ($this->currentUser['User']['sina_uid']) ;//, [int $cid = false]
		print_r($lists);
	}
}