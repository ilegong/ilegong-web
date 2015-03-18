<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/16
 * Time: 下午6:29
 */
class TuanTeamsController extends AppController{
    public function mei_shi_tuan(){
        $this->pageTitle = '美食团';
        $tuan_teams = $this->TuanTeam->find('all', array(
            'conditions' =>array('status'=> 0),
            'order' => array('TuanTeam.priority DESC')
        ));
        $this->set('tuan_teams',$tuan_teams);
        $this->set('op_cate','mei_shi_tuan');
    }
    public function info($tuan_id){

    }

    public function lists($pid=null){
        $this->pageTitle = '团购列表';
//        if($pid !=838){
//            $this->redirect('/tuans/lists/838');
//        }
        $this->loadModel('TuanBuying');
        $date_Time = date('Y-m-d', time());
        $tuan_product_num = $this->TuanBuying->query("select sum(sold_num) as sold_number from cake_tuan_buyings  where pid = $pid");
        $tuan_buy_info = $this->TuanBuying->find('all',array('conditions' => array('pid' => $pid,'end_time >' => $date_Time, 'status'=>0)));
        $tuan_buy = Hash::combine($tuan_buy_info,'{n}.TuanBuying.tuan_id','{n}.TuanBuying');
        $tuan_ids = Hash::extract($tuan_buy_info, '{n}.TuanBuying.tuan_id');
        $tuan_info = $this->TuanTeam->find('all', array(
            'conditions' =>array('id'=>$tuan_ids),
            'order' => array('TuanTeam.priority DESC')
        ));
        $this->set('tuan_info',$tuan_info);
        $this->log('num'.json_encode($tuan_product_num));
        $this->set('tuan_buy',$tuan_buy);
        $this->set('tuan_product_num',$tuan_product_num[0][0]['sold_number']);
        $this->set('hideNav',true);

    }

    public function lbs_map($tuan_id=''){
        $this->pageTitle =__('草莓自取点');
        $teamInfo = $this->TuanTeam->find('first',array('conditions' => array('id' => $tuan_id)));
        $this->set('tuan_id',$tuan_id);
        $this->set('name',$teamInfo['TuanTeam']['tuan_name']);
        $this->set('location_long',$teamInfo['TuanTeam']['location_long']);
        $this->set('location_lat',$teamInfo['TuanTeam']['location_lat']);
        $this->set('addr',$teamInfo['TuanTeam']['tuan_addr']);
        $this->set('hideNav',true);
    }

    public function new_tuan(){
        $this->pageTitle = '创建新团';
    }

    public function join_meishituan(){
        $this->pageTitle = '加入美食团';
    }

    public function goods_lists(){
        $this->pageTitle = '团购商品';
        $this->loadModel('TuanBuying');
        $tuan_products = $this->TuanBuying->find('all',array('conditions' => array('pid !=' => null),'group' => array('pid')));
        $tuan_product_ids = Hash::extract($tuan_products,'{n}.TuanBuying.pid');
        $this->loadModel('Product');

        $tuan_products_info = array();
        foreach($tuan_product_ids as $pid){
        $tuan_products_info[$pid] = $this->TuanBuying->query("select sum(sold_num) as sold_number from cake_tuan_buyings  where pid = $pid");
        $tuan_product = $this->Product->find('first',array('conditions' => array('id' => $pid),'fields' => array('deleted','promote_name')));
        $tuan_products_info[$pid]['status'] = $tuan_product['Product']['deleted'];
        $tuan_products_info[$pid]['dec'] = $tuan_product['Product']['promote_name'];
        }
        $this->set('tuan_product_ids',$tuan_product_ids);
        $this->set('tuan_products_info',$tuan_products_info);

        $this->log('tuan_products'.json_encode($tuan_products_info));

//        $this->set('hideNav',true);

    }
}

