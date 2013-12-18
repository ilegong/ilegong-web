<?php 
/**
 * 仅限制在cache目录，防止其他目录的文件被获取
 */

if(empty($_REQUEST['k'])){
	exit('need param k');
}

$cachefile = str_replace(array('../','./','//'),'',$_REQUEST['k']); //过滤../,./防止通过相对路径访问到其他文件
$file = 'saemc://cache/'.$cachefile;

$info = pathinfo($file);
// Transfer-Encoding	chunked
if(strtolower($info['extension'])=='css'){
	header('Content-Type:text/css; charset=UTF-8');
}
if(strtolower($info['extension'])=='js'){
	header('Content-Type:application/javascript; charset=UTF-8');
}
$file_content = file_get_contents($file);

$lastModifiedTime = filemtime($file);

if(!empty($lastModifiedTime)){
	$etag = md5($lastModifiedTime);
}
else{
	$etag = md5($file_content);
}

if (!empty($lastModifiedTime) && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModifiedTime) {
	Header("HTTP/1.0 304 Not Modified");
      //header('HTTP/1.1 304 Not Modified'); 
      // header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
}elseif(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH']==$etag){
	Header("HTTP/1.0 304 Not Modified");
}
header("server_info: ".implode(',',array_keys($_SERVER)));
header("Pragma: public");
header("Cache-Control: max-age=31536000");
header("Expires: ".gmdate("D, d M Y H:i:s", time()+31536000)."  GMT");
header("Last-Modified: " . gmdate ("D, d M Y H:i:s", $lastModifiedTime)."  GMT");
header('ETag: ' . $etag);
header("Content-Length: ".strlen($file_content));

if($file_content !==false){
	echo $file_content;
}
?>