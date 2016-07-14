<?php
/**
 * 取得语言对应的locale值
 * @param string $language_alias
 * @return string
 */
function getLocal($language_alias) {
    if (!class_exists('I18n')) {
        App::uses('I18n', 'I18n');
    }
    $I18n = I18n::getInstance();
    $I18n->l10n->get($language_alias);
    return $I18n->l10n->locale;
}
$GLOBALS['lang'] = array();

function load_lang($type='default'){
	$pathes = App::path('locales');
	$language = Configure::read('Config.language');
	foreach($pathes as $path){
		$file = $path.DS.$language.DS.$type.'.php';
		if(!defined('LANGUAGE_'.strtoupper($type)) && file_exists($file)){
			$tmp = include_once $file;
			$GLOBALS['lang'] = array_merge($GLOBALS['lang'],$tmp);
		}
	}
}
function lang($name){
	if(isset($GLOBALS['lang'][$name])){
		return $GLOBALS['lang'][$name];
	}
	else{	
		return Inflector::humanize($name);
	}
}

/**
 * 将Configure配置项，写入到配置文件中. 主要是settings表中的记录
 * 
 * Configure::dump($key, $config = 'default', $keys = array())
 */
function write_configure_setting(){
	/**
	 * 将不用写入到settings表中的项清除，防止在配置app/Config/core.php修改内容时，不生效。表dump文件的项覆盖
	 */
	$tmp = Configure::delete('debug');
	Configure::dump('settings','default');// ,array('Hook','Security')
	Configure::write('debug',$tmp);
}

/**
 * 加密解密函数，
 * @param unknown_type $string
 * @param unknown_type $operation  DECODE,ENCODE
 * @param unknown_type $key
 * @param unknown_type $expiry
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : '1a8148f79c8fa5xyz');
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

/**
 * 删除所有缓存
 */
function clearCacheAll(){
	//Cache::config('front', array('engine' => 'File', 'prefix' => 'ideacms_app_',));
	//Cache::clear(false, 'front');
	
	Cache::clear(false, '_cake_model_');
	Cache::clear(false, '_cake_core_');
	Cache::clear(false, 'default');
	clearFolder(WEB_VISIT_CACHE);
	@unlink(DATA_PATH.'settings.php');
}

function clearTemplateCache(){
    clearFolder(WEB_VISIT_CACHE);
    @unlink(DATA_PATH.'settings.php');
}

/**
 * 清空文件夹下的所有文件
 * @param string $dir 文件夹路径
 * @param boolean $recusive 是否递归删除目录
 * @return boolean
 */
function clearFolder($dir,$recusive=false){
	if (is_dir($dir)) {
		$files = glob($dir . '*');
	
		if ($files === false) {
			return false;
		}
	
		foreach ($files as $file) {
			if (is_file($file)) {
				@unlink($file);
			}
			elseif($recusive && is_dir($file)){
				clearFolder($file,$recusive);
				// 不删除文件夹，保留目录下所有文件夹。如删除缓存时，需要保留现有缓存下的文件夹
				//@rmdir($file);
			}
		}
		return true;
	}
}
function is_image($url){
	$file_type = get_mime_type($url);
	if(in_array($file_type,array ("image/png", "image/gif", "image/jpeg", "image/bmp", "image/jpg"))){
		return true;
	}
	return false;
}
/**
 * 是否为搜索引擎抓取
 * @param string $user_agent
 * @return boolean
 */
function is_search_bot($user_agent) {
    if (preg_match('/(bot|spider|baidu|google)/is', $user_agent, $matches)) {
//		print_r($matches);
        return true;
    }
    else
        return false;
}
/**
 * 加载获取一个Model对象，如果不存在时返回false
 * @param string $modelClass
 * @param string $plugin
 * @return boolean|Ambigous <mixed, boolean, multitype:>|Ambigous <mixed, boolean, multitype:, object, unknown>
 */
function loadModelObject($modelClass,$plugin='') {
	if(empty($modelClass)){
		return false;
	}
    $model = false;
    if ($model = & ClassRegistry::getObject($modelClass)) {
        return $model;
    }
    else{
//$plugin .,加上plugin时，购物淘宝客页面显示错误，会莫名其妙的调用setting，暂未发现原因
    	$model =  ClassRegistry::init(array(
    			'class' =>  $modelClass, 'alias' => $modelClass, 'id' => null
    	));
    	ClassRegistry::addObject($plugin . $modelClass, $model);
    	return $model;
    }
}
/**
 * 判断模块对应的数据表是否存在
 * @param unknown_type $modelClass
 * @param unknown_type $plugin
 * @return boolean
 */
function ModelExists($modelClass,$plugin=''){
	if(empty($modelClass)){
		return false;
	}
	try{
		$model = loadModelObject($modelClass,$plugin);
		$model->getDataSource(); //getDataSource调用获取表格信息，判断表格是否存在。
	}
	catch(MissingTableException $e){
		return false;
	}
	return true;
}

/**
 * 用于安装时，导入tree结构的模块数据。让id不固定，并能保存上下级关系。
 * @param array $datas	数据
 * @param string $modelname	模型名称
 * @param int $parentid	上级分类
 */
