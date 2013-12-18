<?php
class Charset{
	
	public static function convert($incharset,$outcharset,$content)
	{
		// 优先使用mb_convert_encoding，容错性强。类似这种 ”目前用户体验?..“编码有错的地方能直接跳过。
		// iconv遇到这种错误，其后的内容会全部丢失。
		/* 使用mb_convert_encoding */
		if ( is_array($content) ){
			foreach ($content as $k => $v){
				$content[$k] = self::convert($incharset,$outcharset,$v);
			}
		}
		else {
			if (function_exists("mb_convert_encoding"))
			{
				$content = mb_convert_encoding($content,$outcharset,$incharset);
			}
			/* 使用iconv */
			else if (function_exists("iconv"))
			{
				$content = iconv($incharset,$outcharset,$content);
			}
		}
		return $content;
	}
	
	public static function convert_utf8($content){
		if (function_exists("mb_convert_encoding")){
			return $content = mb_convert_encoding($content,'UTF-8','UTF-8,ASCII,GBK,GB2312,ISO-8859-1,LATIN1,SJIS,EUC-JP');
		}
		return $content; 
	}
	
	public static function utf8_gbk($content)
	{
		return self::convert('UTF-8','GBK',$content);
	}
	
	public static function gbk_utf8($content)
	{
		return self::convert('GBK','UTF-8',$content);
	}
	
	public static function utf8_big5($content)
	{
		return self::convert('UTF-8','BIG5',$content);
	}
	
	public static function big5_utf8($content)
	{
		return self::convert('BIG5','UTF-8',$content);
	}
}