<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController{

    var $name = 'Tuan';

    var $uses = array('TuanTeam','TuanBuying','Order');

    /**
     * index view
     */
    public function admin_view(){

    }
    /**
     * query tuan orders
     */
    public function admin_tuan_orders(){
        $team_id = $_REQUEST['team_id'];
        $product_id = $_REQUEST['product_id'];
        $time_type = $_REQUEST['time_type'];
        $con_name = $_REQUEST['conn_name'];
        $con_address = $_REQUEST['con_address'];
        $con_phone = $_REQUEST['con_phone'];
        $post_time = $_REQUEST['post_time'];
        $query_tb = array();
        if(!empty($team_id)){
            $query_tb['tuan_id']=$team_id;
        }
        if($time_type==0){
            $query_tb['end_time']=$post_time;
        }else if($time_type==1){
            $query_tb['consign_time']=$post_time;
        }
        if(!empty($product_id)){
            $query_tb['pid'] = $product_id;
        }
        $tuan_buys = $this->TuanBuying->find('all',array(
            'conditions' => $query_tb
        ));
        if(!empty($tuan_buy)){
            $tb_ids = Hash::extract($tuan_buys,'{n}.TuanBuying.id');
            $orders = $this->Order->find('all',array(
                'conditions' => array(
                    'type' => 5,
                    'member_id' => $tb_ids
                )
            ));
            $orders = Hash::combine($orders,'{n}.Order.id','{n}.Order');
            $this->set('orders',$orders);
        }
    }
    /**
     * ajax delete tuan
     * when delete casde tuan buying
     */
    public function admin_delete_tuan(){

    }
    /**
     * ajax delete tuan buying
     */
    public function admin_delete_tuan_buying(){

    }

    /**
     *  ajax save tuan
     */
    public function admin_save_tuan(){

    }

    /**
     * ajax save tuan buying
     */
    public function admin_save_tuan_buying(){

    }
    /**
     * ajax get tuan products
     */
    public function admin_tuan_products(){
        $this->autoRender=false;
        $results = array('838'=>'草莓');
        echo json_encode($results);
    }

    /**
     * ajax get teams
     */
    public function admin_tuan_teams(){
        $this->autoRender=false;
        $teams = $this->TuanTeam->find('all');
        echo json_encode($teams);
    }

}