function saveTreeItems($datas,$modelname,$parentid=null,&$model = null){
	if(empty($model)){
		$model = loadModelObject($modelname);
		$model->recursive = -1;
		if($model->Behaviors->enabled('Tree')){
			$model->Behaviors->disable('Tree'); // 设置了id值tree数据无法插入，先取消tree行为插入数据，再绑定tree行为修复数据。
		}
// 		$model->useDbConfig = 'master';
// 		$model->Behaviors->load('Tree', array('left'=>'left','right'=>'right'));
	}
	$fields = array_keys($model->schema());
	foreach($datas as $item){
// 		$tmpid = $item[$modelname]['id'];
// 		$item[$modelname]['id'] = null; // tree根据id来查询节点。需要先替换id为空，插入后再设置回id的值。
		if(in_array('model',$fields) && !empty($item[$modelname]['model']) && !ModelExists($item[$modelname]['model'])){
			continue;//含model，且对应模块不存在（未安装对应模块）的跳过。如栏目对应的模块
		}
// 		if($parentid){
// 			$item[$modelname]['parent_id'] = $parentid;
// 		}
		$model->create();
		$model->save($item);
		$inner_parentid = $model->id ? $model->id : $model->getLastInsertID();
		
// 		$model->updateAll(array('id'=>$tmpid),array('id'=>$insertid));
// 		$inner_parentid = $tmpid;
		if(!empty($item['children'])){ // $inner_parentid && 
			saveTreeItems($item['children'],$modelname,$inner_parentid,$model);
		}
	} // 按数据的parent_id导入，导入完成后，在fix左右节点
	if($parentid==null){ // 最外层的函数
		// aro,aco等树结构左右节点为lft,rght.
		if(in_array('lft',$fields) && in_array('rght',$fields)){
			$model->Behaviors->load('Tree', array('left'=>'lft','right'=>'rght'));
		}
		else{
			$model->Behaviors->load('Tree', array('left'=>'left','right'=>'right'));
		}
		$model->recover('parent');
	}
}

/**
 * SimpleHtmlDom 的$stripRN要设为false，去掉换行符后，所有内容变成一行，css、js等会出现错误。
 */
// helper functions
// -----------------------------------------------------------------------------
// get html dom from file
// $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
function file_get_html($url, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=false, $defaultBRText=DEFAULT_BR_TEXT)
{
	App::uses('SimpleHtmlDom', 'Utility');
    // We DO force the tags to be terminated.
    $dom = new SimpleHtmlDom(null, $lowercase, $forceTagsClosed, $target_charset, $defaultBRText);
    // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
    $contents = file_get_contents($url, $use_include_path, $context, $offset);
    // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
//    $contents = retrieve_url_contents($url);
    if (empty($contents))
    {
        return false;
    }
    // The second parameter can force the selectors to all be lowercase.
    $dom->load($contents, $lowercase, $stripRN);
    return $dom;
}

// get html dom from string
function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=false, $defaultBRText=DEFAULT_BR_TEXT)
{
	if (empty($str)){
		return false;
	}
	App::uses('SimpleHtmlDom', 'Utility');
    $dom = new SimpleHtmlDom(null, $lowercase, $forceTagsClosed, $target_charset, $defaultBRText);
    $dom->load($str, $lowercase, $stripRN);
    return $dom;
}

// dump html dom tree
function dump_html_tree($node, $show_attr=true, $deep=0)
{
    $node->dump($node);
}




function getStaticFileUrl($url,$full=true){
	$url = Router::url($url,$full);
	$url = str_replace(env('SCRIPT_NAME'),'',$url);
	return $url;
}

/**
 * close all open xhtml tags at the end of the string
 * @param string $html
 * @return string
 */
function closetags($html) {
	#put all opened tags into an array
	preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
	$openedtags = $result[1];
	#put all closed tags into an array
	preg_match_all('#</([a-z]+)>#iU', $html, $result);
	$closedtags = $result[1];
	$len_opened = count($openedtags);
	# all tags are closed
	if (count($closedtags) == $len_opened) {
		return $html;
	}
	$openedtags = array_reverse($openedtags);
	# close tags
	for ($i=0; $i < $len_opened; $i++) {
		if (!in_array($openedtags[$i], $closedtags)){
			$html .= '</'.$openedtags[$i].'>';
		}
		else {
			unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		}
	}
	return $html;
}
/**
 * 获取分页显示的链接
 * @param $total
 * @param $request
 * @param $current_page
 */
function getPageLinks($total, $pagesize, $url, $current_page=1) {
    App::uses('Page', 'Lib');
    if($url instanceof CakeRequest){
	    $url_array = array(
	        'controller' => $url->params['controller'],
	        'action' => $url->params['action'],
	        'plugin' => $url->params['plugin'],
	    );
	    $url_array = array_merge($url_array, $url->params['pass'], $url->params['named']);
	    $url_array = array_rawurlencode($url_array);
	    $query = $url->query;
	    unset($url_array['page']);
	    
	    
	    /*将request对象转变了url字符串*/
	    $url = Router::url($url_array);
    }
    else{ // url is array or string
    	$url = Router::url($url);
    	$query = $_GET;
    }
    unset($query['page'],$query['_']);//"_" is append by jquery ajax.
    if (!empty($query)) {
        $querystring = http_build_query($query);
        
//         foreach ($query as $key => $val) {
//             $querystring .= $key . '=' . urlencode($val) . '&';
//         }
//         $querystring = htmlspecialchars(substr($querystring, 0, -1));
        $url = $url . '?' . $querystring;
    }
    $pagelinks = new Page($total, $pagesize, $url, $current_page);
    return $page_navi = $pagelinks->renderNav(10);
}

