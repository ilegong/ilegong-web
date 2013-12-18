<?php
/**
 * 双色球选号工具
 * @author arlonzou
 * @2012-11-2下午3:30:18
 */
App::uses('RequestFacade', 'Network');
App::uses('Charset', 'Lib');

class LotteriesController extends AppController {

	var $name = 'Lottery';
	
	var $helpers = array('Combinator',);
	
	var $components = array('TaskQueue');//'Auth',
	
	private $lotteries = array();
	private $lottery_reds = array();
	
	public $request_arr =  array(
				'header' => array(
						'Referer' => 'http://baidu.lecai.com/lottery/draw/list/50',
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0',
				),
		);
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->layout = false;
		$lotteries = $this->Lottery->find('all',array(
				'fields'=> 'LotteryRed.*,Lottery.*',
				'joins'=>array(
					array('table'=>'lottery_reds','alias'=>'LotteryRed','type' => 'left','conditions'=>array('Lottery.name=LotteryRed.name')),		
				),
				'order'=> 'created desc',
				'limit'=>300,
				));
		foreach($lotteries as $item){
			$this->lotteries[$item['Lottery']['name']] = $item['Lottery'];
			$this->lottery_reds[$item['Lottery']['name']] = $item['LotteryRed'];
		}
		ksort($this->lotteries);
	}
	/**
	 * 获取蓝球与前n（1-100)期相同的几率
	 * @return array
	 * */
	private function getRates(){
		$lottery = $this->Lottery->find('first',array(
				'order'=> 'created desc',
				'limit'=>100,
				'page'=> 100,
				'rows'=>1,
		));
		$rate = array();
		
		for($i=101;$i<=300;$i++){
			$lotteries = $this->Lottery->find('all',array(
					'conditions' => array('name >'=> $lottery['Lottery']['name']),
					'order'=> 'created desc',
					'group'=> 'param'.$i,
					'fields' => array('count(param'.$i.') as count','param'.$i),
					'limit'=>100,
			));
			$occ = $noc = 0;
			foreach($lotteries as $item){
				if($item['Lottery']['param'.$i]==0){
					$noc = $item[0]['count'];
				}
				elseif($item['Lottery']['param'.$i]==1){
					$occ = $item[0]['count'];
				}
			}
			$rate['param'.$i] = round($occ*100/($occ+$noc));
			
		}
		return $rate;
	}
	/**
	 * 显示选择页面，
	 * @see AppController::index()
	 */
	public function index(){
		
		$rates = $this->getRates();
		
		$reds = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33);
		$blues = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16);
		$lotteries= $this->lotteries;
		krsort($lotteries);
		$lotteries = array_values($lotteries);
