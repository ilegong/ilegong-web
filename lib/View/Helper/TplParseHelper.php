<?php

/**
 * @author Administrator
 *
 */
class TplParseHelper  extends AppHelper {

    public $helpers = array('Html','Section','Request','Session');
    
    public $objdir; // 缓存所在目录
    public $theme;  // 主题theme的文件夹名称
    public $theme_path; // 主题theme的文件夹路径
    public $ext = '.html';
    
    private $tplfile; // layout模板对应的文件名。
    private $objfile;  // 此页面的缓存模板文件
    private $var_regexp = "\@?\\\$[a-z_][\\\$\->\w]*(?:\[[\w\-\.\"\'\[\]\$]+\])*";
    private $literal = array();
    /**
     * 子模板
     * @var array()
     */
    private $subtemplates = array();
    /**
     * 当前页面的action模版
     * @var string
     */
    public $template;
    /**
     * 当前模版所属插件
     * @var string
     */
    public $plugin;

    public function  __construct(View $View, $settings = array()) {
    	parent::__construct($View, $settings);
    	$this->theme = $View->theme;
    	$this->objdir = TMP.'cache'.DS.'views'.DS;    	
    	$this->theme_path = $View->getThemePaths();
    }
	/**
	 * 
	 * @param string $template,模板文件地址，绝对地址或者相对地址。
	 * @param string $plugin	
	 * @param boolean $force
	 * @return string return cache file path.
	 */
    public function gettpl($template, $plugin = '',$force=false) {
    	$this->template = $template;
    	$this->plugin = $plugin;
    	
        $pathinfo = pathinfo($template);
        if (empty($pathinfo['extension'])) {
//         	$this->tplfile = $this->_View->getViewFileName($template);
//         	echo $this->tplfile ;exit;
            $this->tplfile = $this->_getSubTemplateFile($template, $plugin);
        } else {
            $this->tplfile = $template;
        }
        
        $this->objfile = str_replace(ROOT, '', $this->tplfile);
        $this->objfile = str_replace(array('/','\\', ':'), array('_','_', ''), $this->objfile);

        $this->objfile = $this->objdir . $this->objfile . '.tpl.php';
        //tplfile 为模板文件，objfile为缓存文件
        if (Configure::read('debug')>0 || $force || @filemtime($this->objfile) < @filemtime($this->tplfile)) {
            $this->complie();
        }
        return $this->objfile;
    }
    /**
     * 检查缓存是否新鲜。仅在Configure::read('debug')>0时，checkfresh代码才会追加到模版缓存的开始部分。正式线上的不包含，使效率更高
     * 当检测到要更新缓存时，当前请求仍会使用旧的缓存模版，下次请求才会使用更新的缓存。
     * 检查子模板时间与模版缓存时间进行比较。判断缓存是否应该更新
     * @param string $filename
     * @param Timestamp $timestamp
     * @param string $template
     * @param string $plugin
     * @return true or false.
     */
    public function checkfresh($filename,$timestamp,$template,$plugin){
    	if(@filemtime($filename) > $timestamp){
    		$this->gettpl($template, $plugin,1); //强制更新缓存
    		return true;
    	}
    	return false;
    }

    /**
     * 若模版名不是变量，不要使用include方法，而要使用template语法.
     * 在模板中include包含子模板，编译完成后是一条include语句
     * @param array $variable  $matches[1]为子模板的名称，传入的模板名称是一变量。 $matches[1]为plugin插件名
     */
    private function includeSubTemplate($matches) {
    	$variable = $matches[1];    	
        // 当包含plugin中文件时，在plugin中查找模板文件
        if (isset($matches[2])&&!empty($matches[2])) {
            return '<?php  include $this->TplParse->gettpl(' . $variable . ',' . $matches[2] . '); ?>';
        } else {
            return '<?php  include $this->TplParse->gettpl(' . $variable . '); ?>';
        }
    }