/**
 * 获取搜索的条件
 * @param array() $query query参数与值，
 * @param string $modelClass 查询的模块
 * @param boolean $isjoin 是否是连接查询的条件，连接查询条件必须以$modelClass.开头
 */
function getSearchOptions($query,$modelClass){
	/**
	 * 当包含get参数时，当参数名与数据表的字段名相同时，将此get参数加入搜索条件
	 */
	$conditions = array();
	$object = loadModelObject($modelClass);
	//$fileds = $object->getExtSchema();
	$fileds = $object->schema();
	$filedkeys = array_keys($fileds);
	if(in_array('published',$filedkeys)){
		$conditions[$modelClass.'.published'] = 1;
	}
	if(in_array('deleted',$filedkeys)){
		$conditions[$modelClass.'.deleted'] = 0;
	}
	
	if(!empty($query)){
// 		foreach($fileds as $fn => $fi){
// 			if(!empty($query[$fn])){
// 				if($fi['associatetype'] == 'treenode' && !empty($fi['selectmodel'])){
// 					$selectmodel = loadModelObject($fi['selectmodel']);
// 					$selectmodel->recursive = -1;
// 					if($selectmodel->actsAs['Tree']){
// 						if($fi['selectvaluefield']=='id'){
// 							$linkid = $query[$fn];
// 						}
// 						else{
// 							$tmp = $selectmodel->find('first',array('conditions'=>array($fi['selectvaluefield']=> $query[$fn])));
// 							$linkid=$tmp[$fi['selectmodel']]['id'];
// 						}
// 						$children = $selectmodel->children($linkid);
// 						$values = array($query[$fn]);
// 						foreach($children as $item){
// 							$values[] = $item[$fi['selectmodel']][$fi['selectvaluefield']];
// 						}
// 						$conditions[$modelClass.'.'.$fn] = $values;
// 					}
// 				}
// 				if(!isset($conditions[$modelClass.'.'.$fn])){
// 					$conditions[$modelClass.'.'.$fn] = $query[$fn];
// 				}
// 			}
// 		}
		
		foreach ($query as $key => $val) {
			preg_match('/^([\w\.]+)/',$key, $matches);// 形如field_name
			//strpos($key, $jv['alias'] . '.' . $mk) === 0
			if(in_array($matches[1],$filedkeys)){ // 字符部分为字段名时
				if($key!=$matches[1]){ //不等于时追加一个空格，如"price>%3D=100"  -> "price >=100"
					$fieldname = $matches[1];
					$key = $fieldname.' '.substr($key,strlen($fieldname));
					
					$key = $modelClass.'.'.$key;
					
					if (in_array($fileds[$fieldname]['type'], array('string', 'text'))) { // 文本字段加上like作模糊搜索
						if (strpos($key, ' like') !== false) {
							$conditions[$key] = '%' . $val . '%';
						} else {
							$conditions[$key . ' like'] = '%' . $val . '%';
						}
					}
// 					else {
// 						//$this->_extschema
// 						if (in_array($fileds[$fieldname]['selectmodel'],array('Misccate','Modelcate','Category'))){ // 字段值为tree结构（全用Misccate模块）时，加载所有子类id数据
// 							$misccate = ClassRegistry::init(array('class' => $fileds[$fieldname]['selectmodel'], 'alias' => $fileds[$fieldname]['selectmodel'], 'id' => $val));
// 							$chilrens = $misccate->children($val,false,'id');
// 							$ids = array($val);
// 							foreach($chilrens as $child){
// 								$ids[] = $child[$fileds[$fieldname]['selectmodel']]['id'];
// 							}
// 							$conditions[$key] = $ids;
// 						}
// 						else{
// 							$conditions[$key] = $val;
// 						}
// 					}
				}
				else{
					$key = $fieldname = $matches[1];
					if (in_array($fileds[$fieldname]['type'], array('string', 'text','content'))) { // 文本字段加上like作模糊搜索
						if (strpos($key, ' like') !== false) {
							$conditions[$key] = '%' . $val . '%';
						} else {
							$conditions[$key . ' like'] = '%' . $val . '%';
						}
					}
					else{
						$conditions[$key] = $val;
					}
				}
			}
		}
	}
// 	print_r($conditions);
	return $conditions;
}
/**
 * 获取搜索的链接 
 * @param mix $request string or request object.页面request对象
 * @param array $extra	搜索追加参数
 * @param array $delparams	需要删除的参数。 （需要减去的参数（如去掉搜索条件），或可能包含<,>,like等；不方便直接数组覆盖，需要手动指定删除的参数字段名）
 * @param boolean $strip_base 是否去除二级目录的信息，去除时 返回结果仍需要调用Router::url($result).在SectionHelper中使用到
 */
