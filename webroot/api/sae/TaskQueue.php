<?PHP

$queue = new SaeTaskQueue('crons'); //队列名字为crons，根据需要进行修改
if(empty($_POST['url'])){
	echo 'need params.';	exit;
}
//添加单个任务
$queue->addTask($_POST['url']);
//将任务推入队列
$ret = $queue->push();
$url = $_POST['url'];
$url = preg_replace('/cron_secret=\w+/i','secret&',$_POST['url']); // 替换掉 secret相关部分，防止密钥泄漏
if ($ret === false){
	//任务添加失败时输出错误码和错误信息
	echo '<BR/><font color="red">"'.$url.'" add to task error.</font><BR/>';
	var_dump($queue->errno(), $queue->errmsg());
}
else{
	echo '<BR/><font color="green">"'.$url.'" add to task success.</font><BR/>';
}









?>