<?php
App::uses('Xml', 'Utility');
App::uses('CrawlUtility', 'Utility');
App::uses('File', 'Utility');
App::uses('Charset', 'Lib');
App::uses('ImageResize','Lib');
App::uses('RequestFacade', 'Network');
/**
 * ????? ??????????
 * @author arlonzou
 *
 */
class YytsController extends CrawlToolAppController {
	
	public $uses = false;
	/**
	 * /manage/admin/crawl_tool/CrawlHuabans
	 */
	public function beforeFilter(){
		parent::beforeFilter();
		set_time_limit(0);
	}
	
	private function login(){
		
		$loginurl = 'http://124.126.136.215/login.aspx?ReturnUrl=%2fdefault.aspx';
		$request = array(
				'header' => array(
						'Referer' => 'http://124.126.136.215/login.aspx',
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				),
		);
		$response = RequestFacade::get($loginurl, array(), $request);
		print_r($response->headers);
		$content = $response->body($content);
		echo htmlspecialchars($content);
		//<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="tv84SU/nbfO4FzSFTXH3NY/V/pjEBPhBigs27NV7zO3DynlQyW/DA8R8+NuriB8Rpj0f8BtIO96hzRxCLGVqif55EeQn3juMp68+mS8rGbUozfQOtpbZ8BTC7GBAzQPj55qeOXa2KF/PGZ7V0xiWRiPrxmtQrlGXOjE7wZqjkou6lf3JcglLM6QTSp9cj+zye6aEt4w9LvDbH0UH25GaReo+PtKdpeK8VGOrru3zxBWhL782gn68Bpdiam02I+fJFGF3bw/s0vUKKx1g2ZNN7v5E81hlKkhZvyq7kJIirBSR+leoOmkAKRWN1KpSwPAiQBQOB+4WXpDGB3kZdyhpDQ==" />
		//<input type="hidden" name="__VIEWSTATEENCRYPTED" id="__VIEWSTATEENCRYPTED" value="" />
		// <input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="4Yg3KNJBXi14elkmngsPv8nrxvQSK3ZQZgE8defZWBzoK5bwkRttVX2G8Ni2w9A5VhEyJ/ykI2660sRl6aG1MjeVWeXKPTAjQecSysIs/KNL7qbKfFoEQmYDErvEmWuv" />
		
		preg_match('/<input.+?name="__VIEWSTATE".+?value="(.+)"[^>]+?>/',$content,$matches);
		$viewstate = $matches[1];
		preg_match('/<input.+?name="__EVENTVALIDATION".+?value="(.+)"[^>]+?>/',$content,$matches);
		$validation = $matches[1];
		$posts = array(
				'UserName'=> 'renda',
				'PassWord'=> '123456',
				'__VIEWSTATE' => $viewstate,
				'__VIEWSTATEENCRYPTED' => '',
				'__EVENTVALIDATION' => $validation,
				'f_digest'=>'',
				'f_dogerror'=>"找不到加密狗,是否已经安装好插件程序？",
				'f_guid'=>'',
				'ImageButton1.x'=>'38',
				'ImageButton1.y'=>'25',
		);
		$response = RequestFacade::post($loginurl, $posts, $request);
	}
	// psc 存放目录的售楼许可证页
	// scv 存放售楼许可证的查看页
	// stv 存放具体楼的户型table展示
	public function admin_getscv(){
		set_time_limit(0);
		
		//psc_3552.html
		$select = 'http://124.126.136.215/sln/select_project.aspx';
		$request1 = $request = array(
				'header' => array(
						'Referer' => $select,
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				),
		);
		$response = RequestFacade::get($select, array(), $request);
		$header = $response->headers;
		print_r($header);
		if(!empty($header['Location'])){
			$this->login();
			exit;
		}
		$files = glob(UPLOAD_FILE_PATH.'/yyt/psc_*.html');
		foreach ($files as $filename) {
			echo "===filename==$filename=======\n<br/>";
			// $filename = UPLOAD_FILE_PATH.'/yyt/psc/psc_3552.html'; // fix finame for test.
			$request['header']['Referer'] = 'http://124.126.136.215/data/project_salecards.aspx?id=4286';
			//<a href="javascript:View('salecard_view.aspx?code_id=3-10211237-1',950,580)" class="blue">x京房权证通字第1217898号</a>
		    $content = file_get_contents($filename);
		    if(preg_match_all('|href="javascript:View\(\'salecard_view\.aspx\?code_id=(\d+-\d+-\d+)\',|',$content,$matches)){
			    foreach($matches[1] as $code_id){
			    	// 许可证具体页面
					// http://124.126.136.215/data/salecard_view.aspx?code_id=2-3657099-1
					//javascript:View('saletable_view.aspx?buildingid=370721&code_id=2-3657099-1',800,600)
					// 楼盘表详情页
					//http://124.126.136.215/data/saletable_view.aspx?buildingid=370699&code_id=2-3657099-1
					$scv_url = 'http://124.126.136.215/data/salecard_view.aspx?code_id='.$code_id;
					$response = RequestFacade::get($scv_url, array(), $request);
					$html = $response->body();
					echo "===code_id==$code_id=======\n<br/>";
					file_put_contents(UPLOAD_FILE_PATH.'/yyt/scv/scv_'.$code_id.'.html',$html);
					if(preg_match_all('|javascript:View\(\'saletable_view\.aspx\?buildingid=(\d+)&|',$html,$matches1)){
							$request['header']['Referer'] = $scv_url;
							foreach($matches1[1] as $buildingid){
								echo "==building===$code_id===$buildingid====\n<br/>";
								$stv_url = 'http://124.126.136.215/data/saletable_view.aspx?buildingid='.$buildingid.'&code_id='.$code_id;
								$response = RequestFacade::get($stv_url, array(), $request);
								print_r($response->headers);
								$html = $response->body();
								if($html){
									file_put_contents(UPLOAD_FILE_PATH.'/yyt/stv/stv_'.$buildingid.'-'.$code_id.'.html',$html);
								}
							}
					}
			    }
			}			
			//else{
				//UPLOAD_FILE_PATH.'/yyt/'.$page.'.html'
			rename ($filename , UPLOAD_FILE_PATH.'/yyt/psc/'.basename($filename));
			//}
			echo 'finish this time.<meta http-equiv="refresh" content="1; url='.Router::url('/admin/crawl_tool/yyts/getscv').'" />';
			exit;
		}
		echo 'over';
		exit;
	}
	