function getSearchLinks($url, $extra=array(), $delparams=array(),$strip_base=false) {
	if($url instanceof CakeRequest ){
		$query = $url->query;
		$url = $url->here;
		// $url = $url->base.'/'.$url->url;
		if(empty($query)) $query = array();
	}
	else{
		$query = $_GET;
	}
// 	print_r($url);
	// 删除需要删除的字段查询条件
    foreach ($query as $key => $val) {
        foreach ($delparams as $del) {
           if (strpos($key, $del) === 0) {
               unset($query[$key]);
           }
        }
    }
    // 删除要删除的项后，再合并$extra，防止$extra中的项被删除
    $query = array_merge($query,$extra);
    $query = array_delete_value($query ,'');
//     $query = array_rawurlencode($query);
    unset($query['page']);  // 去除分页页码参数，搜索的条件全部默认是显示第一页。这里不需要传页码参数，仅翻页链接需要页码参数。
//     $url = Router::url($url_array);
	
    if($strip_base && defined('APP_SUB_DIR')){
    	$url = str_replace(APP_SUB_DIR,'',$url); // 当应用放在二级目录时，替换掉链接的二级目录，在页面显示时，仍需要调用router::url.防止重复
    }
    if(defined('APPEND_LOCALE_BASE')){
    	$url = '/..'.$url;
    }
    if (!empty($query)) {
        $querystring = http_build_query($query);
//         foreach ($query as $key => $val) {
//         	if(!empty($val)){
//             	$querystring .= urlencode($key). '=' . urlencode($val) . '&';
//         	}
//         }
//         $querystring = substr($querystring, 0, -1);        
        $url = $url . '?' . $querystring;
    }
    return $url;
}

/**
 * 生成随机的串. All characters should be url encoding compatible.
 * @param int $length
 * @param string $type
 * @return string
 */
function random_str($length, $type = "char") {
    $chars = ($type != 'num') ? "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz" : "0123456789";
    $max = strlen($chars) - 1;
    mt_srand((double) microtime() * 1000000);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $chars[mt_rand(0, $max)];
    }
    return $string;
}
/**
 * 文件大小格式化
 * @param unknown_type $filesize
 * @return string
 */
function format_filesize($filesize){
	if($filesize>1024*1024){
		return round($filesize/(1024*1024),2).'M';
	}
	elseif($filesize>1024){
		return round($filesize/1024,2).'KB';
	}
	else{
		return $filesize.'B';
	}
}

function user_calculate_example($arg1, $arg2) {
    if ($arg1 > $arg2) {
        return 'No';
    } else {
        return 'Yes';
    }
}

/**
 *  根据PHP各种类型变量生成唯一标识号
 * @param mix $mix
 * @return string
 */
