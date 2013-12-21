<?php
/**
 * 腾讯问问自助问答
 * @author arlonzou
 * @2012-11-2下午3:30:18
 */
App::uses('RequestFacade', 'Network');
App::uses('Charset', 'Lib');

class AutoWenwensController extends AppController {

	var $name = 'AutoWenwen';
	
	var $helpers = array('Combinator',);
	
	var $components = array('TaskQueue');//'Auth',
	
	var $qqnick = '';
	var $qq = '270308933';
	
	public $request_arr =  array(
			'header' => array(
					'Referer' => 'http://wenwen.soso.com/',
					'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; rv:23.0) Gecko/20100101 Firefox/23.0',
			),
	);
	
	public function beforeFilter(){
		parent::beforeFilter();
		
		$this->qq = $this->Session->read('qq');
		$this->qqnick  = $this->Session->read('qqnick');
		$this->login_sig = $this->Session->read('login_sig');
		
		if(empty($this->qq)) $this->qq = '270308933';
		
		RequestFacade::getHttpRequest()->setCookieFileName($this->qq.'.soso.com');
		
		if($this->currentUser['id'] || defined('IN_CLI')){
			$this->Auth->allowedActions = array('*','signall','sign','');
		}
		
		$this->layout = false;
		$this->theme = 'default';
	}
	
	public function beforeRender(){
		parent::beforeRender();
		$this->set('qqnick',$this->qqnick);
	}
	
	public function check_login(){		
		$this->autoRender = false;
		$url = 'http://wenwen.soso.com/';
		$request = $this->request_arr;
		$response = RequestFacade::get($url, array(),$request);
		if(!empty($this->login_sig)){
			if(!empty($response->cookies['uin']) && !empty($response->cookies['skey'])){				
					if(!empty($response->cookies['ww_nick']['value'])){
						$this->qqnick = $response->cookies['ww_nick']['value'];
					}
					else{
						$this->qqnick = $response->cookies['uin']['value'];
					}
			}
			return true;
		}
		else{
			$this->login();
			return true;
		}
	}
	
	public function open_task(){
		$this->autoRender = false;
		$this->check_login();
		// 开启事半功倍的任务，可获得双倍经验卡
		$request = $this->request_arr;
		
		$url = 'http://wenwen.soso.com/z/MyTaskSummary.htm?ch=iz.dh.rw&pid=iz.dh.rw';
		$response = RequestFacade::get($url, array(),$request);
		
		$url = 'http://wenwen.soso.com/z/MyTaskAll.htm?taskId=196&ch=irw.w.dj';
		$response = RequestFacade::get($url, array(),$request);
		//echo $response->body;
		
		$request['header']['Referer'] = 'http://wenwen.soso.com/z/MyTaskAll.htm?taskId=196&ch=irw.w.dj';
		$request['header']['X-Requested-With']='XMLHttpRequest';
		$request['header']['Host']='wenwen.soso.com';
		$url = 'http://wenwen.soso.com/z/AsyncJson.htm?id=1&taskId=196';
		echo '<br/>'.$url.'<br/>';
		$response = RequestFacade::get($url, array(),$request);
		echo $response->body;
	}
	
