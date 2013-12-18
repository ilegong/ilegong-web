<?php

if (!defined('CAKE_CORE_INCLUDE_PATH')) {
    header('HTTP/1.1 404 Not Found');
    exit('File Not Found');
}
/**
 * Enter description here...
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);

App::uses('File', 'Utility');

function make_clean_css($path, $name) {
    App::uses('CssMin', 'Lib');
    $data = file_get_contents($path);
    $dir_path = dirname($path);
    $remote_path = str_replace(array(WWW_ROOT,'\\'), array('/','/'), $dir_path);//$dir_path;
	//echo "===$dir_path\r\n<br/>====$remote_path\r\n<br/>====";
    //$data = CSSMin::remap( $data, $dir_path, $remote_path,false );
    $data = str_replace($dir_path, $remote_path,$data);
    $output = CSSMin::minify($data);
    $ratio = 100 - (round(strlen($output) / strlen($data), 3) * 100);
    $output = " /* file: $name, compress ratio: $ratio% */ " . $output;
    return $output;
}

function make_javascript_minify($path, $name){
    App::uses('JavaScriptMinifier', 'Lib');
    $data = file_get_contents($path);
    $output = JavaScriptMinifier::minify($data,false,-1);
    $ratio = 100 - (round(strlen($output) / strlen($data), 3) * 100);
    $output = " /* file: $name, compress ratio: $ratio% */ " . $output;
    return $output;
}

function write_css_cache($path, $content) {
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path));
    }
    $cache = new File($path);
    return $cache->write($content);
}

if (preg_match('|\.\.|', $url)) {
    die('Wrong file name.');
}
$file_type = 'css';
if(preg_match('|^ccss/(.+)$|i', $url, $regs)){
    $filename = 'css/' . $regs[1];
    $file_type = 'css';
    $filepath = CSS . $regs[1];
}
elseif(preg_match('|^cjs/(.+)$|i', $url, $regs)){
    $filename = 'js/' . $regs[1];
    $filepath = JS . $regs[1];
    $file_type = 'js';
}
else{
    die('Wrong file name.');
}
//echo "===$filename\r\n===$filepath\r\n";

$cache_key = $filename.guid_string($filename);
$modified_cache_key = $cache_key.'_modify_time';
if (!file_exists($filepath)) {
    die('Wrong file name.');
}
if($_SERVER['SERVER_ADDR'] == '127.0.0.1'){
	Configure::write('Cache.disable', true);
}
$output = Cache::read($cache_key);
$templateModified = Cache::read($modified_cache_key);
if ($output === false) {
    if($file_type=='css'){
        $output = make_clean_css($filepath, $filename);
    }
    elseif($file_type=='js'){
        $output = make_javascript_minify($filepath, $filename);
    }
    $templateModified = time();
    Cache::write($cache_key, $output);
    Cache::write($modified_cache_key, $templateModified);
}
if($file_type=='css'){
    header("Content-Type: text/css");
}
elseif($file_type=='js'){
    header("Content-Type: application/javascript");
}
//header("Date: " . gmdate("D, j M Y G:i:s ", time()) . 'GMT');
//if (!defined('SAE_MYSQL_DB')) {
//    header("Last-Modified: " . gmdate( "D, d M Y H:i:s", $templateModified ) . " GMT" );
//    header("Expires: " . gmdate( "D, d M Y H:i:s", time() + 31536000 ) . " GMT" );
//    header("Cache-Control: s-maxage=31536000,public,max-age=31536000" );
//    header("Pragma: cache");        // HTTP/1.0
//}

print $output;
exit;
?>