function guid_string($mix) {
    if (is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * 选项转换为数组
 * 0=>女
  1=>男
  2=>不详
 *
 */
function optionstr_to_array($string) {
    $return_array = array();
    $array = explode("\n", $string);
    foreach ($array as $val) {
        if (empty($val)) {
            continue;
        }
        $temp = explode('=>', $val);
        if (count($temp) == 2) {
            $return_array[$temp[0]] = $temp[1];
        } else {
            $return_array[$temp[0]] = $temp[0];
        }
    }
    return $return_array;
}
/**
 * 将xml内容转换成sql查询的选项
 * @param unknown_type $xml
 * @return Ambigous <multitype:, multitype:NULL string >
 */
function parseXmlToSqlOption($xml) {

	$xmlarray = xml_to_array($xml);      //$regioninfo['Region']['conditions']
	$searchoptions = $xmlarray['options'];

	//if (!empty($searchoptions['withsubcategory']) && !empty($this->params['withsubcategory'])) {
	//	$searchoptions = array_merge_recursive($this->params['withsubcategory'], $searchoptions);
		//	    		print_r($searchoptions);
	//}

	$regCondition = array();
	if (!is_array($searchoptions['conditions']['conditionskey'])) {
		$searchoptions['conditions']['conditionskey'] = array($searchoptions['conditions']['conditionskey']);
		$searchoptions['conditions']['conditionsval'] = array($searchoptions['conditions']['conditionsval']);
		$searchoptions['conditions']['valid'] = array($searchoptions['conditions']['valid']);
	}
	if (is_array($searchoptions['conditions']['conditionskey'])) {
		foreach ($searchoptions['conditions']['conditionskey'] as $ck => $value) {
			if ($searchoptions['conditions']['conditionsval'][$ck]) {
				if ($searchoptions['conditions']['valid'][$ck] == 'notempty' && empty($searchoptions['conditions']['conditionsval'][$ck])) {
					continue; //不为空时才成立的条件，取消掉
				}
				if (strpos($value, ' ') === false) {
					/**
					 * 不带空格的conditionskey拼接sql，0 => "conditionskey =conditionsval"
					 * 若为值，需要自己加单引号。主要用于一个表的字段等于另一个的字段,如 a.id = b.aid
					 * @var $joinCondition
					 */
					if(intval($value)==$value){
						$regCondition[] = $searchoptions['conditions']['conditionsval'][$ck];
					}
					else{
						$regCondition[] = $value . ' = ' . $searchoptions['conditions']['conditionsval'][$ck];
					}
				} else {
					/**
					 * 带空格的conditionskey，conditionskey => conditionsval
					 * 支持 >=等操作
					 * @var $joinCondition
					 */
					$regCondition[$value] = $searchoptions['conditions']['conditionsval'][$ck];
				}
			}
		}
	}


	$searchoptions['conditions'] = $regCondition;

	if (!empty($searchoptions['joins'])) {
		$searchoptions['joins'] = array_values($searchoptions['joins']);
		foreach ($searchoptions['joins'] as $jk => $join) {
			$joinCondition = array();
			if (!is_array($join['conditions']['conditionskey'])) {
				$join['conditions']['conditionskey'] = array($join['conditions']['conditionskey']);
				$join['conditions']['conditionsval'] = array($join['conditions']['conditionsval']);
				$join['conditions']['valid'] = array($join['conditions']['valid']);
			}
			if (is_array($join['conditions']['conditionskey'])) {
				foreach ($join['conditions']['conditionskey'] as $ck => $value) {
					if ($join['conditions']['conditionsval'][$ck]) {
						if ($join['conditions']['valid'][$ck] == 'notempty' && empty($join['conditions']['conditionsval'][$ck])) {
							continue; //不为空时才成立的条件，取消掉
						}

						if (strpos($value, ' ') === false) {
							/**
							 * 不带空格的conditionskey拼接sql，0 => "conditionskey =conditionsval"
							 * 若为值，需要自己加单引号。主要用于一个表的字段等于另一个的字段,如 a.id = b.aid
							 * @var $joinCondition
							 */
							if(empty($value) || preg_match('/^\d+$/',$value)){  //为空或数字时，为值
								$joinCondition[] = $join['conditions']['conditionsval'][$ck];
							}
							else{
								$joinCondition[] = $value . ' = ' . $join['conditions']['conditionsval'][$ck];
							}
						} else {
							/**
							 * 带空格的conditionskey，conditionskey => conditionsval
							 * 支持 >=等操作
							 * @var $joinCondition
							 */
							$joinCondition[$value] = $join['conditions']['conditionsval'][$ck];
						}
					}
				}
			}
			if (empty($joinCondition)) {
				unset($searchoptions['joins'][$jk]);
			} else {
				$searchoptions['joins'][$jk]['conditions'] = $joinCondition;
				$searchoptions['joins'][$jk]['table'] = Inflector::tableize($join['table']);
			}
		}
	} else {
		$searchoptions['joins'] = array();
	}
	return $searchoptions;
}
/**
 * 将模版中的列表调用参数转换成数组.
 * eval虽然是模板中代码更清晰，但不支持hiphop。另外eval方式不是非常安全，可能加入其它有危害的代码。
 * 故使用parse_str的方式，按url get方式传入字符串。
 * 
 * 例如：
 * 
 * model=Product|cached=900|pagelink=no|title=最新排行|options['fields']=array('Product.id','Product.name','Product.created')|portlet=default|list_tpl=scripts|limitnum=8|orderby=id desc
 * @param string $info
 * @return array
 */
function parseInfoToArray($info){
	$infos = array();
	parse_str($info,$infos);
	return $infos;
// 	$params=array();
// 	// 去除竖线分隔符两侧的空白符
// 	$info = preg_replace('/\s*\|\s*/','|',trim($info));
// 	$param_array = explode('|',$info);
// 	foreach($param_array as $variable){
// 		if(!empty($variable)){
// 			$expresions = explode('=', $variable);
// 			$key = array_shift($expresions);
// 			$value=implode('=',$expresions);
// 			if($key){
// 				$pos = strpos($key,'[');
// 				if($pos===false){ // 不含[
// 					//$params[$key]=$value;
// 					eval('$params[\''.$key.'\']='.$value.';');
// 				}
// 				else{
// 					if($pos==0) continue; // [不能在第一位
// 					//当含有[ 时，为数组，使用eval来处理赋值语句					
// 					//eval('$'.$key.'='.$value.';');$params[$vkey] = $$vkey;
// 					$vkey = substr($key,0,$pos);
// 					eval('$params[\''.$vkey.'\']='.$value.';');					
// 				}
// 			}
// 		}
// 	}
// 	return $params;
}
function arrayToJson($val) {
    App::uses("Services_JSON", "Pear");
    $json = new Services_JSON();
    return $json->encode($val);
}

function jsonToArray($val) {
    App::uses("Services_JSON", "Pear");
    $json = new Services_JSON();
    $obj = $json->decode($val);
    return object_to_array($obj);
}

//function arrayToJson($arr) {
//	if(function_exists('json_encode')) return json_encode($arr); 
//	$parts = array();
//	$is_list = false;
//	
//  //Find out if the given array is a numerical array
//  $keys = array_keys($arr);
//  $max_length = count($arr)-1;
//  if(($keys[0] == 0) && ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
//      $is_list = true;
//      for($i=0; $i<=$max_length;$i++){
//          if($i != $keys[$i]) { //A key fails at position check.
//              $is_list = false; //It is an associative array.
//              break;
//          }
//      }
//  }
//
//  foreach($arr as $key=>$value) {
//      if(is_array($value)) { //Custom handling for arrays
//          if($is_list) $parts[] = arrayToJson($value); /* :RECURSION: */
//          else $parts[] = '"' . $key . '":' . arrayToJson($value); /* :RECURSION: */
//      } else {
//          $str = '';
//          if(!$is_list) $str = '"' . $key . '":';
//
//          //Custom handling for multiple data types
//          if(is_numeric($value)) $str .= $value; //Numbers
//          elseif($value === false) $str .= 'false'; //The booleans
//          elseif($value === true) $str .= 'true';
//          else $str .= '"' . addslashes($value) . '"'; //All other things
//          // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
//
//          $parts[] = $str;
//      }
//  }
//
//  $json = implode(',',$parts);
//
//  if($is_list) return '[' . $json . ']';//Return numerical JSON
//  return '{' . $json . '}';//Return associative JSON
//}
/**
 * 修改数组的所有索引为小写
 * @param unknown_type $array
 * @return multitype:NULL unknown
 */
function array_change_keylower($array) {
    $temparray = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $temparray[strtolower($key)] = array_change_keylower($value);
        } else {
            $temparray[strtolower($key)] = $value;
        }
    }
    return $temparray;
}