	public function index($cateid='',$pageid=''){
		$cateid = $_GET['cateid']?$_GET['cateid']:'387121152';
		$page = $_GET['page']?$_GET['page']:1;
		
		$this->set('cateid',$cateid);
		
		if(empty($cateid)){
			$cateid = current($this->cateids);
		}
		
		if(!$this->check_login()){
			$this->redirect(array('action' => 'login'));
		}
		$this->autoRender = true;
		
		$questions = array();
		if($page>1){
			$url = 'http://wenwen.soso.com/z/c'.$cateid.'.htm?pg='.($page-1);
		}
		else{
			$url = 'http://wenwen.soso.com/z/c'.$cateid.'.htm';
		}
		$response = RequestFacade::get($url, array(), $this->request_arr);
		$this->qqnick = $this->Session->read('qqnick');
		
		if(preg_match_all('/<a target="_blank" id="long_title_\d+" style="display:none;" href="(.+?)">(.+?)<\/a>.+?<span class="n">(\d+)<\/span>/is',$response->body,$matches)){
			foreach($matches[1] as $k => $url){
				$title = $matches[2][$k];
				///z/q464989749.htm?ch=wtk.title
				preg_match('/q(\d+)\.htm/',$url,$inner_matches);
				$qid = $inner_matches[1];
				$questions[$url] = array(
					'num' => $matches[3][$k],
					'title' => $title,
					'qid' => $qid,
					'ans' => array()
				);
			}
		}
		
		if(!empty($questions)){
			$page_navi = getPageLinks(1000, 20, '/auto_wenwens/index.html', $page);
			$this->set('page_navi', $page_navi);
			
			$this->set('questions',$questions);
		}
		else{
			exit('get question error.');
		}
	}
	
	
	
	/**
	 * TODO. 答题时，附赠“随题论语”，随题警句，随题脑筋急转弯，随题心理测试，等等
	 */
	public function answer(){
		
		if(!$this->check_login()){
			$this->redirect(array('action' => 'login'));
		}
		$this->autoRender = true;
		
		$this->qqnick = $this->Session->read('qqnick');
		// http://wenwen.soso.com/z/q465072992.htm?ch=wtk.title
		$url = 'http://wenwen.soso.com/z/q'.$_POST['qid'].'.htm?ch=wtk.title';
		$response = RequestFacade::get($url, array(), $this->request_arr);
		
		preg_match('/<input type="hidden" name="orig" id="orig" value="(.+?)"\/>/',$response,$matches);
		$orig = $matches[1];
		preg_match('/<input type="hidden" name="origTableName" id="origTableName" value="(.+?)"\/>/',$response,$matches);
		$origTableName = $matches[1];
		preg_match('/<input type="hidden" name="param" id="param" value="(.+?)"\/>/',$response,$matches);
		$param = $matches[1];
		if($param[0]=='S'){ //移除第一位的大写S
			$param = substr($param,1);
		}
		// <input type="hidden" name="teamId" id="teamId" value="(.+?)"\/>
		preg_match('/<input type="hidden" name="teamId" id="teamId" value="(.+?)"\/>/',$response,$matches);
		$teamid = $matches[1];
		
		$url = 'http://wenwen.soso.com/z/LoginState.htm?rnd=1373683182571';
		$response = RequestFacade::get($url, array(), $this->request_arr);
		
		$this->request_arr['header']['Referer'] = $url;
		$this->request_arr['header']['Content-Type'] = 'application/json; charset=UTF-8';
		$this->request_arr['header']['Pragma'] = 'no-cache';
			
		$url = 'http://wenwen.soso.com/z/api/answer/submit2?format=json';
		$params = array(
			'questionid'=>	$_POST['qid'],
			'content' => nl2br($_POST['answer_content']),
			'editorstats'=>'b_undo-0,b_redo-0,b_img-0,b_img_suc-0,b_capture-0,b_capture_Xupdate_down-0,b_capture_Xinstall_down-0,b_capture_toolbar_down-0,b_capture_err-0,b_right_click_paste-0,k_z-0,k_y-0,k_ctrlv_paste-0,k_right_click_paste-0,k_v-0,k_c-0,k_x-0,right_click-0,',
			'param'=>	$param,
			'origtablename'=> $origTableName,
			//'teamcooperate' => !empty($_POST['teamcooperate']) ? true : false,
			//'teamid' =>  $teamid,
		);
		//if( rand(0,10)%5 != 0)
		{ // 自动转换为圈子的回答
			$url = 'http://wenwen.soso.com/z/api/answer/submit?format=json';
			unset($params['teamid'],$params['teamcooperate'],$params['origtablename'],$params['param'],$params['editorstats']);
			//anonymous,content,orig,quanziId,questionId,userId,userName
			$circle_ids = array(4503,4419,1202,5677,1200); //4303,801,,1202 ,1300
			$index = array_rand($circle_ids);
			$quanziId = $circle_ids[$index];
			$params['quanziId'] = $quanziId;
			$params['orig'] = 2023;
			$params['userId'] = '';
			$params['userName'] = '';
			$params['anonymous'] = false;
			$this->request_arr['header']['Referer']='http://wenwen.soso.com/x/QuanQuestions.e?sp='.$quanziId.'&ch=ask.h.bner.ques';
			$this->request_arr['header']['X-Requested-With']='XMLHttpRequest';
			//Referer	http://wenwen.soso.com/x/QuanQuestions.e?sp=1202&ch=ask.h.bner.ques
			//Host	wenwen.soso.com
			//Pragma	no-cache
			//X-Requested-With	XMLHttpRequest
		}
		
		if(!empty($_POST['verifycode'])){ // 当有验证码时，提交验证码。
			$params['verifycode'] = $_POST['verifycode'];
		}
		/* 提交的数据为json结构 */
		$params = json_encode($params);
		$this->request_arr['header']['Content-Type']='application/json; charset=UTF-8';
		$this->request_arr['header']['Host']='wenwen.soso.com';
		$this->request_arr['header']['Pragma']='no-cache';
		
		//Content-Type	application/json; charset=UTF-8
		$response = RequestFacade::post($url, $params, $this->request_arr);
		print_r($response->body);
		exit;
	}
	
