<?php


$subdir = ''; // 二级目录名，没有为空。开始不要加斜线‘/’
$filepath =  dirname(__file__);
$Path  = $filepath;

dealfolder($filepath.'/data');  // 批量上传data目录的所有内容
echo 'over';


set_time_limit(0);
//读出文件夹中所有子文件夹
function dealfolder($Path)
{
	$uploadfileurl=array();
	$foldertree=array();
	$handle  = opendir($Path);
	$i=0;
	while($file = readdir($handle))
	{
		$newpath=$Path."/".$file;
		if(is_dir($newpath))
		{
			if($file!=".." && $file!=".")
			{
				$foldertree[] = $newpath;
			}
		}
		else
		{
			$folderfiles[]=$newpath;
			//$uploadfileurl[$i]['remoteurl']=$foldername.'/'.$file;
			$i++;
		}
	}
	if(count($folderfiles) > 0)
	{
		dealfile($folderfiles);
	}
	if(is_array($foldertree)&& count($foldertree) > 0)
	{
		foreach($foldertree as $key => $value){
			dealfolder($value);
		}
	}

}

function filegetcontents($filename)
{
	global $phpversion,$referer;
	if (@version_compare($phpversion, "4.3.0", ">=") && @function_exists('file_get_contents'))
	{
		$content = @file_get_contents($filename);
	}
	else
	{
		if (function_exists('file'))
		{
			$content = '';
			$_content = @file($filename);
			if (!empty($_content))
			{
				foreach ($_content as $line)
				{
					$content .=$line;
				}
			}
		}
		else
		{
			echo $filename.' Get content error!<br>';
		}
	}
	return $content;
}

function dealfile($paths)
{
	global $filepath;
	$ignorearray=array('db','bak');
	//$ignorearray=array('js','JS','html');
	foreach($paths as $filename)
	{
		$filenameext=end(explode('/',$filename));
		if('/'.$filenameext==$_SERVER['PHP_SELF'])
			 continue;
		$ext=end(explode('.',$filename));
		
		if(in_array($ext,$ignorearray))
			continue;
		//替换至图片路径
		$destfile = str_replace($filepath,'',$filename);
		$destfile = str_replace('/data/','',$destfile);
		// 论坛图片在discuz域下以bbs开头
		//$destfile = 'bbs/'.$destfile;
		//echo $destfile;exit;
		//echo $filename."\r\n".'---'.$destfile."\r\n";
		dealfilecontent($filename);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://sinaedu.sinaapp.com/interface/college360.php");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		
		$data = array('sec_code' => 'asdf;oirljadlskfL:*E@#!adf','filekey'=> $destfile,'saefile' => '@'.$filename);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$data = curl_exec($ch);
		curl_close($ch);
		echo "\r\n";
		// 将文件批量上传至SAE S3		
		print_r($data );
		echo "\r\n";
	}
}
function dealfilecontent($filename)
{
	if(strpos($filename,'index.htm'))
	{
		$content = file_get_contents($filename);
		$content = str_replace('77','66',$content);
		$fp = fopen($filename, 'w');
		fwrite($fp,$content);
		fclose($fp);
		//echo $content;
	}
}

?>
