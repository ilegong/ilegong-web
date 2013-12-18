<?php
/*
 * 数据库迁移，从一个数据库转换结构处理进入第二个数据库
 * user: http://www.x.com/admin/transfers/transferuser
 * blogclass: http://www.x.com/admin/transfers/transferclass
 * blog: http://www.x.com/admin/transfers/transfer
 */
require_once ROOT.'/cake/libs/debugger.php';

class TransfersController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Transfers';
    
    
    function _listSourse($dbConfig)
    {
		$params = func_get_args();
		$db =& ConnectionManager::getDataSource($dbConfig);
		return call_user_func_array(array(&$db, 'listSources'), $params);
	}
	
	function beforeFilter() {
		set_time_limit(0);
		parent::beforeFilter();
	}
	
	function admin_transferuser($page=1)
	{
		//$tables = $this->_listSourse('mssql');	
		//print_r($tables);
		//$this->loadModel('');
		$srcmodel = 'huiyuan';
		$this->{$srcmodel} = new AppModel(array(
						'name' => $srcmodel,
						'table' => $srcmodel,
						'ds' => 'mssql'
					));
		
		//print_r($result);
		$target1 = 'uchome_member';
		$this->{$target1} = new AppModel(array(
						'name' => $target1,
						'table' => $target1,
						'ds' => 'discuz'
					));
		$this->{$target1}->primaryKey = 'uid';
		
		$target2 = 'uc_members';
		$this->{$target2} = new AppModel(array(
						'name' => $target2,
						'table' => $target2,
						'ds' => 'discuz'
					));
		$this->{$target2}->primaryKey = 'uid';
		
		$target3 = 'uchome_space';
		$this->{$target3} = new AppModel(array(
						'name' => $target3,
						'table' => $target3,
						'ds' => 'discuz'
					));
		$this->{$target3}->primaryKey = 'uid';
		
		$target4 = 'uchome_spacefield';
		$this->{$target4} = new AppModel(array(
						'name' => $target4,
						'table' => $target4,
						'ds' => 'discuz'
					));
		$this->{$target4}->primaryKey = 'uid';
		
		//$page = 20;
		do
		{
			//$page++;
			$result = $this->{$srcmodel}->find('all', array('limit' => 100,'fields' => array('*'),'page'=>$page, 'order' => $srcmodel.'.id ASC'));
			echo "start page:$page per 100/p<br/>";
			if(empty($result))
			{
				break;	
			}
			foreach($result  as $value)
			{
				$item = $value['huiyuan'];
				$item = $this->_gbk2utf8($item);
				$new_uid = $item['id']+10000;
				$user = $this->{$target1}->findByUid($new_uid);
				if($user[$target1]['uid']>0)
				{
					echo $user[$target1]['uid']." continue<br/>";
					continue;
					//print_r($user[$target1]);
				}
				
				// uchome会员
				$this->{$target1}->create();
				$this->data[$target1]['uid'] = $new_uid;
				$this->data[$target1]['username'] = $item['huiyuan'];
				$this->data[$target1]['password'] = $item['password'];			
				$this->{$target1}->save($this->data);
				
				// uc会员
				$this->{$target2}->create();
				$this->data[$target2]['uid'] = $new_uid;
				$this->data[$target2]['username'] = $item['huiyuan'];
				$this->data[$target2]['password'] = $item['password'];	
				$this->data[$target2]['email'] = $item['mail'];
				$this->data[$target2]['regip'] = $item['ip'];
				$this->data[$target2]['regdate'] = strtotime($item['sj']);		
				$this->{$target2}->save($this->data);
				
				// uchome_space
				$this->{$target3}->create();
				$this->data[$target3]['uid'] = $new_uid;
				$this->data[$target3]['username'] = $item['huiyuan'];
				if($item['name'])
				{
					$this->data[$target3]['name'] = $item['name'];
					$this->data[$target3]['namestatus'] = '1';
				}
				$this->data[$target3]['viewnum'] = $item['js'];			
				$this->data[$target3]['groupid'] = '5';	
				$this->data[$target3]['credit'] = '25';	
				$this->data[$target3]['experience'] = '15';	
				$this->data[$target3]['regip'] = $item['ip'];
				$this->data[$target3]['dateline'] = strtotime($item['sj']);	
				$this->{$target3}->save($this->data);
				
				// uchome_spacefield
				$this->{$target4}->create();
				$this->data[$target4]['uid'] = $new_uid;
				if($item['xb']=='True')
				{
					$this->data[$target4]['sex'] = '1';	
				}
				else
				{
					$this->data[$target4]['sex'] = '0';	
				}
				$this->data[$target4]['mobile'] = $item['tel'];	
				$this->data[$target4]['note'] = $item['txt'];
				$this->data[$target4]['spacenote'] = $item['txt'];			
				$this->data[$target4]['email'] = $item['mail'];
				$this->{$target4}->save($this->data);
			}
			
			echo "page $page over goto next\n\n";
			Debugger::log("page $page over goto next\n\n");
			$page++;
			//echo "<meta http-equiv=\"refresh\" content=\"3; url=/admin/transfers/transferuser/$page\" />";
		}while(1);
		echo 'over';
		exit;
	}
	
		
	function admin_transferclass()
	{
		//$tables = $this->_listSourse('mssql');	
		//print_r($tables);
		//$this->loadModel('');
		$srcmodel = 'blog_lb';
		$this->{$srcmodel} = new AppModel(array(
						'name' => $srcmodel,
						'table' => $srcmodel,
						'ds' => 'mssql'
					));
			$page=0;
		$members = 'uc_members';
		$this->{$members} = new AppModel(array(
						'name' => $members,
						'table' => $members,
						'ds' => 'discuz'
					));
					
		//print_r($result);
		$target1 = 'uchome_class';
		$this->{$target1} = new AppModel(array(
						'name' => $target1,
						'table' => $target1,
						'ds' => 'discuz'
					));
		$this->{$target1}->primaryKey = 'classid';
		
		$totalnum = 	$this->{$srcmodel}->find('count');		
		//echo $totalnum;exit;

			
		$result = $this->{$srcmodel}->find('all');
		//print_r($result);
		foreach($result  as $value)
		{
			//print_r($value);
			$blog = $value[$srcmodel];
			$blog = $this->_gbk2utf8($blog);
			if($blog['huiyuan'])
			{
				// 回复
				$this->{$target1}->create();
				$this->data[$target1]['classid'] = $blog['lbid']+10000; // 博客类别id+10000
				$user = $this->{$members}->findByUsername($blog['huiyuan']);
				//echo $members;print_r($user);exit;
				$this->data[$target1]['uid'] = $user[$members]['uid'];
				
				$this->data[$target1]['classname'] = $blog['lbmc'];
				$this->data[$target1]['dateline'] = time();
				$this->{$target1}->save($this->data);
				continue;
			}
			
		}
		
		echo 'over';
	}
	
	function admin_transferforums()
	{
		//$tables = $this->_listSourse('mssql');	
		//print_r($tables);
		//$this->loadModel('');
		$srcmodel = 'bbs_lb';
		$this->{$srcmodel} = new AppModel(array(
						'name' => $srcmodel,
						'table' => $srcmodel,
						'ds' => 'mssql'
					));
		$page=0;		
		
		$target1 = 'cdb_forums';
		$this->{$target1} = new AppModel(array(
						'name' => $target1,
						'table' => $target1,
						'ds' => 'discuz'
					));
		$this->{$target1}->primaryKey = 'fid';
		
		$target2 = 'cdb_forumfields';
		$this->{$target2} = new AppModel(array(
						'name' => $target2,
						'table' => $target2,
						'ds' => 'discuz'
					));
		$this->{$target2}->primaryKey = 'fid';
		
		
		$result = $this->{$srcmodel}->find('all');
		//print_r($result);
		foreach($result  as $value)
		{
			//print_r($value);
			$forums = $value[$srcmodel];
			$forums = $this->_gbk2utf8($forums);			
			
			$this->{$target1}->create();
			$this->data[$target1]['fid'] = $forums['lbid'];
			$this->data[$target1]['fup'] = $forums['qid'];
			if($forums['qid']==0)
			{
				$this->data[$target1]['type'] = 'group';
			}
			else
			{
				$this->data[$target1]['type'] = 'forum';
				$this->data[$target1]['allowsmilies'] = 1;
			}
			
			$this->data[$target1]['name'] = $forums['lbname'];
			$this->data[$target1]['status'] = 1;
			$this->data[$target1]['allowbbcode'] = 1;
			$this->data[$target1]['allowimgcode'] = 1;
			$this->data[$target1]['allowmediacode'] = 1;
			$this->data[$target1]['allowshare'] = 1;
			$this->data[$target1]['allowpostspecial'] = 63;
			
			$this->{$target1}->save($this->data);
			
			//cdb_forumfields
			$this->{$target2}->create();
			$this->data[$target2]['fid'] = $forums['lbid'];
			$this->data[$target2]['description'] = $forums['lbsm'];
			$this->{$target2}->save($this->data);
			
		}
		
		echo 'over';
	}
		
	function admin_transfer()
	{
		//$tables = $this->_listSourse('mssql');	
		//print_r($tables);
		//$this->loadModel('');
		$srcmodel = 'blog';
		$this->{$srcmodel} = new AppModel(array(
						'name' => $srcmodel,
						'table' => $srcmodel,
						'ds' => 'mssql'
					));
		$page=0;
		$members = 'uc_members';
		$this->{$members} = new AppModel(array(
						'name' => $members,
						'table' => $members,
						'ds' => 'discuz'
					));
					
		//print_r($result);
		$target1 = 'uchome_blog';
		$this->{$target1} = new AppModel(array(
						'name' => $target1,
						'table' => $target1,
						'ds' => 'discuz'
					));
		$this->{$target1}->primaryKey = 'blogid';
		
		$target2 = 'uchome_blogfield';
		$this->{$target2} = new AppModel(array(
						'name' => $target2,
						'table' => $target2,
						'ds' => 'discuz'
					));
		$this->{$target2}->primaryKey = 'blogid';
		
		
		$target3 = 'uchome_comment';
		$this->{$target3} = new AppModel(array(
						'name' => $target3,
						'table' => $target3,
						'ds' => 'discuz'
					));
		$this->{$target3}->primaryKey = 'cid';
		
//		$totalnum = 	$this->{$srcmodel}->find('count');		
		//echo $totalnum;exit;
//		$result = $this->{$srcmodel}->find('first', array('conditions'=>array('id' => 25396),'fields' => array('*'),'page'=>$page, 'order' => $srcmodel.'.id ASC'));
//		print_r($result);
//		exit;
		
		do
		{
//			if(($page-1)*5000 > $totalnum)
//			{
//				echo "=====page:== $page====";
//				exit;	
//			}	
			// update uchome_blog set uid =(select uid from uchome_member where uchome_member.username=uchome_blog.username limit 1)	
			// update uchome_blogfield set uid =(select uid from uchome_blog where uchome_blog.blogid=uchome_blogfield.blogid limit 1)
			$page++;			
			
			//$result = $this->{$srcmodel}->find('all', array('limit' => 50,'fields' => array('*'),'page'=>$page, 'order' => $srcmodel.'.id ASC'));
			$result = $this->{$srcmodel}->find('all', array('limit' => 5,'fields' => array('*','cast(nr as text) as nr'),'page'=>$page, 'order' => $srcmodel.'.id ASC'));
			
			
			echo "=====page:== $page===per 50/p=<br/>";
			
			if(empty($result))
			{
				break;	
			}
			
			foreach($result  as $value)
			{
				$blog = $value['blog'];
				$blog['nr'] = $value['0']['nr'];
				//print_r($blog);
				$blog = $this->_gbk2utf8($blog);
				if($blog['blog_id'])
				{
					// 回复
					$this->{$target3}->create();
					$this->data[$target3]['id'] = $blog['id'];
					$user = $this->{$members}->findByUsername($blog['huiyuan2']);
					//echo $members;print_r($user);exit;
					$this->data[$target3]['uid'] = $user[$members]['uid'];
					
					$this->data[$target3]['idtype'] = 'blogid';
					$this->data[$target3]['author'] = $blog['huiyuan'];
					$this->data[$target3]['ip'] = $blog['ip'];
					$this->data[$target3]['dateline'] = strtotime($blog['sj']);
					$this->data[$target3]['message'] = nl2br($blog['nr']);
					//message
					
					$this->{$target3}->save($this->data);
					continue;
				}
				// 标题
				$this->{$target1}->create();
				$this->data[$target1]['blogid'] = $blog['id'];
				$this->data[$target1]['username'] = $blog['huiyuan'];
				$this->data[$target1]['subject'] = $blog['bt'];
				$this->data[$target1]['classid'] = $blog['user_lbid']+10000; // 博客类别id+10000
				$this->data[$target1]['dateline'] = strtotime($blog['sj']);
				
				$this->{$target1}->save($this->data);
				
				// 内容
				$this->{$target2}->create();
				$this->data[$target2]['blogid'] = $blog['id'];
				$this->data[$target2]['message'] = nl2br($blog['nr']);
				$this->data[$target2]['ip'] = $blog['ip'];
				$this->data[$target2]['relatedtime'] = strtotime($blog['sj']);
				
				$this->{$target2}->save($this->data);
				
				//exit;
			}
		}while(1);
		echo 'over';
	}
	
	function admin_transferbbs()
	{
		//$tables = $this->_listSourse('mssql');	
		//print_r($tables);
		//$this->loadModel('');
		$srcmodel = 'bbs';
		$this->{$srcmodel} = new AppModel(array(
						'name' => $srcmodel,
						'table' => $srcmodel,
						'ds' => 'mssql'
					));
		$page=0;
		$target1 = 'cdb_threads';
		$this->{$target1} = new AppModel(array(
						'name' => $target1,
						'table' => $target1,
						'ds' => 'discuz'
					));
		$this->{$target1}->primaryKey = 'tid';
		
		$target2 = 'cdb_posts';
		$this->{$target2} = new AppModel(array(
						'name' => $target2,
						'table' => $target2,
						'ds' => 'discuz'
					));
		$this->{$target2}->primaryKey = 'pid';
		
		do
		{
			// update cdb_posts set authorid =(select uid from uc_members where uc_members.username=cdb_posts.author limit 1)	
			$page++;
			
			$result = $this->{$srcmodel}->find('all', array('limit' => 50,'fields' => array('*'),'page'=>$page, 'order' => $srcmodel.'.id ASC'));
			echo "page: $page per 50/p=<br/>";
			
			if(empty($result))
			{
				break;	
			}
			
			foreach($result  as $value)
			{
				$bbs = $value['bbs'];
				$bbs = $this->_gbk2utf8($bbs);
				
				if($bbs['bbsid']==0)
				{
					// threads
					$this->{$target1}->create();
					$this->data[$target1]['tid'] = $bbs['id'];
					$this->data[$target1]['fid'] = $bbs['lbid'];
					$this->data[$target1]['author'] = $bbs['huiyuan'];
					
					$this->data[$target1]['subject'] = $bbs['bt'];					
					$this->data[$target1]['dateline'] = strtotime($bbs['sj']);
					$this->data[$target1]['lastpost'] = strtotime($bbs['sj2']);
					$this->data[$target1]['lastposter'] = $bbs['huiyuan2'];
					$this->data[$target1]['views'] = $bbs['js'];
					$this->{$target1}->save($this->data);
					
					// posts
					
					$this->{$target2}->create();
					$this->data[$target2]['tid'] = $bbs['id'];
					
					$this->data[$target2]['first'] = '1';
					$this->data[$target2]['fid'] = $bbs['lbid'];
					
					$this->data[$target2]['subject'] = $bbs['bt'];
					$this->data[$target2]['message'] = nl2br($bbs['nr']);
					
					$this->data[$target2]['author'] = $bbs['huiyuan'];
					$this->data[$target2]['useip'] = $bbs['ip'];
					$this->data[$target2]['dateline'] = strtotime($bbs['sj']);
					
					$this->{$target2}->save($this->data);
				}
				
				if($bbs['bbsid'])
				{
					// 回复 posts
					$this->{$target2}->create();
					$this->data[$target2]['tid'] = $bbs['bbsid'];
					
					$this->data[$target2]['first'] = '0';
					$this->data[$target2]['fid'] = $bbs['lbid'];
					
					$this->data[$target2]['subject'] = $bbs['bt'];
					$this->data[$target2]['message'] = nl2br($bbs['nr']);
					
					$this->data[$target2]['author'] = $bbs['huiyuan'];
					$this->data[$target2]['useip'] = $bbs['ip'];
					$this->data[$target2]['dateline'] = strtotime($bbs['sj']);
					
					$this->{$target2}->save($this->data);
					continue;
				}
				
				//exit;
			}
		}while(1);
		echo 'over';
	}
	
	function _gbk2utf8($array)
	{
		$return = array();
			foreach($array as $key => $value)
			{
				if(is_array($value))
				{
					$return[$key]=$this->_gbk2utf8($value);
				}
				else
				{
					$return[$key]=iconv('GBK','UTF-8',$value);
				}
			}
		return $return;
	}
	
	function transfer()
	{
		$tables = $this->_listSourse('mssql');	
		print_r($tables);
	}
}
?>