	public function getWenwenVerifyCode($qid='465890451'){
		$verify_url = 'http://ptlogin2.soso.com/getimage?aid=6000201&rdm0.084127228'.rand(0,10000000);
		$this->request_arr['header']['Referer'] = 'http://wenwen.soso.com/z/q'.$qid.'.htm';
		$response = RequestFacade::get($verify_url, array(), $this->request_arr);
		header('Content-Type: image/jpeg');
		echo $response->body;
		exit;
	}
	
	public function getPtloginVerifyCode($uin=''){
		$verify_url = 'http://captcha.soso.com/getimage?aid=6000201&r=0.084127'.rand(0,10000000).'&uin='.$uin;
		$this->request_arr['header']['Referer'] = 'http://ui.ptlogin2.soso.com/cgi-bin/login?link_target=blank&appid=6000201&f_url=loginerroralert&hide_uin_tip=1&target=self&qtarget=0&hide_title_bar=1&s_url=http%3A%2F%2Fwenwen.soso.com%2Fz%2FPopLogin.htm';
		$response = RequestFacade::get($verify_url, array(), $this->request_arr);
		header('Content-Type: image/jpeg');
		echo $response->body;
		exit;
	}
	
	public function getAnswerContent(){
		$bqid = $_REQUEST['bqid'];
		$qid = $_REQUEST['qid'];
		$need_verity_code = false;
		$url = 'http://wenwen.soso.com/z/q'.$qid.'.htm';
		$response = RequestFacade::get($url, array(), $this->request_arr);
		if(preg_match('/<div class="vercode">.+?<img id="imgVerifyCode"[^>]*?>/is',$response->body,$matches)){
			$need_verity_code = true;
		}
	
		$url = 'http://zhidao.baidu.com/question/'.$bqid.'.html';
		$response = RequestFacade::get($url, array(), $this->request_arr);
		$content = Charset::gbk_utf8($response->body);
		//<pre id="recommend-content-608951539" 
		// http://zhidao.baidu.com/question/448175599.html		
		if(preg_match('/<pre id="best-content-\d+"[^>]+?>(.+?)<\/pre>/is',$content,$matches) 
			||	preg_match('/<pre id="recommend-content-\d+"[^>]+?>(.+?)<\/pre>/is',$content,$matches)
			|| preg_match('/<pre id="answer-content-\d+"[^>]+?>(.+?)<\/pre>/is',$content,$matches)
			){
			$content = str_replace(array('<br/>','<br />'),"\n",$matches[1]);
			echo json_encode(array('content'=>$content,'need_verify_code'=>$need_verity_code));
			exit;
		}
		else{
			echo json_encode(array('content'=>'no','verifycode'=>$need_verity_code));
			exit;
		}		
	}
	/**
	 * 
	 * 
	 */
	public function getans(){
		$word = Charset::utf8_gbk(strip_tags($_REQUEST['word']));
		$url = 'http://zhidao.baidu.com/search?word='.urlencode(strip_tags($word)).'&lm=0&oa=0&fr=search&date=0&ie=gbk';
		$response = RequestFacade::get($url, array(), $this->request_arr);
		$content = Charset::gbk_utf8($response->body);
		//print_r($content);
		preg_match_all('/<dt class="dt mb-4" alog-alias="result-title-\d+">(.+?)<\/dt>\s+<dd class="dd answer">(.+?)<\/dd>/is',$content,$matches);
		//print_r($matches);
		$answers = $diggs = array();
		foreach($matches[1] as $key => &$value){
			preg_match('/<a href="(.+?(\d+)\.html)"/is',$value,$inner_matches);
			$url = $inner_matches[1];
			$bqid = $inner_matches[2];
			//http://zhidao.baidu.com/question/448175599.html
			$answers[$key] = array(
				'bqid' => $bqid,
				'url' => $url,
				'title' => 	strip_tags($value),
				'content' => strip_tags($matches[2][$key]),
				'digg' => intval($matches[3][$key])?intval($matches[3][$key]):0,
			);
			//$diggs[$key]=$matches[4][$key];
		}
		//array_multisort($diggs, SORT_DESC,$answers,SORT_DESC);
		$items = 3;
		if(count($answers)>$items){
			$answers = array_slice($answers, 0, $items);
		}
		$this->set('answers', $answers);
		$this->set('_serialize', 'answers');
		return $answers;
	} 
	
