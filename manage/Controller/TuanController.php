<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController{

    var $name = 'Tuan';

    var $uses = array('TuanTeam','TuanBuying','TuanTeam');

    /**
     * index view
     */
    public function admin_view(){

    }
    /**
     * query tuan orders
     */
    public function admin_tuan_orders(){




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
}