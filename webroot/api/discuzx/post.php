<?PHP
/*
需要在forum_thread表增加一个字段。source_url，字段长度为80，允许为空

INSERT INTO dzx_forum_thread (fid, posttableid, readperm, price, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, status, isgroup, replycredit, closed)
		VALUES ('2', '0', '0', '0', '0', '0', 'admin', '1', 'sfdsdf', '1323479118', '1323479118', 'admin', '0', '0', '0', '0', '0', '0', '0', '0', '0')

INSERT INTO dzx_forum_post SET `fid`='2',`tid`='1',`first`='1',`author`='admin',`authorid`='1',`subject`='sfdsdf',`dateline`='1323479118',`message`='sdfdsfdfdsfds',`useip`='127.0.0.1',`invisible`='0',`anonymous`='0',`usesig`='1',`htmlon`='0',`bbcodeoff`='-1',`smileyoff`='-1',`parseurloff`='',`attachment`='0',`tags`='',`replycredit`='0',`status`='0',`pid`='1'
*/
define('APPTYPEID', 2);
define('CURSCRIPT', 'forum');


require '../../source/class/class_core.php';
require '../../source/function/function_forum.php';
$discuz = & discuz_core::instance();
$discuz->init();

$_POST = dstripslashes($_POST);


if($_REQUEST['sec']!='dfjIUbdfsKJsd832^7*sd@s'){
	echo 'echo error secrite.';	exit;
}

