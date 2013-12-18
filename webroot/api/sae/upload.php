<?php
//domain为college360，院校360全景图片
//require_once('s3.php');
//require_once('stor.php');
if($_POST['sec_code']=='asdf;oirljadlssd3242kfL:*E@#!adf'){
	$filekey = $_POST['filekey'];	
	$domain = $_POST['domain'];	
	$accessKey = $_POST['accessKey'];
	$secretKey = $_POST['secretKey'];
	$appname = $_POST['appname'];
	if($_POST['savetype']=='S3'){		
		$s = new ArlonS3($accessKey,$secretKey,$appname);
		$url = $s->upload($domain , $filekey , $_FILES['saefile']["tmp_name"]);	
	}
	else{
		$s = new SaeStorage();
		$s = new ArlonStorage($accessKey,$secretKey,$appname);
		$url = $s->upload($domain , $filekey , $_FILES['saefile']["tmp_name"]);	
	}
	if(empty($url)){
		echo 'upload error.'.$s->errmsg();	
	}
	else{
		echo $url;
	}
}
//print_r($_FILES);
//print_r($_POST);
?>