<?php
/**
 * 六合彩特码选号工具
 * @author arlonzou
 * @2013-8-9晚上21:12:18
 */
App::uses('RequestFacade', 'Network');
App::uses('Charset', 'Lib');

class LotteryLhcsController extends AppController {

	var $name = 'LotteryLhc';
	
	var $helpers = array('Combinator',);
	
	var $components = array('TaskQueue');//'Auth',
	
	private $lotteries = array();
	
	public $request_arr =  array(
				'header' => array(
						'Referer' => 'http://baidu.lecai.com/lottery/draw/list/50',
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0',
				),
		);
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->layout = false;
	}
	/**
	 * 显示选择页面，
	 * @see AppController::index()
	 */
	public function index(){
		
		$temas = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49);
		$all_shengxiaos = $shengxiaos = array('蛇','龙','兔','虎','牛','鼠','猪','狗','鸡','猴','羊','马');
		$lotteries = $this->LotteryLhc->find('all',array(
				'fields'=> 'LotteryLhc.*',
				'order'=> 'riqi desc',
				'limit'=>300,
		));
		foreach($lotteries as $item){
			$this->lotteries[$item['LotteryLhc']['riqi']] = $item['LotteryLhc'];
		}
		//ksort($this->lotteries);
		
		$lotteries = array_values($this->lotteries);
