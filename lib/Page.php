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
	
	public $_seprator = '&nbsp;|&nbsp;';
	
	/**
	 * 构造函数
	 *
	 * @param   int     $total    记录总树
	 * @param   int     $perpage  每页的记录条数
	 * @param   string  $url      请求地址，如果不提供，将根据当前请求处理数据. url中可使用named方式传入page
	 * @param   string  $current_page 当前页
	 **/
	public function __construct($total,$perpage,$url='',$current_page=1){
		//echo $url.'<br/>';		
		$this->_first_page = '首页';
		$this->_current_page = $current_page ? $current_page :1;
		$this->_pre_page = '上一页';
		
		$this->_next_page = '下一页';
		$this->_select_txt = '第%d页';
		$this->_last_page = '尾页';
		$this->_total_txt = '共<span>%d</span>条';
		$this->_pages_txt = '共<span>%d</span>页';
		$this->_pagesize_txt = '<span>%d</span>条/页';
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
		$namedConfig = Router::namedConfig();
		$this->_url_pre = $url;
//		echo $this->_url_pre.'<br>';
	}
	
	function getLink($page){		
		if(strpos($this->_url_pre,'?')===false){
			$link = $this->_url_pre."?page=$page";
		}
		else{
			$link = $this->_url_pre."&page=$page";
		}
		return $link;
	}
	
	/**
	 * 创建链接
	 *
	 * @param int $page 
	 * @param string $text
	 * @return string
	 */
	public function buildPageLink($page,$text=''){
		$link = $this->getLink($page);
		
		if ( $this->_ajax_func ){
			$link = 'javascript:'.$this->_ajax_func.'(\''.substr($link,strlen($this->_url)+1).'\');';
		}
		/**
		 * target为_self,在desktop风格中,本窗口打开链接内容,不弹出新窗口
		 */
		if ( $text ){
			$link = '<a href="'.$link.'" target="_self"'; 
			if ( $this->_link_css ){
				$link .= ' class="'.$this->_link_css.' ui-page-default"';
			}
			else{
				$link .= ' class="ui-page-default"';
			}
			$link .= '>'.$text.'</a>';
		}
		$link .= ' ';
		return $link;
	}
	
	/**
	 * //style=2 共118条 | 首页 | 上一页 | 下一页 | 尾页 | 65条/页 | 共2页  <select>第1页</select>
	 */
	public function renderNav2($page_num=10){
		$ret = sprintf($this->_total_txt,$this->_total).$this->_seprator;
		// 首页
		$ret .= $this->buildPageLink(1,$this->_first_page).$this->_seprator;
		// 上一页
		if($this->_current_page>1){
			$ret .= $this->buildPageLink(1,$this->_pre_page).$this->_seprator;
		}
		else{
			$ret .= '<span class="disabled">'.$this->_pre_page.'</span>'.$this->_seprator;
		}
		// 下一页
		if($this->_current_page < $this->_total_page){
			$ret .= $this->buildPageLink(1,$this->_next_page).$this->_seprator;
		}
		else{
			$ret .= '<span class="disabled">'.$this->_next_page.'</span>'.$this->_seprator;
		}
		// 尾页
		$ret .= $this->buildPageLink($this->_total_page,$this->_last_page).$this->_seprator;
		$ret .= sprintf($this->_pagesize_txt,$this->_perpage).$this->_seprator;
		$ret .= sprintf($this->_pages_txt,$this->_total_page).$this->_seprator;
		
		$pre_num = ceil($page_num/2)-1;
		$ret .='<select onchange="window.open(this.options[this.selectedIndex].value,\'_self\');">';
		// 中间页列表
		$start_page = $this->_current_page-$pre_num;
		if ( $start_page<1 ){
			$start_page = 1;
		}
		$end_page = $start_page + $page_num - 1;
		if ( $end_page > $this->_total_page ){
			$end_page = $this->_total_page;
			$start_page = $this->_total_page - $page_num + 1;
			if ( $start_page<1 ){
				$start_page = 1;
			}
		}
		
		if ( $start_page>1 ){
			$ret .= '<option value="'.$this->getLink(1).'">'.$p.'</option>';
		}
		for ($p=$start_page;$p<=$end_page;$p++){
			if ( $p!=$this->_current_page ){
				$ret .= '<option value="'.$this->getLink($p).'">'.sprintf($this->_select_txt,$p).'</option>';
			}
			else {
				$ret .= '<option value="'.$this->getLink($p).'" selected="selected">'.sprintf($this->_select_txt,$p).'</option>';
			}
		}
		if ( $end_page<$this->_total_page ){
			for($i=$end_page; $i<$this->_total_page;$i+=$pre_num){
				$ret .= '<option value="'.$this->getLink($i).'">'.sprintf($this->_select_txt,$i).'</option>';
			}
			$ret .= '<option value="'.$this->getLink($this->_total_page).'">'.sprintf($this->_select_txt,$this->_total_page).'</option>';
		}
		$ret .='</select>';
		return '<div class="pagelink">'.$ret.'</div>';
	}
	

/**
 * 生成翻页代码
 * style1 共2991条 200页 当前第11页 [ 1 ... 7 8 9 10 11 12 13 14 15 16 ... 200 ] 
 * @param $page_num
 * @return string
 */
	public function renderNav($page_num=10){
		global $page_style;
		if($page_style==2){
			return $this->renderNav2($page_num);
		}
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
				$ret .= '<span class="ui-page-active">'.$p.'</span> ';
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