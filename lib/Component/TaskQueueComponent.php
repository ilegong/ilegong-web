<?php

App::uses('RequestFacade', 'Network');

/**
 * 队列服务，主要用于cron中执行。队列自动循环进行
 */
class TaskQueueComponent extends Component {
	/**
	 *
	 * @param type $url  a url like Router::url's first param
	 * @param type $return boolean,true return message,false echo msg. 
	 */
	public function add($url,$return = false){
		if (defined('SAE_TASK_QUEUE_URL') && defined('SAE_MYSQL_DB')) {
			if(is_array($url)){
				if(is_array($url['?'])){
					$url['?']['cron_secret'] = CLOUD_CRON_SECRET;
				}
				else{
					$url['?'] = array('cron_secret' => CLOUD_CRON_SECRET);
				}
			}
			else{
				if(strstr($url,'?')){
					$url .= '&cron_secret='.CLOUD_CRON_SECRET;
				}
				else{
					$url .= '?cron_secret='.CLOUD_CRON_SECRET;
				}
			}
// 			$url = Router::url($url, true);
			$url = 'http://ideacms.sinaapp.com'.Router::url($url);
// 			$url = str_replace(array('manage/webroot/index.php','manage/index.php','manage'), 'cron.php', $url);
			//echo $url."<BR/>";
			$response =  RequestFacade::post(SAE_TASK_QUEUE_URL, array('url'=>$url));
			$message = $response->body;
		}
		else{
			echo "<span style=\"color:red;\">";
			echo "request:";print_r($url);
			echo "</span>\r\n<BR/>";
			$message = $this->requestAction($url);
			return $message;
		}
		if($return){
			return $message;
		}
		else{
			echo $message;
		}
	}

}
?>