// 		for($i=0;$i<2;$i++)
// 		{
// 			array_shift($lotteries); //弹出最后的一期，根据之前的期数，预测最后的一期判断是否预测正确
// 		}
		
		$lastlottery = current($lotteries);
		$year = substr($lastlottery['name'],0,4); //年份
		$index = substr($lastlottery['name'],4)+1; // 下期号
		
		$exclude_blues = array();
		
		$include_reds = $exclude_reds = array();
		
		$i=0;
		foreach($_REQUEST as $k => $qv){
			if(strpos($k,'blue_red_')!==false){ // 蓝球可选项 排除前几期的红球号
				$num = substr($k,9);
				$seclottery = $lotteries[$num-1];
				$secreds = array($seclottery['red1'],$seclottery['red2'],$seclottery['red3'],$seclottery['red4'],$seclottery['red5'],$seclottery['red6'],);
				$blues = array_diff($blues,$secreds);
			}
			elseif(strpos($k,'blue_blue_')!==false){ // 蓝球可选项 排除前几期的蓝球号
				$num = substr($k,10);
				$seclottery = $lotteries[$num-1];
				$secreds = array($seclottery['red1'],$seclottery['red2'],$seclottery['red3'],$seclottery['red4'],$seclottery['red5'],$seclottery['red6'],);
				$exclude_blues[] = $seclottery['blue1'];
				foreach($blues as $bk => $v){
					if($v==$seclottery['blue1']){
						unset($blues[$bk]);break;
					}
				}
			}
			elseif(strpos($k,'red_red_')!==false){ // 蓝球可选项 排除前几期的蓝球号
				//echo "====$k===$qv==".var_dump($qv===0,true)."=";
				if($qv==="" ) continue;
				
				$num = substr($k,8);
				$seclottery = $lotteries[$num-1];
				$secreds = array($seclottery['red1'],$seclottery['red2'],$seclottery['red3'],$seclottery['red4'],$seclottery['red5'],$seclottery['red6'],);
				echo "<br/>$num===$qv==";
				echo implode(',',$secreds);
				
				foreach($secreds as $red){
					if($qv==0){
							$exclude_reds[]=$red;
					}
					else{
						if(isset($include_reds[$red])){
							$include_reds[$red]++;
						}
						else{
							$include_reds[$red]=1;
						}
					}
				}
				$lottery_histories = '';
				$i=0;
				foreach($this->lottery_reds as $ki => $item){
					$i++;
					if($i>=15) break;
					$lottery_histories .= $item['param'.(100+$num)]?'<font color="blue">'.$item['param'.(100+$num)].'</font>':'0';
				}
				echo "&nbsp;&nbsp;&nbsp;&nbsp;\t\t  $lottery_histories\n";
			}
			elseif(strpos($k,'blue_year_red_')!==false){ // 蓝球可选项 排除去年同期的红球号
				$num = substr($k,14);
				$tmp_year = $year-$num;
				$qihao = $tmp_year.sprintf('%03d',$index);
				if(isset($this->lotteries[$qihao])){
					$seclottery = $this->lotteries[$qihao];
					$secreds = array($seclottery['red1'],$seclottery['red2'],$seclottery['red3'],$seclottery['red4'],$seclottery['red5'],$seclottery['red6'],);
					$blues = array_diff($blues,$secreds);
				}
			}
			elseif(strpos($k,'blue_date')!==false){ // 蓝球可选项 排除当天日期的尾数
				$day_sufix = substr(date('d'),-1);
				foreach($blues as &$v){
					if(substr($v,-1)==$day_sufix){
						unset($v);break;
					}
				}
			}
			elseif(strpos($k,'blue_qihao')!==false){ // 蓝球可选项 排除当天期号的尾数
				$q_sufix = substr($index,-1);
				foreach($blues as &$v){
					if(substr($v,-1)==$q_sufix){
						unset($v);break;
					}
				}
			}
		}
		ksort($include_reds);
		foreach($include_reds as $k => $v){
			if($v<2) continue;
			echo "<br/>$k====>$v\n";
		}
		if(!empty($exclude_reds)){
			echo "<br/>";
			$tmp = array_unique($exclude_reds); sort($tmp);
			echo "<u>被排除的红球： ".implode(',',$tmp); echo "</u><br/>";
		}
