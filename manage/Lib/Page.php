<?php
/**
 * 
 * 分页类
 */
class Page {
	/* 成员变量 */
	// 记录总数
	public $_total = 0;
	
	public $_start = 0;

	// 每页的记录条数
	protected $_perpage = 20;
	
	// 当前页开始的记录序号
	public $_current_start = 0;

	// 当前页最后的记录序号
	public $_current_end = 0;

	// 当前页号
	public $_current_page = 1;

	// 总页数
	public $_total_page = 0;

	// 页码连接的URL请求地址
	protected $_url = '';
	
	// 翻页代码上的URL
	protected $_url_pre = '';

	//
	protected $_search = array();

	// 地址中表示页码所采用的参数名
	protected $_page_name = 'page';

	// 当前页的样式表名
	public $_current_css = "";

	// 连接的样式表名
	public $_link_css = "";
	
	// 如果是Ajax调用，请设置ajax调用函数名
	public $_ajax_func = '';
	public $_first_page = '&lt;&lt;';
	public $_pre_page = '&lt;';
	public $_next_page = '&gt;';
	public $_last_page = '&gt;&gt;';


	/**
	 * 构造函数
	 *
	 * @param   int     $total    记录总树
	 * @param   int     $perpage  每页的记录条数
	 * @param   string  $url      请求地址，如果不提供，将根据当前请求处理数据
	 * @param   array   $search   额外的搜索参数，此数组中的数据将会被urlencode
	 * @param   string  $page_name 链接中，表示页号的参数名
	 * @param   string  $current_css 当前页使用的样式，当前页用<span></span>包含
	 * @param   string  $link_css 页号中的颜色
	 **/
	public function __construct($total,$perpage,$url='',$current_page){
		//echo $url.'<br/>';
		if($url=='/')
		{
			$url='index';
		}
		$this->_first_page = '首页';
		$this->_current_page = $current_page;
		$this->_pre_page = '上一页';
		$this->_next_page = '下一页';
		$this->_last_page = '尾页';
		$this->_total = intval($total);
		if ( $perpage<1 ){
			$perpage = $this->_perpage;
		}
		else {
			$this->_perpage = intval($perpage);
		}
		$this->_total_page = ceil($total/$perpage);
		if ( $this->_total_page<1 ){
			$this->_total_page = 1;
		}
		$this->_current_start = ($this->_current_page-1)*$this->_perpage+1;
		$this->_start = $this->_current_start -1;
		$end = $this->_current_page*$this->_perpage;
		$this->_current_end = $end>$this->_total?$this->_total:$end;
		//处理url前缀
		
		$this->_url_pre = '/'.$url;
//		echo $this->_url_pre.'<br>';
	}
	
	/**
	 * 创建链接
	 *
	 * @param int $page
	 * @param string $text
	 * @return string
	 */
	public function buildPageLink($page,$text=''){
		$link = $this->_url_pre.'/'.$page;
		if ( $this->_ajax_func ){
			$link = 'javascript:'.$this->_ajax_func.'(\''.substr($link,strlen($this->_url)+1).'\');';
		}
		if ( $text ){
			$link = '<a href="'.Router::url($link).'"';
			if ( $this->_link_css ){
				$link .= ' class="'.$this->_link_css.' page_'.$page.'"';
			}
			else
			{
				$link .= ' class="page_'.$page.'"';
			}
			$link .= ' title="第'.$page.'页">'.$text.'</a>';
		}
		$link .= ' ';
		return $link;
	}
	
	/**
	 * 生成翻页代码
	 *
	 * @return string
	 */
	public function renderNav($page_num=10){
		$pre_num = ceil($page_num/2)-1;
		$ret = '';
		if ( $this->_total_page<1 ){
			return $ret;
		}
		$ret = '共'.$this->_total.'条 共'.$this->_total_page.'页 当前第<strong>'.$this->_current_page.'</strong>页 [ ';
		// 中间页列表
		$start_page = $this->_current_page-$pre_num;
		if ( $start_page<1 ){
			$start_page = 1;
		}
		$end_page = $start_page + $page_num - 1;
		if ( $end_page>$this->_total_page ){
			$end_page = $this->_total_page;
			$start_page = $this->_total_page - $page_num + 1;
			if ( $start_page<1 ){
				$start_page = 1;
			}
		}
		if ( $end_page<$start_page ){
			return $ret;
		}
		if ( $start_page>1 ){
			$ret .= $this->buildPageLink(1,1).' ';
			$ret .= ' ... ';
		}
		for ($p=$start_page;$p<=$end_page;$p++){
			if ( $p!=$this->_current_page ){
				$ret .= $this->buildPageLink($p,$p).' ';
			}
			else {
				$ret .= '<span>'.$p.'</span> ';
			}
		}
		if ( $end_page<$this->_total_page ){
			$ret .= ' ... ';
			$ret .= $this->buildPageLink($this->_total_page,$this->_total_page).' ';
		}
		$ret .= ' ] ';
		return '<div class="pagelink">'.$ret.'</div>';
	}

}
?>