/**
 * substr 对字符截取，英文字符两个占一个长度，汉字一个占一个长度
 * @param $string
 * @param $length
 * @param $strpad
 */
function gsubstr($string, $length, $strpad='') {
    if (strlen($string) > $length) {
        for ($i = 0; $i < $length; $i++)
            if (ord($string[$i]) > 128) {
                $i++;
            }
        if ($i > $length) {
            $i -= 2;
        }
        $string = substr($string, 0, $i) . $strpad;
    }
    return $string;
}

function usubstr($str, $position, $length, $strpad='…') {
    $start_byte = 0;
    $totallenth = $start_position = $end_position = strlen($str);
    $count = 0.0;
    for ($i = 0; $i < strlen($str); $i++) {
        if ($count >= $position && $start_position > $i) {
            $start_position = $i;
            $start_byte = $count;
        }
        if (($count - $position) >= $length) {
            $end_position = $i;
            break;
        }
        $value = ord($str[$i]);
        if ($value > 127) {
            if ($value >= 192 && $value <= 223) {
                $i++;
            } else if ($value >= 224 && $value <= 239) {
                $i = $i + 2;
            } else if ($value >= 240 && $value <= 247) {
                $i = $i + 3;
            } else if ($value >= 248 && $value <= 251) {
                $i = $i + 4;
            } else if ($value >= 252 && $value <= 253) {
                $i = $i + 5;
            } else {
                $start_position++;
                //die('Not a UTF-8 compatible string');
            }
            $count++;
        } else {
            $count = $count + 0.5;  //英文字符两个占一个长度，汉字一个占一个长度
        }
    }
    $returnstr = substr($str, $start_position, $end_position - $start_position);

    if ($totallenth > $end_position)
        $returnstr.=$strpad;
    return $returnstr;
}

/**
 * XML字符串转数组
 *
 * @param string $string	XML字符串
 */
function xml_to_array($string) {
    if (empty($string))
        return array();
    //App::import('Lib', 'Xml');
    require_once CORE_PATH . 'Cake/Utility/Xml.php';
    // cake 2.0
    $xml = Xml::build($string);
    $xmlarray = Xml::toArray($xml);
    //print_r($string); print_r($xmlarray);exit;
    //$xml = Xml::build($string);
    //$xmlarray=array();
    //$xmlarray['options'] = simplexml2array($xml);
//	$xmlarray = array_change_keylower($xmlarray,CASE_LOWER);
    return $xmlarray;
}

/**
 * 数组转XML
 * @param string $data	XML文件、URL或字符串
 * @param bool $isfile	data类型是XML文件、URL还是字符串
 * @param string $is_iconv	转码格式，默认为空不进行转码，格式如 'utf-8|gbk' 为把数据由 utf-8 转码为 gbk
 */
function array_to_xml($data, $options =array()) {
    /**
     * use this to convert array  in cake 1.3.x
     */
    //require_once CORE_PATH.'Cake/Utility/Xml.php';
    App::uses('Xml', 'Utility');
    //print_r($data);print_r($options);exit;
    if (count($data) > 1) {
        $t_array = array();
        $t_array['xml'] = $data;
        $data = $t_array;
    }
    $options += array('format' => 'tags');
    $xml = Xml::build($data, $options);
    return $xml->asXML();
}

/**
 * 类、对象转数组
 *
 * @param object $object	类、对象
 * @param string $is_iconv	转码格式，默认为空不进行转码，格式如 'utf-8|gbk' 为把数据由 utf-8 转码为 gbk
 */
function object_to_array($object, $is_iconv = '') {
    $array = array();
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    if (is_array($object)) {
        foreach ($object as $key => $val) {
            $array[$key] = object_to_array($val, $is_iconv);
        }
    } else {
        $array = $object;
        if (!empty($is_iconv)) {
            $is_iconv = explode('|', $is_iconv);
            $array = @iconv($is_iconv[0], $is_iconv[1], $array);
        }
    }
    unset($object);
    return $array;
}

/**
 * 删除数组中为某一值的所有元素,不传value时，删除空值
 * @param $array
 * @param $value
 * @return array
 */
function array_delete_value($array, $value=null,$trim = false) {
    if (!empty($array) && is_array($array)) {
        foreach ($array as $k => $v) {
            if($trim && $v == trim($value)){
            	unset($array[$k]);
            }
            elseif ($v == $value) {
                unset($array[$k]);
            }
        }
    } else {
        $array = array();
    }
    return $array;
}

/**
 * @param $array
 * @param null $value
 * @param bool $trim
 * @return array
 */