	public function login(){
		$params = array();
		
		if(empty($_GET['qq']) || $_GET['qq']=='1'){
			$qq = '270308933';
			$pwd = '65E4E803F508209BB7F2164C227D5EA2';
		}
		elseif($_GET['qq']=='2'){
			$qq = '1801832973';
			$pwd = '200820E3227815ED1756A6B531E7E0D2';
		}
		elseif(!empty($_GET['qq']) && !empty($_GET['pwd'])){
			$qq = $_GET['qq'];
			$pwd = strtoupper(md5($_GET['pwd']));
		}
		
		$this->Session->write('qq',$qq);
		$this->Session->write('qq_pwd',$pwd);
		if($qq){
			RequestFacade::getHttpRequest()->setCookieFileName($qq.'.soso.com');
		}
		
		$request = $this->request_arr;		
		
		$url = 'http://wenwen.soso.com/';
		$response = RequestFacade::get($url, array(), $request);
		if(!empty($response->cookies['uin']) && !empty($response->cookies['skey'])){
			$this->qqnick = str_replace('"','',urldecode($response->cookies['ww_nick']['value']));
			$this->Session->write('qqnick',$this->qqnick);
			// 号码相同时，使用已有cookie；不同时，表示换了新的号码，则重新登录
			if(strpos($response->cookies['uin']['value'],$qq)!==false){
				$this->redirect('/auto_wenwens/index');
			}
		}
		
		list($usec, $sec) = explode(" ", microtime());
		$time = $sec.substr($usec,2,3);
		$url = 'http://wenwen.soso.com/z/LoginState.htm?rnd='.$time;
		$response = RequestFacade::get($url, array(), $request);
		
		$url = 'http://ui.ptlogin2.soso.com/cgi-bin/login?link_target=blank&appid=6000201&f_url=loginerroralert&hide_uin_tip=1&target=self&qtarget=0&hide_title_bar=1&s_url=http%3A%2F%2Fwenwen.soso.com%2Fz%2FPopLogin.htm';
		$response = RequestFacade::get($url, array(), $request);
		$login_sig = '';
		if(preg_match('/g_login_sig=encodeURIComponent\("(.+?)"\);/',$response->body,$matches)){
			//$login_sig = urlencode($matches[1]);
			$login_sig = $matches[1];
		}
		else{
			$this->qqnick = '<font color="red">LoginSig Error.</font>';
			return false;
		}
		$this->Session->write('login_sig',$login_sig);
		
		$request['header']['Referer'] = $url;		
		$url = 'http://ui.ptlogin2.soso.com/cgi-bin/ver';
// 		$request['cookies'] = $this->cookies;
		$response = RequestFacade::get($url, array(), $request);
		
		$url = 'http://check.ptlogin2.soso.com/check?uin='.$qq.'&appid=6000201&js_ver=10039&js_type=0&login_sig='.$login_sig.'&u1=http%3A%2F%2Fwenwen.soso.com%2Fz%2FPopLogin.htm&r=0.16855613203266717';
		$response = RequestFacade::get($url, array(), $request);
// 		echo '<hr/>';
// 		echo $url;
// 		echo '<hr/>';
// 		print_r($response->body);
// 		echo '<hr/>';
		
		//ptui_checkVC('0','!FQO','\x00\x00\x00\x00\x10\x1c\x96\x45');
		$verify_code = $pt_uin = '';
		if(preg_match("/ptui_checkVC\('(\d+)','(.+?)','(.+?)'\)/",$response->body,$matches)){
			print_r($matches);
			
			$verify_code = strtoupper($matches[2]);
			$pt_uin = $matches[3];
			eval('$pt_uin="'.$pt_uin.'";'); // 会进行转码，
			//			也可以这样进行转码
			// 			$tmp = '';
			// 			for ($i=0; $i < strlen($pt_uin); $i+= 4) {
			// 				$tmp .= chr(hexdec(substr($pt_uin, $i+2, 2)));
			// 			}
			
			$this->Session->write('pt_uin',$pt_uin);
			
			if($matches[1]==1){
				echo '
				<form action="'.Router::url(array('controller'=>'auto_wenwens','action'=>'slogin')).'" method="post">
				<img id="verify_img" src="'.Router::url(array('controller'=>'auto_wenwens','action'=>'getPtloginVerifyCode',$qq)).'">
				<a href="#" onclick="change_verify_img();">换一张</a>
				<input type="text" name="verify_code" size="5"><br/>
				<input type="submit" name="Submit">
				<script>
				function change_verify_img(){
					document.getElementById("verify_img").src="'.Router::url(array('controller'=>'auto_wenwens','action'=>'getPtloginVerifyCode',$qq)).'?rdm="+Math.random();
				}
				</script>
				';
				exit;
			}
		}
		else{
			$this->qqnick = '<font color="red">VerifyCode Error.</font>';
			return false;
		}
		
		return $this->__login($qq,$pwd, $pt_uin, $verify_code,$login_sig);		
	}
	