// 		for($i=0;$i<1;$i++)
// 		{
// 			array_shift($lotteries); //弹出最后的一期，根据之前的期数，预测最后的一期判断是否预测正确
// 		}
		
		$lastlottery = current($lotteries);
		$exclude_temas = array();
		$exclude_shengxiaos = array();
		
		$i=0;
		foreach($_REQUEST as $k => $qv){
			if(strpos($k,'tema_tema_')!==false){ // 蓝球可选项 排除前几期的蓝球号
				$num = substr($k,10);
				$seclottery = $lotteries[$num-1];
				$exclude_temas[] = $seclottery['tema'];
				foreach($temas as $bk => $v){
					if($v==$seclottery['tema']){
						unset($temas[$bk]);break;
					}
				}
			}
			elseif(strpos($k,'shengxiao_shengxiao_')!==false){ // 蓝球可选项 排除前几期的蓝球号
				if($qv==="") continue;
				$num = substr($k,20);
				$seclottery = $lotteries[$num-1];
				$exclude_shengxiaos[] = $seclottery['shengxiao'];
				foreach($shengxiaos as $bk => $v){
					if($v==$seclottery['shengxiao']){
						unset($shengxiaos[$bk]);break;
					}
				}
			}
		}
		
		$this->set('lotteries',$lotteries);
		$this->set('explode_rate',$_REQUEST['explode_rate']?$_REQUEST['explode_rate']:1);
		$this->set('temas',$temas);
		$this->set('shengxiaos',$shengxiaos);
		$this->set('all_shengxiaos',$all_shengxiaos);
		
		sort($exclude_temas);
		sort($exclude_shengxiaos);
		$this->set('exclude_temas',$exclude_temas);
		$this->set('exclude_shengxiaos',$exclude_shengxiaos);
		
		
	}
	

	/**
	 * 
	 * 计算蓝球，红球与各期比较，设置相应的字段标记位的值
	 */	
	public function calculate(){
		$this->autoRender = false;
		
		$lotteries = $this->LotteryLhc->find('all',array(
				'fields' =>array('qihao','riqi','tema','shengxiao','danshuang','daxiao','rgb','jiaye'),
				'order' => 'riqi desc',
				'limit' => '300'
		));
		
		krsort($lotteries);
		$lotteries = array_values($lotteries);
		
		$key = 0;
		foreach($lotteries as $v_lottery){
			$item = $v_lottery['LotteryLhc'];			
			if($key==0){ $key++;continue;}
			$tema = $item['tema'];
			$shengxiao = $item['shengxiao'];
			$data = array();			
			$red_data = array();
			for($j=1;$j<201;$j++){			
				if($key>$j){
					$seclottery = $lotteries[$key-$j];// 与前$j期进行比较
					if($tema==$seclottery['LotteryLhc']['tema']){
						$data['param'.(100+$j)] = 1;//特码是否与前$i期特码相同
					}
					if($shengxiao==$seclottery['LotteryLhc']['shengxiao']){
						$data['param'.(500+$j)] = 1;//生肖是否与前$i期生肖相同
					}
				}
			}			
			if(!empty($data)){
				$this->LotteryLhc->updateAll($data, array('riqi'=>$item['riqi']));
			}
			$key++;
		}
		echo 'over';
	}
	
	/**
	 * 从百度乐彩网抓取往期双色球的开奖记录
	 update cake_lotteries set param1=0,param2=0,param3=0,param4=0,param5=0,param6=0,param7=0,param8=0,param9=0,param10=0,param11=0,param12=0,param13=0,param14=0,param15=0,param16=0,param26=0,param27=0,param28=0,param29=0,param30=0,param17=0,param18=0,param19=0,param20=0,param21=0,param22=0,param23=0,param31=0,param32=0,param33=0,param34=0,param35=0,param36=0,param37=0,param38=0,param39=0,param40=0,param41=0,param42=0,param43=0,param44=0,param45=0,param46=0,param47=0,param48=0,param49=0,param50=0,param51=0,param52=0,param53=0,param54=0,param55=0,param56=0,param57=0,param58=0,param59=0,param60=0,param101=0,param102=0,param103=0,param104=0,param105=0,param106=0,param107=0,param108=0,param109=0,param110=0,param111=0,param112=0,param113=0,param114=0,param115=0,param116=0,param117=0,param118=0,param119=0,param120=0,param121=0,param122=0,param123=0,param124=0,param125=0,param126=0,param127=0,param128=0,param129=0,param130=0,param131=0,param132=0,param133=0,param134=0,param135=0,param136=0,param137=0,param138=0,param139=0,param140=0,param141=0,param142=0,param143=0,param144=0,param145=0,param146=0,param147=0,param148=0,param149=0,param150=0,param151=0,param152=0,param153=0,param154=0,param155=0,param156=0,param157=0,param158=0,param159=0,param160=0,param161=0,param162=0,param163=0,param164=0,param165=0,param166=0,param167=0,param168=0,param179=0,param170=0,param171=0,param172=0,param173=0,param174=0,param175=0,param176=0,param177=0,param178=0,param169=0,param180=0,param181=0,param182=0,param183=0,param184=0,param185=0,param186=0,param187=0,param188=0,param189=0,param190=0,param191=0,param192=0,param193=0,param194=0,param195=0,param196=0,param197=0,param198=0,param199=0,param200=0,param201=0,param202=0,param203=0,param204=0,param205=0,param206=0,param207=0,param208=0,param209=0,param210=0,param211=0,param212=0,param213=0,param214=0,param215=0,param216=0,param217=0,param218=0,param219=0,param220=0,param221=0,param222=0,param223=0,param224=0,param225=0,param226=0,param227=0,param228=0,param229=0,param230=0,param231=0,param232=0,param233=0,param234=0,param235=0,param236=0,param237=0,param238=0,param239=0,param240=0,param241=0,param242=0,param243=0,param244=0,param245=0,param246=0,param247=0,param248=0,param249=0,param250=0,param251=0,param252=0,param253=0,param254=0,param255=0,param256=0,param257=0,param258=0,param259=0,param260=0,param261=0,param262=0,param263=0,param264=0,param265=0,param266=0,param267=0,param268=0,param269=0,param270=0,param271=0,param272=0,param273=0,param274=0,param275=0,param276=0,param277=0,param278=0,param279=0,param280=0,param281=0,param282=0,param283=0,param284=0,param285=0,param286=0,param287=0,param288=0,param289=0,param290=0,param291=0,param292=0,param293=0,param294=0,param295=0,param296=0,param297=0,param298=0,param299=0,param300=0;
	 update cake_lottery_reds set param101=0,param102=0,param103=0,param104=0,param105=0,param106=0,param107=0,param108=0,param109=0,param110=0,param111=0,param112=0,param113=0,param114=0,param115=0,param116=0,param117=0,param118=0,param119=0,param120=0,param121=0,param122=0,param123=0,param124=0,param125=0,param126=0,param127=0,param128=0,param129=0,param130=0,param131=0,param132=0,param133=0,param134=0,param135=0,param136=0,param137=0,param138=0,param139=0,param140=0,param141=0,param142=0,param143=0,param144=0,param145=0,param146=0,param147=0,param148=0,param149=0,param150=0,param151=0,param152=0,param153=0,param154=0,param155=0,param156=0,param157=0,param158=0,param159=0,param160=0,param161=0,param162=0,param163=0,param164=0,param165=0,param166=0,param167=0,param168=0,param179=0,param170=0,param171=0,param172=0,param173=0,param174=0,param175=0,param176=0,param177=0,param178=0,param169=0,param180=0,param181=0,param182=0,param183=0,param184=0,param185=0,param186=0,param187=0,param188=0,param189=0,param190=0,param191=0,param192=0,param193=0,param194=0,param195=0,param196=0,param197=0,param198=0,param199=0,param200=0,param201=0,param202=0,param203=0,param204=0,param205=0,param206=0,param207=0,param208=0,param209=0,param210=0,param211=0,param212=0,param213=0,param214=0,param215=0,param216=0,param217=0,param218=0,param219=0,param220=0,param221=0,param222=0,param223=0,param224=0,param225=0,param226=0,param227=0,param228=0,param229=0,param230=0,param231=0,param232=0,param233=0,param234=0,param235=0,param236=0,param237=0,param238=0,param239=0,param240=0,param241=0,param242=0,param243=0,param244=0,param245=0,param246=0,param247=0,param248=0,param249=0,param250=0,param251=0,param252=0,param253=0,param254=0,param255=0,param256=0,param257=0,param258=0,param259=0,param260=0,param261=0,param262=0,param263=0,param264=0,param265=0,param266=0,param267=0,param268=0,param269=0,param270=0,param271=0,param272=0,param273=0,param274=0,param275=0,param276=0,param277=0,param278=0,param279=0,param280=0,param281=0,param282=0,param283=0,param284=0,param285=0,param286=0,param287=0,param288=0,param289=0,param290=0,param291=0,param292=0,param293=0,param294=0,param295=0,param296=0,param297=0,param298=0,param299=0,param300=0;
	 */
	public function getLotteries(){
		$this->autoRender = false;
		set_time_limit(0);
		$url = 'http://www.111kj.com/kjjg/2013.htm';//2008~的规律相同，07年及以前的规律不同，忽略不用了
		$response = RequestFacade::get($url, array(), $this->request_arr);
		$useDbConfig = $this->LotteryLhc->useDbConfig;
		$dbconfig = new DATABASE_CONFIG();
		$prefix = $dbconfig->{$useDbConfig}['prefix'];
		$content = $response->body();
		$content = Charset::gbk_utf8($content);
		
		if(preg_match_all('/<tr>.+?<font face="Terminal">(.+?)<\/font><\/td>\s+<td.+?>\s*<font face="Terminal">(.+?)<\/font><\/td>.+?<td.+?><IMG src="49m\/(\d+).gif"><\/td>\s+<td.+?>.+?<\/td>\s+<td.+?>(.+?)<\/td>\s+<\/tr>/is',$content,$matches)){
			foreach($matches[1] as $k=>$v){
				$data = array();				
				$data['riqi'] = str_replace('/','',$v);
				$data['qihao'] = trim($matches[2][$k]);
				$data['tema'] = $matches[3][$k];
				$des = trim(strip_tags($matches[4][$k]));
				//echo $des."\n"; //蛇.单数.小数.红波.野兽
				$tmp = explode('.',$des);
				$data['shengxiao'] = $tmp[0];
				$data['danshuang'] = $tmp[1];
				$data['daxiao'] = $tmp[2];
				$data['rgb'] = $tmp[3];
				$data['jiaye'] = $tmp[4];
				$fields = $values = array();
				$hasExists = $this->LotteryLhc->find('first',array('conditions'=>array('riqi'=>$data['riqi'])));
				if($hasExists){
					// updateAll非数字类字段需要手动加上单引号
					//$data['riqi'] = "'".$v."'";
					$data['shengxiao'] = "'".$data['shengxiao']."'";
					$data['danshuang'] = "'".$data['danshuang']."'";
					$data['daxiao'] = "'".$data['daxiao']."'";
					$data['rgb'] = "'".$data['rgb']."'";
					$data['jiaye'] = "'".$data['jiaye']."'";
					$this->LotteryLhc->updateAll($data, array('riqi'=>$data['riqi']));
				}
				else{
					$this->LotteryLhc->create();
					$this->LotteryLhc->save($data);
				}
			}
		}
		echo "get <i>$url</i> over.";
		$this->calculate();
	}
	
}
?>