<?php
if(!defined('APP_PATH')) exit('error access.');

$a = isset($_REQUEST['a']) ? $_REQUEST['a']:'';
$k = isset($_REQUEST['k'])? $_REQUEST['k']:'';
$v = isset($_REQUEST['v'])? $_REQUEST['v']:'';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SAE KVDB存储管理</title>
<meta http-equiv="MSThemeCompatible" content="Yes" />
	<link rel="stylesheet" type="text/css" href="/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="/css/admin.css" />
	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" />
</head>

<div id="header">
	<h3>SAE KVDB Manager</h3>
	<a href="/manage/admin/tools/saekv?a=set">SET</a> | <a href="/manage/admin/tools/saekv?a=get">GET</a>  | <a href="/manage/admin/tools/saekv?a=del">DEL</a>  | <a href="/manage/admin/tools/saekv?a=allkv">ALL KV</a> 
</div>
<?php

if($a == 'delfolder'){	
	clearkvfolder($k);
	$a ='allkv';
}

if($a == 'set'){		
		if(!empty($_POST['saekv_key']) && !empty($_POST['saekv_val']) ){
			$_POST['saekv_val'] = stripslashes($_POST['saekv_val']);
			file_put_contents('saekv://'.$_POST['saekv_key'],$_POST['saekv_val']);
			
			echo "<p>设置成功:{$_POST['saekv_key']} => <pre style=\"margin:5px;border:1px solid #CCC;\">".htmlspecialchars($_POST['saekv_val'])."</pre></p>";
		}else{
?>
			<form action="/manage/admin/tools/saekv?a=set" name="setform" method="post">
				<p>&nbsp;&nbsp;saekv://<input type="text" name="saekv_key" value="" /></p>
                          <p>Value:<textarea name="saekv_val" cols="60" row="8" ></textarea></p>
				<p>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit"  value="设置" /></p>
			</form>
<?php
		}
}else if ($a == 'get'){
?>
			<form action="/manage/admin/tools/saekv?a=get" name="setform" method="post">
				<p>&nbsp;&nbsp;saekv://<input type="text" name="k" value="" /></p>
				<p>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit"  value="获得" /></p>
			</form>

<?php
		if(!empty($k)){
			$v = file_get_contents('saekv://'.$k);
			if($v){
				echo "<p>取值成功:{$k} => <pre style=\"margin:5px;border:1px solid #CCC;\">".htmlspecialchars($v)."</pre></p>";
			}else{
				echo "<p>{$k}不存在！</p>";
			}
			
		}		
}else if($a == 'del'){
		$kv = new SaeKV();
	
		$ret = $kv->init();
		if(!empty($k) ){
			$v = $kv->delete($k);
			echo "<p>saekv://{$k}删除成功！</p>";
			
		}else if(!empty($_GET['k'])){
			$v = $kv->delete($_GET['k']);
			echo "<p>saekv://{$_GET['k']}删除成功！</p>";
			
		}
		else{
?>
			<form action="/manage/admin/tools/saekv?a=del" name="setform" method="post">
				<p>&nbsp;&nbsp;saekv://<input type="text" name="k" value="" /></p>
				<p>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit"  value="删除" /></p>
			</form>
<?php		
		}
}else if ($a =='allkv'){
		$kv = new SaeKV();
	
		$ret = $kv->init();
		$ret = $kv->pkrget('', 100);     
		while (true) {
			foreach($ret as $k=>$v){
                echo "<p>saekv://{$k} &nbsp;&nbsp;&nbsp;&nbsp; 
                	<a href=\"/manage/admin/tools/saekv?a=get&k={$k}\" style='color:red;'>VIEW</a> &nbsp;&nbsp; 
                	<a href=\"/manage/admin/tools/saekv?a=del&k={$k}\" onclick=\"return confirm('确认删除？');\" style='color:red;'>DEL</a></p>
                <a href=\"/manage/admin/tools/saekv?a=delfolder&k={$k}\" onclick=\"return confirm('确认删除？');\" style='color:red;'>DEL Folder</a></p>";
            }
			end($ret);                                
			$start_key = key($ret);
			$i = count($ret);
			if ($i < 100) break;
			$ret = $kv->pkrget('', 100, $start_key);
		}

}
	
	/*删除saekv指定目录的所有文件 */
	function clearkvfolder($folder){  //data/template
		$kv = new SaeKV();
		$kv->init();
		$ret = $kv->pkrget($folder, 100);
		while (true) {
			foreach($ret as $k => $v){
			 $kv->delete($k);
			}
			$start_key = $k;
			$i = count($ret);
			if ($i < 100) break;
			$ret = $kv->pkrget($folder, 100, $start_key);
		}
	}
	
?>
</body>
</html>