	public function admin_getproject($page=1){
		set_time_limit(0);
		$select = 'http://124.126.136.215/sln/select_project.aspx';
		$request1 = $request = array(
				'header' => array(
						'Referer' => $select,
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				),
		);
		$response = RequestFacade::get($select, array(), $request);
		$header = $response->headers;
		print_r($header);
		if(!empty($header['Location'])){
			$this->login();
		}
		else{
			//for($page=1;$page<=20;$page++){
				$content = file_get_contents(UPLOAD_FILE_PATH.'/yyt/'.$page.'.html');
				//<a href="/data/project_view.aspx?id=4303">
				preg_match_all('|"/data/project_view\.aspx\?id=(\d+)"|',$content,$matches);
				foreach($matches[1] as $id){
					$project_url = 'http://124.126.136.215/data/project_view.aspx?id='.$id;
					$response = RequestFacade::get($project_url, array(), $request);
					$html = $response->body();
					file_put_contents(UPLOAD_FILE_PATH.'/yyt/p_'.$id.'.html',$html);
					
					// 预售许可证
					//http://124.126.136.215/data/project_salecards.aspx?id=4286
					$psc_url = 'http://124.126.136.215/data/project_salecards.aspx?id='.$id;
					$request1['header']['Referer'] = $project_url;
					$response = RequestFacade::get($psc_url, array(), $request1);
					$html = $response->body();
					file_put_contents(UPLOAD_FILE_PATH.'/yyt/psc_'.$id.'.html',$html);
					echo "page $page==get id===$id=====\n";
				}
				if($page < 20){
					$page++;
					echo '<meta http-equiv="refresh" content="3; url='.Router::url('/admin/crawl_tool/yyts/getproject/'.$page).'" />';
				}
				else{
					echo 'over';	
				}
				exit;
			//}
		}
	}
	