	public function __login($qq,$pwd, $pt_uin, $verify_code,$login_sig){
		
		$verify_code = strtoupper($verify_code);
		
		$p = $this->encpwd($pwd, $pt_uin, $verify_code);
		/*
		 * 使用Fiddler调试 http://imgcache.qq.com/ptlogin/ver/10034/js/comm.js
		 * 
		 * http://imgcache.qq.com/ptlogin/ver/10039/js/comm.js?ptui_identifier=000DE0CB8C8BF04473B6DD455E376903E74EF53F01B13C0469747BBB
		 * 
		* if (C[J].name == "p") {
		var M = C.p.value;
		var I = hexchar2bin(md5(M));
		var H = md5(I + pt.uin);
		var G = md5(H + C.verifycode.value.toUpperCase());
		A += G
		}
		*/
		$action = rand(3,10).'-'.(20+rand(0,10)).'-'.(rand(15450,35450)); // action 第一位为鼠标点击数，第二位为输入框的鼠标按下数,第三位为提交页面操作时间
		$request = $this->request_arr;
		$request['header']['Referer'] = 'http://ui.ptlogin2.soso.com/cgi-bin/login?link_target=blank&appid=6000201&f_url=loginerroralert&hide_uin_tip=1&target=self&qtarget=0&hide_title_bar=1&s_url=http%3A%2F%2Fwenwen.soso.com%2Fz%2FPopLogin.htm';
		$loginurl = 'http://ptlogin2.soso.com/login?u='.$qq.'&p='.$p.'&verifycode='.$verify_code.'&aid=6000201&u1=http%3A%2F%2Fwenwen.soso.com%2Fz%2FPopLogin.htm&h=1&ptredirect=0&ptlang=2052&from_ui=1&dumy=&fp=loginerroralert&action='.$action.'&mibao_css=&t=1&g=1&js_type=0&js_ver=10039&login_sig='.$login_sig;
// 		echo '<hr/>';
// 		echo $loginurl;
// 		echo '<hr/>';
		$response = RequestFacade::get($loginurl, array(), $request);
		print_r($response->body);
		
		
		$url = 'http://wenwen.soso.com/z/PopLogin.htm';
		$response = RequestFacade::get($url, array(), $request);
		
		$url = 'http://wenwen.soso.com/';
		$response = RequestFacade::get($url, array(), $request);
		print_r($response->cookies);
		
		if(!empty($response->cookies['uin']) && !empty($response->cookies['skey'])){
			$this->qqnick = str_replace('"','',urldecode($response->cookies['ww_nick']['value']));
			
			$this->Session->write('qqnick',$this->qqnick);
			$this->Session->write('uin',$response->cookies['uin']['value']);
			
			$this->redirect('/auto_wenwens/index');
			return true;
		}
		else{
			$this->qqnick = '<font color="red">Login Error.</font>';
			return false;
		}
	}
	