function array_delete_value_ref(&$array, $value=null,$trim = false) {
    if (!empty($array) && is_array($array)) {
        foreach ($array as $k => $v) {
            if($trim && $v == trim($value)){
            	unset($array[$k]);
            }
            elseif ($v == $value) {
                unset($array[$k]);
            }
        }
    } else {
        $array = array();
    }
    return $array;
}

function array_to_table($array){
	if(!is_array($array) || empty($array)){
		return '';
	}
	$HTML = "<table class=\"table\">"; 
	foreach($array as $key=> $value){
		if(is_array($value)){
			$HTML .= "<tr><td>$key</td><td class=\"make_table_td\">".array_to_table($value)."</td></tr>";
		}
		else{
			$HTML .= "<tr><td>$key</td><td class=\"make_table_td\">$value</td></tr>";
		} 
	}
	$HTML .= "</table>"; 
	return $HTML;
}

/**
 * 对数组的各项key,value递归进行rawurlencode，并返回数组.key,value同时处理
 * @param $array
 * @return array
 */
function array_rawurlencode($array) {
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $newkey = rawurlencode($key);
                $array[$newkey] = array_rawurlencode($val);
                if ($newkey != $key) {
                    unset($array[$key]);
                }
            } else {
                $newkey = rawurlencode($key);
                $array[$newkey] = rawurlencode($val);
                if ($newkey != $key) {
                    unset($array[$key]);
                }
            }
        }
        return $array;
    } else {
        return rawurlencode($array);
    }
}
function array_strip_tags($array,$trim=true){
	if (is_array($array)) {
		foreach ($array as $key => &$val) {
			if (is_array($val)) {
				$val = array_strip_tags($val);
			} else {
				if($trim){
					$val = trim(strip_tags($val));
				}
				else{
					$val = strip_tags($val);
				}				
			}
		}
		return $array;
	} else {
		if($trim){
			return trim(strip_tags($array));
		}
		else{
			return strip_tags($array);
		}
	}
}

/**
 * mime_content_type函数已不建议使用，定义get_mime_type函数获取文件的mime类型。
 * 获取mime的函数环境很可能不支持，默认使用文件后缀对应类型的方法来匹配，不存在的类型再用函数获取。
 */
function get_mime_type($filename) {

	$mime_types = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			'ief' => 'image/ief',
			'djvu' => 'image/vnd.djvu',
			'djv' => 'image/vnd.djvu',
			'wbmp' => 'image/vnd.wap.wbmp',
			'ras' => 'image/x-cmu-raster',
			'pnm' => 'image/x-portable-anymap',
			'pbm' => 'image/x-portable-bitmap',
			'pgm' => 'image/x-portable-graymap',
			'ppm' => 'image/x-portable-pixmap',
			'rgb' => 'image/x-rgb',
			'xbm' => 'image/x-xbitmap',
			'xpm' => 'image/x-xpixmap',
			'xwd' => 'image/x-windowdump',
			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mxu' => 'video/vnd.mpegurl',
			'avi' => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie',
			'au' => 'audio/basic',
			'snd' => 'audio/basic',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'kar' => 'audio/midi',
			'mpga' => 'audio/mpeg',
			'mp2' => 'audio/mpeg',
			'aif' => 'audio/x-aiff',
			'aiff' => 'audio/x-aiff',
			'aifc' => 'audio/x-aiff',
			'm3u' => 'audio/x-mpegurl',
			'ram' => 'audio/x-pn-realaudio',
			'rm' => 'audio/x-pn-realaudio',
			'rpm' => 'audio/x-pn-realaudio-plugin',
			'ra' => 'audio/x-realaudio',
			'wav' => 'audio/x-wav',
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	);

	$ext = strtolower(end(explode('.', $filename)));
	if (array_key_exists($ext, $mime_types)) {
		return $mime_types[$ext];
	}
	else {
		return 'application/octet-stream';
	}
}

/**
 * 判断一个ip是否是内网IP
 * @param unknown_type $ip
 * @return boolean
 */
function is_inner_ip($ip) {
    $segs = explode('.', $ip);
    if ($segs[0] == '10' || ($segs[0] == '172' && $segs[1] >= 16 && $segs[1] <= 31) || ($segs[0] == '192' && $segs[1] == '168')) {
        return true;
    } else {
        return false;
    }
}

/**
 * 
 * 获取客户端的IP
 */
function get_remote_ip() {
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : getenv('HTTP_CLIENT_IP');
    if ($ip)
        return $ip;
    $http_x_forwarded_for = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : getenv('HTTP_X_FORWARDED_FOR');
    if ($http_x_forwarded_for) {
        $forward_ip_list = preg_split('/,\s*/', $http_x_forwarded_for);
        foreach ($forward_ip_list as $ip) {
            if (!is_inner_ip($ip)) {
                return $ip;
            }
        }
    }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
    if ($ip && $ip != 'unknown')
        return $ip;

    if (!empty($forward_ip_list)) {
        return $forward_ip_list[0];
    }
    return 'unknown';
}

function str2date($sTime) {
    $dt = DateTime::createFromFormat(FORMAT_DATETIME, $sTime);
    return !empty($dt) ? $dt->getTimestamp() : 0;
}

function friendlyDateFromStr($sTime, $type = 'normal') {
    if ($sTime) {
        $dt = DateTime::createFromFormat(FORMAT_DATETIME, $sTime);
        return friendlyDate($dt->getTimestamp(), $type);
    } else {
        return '';
    }
}

