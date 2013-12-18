<?php

class WeiboUtil{
	
	var $oauth = null;

	function __construct($oauth_token,$oauth_token_secret){
            $this->oauth = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token']);
	}
	
	
	function new_weibo($content,$imgurl='',$videocode='')
	{
//		$o = new WeiboClient( WB_AKEY , WB_SKEY , $oauthkeys['oauth_token'] , $oauthkeys['oauth_token_secret']  );
		$result = '';
		if($imgurl)
		{
			$result = $this->oauth->upload ($content,$imgurl);
		}
		else
		{
			$result = $this->oauth->update($content);
		}
		return $result;  
	}
	
	function delete_weibo($id)
	{
		return $this->oauth->destroy($id);
	}
	
	function friends($uid,$page=false,$count=20)
	{
		$cursor = $page * $count;
		return $this->oauth->friends($cursor,$count,$uid);
	}
	
	function followers($uid,$page=false,$count=20)
	{
		$cursor = $page * $count;
		return $this->oauth->followers($cursor,$count,$uid);
	}
	
	function send_dm($uid_or_name,$text)
	{
		return $this->oauth->send_dm($uid_or_name,$text);
	}
	
	function add_to_favorites($id)
	{
		return $this->oauth->add_to_favorites($id);
	}
	
	function remove_from_favorites($id)
	{
		return $this->oauth->remove_from_favorites($id);
	}
	
	function repost($id,$text)
	{
		return $this->oauth->repost($id,$text);
	}

	function mentions($page,$count)
	{
		return $this->oauth->mentions($page,$count);
	}
	
	function friends_timeline($page=1,$count=20,$sinceid=0,$maxid=0)
	{
		if($page==1 && $count==20)
		{
			return $this->oauth->friends_timeline($page,$count);
		}
		$parameters = array('page'=>$page,'count'=>$count);
		if($sinceid>0)
		{
			$parameters['since_id'] = $sinceid;
		}
		if($maxid>0)
		{
			$parameters['max_id'] = $maxid;
		}
		return $this->oauth->oauth->get("http://api.t.sina.com.cn/statuses/home_timeline.json",$parameters);
	}
	
	function shorturl($url){
		$parameters = array('url'=>$url,'is_short'=>false);
		if(is_array($url))
		{
			$parameters['url'] = implode(',',$url);
			$parameters['is_batch'] = true;
		}
		return $this->oauth->oauth->post("http://api.t.sina.com.cn/shortUrl.json",$parameters);
	}
	
	function user_timeline($page='1',$count='20',$uid_or_name=null)
	{
		return $this->oauth->user_timeline($page,$count,$uid_or_name);
	}
	
	function  comments_timeline($page = 1,$count = 20) 
	{
		return $this->oauth->comments_timeline($page,$count);	
	}
	
	function  get_favorites($page = 1) 
	{
		return $this->oauth->get_favorites($page);	
	}
	
	function  send_comment ($sid, $text, $cid = false) {
		return $this->oauth->send_comment ($sid,$text,$cid);
	}
	
	function request_with_uid($url,$uid)
	{
		//$this->oauth->oauth->get()
		return $this->oauth->request_with_uid($url,$uid);
	}
	
	
	function group_list($uid)
	{
		//http://api.t.sina.com.cn/:user/lists.format
		$parameters = array('listtype'=>1,'cursor'=>-1);
		return $this->oauth->oauth->get("http://api.t.sina.com.cn/".$uid."/lists.json",$parameters);
	}
}