if($_REQUEST['action']=='newthread'){
	
	if($_REQUEST['model']=='forum'){
		
		$_G['cateid'] = $_G['fid'] = $_REQUEST['cid'];  
		$_G['uid'] = '1';
		$_G['username'] = $author = 'admin';
		$_G['timestamp'] = time();		
		$subject = mysql_escape_string($_POST['name']);
		$message = mysql_escape_string($_POST['content']);
		$source_url = mysql_escape_string($_POST['source_url']);
		
		$summary  = $_POST['summary'];

		$tid = DB::result_first("SELECT tid FROM ".DB::table('forum_thread')." WHERE source_url='{$source_url}'");
		if($tid){
			DB::update('forum_thread', array('subject' => $subject), "tid='$tid'");
			DB::delete('forum_post', "tid= '$tid' and fid='{$_G['fid']}'");
		}
		else{
			DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, posttableid, readperm, price, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, status, isgroup, replycredit, closed,source_url)
					VALUES ('$_G[fid]', '0', '0', '0', '0', '0', '$author', '$_G[uid]', '$subject', '$_G[timestamp]', '$_G[timestamp]', '$author', '0', '0', '0', '0', '0', '0', '0', '0', '0','$source_url')");		
			$tid = DB::insert_id();
		}
		useractionlog($_G['uid'], 'tid');	
		DB::update('common_member_field_home', array('recentnote'=> $subject), array('uid'=> $_G['uid']));
		
		$pid = insertpost(array(
				'fid' => $_G['fid'],
				'tid' => $tid,
				'first' => '1',
				'author' => $_G['username'],
				'authorid' => $_G['uid'],
				'subject' => $subject,
				'dateline' => $_G['timestamp'],
				'message' => $message,
				'useip' => $_G['clientip'],
				'invisible' => 0,
				'anonymous' => 0,
				'usesig' => 1,
				'htmlon' => 1,
				'bbcodeoff' => -1,
				'smileyoff' => -1,
				'parseurloff' => '',
				'attachment' => '0',
				'tags' => '',
				'replycredit' => 0,
				'status' => 0
		));
		echo "topic publish over.<BR/>";	
		// 发表评论
		$posts = json_decode(dstripslashes($_POST['posts']),TRUE);
		if(!empty($posts)){		
			foreach($posts as $message){
				$message =  trim(daddslashes($message));
				$pid = insertpost(array(
					'fid' => $_G['fid'],
					'tid' => $tid,
					'first' => '0',
					'author' => '',
					'authorid' => '',
					'subject' => '',
					'dateline' => $_G['timestamp'],
					'message' => $message,
					'useip' => $_G['clientip'],
					'invisible' => 0,
					'anonymous' => 1,
					'usesig' => 1,
					'htmlon' => 1,
					'bbcodeoff' => -1,
					'smileyoff' => -1,
					'parseurloff' => '',
					'attachment' => '0',
					'tags' => '',
					'replycredit' => 0,
					'status' => 0
				));
				echo "=$pid=<BR/>";
			}
		}
	}
	elseif($_REQUEST['model']=='portal'){
		require_once libfile('function/portalcp');
		require_once libfile('function/home');
		$_POST['title'] = getstr(trim($_POST['name']), 80, 1, 1);
		$_G['cateid'] = $_G['fid'] = $_REQUEST['cid'];  
		$_G['timestamp'] = time();	
		$source_url = mysql_escape_string($_POST['source_url']);
		
		if(strlen($_POST['title']) < 1) {
			echo 'title_not_too_little';
		}
		$_POST['title'] = censor($_POST['title']);
		if(empty($_POST['summary'])) $_POST['summary'] = preg_replace("/(\s|###NextPage(\[title=.*?\])?###)+/", ' ', $_POST['content']);
		$summary = portalcp_get_summary(stripslashes($_POST['summary']));
		$summary = censor($summary);
		$content = mysql_escape_string($_POST['content']);
		$title = mysql_escape_string($_POST['title']);
	
		$_POST['pagetitle'] = getstr(trim($_POST['pagetitle']), 60, 1, 1);
		$_POST['pagetitle'] = censor($_POST['pagetitle']);
		
		$article = DB::fetch_first("SELECT aid FROM ".DB::table('portal_article_title')." WHERE fromurl='$source_url'");
		
		if($article['aid']){
			$aid = $article['aid'];
			DB::query('UPDATE '.DB::table('portal_article_title')." SET title='$title',catid='{$_G['cateid']}' WHERE aid = $aid");
			//DB::update('portal_article_title', $setarr, array('aid' => $aid));
			DB::query('UPDATE '.DB::table('portal_article_content')." set  content='$content'  WHERE aid = $aid");
		}
		else{		
			$setarr = array(
				'title' => $_POST['title'],
				'shorttitle' => $_G['gp_shorttitle'],
				'author' => '',
				'from' => '',
				'fromurl' => $source_url,
				'dateline' => $_G['timestamp'],
				'url' => '',
				'allowcomment' => '1',
				'summary' => addslashes($summary),
				'catid' => intval($_G['cateid']),
				'tag' => '',
				'status' => 0,
				'highlight'=> '|||',
				'showinnernav' => '0',
				'uid'=>'1',
				'username'=>'admin',
			);
			$aid = DB::insert('portal_article_title', $setarr, 1);
			DB::query('UPDATE '.DB::table('portal_category')." SET articles=articles+1 WHERE catid = '$setarr[catid]'");
			DB::insert('portal_article_count', array('aid'=>$aid, 'catid'=>$setarr['catid'], 'dateline'=>$setarr['dateline'],'viewnum'=>1));
			//DB::update('portal_article_title', $setarr, array('aid' => $aid));
			DB::query('INSERT INTO '.DB::table('portal_article_content')."(aid, content, pageorder, dateline, id, idtype)
				 VALUES ('$aid','$content','1','$_G[timestamp]', '0', '' )");
			//('1', '', '撒撒旦法第三方<br><br>撒旦法撒大是大非<br><br>撒大法师东方<br>', '1', '1328015775', '0', '')
		}
		
		echo 'post success.';
	}
}
elseif($_REQUEST['action']=='getmodule'){
	
	$xml = '<?xml version="1.0" encoding="utf-8"?>';
	$xml .= '<modules>';
	
	// 论坛
	$xml .= '<item>';
	$xml .= '<modulename>forum</modulename>';
	
		$sql = "SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.lastpost, f.inheritedmod, f.domain,
				f.forumcolumns, f.simple, ff.description, ff.moderators, ff.icon, ff.viewperm, ff.redirect, ff.extra
				FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid)
				WHERE f.status='1' ORDER BY f.type, f.fup";
		$query = DB::query($sql);
	
		while($forum = DB::fetch($query)) {
			$xml .= '<modulecates>';
			$xml .= '<cate_id>'.$forum['fid'].'</cate_id>';
			$xml .= '<type>'.$forum['type'].'</type>';
			$xml .= '<parent_id>'.$forum['fup'].'</parent_id>';
			$xml .= '<cate_name><![CDATA['.$forum['name'].']]></cate_name>';
			$xml .= '</modulecates>';
		}
	$xml .= '</item>';
	
	// 门户 
	$xml .= '<item>';
	$xml .= '<modulename>portal</modulename>';	
	$query = DB::query('SELECT * FROM '.DB::table('portal_category')."");
		while($value = DB::fetch($query)) {
			$xml .= '<modulecates>';
			$xml .= '<cate_id>'.$value['catid'].'</cate_id>';
			$xml .= '<parent_id>'.$value['upid'].'</parent_id>';
			$xml .= '<cate_name><![CDATA['.$value['catname'].']]></cate_name>';
			$xml .= '</modulecates>';
		}
	$xml .= '</item>';
	
	
	$xml .= '</modules>';
	header("Content-type: text/xml; charset=utf-8");
	echo $xml;
}

function portalcp_get_summary($message) {
	$message = preg_replace(array("/\[attach\].*?\[\/attach\]/", "/\&[a-z]+\;/i", "/\<script.*?\<\/script\>/"), '', $message);
	$message = preg_replace("/\[.*?\]/", '', $message);
	$message = getstr(strip_tags($message), 200);
	return $message;
}