/**
 * 友好的时间显示
 *
 * @param int    $sTime 待显示的时间
 * @param string $type  类型. normal | mohu | full | ymd | other
 * @param string $alt   已失效
 * @return string
 */
function friendlyDate($sTime, $type = 'normal', $alt = 'false') {
    if (!$sTime)
        return '';
    //sTime=源时间，cTime=当前时间，dTime=时间差
    $cTime      =   time();
    $dTime      =   $cTime - $sTime;
    $dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
    //$dDay     =   intval($dTime/3600/24);
    $dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
    //normal：n秒前，n分钟前，n小时前，日期
    if($type=='normal'){
        if($dTime >= 0 && $dTime < 60 ){
            if($dTime < 10){
                return '刚刚';    //by yangjs
            }else{
                return intval(floor($dTime / 10) * 10)."秒前";
            }
        }elseif($dTime >= 0 && $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
            //今天的数据.年份相同.日期相同.
        }elseif( $dYear==0 && ($dDay <= 0 && $dDay >= -1)  ) {
            return ($dDay == 0 ? '今天' : '明天') . date('H:i', $sTime);
        }elseif($dYear==0){
            return date("m月d日 H:i",$sTime);
        }else{
            return date("Y-m-d H:i",$sTime);
        }
    }elseif($type=='mohu'){
        if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif( $dDay > 0 && $dDay<=7 ){
            return intval($dDay)."天前";
        }elseif( $dDay > 7 &&  $dDay <= 30 ){
            return intval($dDay/7) . '周前';
        }elseif( $dDay > 30 ){
            return intval($dDay/30) . '个月前';
        }
        //full: Y-m-d , H:i:s
    }elseif($type=='full'){
        return date("Y-m-d, H:i:s",$sTime);
    }elseif($type=='ymd') {
        return date("Y-m-d", $sTime);
    }else if($type == 'chinese_m_d') {
        return date('n月d日', $sTime);
    }else if($type == FFDATE_CH_MDW) {
        $weeks = array(
            '0' => '日',
            '1' => '一',
            '2' => '二',
            '3' => '三',
            '4' => '四',
            '5' => '五',
            '6' => '六',
            );
        $s = date('n月d日', $sTime);
        return $s.'周'.$weeks[date('w', $sTime)];
    }else{
        if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif($dYear==0){
            return date("Y-m-d H:i:s",$sTime);
        }else{
            return date("Y-m-d H:i:s",$sTime);
        }
    }
}

/**
 * @param $word
 * @return string
 */
function generate_slug($word) {
    if (empty($word)) {
        return '';
    }
    App::uses('Charset', 'Lib');
    App::uses('Pinyin', 'Lib');
    $PY = new Pinyin();
    $slug = $PY->stringToPinyin(Charset::utf8_gbk($word));
    $slug = Inflector::slug($slug);
    return $slug;
}


/**
 * FIXME: fix year 2016 problem
 * @param $time
 * @return bool|string
 */
function get_shichituan_period($time = null) {
    if ($time == null) {
        $time = time();
    }
    return ((date('Y', $time) <= 2014) ? (date('m', $time) - 8) : (date('m', $time) + 4));
}

function create_user_cond($uid, $sessionId = null) {
    $user_cond = array();
    if (!empty($sessionId)) {
        $user_cond['session_id'] = $sessionId;
    }

    if (!empty($uid)) {
        $user_cond['creator'] = $uid;
    }

    if (empty($user_cond)) {
        throw new Exception("You have to provide session-id or user-id");
    }

    return $user_cond;
}

/**
 * @param $id
 * @param string $acc default pys
 * @return string
 */
function key_cache_sub($id, $acc='pys') {
    return '_wx_sub_'.$acc.'_' . $id;
}


/**
 * @param $exId
 * @param $id
 * @return string
 */
function key_follow_brand_time($exId, $id) {
    $key = '_fo_bra_' . $id . '_' . $exId;
    return $key;
}

/**
 * @param $exId
 * @param $id
 * @return string
 */
function key_assigned_times($exId, $id) {
    return '_fo_ass_' . $id . '_' . $exId;
}

function name_empty_or_weixin($nick) {
    $nick = trim($nick);
    return (!$nick || strpos($nick, '微信用户') === 0);
}

function special_privacy($address, $len_from_last = 6) {
    if ($len_from_last <= 0) {
        return $address;
    }
    $len = mb_strlen($address);
    $start_pos = max($len - $len_from_last, 0);
    $str = $start_pos == 0 ?  "" : mb_substr($address, 0, $start_pos);
    for($i = $start_pos; $i < $len; $i++) {
        $str .= ($i % 2 == 0 ? mb_substr($address, $i, 1) : '*');
    }
    return $str;
}

function is_mobile($str){
    return preg_match('/^1[0-9]{10}$/', $str);
}


function make_union_code()
{
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0, 25)]
        . strtoupper(dechex(date('m')))
        . date('d') . substr(time(), -5)
        . substr(microtime(), 2, 5)
        . sprintf('%02d', rand(0, 99));
    for (
        $a = md5($rand, true),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
        $d = '',
        $f = 0;
        $f < 8;
        $g = ord($a[$f]),
        $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
        $f++
    ) ;
    return $d;
}
