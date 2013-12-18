<?php
class RequestFacade{
	
	private static $request = array();
	
	public static function getHttpRequest($request_mode = ''){	
		if(empty($request_mode)) $request_mode = HTTP_REQUEST_METHOD;
		
		if(empty(self::$request[$request_mode])){		
			App::uses($request_mode,'Network/Http');
			if (!class_exists($request_mode)) {
				throw new Exception(__d('cake_dev', 'Class %s not found.', $request_mode));
			}
			self::$request[$request_mode] = new $request_mode();
		}
		return self::$request[$request_mode];
	}
	
	public static function get($uri = null, $query = array(), $request = array()) {
		$httprequest = self::getHttpRequest();
		$i = 0;
		// 同一个地址，如果GET获取内容失败为空时，重复5次
		do {
			$response = $httprequest->get($uri , $query , $request);
			//$content = $httpsocket->get($url, array(), $request);
			$i++;
		} while ($i < 5 && empty($response));
		
		return $response;
	}
	
	public static function post($uri = null, $data = array(), $request = array()){
		$httprequest = self::getHttpRequest();
		return $httprequest->post($uri , $data , $request);
	}
	
	public static function delete($uri = null, $data = array(), $request = array()) {
		$httprequest = self::getHttpRequest();
		return $httprequest->delete($uri , $data , $request);
	}
	
	public static function put($uri = null, $data = array(), $request = array()){
		$httprequest = self::getHttpRequest();
		return $httprequest->put($uri , $data , $request);
	}
	
}