    /**
     * 取得子模板的文件路径
     * @param $filename
     * @param $plugin
     */
    private function _getSubTemplateFile($filename, $plugin = '') {
        $plugins = CakePlugin::loaded();
        if ($this->ext != substr($filename, - strlen($this->ext))) {
            $filename = $filename . $this->ext;
        }
        
        // 当包含plugin中文件时，在plugin中查找模板文件
        if ($plugin && in_array($plugin, $plugins)) {
            $plugin_path = CakePlugin::path($plugin);
            if (file_exists($plugin_path . 'View' . DS . 'Themed' . DS . $this->theme . DS . $filename)) {
                $filename = $this->tplfile = $plugin_path . 'View' . DS . 'Themed' . DS . $this->theme . DS . $filename;
            }
        	elseif (file_exists($plugin_path . 'View' . DS . 'Themed' . DS . 'default' . DS . $filename)) {
                $filename = $this->tplfile = $plugin_path . 'View' . DS . 'Themed' . DS . 'default'. DS . $filename;
            }
            elseif (file_exists($plugin_path . 'View' . DS . $filename)) {
                $filename = $this->tplfile = $plugin_path . 'View' . DS . $filename;
            }
            return $filename;
        }
        if(is_array($this->theme_path)){
	        foreach($this->theme_path as $themepath){
	        	if (file_exists($themepath . $filename)) {
	        		return $filename = $this->tplfile = $themepath . $filename;
	        	}
	        }
        }
        elseif (file_exists($this->theme_path . $filename)) {
            return $filename = $this->tplfile = $this->theme_path . $filename;
        }
        if ($this->_View->request->params['plugin']) { // 当前请求所在的插件
            $plugin = $this->_View->request->params['plugin'];
            $plugin_path = CakePlugin::path($plugin);
            if (file_exists($plugin_path . 'View' . DS . $this->theme . DS . $filename)) {
                $filename = $this->tplfile = $plugin_path . 'View' . DS . $this->theme . DS . $filename;
            }
        	elseif (file_exists($plugin_path . 'View' . DS . 'default' . DS . $filename)) {
                $filename = $this->tplfile = $plugin_path . 'View' . DS . 'default' . DS . $filename;
            }
            elseif (file_exists($plugin_path . 'View' . DS . $filename)) {
                $filename = $this->tplfile = $plugin_path . 'View' . DS . $filename;
            }
        }
        return $filename;
    }

    /**
     * 读取子模版文件
     * @param mix $template 若为数组，表示是preg_replace_callback中调用;为字符串时，在其它函数中调用
     * @param string $plugin
     * @return mixed|string
     */
    private function readtemplate($template, $plugin = '') {//$template, $plugin = '' 
    	if(is_array($template)){ // 
    		if(count($template)==3){
    			$plugin = $template[2];
    		}
    		$template = $template[1];    		
    	}
        $filename = $this->_getSubTemplateFile($template, $plugin);
        /**
         * 使用$template.'_'.$plugin做索引，排除重复
         * @var unknown_type
         */
        $this->subtemplates[] = $filename;
        $content = '';
        App::uses('File', 'Utility');
        if (file_exists($filename)) {
            $file = new File($filename, true);
            $content = $file->read();
            $content = $this->__includeSubTemplate($content);
            return $content;
        } else {
            //throw new NotFoundException('Sub Template not Found.'.$filename);
            return '';
        }
    }

    //写入模板缓存文件
    private function writetemplate($filename, $content) {
        if ($filename) {
            if (file_put_contents($filename, $content) === false) {
                App::uses('File', 'Utility');
                $file = new File($filename, true);
                return $file->write($content);
            }
        }
    }

