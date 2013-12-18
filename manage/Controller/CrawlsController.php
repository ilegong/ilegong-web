<?php

App::uses('Xml', 'Utility');
App::uses('CrawlUtility', 'Utility');
App::uses('File', 'Utility');
App::uses('Charset', 'Lib');

class CrawlsController extends AppController {

    var $name = 'crawls';
    
    var $components = array('HtmlPurifier');

    // 后台增加
    function admin_add() {

        if (!empty($this->data)) {
            $regexps = array();
            foreach ($this->data['Crawl'] as $key => $val) {
                if ($val && strpos($key, 'regexp_') !== false) {
                    $regexps[$key] = $val;
                }
            }
            if (!empty($regexps)) {
                $this->data['Crawl']['regexp_xml'] = array_to_xml($regexps); //$xml->toString();
            } else {
                $this->data['Crawl']['regexp_xml'] = '';
            }
        }
        parent::admin_add();
    }

    // 后台编辑
    function admin_edit($id = null,$copy = NULL) {

        if (!empty($this->data)) {
            $regexps = array();
            foreach ($this->data['Crawl'] as $key => $val) {
                if ($val && strpos($key, 'regexp_') !== false) {
                    $regexps[$key] = $val;
                }
            }

            if (!empty($regexps)) {
                $this->data['Crawl']['regexp_xml'] = array_to_xml($regexps);
            } else {
                $this->data['Crawl']['regexp_xml'] = '';
            }
            // 保存修改内容
            if (is_array($this->data['Crawl']['category_id'])) {
                $this->data['Crawl']['category_id'] = implode(',', $this->data['Crawl']['category_id']);
            }
            parent::admin_edit($id,$copy);
        } else {
            //显示编辑界面
            parent::admin_edit($id,$copy);
            if (!empty($this->data['Crawl']['regexp_xml'])) {
                //if (strstr($this->data['Crawl']['regexp_xml'], '<regexp>') === false && strstr($this->data['Crawl']['regexp_xml'], '<regexp/>') === false) {
                    // 不包含外围的<regexp>时，添加上。向上兼容旧的数据
                //    $this->data['Crawl']['regexp_xml'] = '<regexp>' . $this->data['Crawl']['regexp_xml'] . '</regexp>';
                //}
                $xmlarray = xml_to_array($this->data['Crawl']['regexp_xml']);
                if (!is_array($xmlarray['xml'])) {
                    $xmlarray['xml'] = array();
                }
            } else {
                $xmlarray = array('xml' => array());
            }
            //'<regexp>' . $this->data['Crawl']['regexp_xml'] . '</regexp>'
            // 模型显示一些规则输入框
            $xmlarray['xml'] = array_merge(array('regexp_name' => '', 'regexp_coverimg' => ''), $xmlarray['xml']);
            $this->set('regexp_list', $xmlarray['xml']);
            $this->set('categories', $this->data['Crawl']['category_id']);
            if ($this->data['Crawl']['category_id']) {
                $this->data['Crawl']['categories'] = $this->data['Crawl']['category_id'] = explode(',', $this->data['Crawl']['category_id']);
                
            }
        }
    }

    /*
     * 处理页码，按从后向前排列，使靠前的最新发布
     */

    function __dealpages($pages) {
        $pages_array = array();
        foreach (explode(',', $pages) as $k => $v) {
            $temparray = explode('~', $v);
            if (count($temparray) > 1) {
                $start = $temparray[0];
                $end = $temparray[1];
                if ($temparray[0] > $temparray[1]) {
                    $start = $temparray[0];
                    $end = $temparray[1];
                } else {
                    $start = $temparray[1];
                    $end = $temparray[0];
                }
                for ($i = $start; $i >= $end; $i--) {
                    $pages_array[] = $i;
                }
            } else {
                $pages_array[] = $v;
            }
        }
        $pages_array = array_unique($pages_array);
        rsort($pages_array);
        return $pages_array;
    }