// 		$i=0;
// 		foreach($this->lottery_reds as $k =>$v){
// 			$i++;
// 			print_r($k);
// 			print_r($v);
// 			if($i<5){
// 				break;
// 			}
// 		}
// 		exit;
		$this->set('lotteries',$lotteries);
		$this->set('lottery_reds',array_values($this->lottery_reds));
		$this->set('rates',$rates);
		$this->set('explode_rate',$_REQUEST['explode_rate']?$_REQUEST['explode_rate']:2);
		$this->set('blues',$blues);
		$this->set('reds',$reds);
		
		sort($exclude_blues);
		$this->set('exclude_blues',$exclude_blues);
		
		
	}
	

	/**
	 * 
	 * 计算蓝球，红球与各期比较，设置相应的字段标记位的值
	 */	
	public function calculate(){
		$this->autoRender = false;
		
		$lotteries = $this->Lottery->find('all',array(
				'fields'=>array('name','red1','red2','red3','red4','red5','red6','blue1','created'),
				'order'=> 'created desc',
				'limit'=> '600'
		));
		
		krsort ($lotteries);
		$lotteries = array_values($lotteries);
		
		$this->loadModel('LotteryReds');
		
		$key = 0;
		foreach($lotteries as $v_lottery){
			$item = $v_lottery['Lottery'];			
			if($key==0){ $key++;continue;}
			$reds = array($item['red1'],$item['red2'],$item['red3'],$item['red4'],$item['red5'],$item['red6'],);
			$blue = $item['blue1'];
			$year = substr($item['name'],0,4);
			$data = array();			
			$red_data = array();
			for($j=1;$j<201;$j++){			
				if($key>$j){
					$seclottery = $lotteries[$key-$j];// 与前$j期进行比较
					$secreds = array($seclottery['Lottery']['red1'],$seclottery['Lottery']['red2'],$seclottery['Lottery']['red3'],$seclottery['Lottery']['red4'],$seclottery['Lottery']['red5'],$seclottery['Lottery']['red6'],);
					if($blue==$seclottery['Lottery']['blue1']){
						$data['param'.(100+$j)] = 1;//篮球是否与前$i期蓝球相同
					}
					$tmp = array_intersect($secreds,$reds);
					//if(in_array($seclottery['Lottery']['blue1'],$reds)){ //红球是否包含前$j期蓝球
					if(!empty($tmp)){
						$red_data['param'.(100+$j)] = count($tmp);
					}
				}
			}
			
			if(!empty($data)){
				$this->Lottery->updateAll($data, array('name'=>$item['name']));
			}
			if(!empty($red_data)){
				$exists = $this->LotteryReds->find('first',array(
					'fields'=>array('name'),
					'conditions'=>array('name'=>$item['name']),
				));
				if(!empty($exists)){
					$this->LotteryReds->updateAll($red_data, array('name'=>$item['name']));;
				}
				else{
					$red_data['name'] = $item['name'];
					$this->LotteryReds->save($red_data);
				}
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
		$page = intval($_REQUEST['page'])?intval($_REQUEST['page']):1;
		if($page>1){		
			$url = 'http://baidu.lecai.com/lottery/draw/list/50?lottery_type=50&page='.$page.'&ds=2000-04-18&de='.date('Y-m-d');
		}
		else{
			$url = 'http://baidu.lecai.com/lottery/draw/list/50';
		}
		$response = RequestFacade::get($url, array(), $this->request_arr);
		$useDbConfig = $this->Lottery->useDbConfig;
		$dbconfig = new DATABASE_CONFIG();
		$prefix = $dbconfig->{$useDbConfig}['prefix'];
		if(preg_match_all('/<tr class="bgcolor\d">\s+<td class="td1">(.+?)<\/td>\s+<td class="td2">\s+<a href=".+?">(.+?)<\/a>\s+<\/td>\s+<td class="td3"><span class="result">\s+<span class="ball_\d">(\d+)<\/span>\s+<span class="ball_\d">(\d+)<\/span>\s+<span class="ball_\d">(\d+)<\/span>\s+<span class="ball_\d">(\d+)<\/span>\s+<span class="ball_\d">(\d+)<\/span>\s+<span class="ball_\d">(\d+)<\/span>\s+<span class="ball_\d">(\d+)<\/span>\s+/is',$response->body(),$matches)){
			foreach($matches[1] as $k=>$v){
				$data = array();
				
				$data['name'] = $matches[2][$k];
				$data['red1'] = $matches[3][$k];
				$data['red2'] = $matches[4][$k];
				$data['red3'] = $matches[5][$k];
				$data['red4'] = $matches[6][$k];
				$data['red5'] = $matches[7][$k];
				$data['red6'] = $matches[8][$k];
				$data['blue1'] = $matches[9][$k];
				$fields = $values = array();
				print_r($data);
				$hasExists = $this->Lottery->find('first',array('conditions'=>array('name'=>$data['name'])));
				if($hasExists){
					// updateAll非数字类字段需要手动加上单引号
					$data['created'] = "'".$v."'";
					$this->Lottery->updateAll($data, array('name'=>$data['name']));
				}
				else{
					$data['created'] = $v;
					$this->Lottery->create();
					$this->Lottery->save($data);
				}
// 				foreach($data as $key => $val){
// 					$fields[] = "`$key`";
// 					$values[] = "'".$val."'";
// 				}
// 				$sql = "REPLACE INTO `{$prefix}lotteries` (".implode(',',$fields).") VALUES(".implode(',',$values).")";				
// 				$this->Lottery->query($sql);
			}
		}
		echo "get <i>$url</i> over.";
		$this->calculate();
	}
	
}
?>