	public function slogin(){
		if(!empty($_POST)){
			$qq = $this->Session->read('qq');
			$pwd = $this->Session->read('qq_pwd');
			$login_sig  = $this->Session->read('login_sig');
			$pt_uin = $this->Session->read('pt_uin');
			
			$verify_code = $_POST['verify_code'];
			return $this->__login($qq,$pwd, $pt_uin, $verify_code,$login_sig);
		}
		else{
			$this->redirect(array('controller'=>'auto_wenwens','action'=>'login'));
		}
	}
	
	
	/**
	 * 三次MD5运算，QQ模拟登录密码算法
	 * @param $str
	 */
	function md5_3($str) {
		return strtoupper ( md5 ( md5 ( md5 ( $str, true ), true ) ) );
	}
	/**
	 * http://code.google.com/p/phx-svns/source/browse/trunk/PHP/weibo_mo/_CLASS/QZoneSimulaAPI.class.php?r=29
	 * @param unknown_type $hex
	 * @return string
	 */
	function hexchar2bin($hex) {
		$ret = '';
		for($i = 0; $i < strlen ( $hex ); $i += 2) {
			$ret .= chr ( hexdec ( substr ( $hex, $i, 2 ) ) );
		}
		return $ret;
	}
	function uin2hex($qq) {
		$hex = dechex ( $qq );
		$hex = str_repeat ( '0', 16 - strlen ( $hex ) ) . $hex;
		return $this->hexchar2bin ( $hex );
	}
	/**
	 * js的md5函数转换后都是大写的，这里都要将转换为大写 
	 * @param string $pwd
	 * @param string $pt_uin
	 * @param string $vcode
	 */
	function encpwd($pwd, $pt_uin, $vcode) {
		$pbin = $this->hexchar2bin ($pwd); //strtoupper (md5 ( $pwd ))
		$pbin_q = md5 ( $pbin . $pt_uin);		
		$ret = md5 ( strtoupper ( $pbin_q ) . strtoupper ( $vcode ) );
		return  strtoupper ($ret);
	}
}
?>