    /**
     * 包含子模板
     * @param $template 模板内容
     */
    function __includeSubTemplate($template) {
        $temp = $template;
        //varhtml 处理要放在模板中编译的变量
        $template = preg_replace_callback('/{{varhtml (.+?)}}/is',array($this,'varhtml'),$template);
        $template = preg_replace_callback("/{{template\s+([A-Za-z0-9_\/]+?)}}/i", array($this,'readtemplate'),$template);
//		preg_match("/{{template\s+name=\"(.+?)\"\s+plugin=\"(.+?)\"\s*}}/i",$template,$matches);
//		print_r($matches);
        $template = preg_replace_callback("/{{template\s+name=\"(.+?)\"\s+plugin=\"(.*?)\"\s*}}/i", array($this,'readtemplate'),$template);
        $template = preg_replace_callback("/{{template\s+name=\"(.+?)\"\s+plugin=\"(.*?)\"\s*}}/i", array($this,'readtemplate'),$template);
        $template = preg_replace_callback("/\{\{template\s+name=\"(.+?)\"\s*\}\}/i", array($this,'readtemplate'),$template);
        // 若子模板引入了子模板，则继续转换内部子模板。
        if (strpos($template,'{{template')!==false) {
        	$template = $this->__includeSubTemplate($template);
        }
        return $template;
    }


    public function complie() {
        $template = file_get_contents($this->tplfile);
        $template = $this->_complieContent($template);
        $this->writetemplate($this->objfile, $template);
    }
    
    private function _complieContent($template,$withheader = true){
    	//处理literal内部的内容，用标记代替。在最后替换回来
    	$template = preg_replace_callback('/{{literal}}(.+?){{\/literal}}/is', array($this,'removeLiteral'),$template);
    	
    	$template = $this->__includeSubTemplate($template);
    	$template = $this->__compilePortlet($template);
    	/*include在portlet转换后进行，防止编辑模板时声称innertext被转换过了。*/
    	$template = preg_replace_callback("/{{include\s+name=\"(.+?)\"\s+plugin=\"(.*?)\"\s*}}/i", array($this,'includeSubTemplate'), $template);
    	$template = preg_replace_callback("/{{include\s+(.+?)}}/i", array($this,'includeSubTemplate'),$template);
    	
    	//变量
    	$template = preg_replace("/\<\!\-\-\{\{(.+?)\}\}\-\-\>/s", "{{\\1}}", $template);
    	// 替换模板中的注释代码 <!-- -->
    	//$template = preg_replace("/\<\!\-\-(.+?)\-\-\>/s", "", $template);
    	
    	/* 给所有的 ?>结束前面加上空格，方便后面的 <?=$xx?>匹配 */
    	/* $template = preg_replace("/\<\?(php)?\s+(.*?)\?\>/is", "<?php \\2 ?>", $template); */
    	
    	/* 变量处理成 <?=$xx?>这种格式,包括loop循环，if中的 .?问号两边都不带空格 */
    	/*
    	 * $template = preg_replace("/(?<!\<\?\=|\\\\)$this->var_regexp/", "<?=\\0?>", $template);
    	*/
    	
    	//逻辑
    	$template = preg_replace("/\{\{eval\s+(.*?)\}\}/is", "<?php \\1 ?>", $template);
    	
    	$template = preg_replace("/\{\{if\s+(.+?)\}\}/is", "<?PHP if(\\1) { ?>", $template);
    	$template = preg_replace("/\{\{elseif\s+(.+?)\}\}/is", "<?PHP } elseif(\\1) { ?>", $template);
    	$template = preg_replace("/\{\{else\}\}/is", "<?PHP } else { ?>", $template);
    	
    	$template = preg_replace("/{{\/if}}/i", "<?PHP } ?>", $template);
    	//        preg_match("/\{\{($this->var_regexp)\|default:(.+?)\}\}/is",$template,$matches);
    	//        print_r($matches);exit;
    	$template = preg_replace("/\{\{($this->var_regexp)\|default:(.+?)\}\}/is", "<?PHP echo (\\1)?(\\1):\\2; ?>", $template);
    	// 多循环几次，处理嵌套
    	for ($i = 0; $i < 3; $i++) {
    		$template = preg_replace_callback("/\{\{loop\s+($this->var_regexp)\s+($this->var_regexp)\s+($this->var_regexp)\}\}(.+?)\{\{\/loop\}\}/is",array($this,'loopsection'), $template);
    		$template = preg_replace_callback("/\{\{loop\s+($this->var_regexp)\s+($this->var_regexp)\}\}(.+?)\{\{\/loop\}\}/is", array($this,'loopsection'),$template);
    	}
    	
    	//$template = preg_replace("/(\\\$[a-zA-Z_]\w+\[)([a-zA-Z_]\w+)\]/i", "\\1'\\2']", $template);
    	// 未在上述规则中处理的{{}}双大括号中的内容作为变量输出
    	$template = preg_replace("/\{\{(.+?)\}\}/s", "<?php echo \\1; ?>", $template);
    	$template = str_replace('\$', '$', $template); // 模板中若要出现"$"符号，在$前面加双斜线\\来标记，否则处理成变量
    	//恢复literal内部的内容 <!--[[LITERAL'.$length.']]-->
    	$template = preg_replace_callback('/<LITERAL--\[\[html\]\]--LITERAL>/is', array($this,'recoverLiteral'), $template);
    	
    	$headeradd = '';
    	// 仅在Configure::read('debug')>0时，才包含checkfresh代码
    	if($withheader && Configure::read('debug')>0 && !empty($this->subtemplates)) {
    		$this->subtemplates = array_unique($this->subtemplates);
    		$headeradd .= "\n";
    		$prefix = '';
    		foreach($this->subtemplates as $subtemp) {
    			$headeradd .= $prefix.' $this->TplParse->checkfresh(\''.$subtemp.'\', '.time().', \''.$this->template.'\', \''.$this->plugin."')\n";
    			$prefix = '||';
    		}
    		$headeradd .= ';';
    	}
    	$template = preg_replace('/\?>\s+<\?PHP/is',' ?><?PHP ',$template);  //去除php标签之间的空格，排除多余空格的影响
    	
    	$template = "<?php if(!defined('APP_DIR')) exit('Access Denied'); {$headeradd}?>\r\n$template";
    	return $template;
    }
    