	public function admin_index(){
		return;
		set_time_limit(0);
		$select = 'http://124.126.136.215/sln/select_project.aspx';
		$request = array(
				'header' => array(
						'Referer' => $select,
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				),
		);
		$response = RequestFacade::get($select, array(), $request);
		$header = $response->headers;
		print_r($header);
		if(!empty($header['Location'])){
			$this->login();
		}
		else{
			$html = $response->body();
			set_time_limit(0);
			for($i=1;$i< 21;$i++){
				set_time_limit(0);
				preg_match('/<input.+?name="__VIEWSTATE".+?value="(.+)"[^>]+?>/',$html,$matches);
				$viewstate = $matches[1];
				preg_match('/<input.+?name="__EVENTVALIDATION".+?value="(.+)"[^>]+?>/',$html,$matches);
				$validation = $matches[1];
				if($i==1){
					$eventtarget = 'ctl00$main$PreviousPager1$f_pagesize';
				}
				else{
					$eventtarget = 'ctl00$main$NumericPager1$p'.$i;//	ctl00$main$NumericPager1$p5
				}
				$posts = array(
						'__VIEWSTATE' => $viewstate,
						
						'__EVENTVALIDATION' => $validation,
						'__EVENTTARGET'=> $eventtarget,
						'__EVENTARGUMENT'=>'',
						'__LASTFOCUS'=>'',
						'__VIEWSTATEENCRYPTED' => '',
						'ctl00$main$PreviousPager1$f_pageindex'=> $i,
						'ctl00$main$PreviousPager1$f_pagesize'=> '200',
						'ctl00$main$f_city_code'=>'不限',
						'ctl00$main$f_direction'=>'不限',
						'ctl00$main$f_district'=>'不限',
						'ctl00$main$f_propery'=>'不限用途',
						'ctl00$main$f_ring_road'=>'不限环线',
						'ctl00$main$f_project'=>'',
						'ctl00$main$f_slnname'=>'',
				);
				$response = RequestFacade::post($select, $posts, $request);
				print_r($response->headers);
				$html = $response->body();
				file_put_contents(UPLOAD_FILE_PATH.'/yyt/'.$i.'.html',$html);
				echo time()."===$i===\n";
				//exit;
			}
			////__VIEWSTATE,__EVENTVALIDATION,,__EVENTTARGET
			//__EVENTARGUMENT,__VIEWSTATEENCRYPTED
			/*
			ctl00$main$PreviousPager1...	2 //页数
ctl00$main$PreviousPager1...	200 //每页200条
ctl00$main$f_city_code	不限
ctl00$main$f_direction	不限
ctl00$main$f_district	不限
ctl00$main$f_project	
ctl00$main$f_propery	不限用途
ctl00$main$f_ring_road	不限环线
ctl00$main$f_slnname
			*/
		}
		
		// 项目档案
		//http://124.126.136.215/data/project_view.aspx?id=4296
		// 销售许可证
		//http://124.126.136.215/data/project_salecards.aspx?id=4286
		// javascript:View('salecard_view.aspx?code_id=2-3657099-1',950,580)
		// 许可证具体页面
		// http://124.126.136.215/data/salecard_view.aspx?code_id=2-3657099-1
		//javascript:View('saletable_view.aspx?buildingid=370721&code_id=2-3657099-1',800,600)
		// 楼盘表详情页
		//http://124.126.136.215/data/saletable_view.aspx?buildingid=370699&code_id=2-3657099-1
		exit;
	}
}
?>