    // 图片新闻采集地址  http://roll.news.sina.com.cn/picnews/gjxw/index.shtml
    /**
     * 抓取列表及标题
     * @param $id  采集规则，
     * @param $page 采集的页码。
     * @param $url_seq 采集的url序号，规则设置了多个url，每行一个。
     * @return unknown_type
     */
    public function admin_crawl($id, $page = 0,$url_seq = 0) {  
    	header('Content-Type:text/html; charset=UTF-8');
    	$this->autoRender = false;    	
        $rules = $this->Crawl->read(null, $id);
        @set_time_limit(0);
        $totalpage = $current_page = $nextpage = 0;        
        if(empty($rules['Crawl']['targeturl'])){
        	echo '错误。没有设置抓取目标地址';
            return false;
        }
        $urls = array_delete_value(explode("\r\n",$rules['Crawl']['targeturl']),'',true); // 转换成数组并删除空值
        $urls = array_values($urls);//重排索引，删除空行项后，数字索引不连续。重排使其连续
        
        $total_seq = count($urls);
    	if($total_seq == $url_seq){ // 从0开始计数的，相等时循环完了。
        	echo '已完成。所有url已抓取完成。';
            return true;
        }
                
        $targeturl = $urls[$url_seq];
        /**
         * 链接支持形如\/**\/的注释内容，注释内容及其两端的空白符会被替换为空
         */
        $targeturl = preg_replace('|\s*/\*.+?\*/\s*|','',$targeturl);
        $pages_str = '';
        if(strstr($targeturl,'<<')){
        	$pagesinfo = explode('<<',$targeturl);
        	$targeturl = $pagesinfo[0];
        	$pages_str = $pagesinfo[1];
        }
    	if ($pages_str) {
            $pages = $this->__dealpages($pages_str);
            $current_page = $pages[$page];
            $totalpage = count($pages);
        }

        if (strstr($targeturl, '{page}')) {
            $targeturl = str_replace('{page}', $current_page, $targeturl);
            $nextpage = ++$page;
        }
        
        echo $targeturl."<BR/>\n";
        
//         print_r($urls);exit;
        
        $content = CrawlUtility::getRomoteUrlContent($targeturl);

        if ($rules['Crawl']['targetcharset'] == 'GBK') {
            $content = Charset::gbk_utf8($content);
        } elseif ($rules['Crawl']['targetcharset'] == 'BIG5') {
            $content = Charset::big5_utf8($content);
        }        

        $replaceA = array("/", "?", "'");
        $replaceB = array('\/', '\?', "\'");
        $rules['Crawl']['urlnotcontains'] = str_replace($replaceA, $replaceB, $rules['Crawl']['urlnotcontains']);
        $rules['Crawl']['urlcontains'] = str_replace($replaceA, $replaceB, $rules['Crawl']['urlcontains']);
        $rules['Crawl']['urlcontent_regexp'] = str_replace($replaceA, $replaceB, $rules['Crawl']['urlcontent_regexp']);
        // 匹配链接出现的内容区域，排除其他可能出现的包含urlcontaints规则的链接
        $urlcontent_regexp = '/' . $rules['Crawl']['urlcontent_regexp'] . '/iUs';
        if (!empty($rules['Crawl']['urlcontent_regexp']) && preg_match($urlcontent_regexp, $content, $urlcontent_matches)) {
            $content = $urlcontent_matches[1];
        }

        if ($rules['Crawl']['datatype'] == 'json' || $rules['Crawl']['datatype'] == 'jsonp') {
            $content = trim($content);
            if ($rules['Crawl']['datatype'] == 'jsonp') {
                if (preg_match('/^jsonp\d+\((.+)\)$/iUs', $content, $json_matches)) {
                    $content = $json_matches[1];
                    unset($json_matches);
                } else {
                    return $this->__message(__('Error jsonp data', true), array('action' => 'crawl', 'action' => 'list'));
                }
            }
            $content = json_decode($content);
        }
        // 去除html标签，后再匹配链接，防止<a><span></span></a>这类标签不好匹配
        //    	 $content = strip_tags($content,'<a><img>');
        //    	 $content='href="s d\'">f" sf>sd<span>fjs</a>kl</span></a>';
        // [^>|^"|^\'] 指 > " ' 这三个都不能出现
        //[^xyz] 	负值字符集合。匹配未包含的任意字符。例如， '[^abc]' 可以匹配 "plain" 中的'p'。
        //?号前面有*、+、?时表示非贪婪  ； a?表示a0次或一次  ;  a+?和a*?就表示非贪婪。
        // /U修正符，不要与？号的非贪婪模式一起使用，否则又变成贪婪匹配了。
        $url_regular = '/<a\s[^>]*href=["\']?([^>"\'\s]*' . $rules['Crawl']['urlcontains'] . '[^>"\'\s]*?)["\'\s]*[^>]*>(.+)<\/a>/iUs';
        // 匹配链接时，[^>"\'\s]*? /U这里加上问号，贪婪匹配到链接分隔符取得链接。
        preg_match_all($url_regular, $content, $matches);

        // <table>\s<tr>\s<td style="text-align:center;"><a href="(http://news.sina.com.cn/\w/\d{4}-\d{2}-\d{2}/.+\.shtml)" target="_blank"><img src="(.+)" .+/></a></td>
        // 图片的匹配，要包含进入的页面的链接，不然可能出现图片与标题对不上的情况
        // U大写U修正符，本修正符反转了匹配数量的值使其不是默认的重复，而变成在后面跟上“?”才变得重复。
        //if (strstr($rules['Crawl']['regexp_xml'], '<regexp>') === false && strstr($rules['Crawl']['regexp_xml'], '<regexp/>') === false) {
            // xml不包含外围的<regexp>时，添加上。向上兼容旧的数据
        //    $rules['Crawl']['regexp_xml'] = '<regexp>' . $rules['Crawl']['regexp_xml'] . '</regexp>';
        //}

        $xmlarray = xml_to_array($rules['Crawl']['regexp_xml']);
        $coverimg_array = array();
        $img_ext = array('jpg', 'gif', 'jpeg', 'png', 'bmp', 'jpe');

        if (isset($xmlarray['xml']['regexp_coverimg'])) {
            $regular = str_replace('/', '\/', $xmlarray['xml']['regexp_coverimg']);
            if (preg_match_all('/' . $regular . '/iUs', $content, $image_matches)) {
                $urlindex = 1;
                $imageindex = 2;
                foreach ($image_matches[1] as $key => $value) {
                    if ($key == 0) {
                        $ext = strtolower(array_pop(explode('.', $image_matches[1][0])));
                        if (in_array($ext, $img_ext)) {
                            $imageindex = 1;
                            $urlindex = 2;
                        } else {
                            $ext = strtolower(array_pop(explode('.', $image_matches[2][0])));
                            if (!in_array($ext, $img_ext)) {
                                continue;
                            }
                        }
                    }

                    $coverimg_array[$image_matches[$urlindex][$key]] = $image_matches[$imageindex][$key];
                }
            } else {
                return $this->__message('标题图片匹配失败', array('action' => 'crawl', 'action' => 'list'));
            }
        }

        if (empty($matches[1])) {
            echo '抓取参数设置不正确，页面中没有找到对应的链接';
            return false;
        }
             
        // 数组逆序，排后面的内容是新发布的。抓取的时候排后面的先抓取
        $matches[1] = array_reverse($matches[1], true);
        $title_array = array_strip_tags(end($matches)); // 使用end取最后一个数组，使正则表达式能够包含括号。$rules['Crawl']['urlcontains']
        $title_array = array_reverse($title_array, true);
        $image_save_path = ROOT . '/files/remote/' . date('Y-m') . '/';
        /* 去除要排除的链接 */
        foreach ($matches[1] as $key => $page_url) {
        	
            if(empty($title_array[$key])){ //没有匹配到标题的或者标题经过trim,strip_tags后为空的，去除
            	unset($matches[0][$key], $matches[1][$key], $title_array[$key]);
            }
            elseif (!empty($rules['Crawl']['urlnotcontains']) && preg_match('/' . $rules['Crawl']['urlnotcontains'] . '/iUs', $page_url)) {
                // 链接包含了设置的不能包含的规则，则跳过不处理。
//                 echo "skip. bad url :$page_url,urlnotcontains:".$rules['Crawl']['urlnotcontains']."\r\n";
                unset($matches[0][$key], $matches[1][$key], $title_array[$key]);
            } elseif (!empty($rules['Crawl']['urltextnotcontains']) && preg_match('/' . $rules['Crawl']['urltextnotcontains'] . '/iUs', $title_array[$key])) {
                // 链接包含了设置的不能包含的规则，则跳过不处理。
//                 echo "skip. bad url :$page_url,urltextnotcontains:".$rules['Crawl']['urltextnotcontains']."\r\n";
                unset($matches[0][$key], $matches[1][$key], $title_array[$key]);
            }
            elseif (!empty($rules['Crawl']['urltextcontains']) && !preg_match('/' . $rules['Crawl']['urltextcontains'] . '/iUs', $title_array[$key])){
            	// 链接没有包含了设置的需要包含的规则，则跳过不处理。
//             	echo "skip. bad url :$page_url,urltextcontains:".$rules['Crawl']['urltextcontains']."\r\n";
            	unset($matches[0][$key], $matches[1][$key], $title_array[$key]);
            }
        }
        if(!empty($_GET['test'])){
        	echo '<pre>';
        	 print_r($matches[0]);print_r($matches[1]);print_r($title_array);
        	 echo '</pre>';
        	 return false;
        }
        else{
        	echo '<pre>';
        	print_r($matches[0]);
        	echo '</pre>';
        }
       
        $this->loadModel('CrawlTitleList');
        $matches[1] = array_unique($matches[1]);
        foreach ($matches[1] as $key => $page_url) {

            unset($this->data['CrawlTitleList']);
            $this->CrawlTitleList->create();
            $this->data['CrawlTitleList']['creator'] = $this->currentUser['id'];
            $this->data['CrawlTitleList']['name'] = trim(strip_tags($title_array[$key]));
           
            if (empty($title_array[$key])) {
                continue;
            }
            if (isset($xmlarray['xml']['regexp_coverimg'])) {
                if (isset($coverimg_array[$page_url])) {
                    // 若标题图片存在， 保存标题图片
                    $this->data['CrawlTitleList']['coverimg'] = CrawlUtility::getPagelinkUrl($coverimg_array[$page_url], $targeturl);
                }
            }
            $page_url = CrawlUtility::getPagelinkUrl($page_url, $targeturl); // 将相对的url转换成完整url地址

            $this->data['CrawlTitleList']['remoteurl'] = $page_url; // 页面地址
            $this->data['CrawlTitleList']['refererurl'] = $targeturl; // 页面来源地址
            $this->data['CrawlTitleList']['crawl_id'] = $id;

            $this->CrawlTitleList->recursive = -1;
            $havegot = $this->CrawlTitleList->findByRemoteurl($this->data['CrawlTitleList']['remoteurl']);
            if (!empty($havegot['CrawlTitleList']) && empty($_GET['test']) && empty($_GET['replace'])) {
                //echo 'skip. has get . "' . $this->data[$targetModel]['remoteurl'] . "\"\r\n";
                continue; // 对已抓取的页面跳过。
            } else {
                $this->CrawlTitleList->save($this->data);
            }
        }
        if ($nextpage && $totalpage > $nextpage) {
            //当前抓取地址的某一页
            if(defined('IN_CLI')){
                return $this->TaskQueue->add(array('action' => 'crawl', $id, $nextpage,$url_seq));
            }
            else{
                return $this->__message('第'.($url_seq+1).'/'.$total_seq.'个地址，第' . $nextpage . '/'.$totalpage.'页', array('action' => 'crawl', $id, $nextpage,$url_seq), 3);
            }
        }
        elseif($total_seq==1){ //仅有一个抓取地址
            return $this->__message('已完成', array('action' => 'crawl', 'action' => 'list'), 20000);
        }
        else{
            // 进入下一个抓取地址的第一页
            if(defined('IN_CLI')){
                return $this->TaskQueue->add(array('action' => 'crawl', $id, 0,$url_seq+1));
            }
            else{
        		return $this->__message('第'.($url_seq+1).'/'.$total_seq.'个地址，第' . $nextpage . '/'.$totalpage.'页', array('action' => 'crawl', $id, 0,$url_seq+1), 3);
            }
        }
    }

    public function admin_cronCrawlAll(){
    	$this->autoRender = false;
        if(!defined('IN_CLI')){
        	define('IN_CLI',true);
        }

        $datas = $this->Crawl->find('all',
              array(
                     'conditions' => array('deleted' => 0,'published' => 1,),                            
                     'fields' => array('Crawl.id','targeturl'),
              )
        );
        foreach($datas as $item){
        	echo '<pre style="border: 1px solid #CCCCCC;padding:5px;">'.str_replace('&','&amp;',$item['Crawl']['targeturl']).'</pre>';
            $this->TaskQueue->add(array('action' => 'crawl', $item['Crawl']['id']));
        }
        $this->autoRender = false;
    }
    

}