    /**
     * 执行新闻等内容中的<portlet>
     * @param string $content
     */
    public function executePortlet($content){
    	return preg_replace_callback("/\<portlet[^\>]*?\>(.*?)\<\/portlet\>/is", array($this,'__executePortlet'),$content);
    }
    /**
     * 内容中的<portlet>，由于是在变量中，不能预编译写入模板的缓存。
     * 对portlet生成一个单独的模板缓存文件。md5值做文件名标示。
     * ob_get_clean获取执行的内容输出
     * @param string $matches preg匹配的数组
     */
    private function __executePortlet($matches){
    	$tpl_name = 'inner_portlet_'.md5($matches[0]).'.php';
    	$file = $this->objdir .$tpl_name;
    	if(!file_exists($file)){
    		$template = $this->_complieContentPortlet($matches);
    		$template = $this->_complieContent($template,false);
    		$this->writetemplate($file, $template);
    	}
    	ob_start();
    	include $file;
    	return $content = ob_get_clean();    	
    }

    /**
     * 编译，转换portlet标签,处理形如<portlet>.+?</portlet>的区域
     * @param $template 模板内容
     */
    private function __compilePortlet($template) {
        // 处理页面的portlet区域,条件存在section表里，普通类型
        $template = preg_replace_callback("/<portlet\s+id=\"portlet[\-|_](\d+)\"\s*>.*?<\/portlet>/is",array($this,'_complieRegion'),$template);
        // 条件写在模板中的列表或内容区域
        $template = preg_replace_callback("/\<portlet[^\>]*?\>(.*?)\<\/portlet\>/is", array($this,'_complieContentPortlet'),$template);
        /**
         * 去除使用div标签表示portlet，不方便做递归嵌套的处理。
         */
        // 处理页面的portlet区域,条件存在section表里，普通类型
        //$template = preg_replace_callback("/<div\s+id=\"portlet[\-|_](\d+)\"\s*>.*?<\/div>/is", array($this,'_complieRegion'),$template);
		
        // 若portlet引入了子模板，则转换子模板。
        if (strpos($template,'{{template')!==false || strpos($template,'{{include')!==false) {
        	$template = $this->__includeSubTemplate($template);
		}
		// 若portlet引入了子模板，则判断子模板中是否还有子portlet存在，若存在则继续转换
        if (preg_match("/\<portlet[^\>]*?\>(.+?)\<\/portlet\>/is",$template)) {
            $template = $this->__compilePortlet($template);
        }
        return $template;
    }
    
    /**
     * 将region数组转换为sql查询条件。
     * @param array $info
     * @return Ambigous <multitype:, multitype:NULL string >
     */
    private function _getPortletSqlOption(&$info){
    	$searchoptions = $info['options'];
    	
    	if (!empty($searchoptions['withsubcategory']) && !empty($this->params['withsubcategory'])) {
    		$searchoptions = array_merge_recursive($this->params['withsubcategory'], $searchoptions);
    		//	    		print_r($searchoptions);
    	}
    	
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
     * 转换模版中配置的<portlet>
     * @todo. 考虑将info的格式转换使用parse_str,但url参数形式的传数组需要太多的前缀，也不方便阅读
     * eval虽然是模板中代码更清晰，但不支持hiphop。另外eval方式不是非常安全，可能加入其它有危害的代码。
 	 * 故使用parse_str的方式，按url get方式传入字符串。
 	 * $template = preg_replace_callback("/\<portlet[^\>]*?\>(.+?)\<\/portlet\>/is", array($this,'_complieContentPortlet'),$template);
     * */
	private function _complieContentPortlet($matches=array())
	{
		$html = $matches[0];
		$innertext = trim($matches[1]); // 内部内容
		$info = array();
		$htmldom = str_get_html(stripslashes($html));
		$portlet = $htmldom->find('portlet', 0);
		$params =  $portlet->getAllAttributes(); // 解析portlet的所有属性
		if($params['info']){
			$params['info'] = str_replace('&amp;','&',$params['info']);
			$info = array();
			parse_str($params['info'],$info);
			if(!isset($info['data']['Region'])) $info['data']['Region'] = array();
			$searchoptions = $this->_getPortletSqlOption($info);
			
			$params=array_merge($params,$searchoptions,$info,$info['data']['Region']); // info 按规则解析 parseInfoToArray
			unset($params['info'],$params['data']);
		}
		
		if(empty($params['portlet']))	$params['portlet']='default';
		if(!empty($params['name'])){
			$params['title'] = $params['name'];
			unset($params['name']);
		}
		
		
		$portlet_html = $this->readtemplate('portlets/'.$params['portlet']);
		
		$portlet_html = str_replace('{{$custom_class}}', $params['custom_class'], $portlet_html);
		$portlet_html = str_replace('{{$title}}',$params['title'],$portlet_html);
		if(empty($innertext)){
			$data_attribute = ' ';
		}
		else{
			$data_attribute = ' innertext="'.urlencode($innertext).'" ';
		}
		
		if(empty($params['model'])){
			foreach($params as $key=>$item){
				if(is_array($item) || in_array($key,array('model','limit'))){					
					continue;
				}
				$data_attribute .= $key.'="'.urlencode($item).'" ';
			}
			$portlet_html = str_replace('{{$data_attribute}}', $data_attribute, $portlet_html);
			$portlet_html = str_replace('{{$body}}',$innertext,$portlet_html);
			return $portlet_html;
		}
		$control_name = Inflector::tableize($params['model']);
		$params['model'] = $region_model_name = Inflector::classify($params['model']); 
// 		print_r($params);exit;
		/*设置列表项模板，优先查询本模块对应目录中的模版，便于个性化*/
		if(empty($params['list_tpl'])){
			$tempname='regions/_titlelist';
		}
		else{
			if(is_array($this->theme_path)){
				foreach($this->theme_path as $themepath){
					if(file_exists($themepath.$control_name.'/'.$params['list_tpl'].'.html')){
						$tempname=$control_name.'/'.$params['list_tpl'];
					}
				}
			}
			elseif(file_exists($this->theme_path.$control_name.'/'.$params['list_tpl'].'.html')){
				$tempname=$control_name.'/'.$params['list_tpl'];
			}
			/*若在主题目录和当前主题当前模块模板文件夹中没有找到模板，则直接使用list_tpl值*/
			if(empty($tempname)){
				$tempname=$params['list_tpl'];
			}
		}
		
		$variables = '<?php $control_name="'.$control_name.'";
			unset($data_array);
			$params = '.var_export($params,true).';				
			$data_array = $this->Section->getRegionListByArray($params);			
			$region_page_navi = $data_array[\'page_navi\'];
			$data_array = $data_array[\'datalist\'];
			$count = count($data_array); // 可根据$key和$count来判断，对某条数据进行特殊处理 
			$region_control_name = \'' . $region_control_name . '\';
			$region_model_name = \'' . $region_model_name . '\';
			?>';
		
		// 若list_tpl的值为inner，则使用$innertext的内容来作为列表项模板;否则将$innertext内容放在列表之前。
		$body =($params['list_tpl']=='inner'?'': $innertext).'{{loop $data_array $key $item_all}}<?PHP  $item = $item_all[\'' . $region_model_name . '\'];$item[\'slug\'] = $item[\'slug\']?$item[\'slug\']:$item[\'id\']; ?>'.
				($params['list_tpl']=='inner'? $innertext:'{{template name="' . $tempname . '" plugin="' . $plugin . '"}}')
				.'{{/loop}}'; /* <?php echo $region_page_navi; ?> */
		
		if(isset($params['display'])){
			$portlet_html = str_replace('{{$portletdisplay}}',' style="display:'.$params['display'].'" ',$portlet_html);
		}
		foreach($params as $key=>$item){
			if(is_array($item)){
				$info[$key] = $item;
				unset($params[$key]);
				continue;
			}
			$data_attribute .= $key.'="'.$item.'" ';
		}
		$data_attribute .= ' info="'.http_build_query($info).'" ';
		
		$portlet_html = str_replace('{{$data_attribute}}', $data_attribute, $portlet_html);
// 		echo $body;exit;
		return  $variables.str_replace('{{$body}}',$body,$portlet_html);
	}
	
    private function _complieRegion($matches) {
    	$id = $matches[1];
        $body = $portletid = '';
        $regioninfo = Cache::read('regioninfo_' . $id);
        if ($regioninfo === false) {
            $region = loadModelObject('Region');
            $GLOBALS['regioninfo'][$id] = $regioninfo = $region->find('first',array('conditions'=>array('id'=>$id)));//$region->read(null, $id);
            if (empty($regioninfo)) {
            	//throw new NotFoundException("Region $id not found");
            	return '';
            }
            $regioninfo = current($regioninfo);
            Cache::write('regioninfo_' . $id, $regioninfo);
        }
        if (empty($regioninfo)) {
            //throw new NotFoundException("Region $id not found");
            return '';
        }

        $title = $regioninfo['name'];
        $attributes = unserialize($regioninfo['attribute']);
        $data_attribute = ' ';

        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                if (empty($key)) {
                    continue;
                }
                if (is_array($value)) {
                    $data_attribute.=' ' . $key . '=",' . implode(',', $value) . '," ';
                } else {
                    $data_attribute.=' ' . $key . '=",' . $value . '," ';
                }
            }
        }
        $data_attribute.=' region_info="'.addslashes(http_build_query($regioninfo)).'"';
        
        if(empty($regioninfo['portlet_tpl'])){
        	$regioninfo['portlet_tpl']='default';
        }

        if ('portlets/' == substr($regioninfo['portlet_tpl'], 0, 9)) {
            $portlet_html = $this->readtemplate($regioninfo['portlet_tpl']);
        } else {
            $portlet_html = $this->readtemplate('portlets/' . $regioninfo['portlet_tpl']);
        }
		$variable = '';
        if ($regioninfo['content_url']) {
            $body = '<?php
			echo $this->requestAction("' . $regioninfo['content_url'] . '?inajax=1",array("return"));
			?>';
        } elseif ($regioninfo['rows'] > 0 && !empty($regioninfo['conditions'])) {
            list($plugin, $model_name) = pluginSplit($regioninfo['model'], false);
            $region_model_name = Inflector::classify($model_name);
            $region_control_name = Inflector::tableize($model_name);

            $tempname = $regioninfo['template'];
            $variable= '<?php unset($data_array);
			$data_array = $this->Section->getRegionListById(\'' . $id . '\');
			$region_page_navi = $data_array[\'page_navi\'];
			$data_array = $data_array[\'datalist\'];
			$count = count($data_array);
			$region_control_name = \'' . $region_control_name . '\';
			$region_model_name = \'' . $region_model_name . '\';
			?>';
            $body = '{{loop $data_array $key $item_all}}<?PHP  $item = $item_all[\'' . $region_model_name . '\'];$item[\'slug\'] = $item[\'slug\']?$item[\'slug\']:$item[\'id\']; ?>'
					.'{{template name="' . $tempname . '" plugin="' . $plugin . '"}}'
				.'{{/loop}}';/* <?php echo $page_navi; ?> */
        } else {
            $body = $regioninfo['content'];
        }
        $portlet_html = str_replace('{{$custom_class}}', $regioninfo['custom_class'], $portlet_html);
        $portlet_html = str_replace('{{$data_attribute}}', $data_attribute, $portlet_html);
        $portlet_html = str_replace('{{$title}}', $title, $portlet_html);
        $portlet_html = str_replace('{{$body}}', $body, $portlet_html);
        if (!empty($regioninfo['custom_style'])) {
            $portlet_html = '<style>' . $regioninfo['custom_style'] . '</style>' . $portlet_html;
        }
        return $variable.$portlet_html;
    }

    private function loopsection($marches) {
    	//$arr, $k, $v, $statement
    	$k = false;
    	if(count($marches)==5){
    		$arr = $marches[1];$k = $marches[2];$v = $marches[3];$statement = $marches[4];
    	}
    	else{
    		$arr = $marches[1];$v = $marches[2];$statement = $marches[3];
    	}
        $statement = trim(str_replace("\\\"", '"', $statement));
        return $k ? "<?PHP foreach((array)$arr as $k => $v) { ?>$statement<?PHP } ?>" : "<?PHP foreach((array)$arr as $v) { ?>$statement<?PHP } ?>";
    }

    /**
     * varhtml,变量的值作为模板内容，并将其内容按模板语法进行编译
     * 如果获取的变量是数组时，返回数组中的第一项
     * @param string $k
     */
    private function varhtml($matches) {
    	$k = $matches[1];
    	if(strpos($k,'[')===false){
        	$val = $this->_View->get($k);        	
    	}
    	else{ // 返回数组中的项，最多支持3维数组，var[idx][idx1];
    		$k = str_replace(array('"','\'',']'),'',$k);
    		$ks = explode('[',$k);
    		$kval = $this->_View->get($ks[0]);
    		if(count($ks)==2){
    			$val =  $kval[$ks[1]];
    		}
    		elseif(count($ks)==3){
    				$val =  $kval[$ks[1]][$ks[2]];
    		}
    		elseif(count($ks)==4){
    			$val =  $kval[$ks[1]][$ks[2]][$ks[3]];
    		}
    		else{
    			return '--array too deep.--';
    		}
    	}
    	if(is_object($val)){
    		$val = object_to_array($val); // 若为对象，则将对象转换成数组
    	}
    	
    	if(is_array($val)){
    		return current($val);
    	}
    	else{
    		return $val;
    	}
    }

    /**
     * literal语法的支持，{{literal}}(.+?){{/literal}}，将literal中的内容保存到数组，并用标签代替.最后通过recoverliteral来恢复内容。
     * @param unknown_type $matches preg_replace_callback匹配出的数组
     * @return string
     */
    private function removeLiteral($matches) {
    	$html = $matches[1];
        $html = stripslashes($html);        
        array_push($this->literal,$html);
        return '<LITERAL--[[html]]--LITERAL>';
    }

    /**
     *
     * 将用标签代替的literal替换回来。
     */
    private function recoverLiteral($matches) {    	
        return array_pop($this->literal);
    }

}
?>