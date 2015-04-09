<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/9/15
 * Time: 10:42
 */
class TuanProductsController extends AppController{

    var $name = 'TuanProducts';

    var $uses = array('TuanProduct');

    public function admin_index(){
        $tuan_products = $this->TuanProduct->find('all',array(
            'conditions' => array(
                'deleted'=>DELETED_NO
            )
        ));
        $this->set('datas',$tuan_products);
    }

    public function admin_new(){
    }
    public function admin_create(){
        if($this->TuanProduct->save($this->data)){
            $this->redirect(array('controller' => 'tuan_products','action' => 'index'));
        }else{
            //error
        }
    }

    public function admin_edit($id){
        $data_info  = $this->TuanProduct->find('first',array(
            'conditions' => array(
                'id' => $id
            )
        ));
        if(empty($data_info)){
            throw new ForbiddenException(__('该团购商品不存在！'));
        }
        $this->set('data',$data_info);
        $this->set('id',$id);
    }

    public function admin_update($id){
        $this->log('update tuan product '.$id.': '.json_encode($this->data));
        $this->autoRender = false;
        if($this->TuanProduct->save($this->data)){
            $this->redirect(array('controller' => 'tuan_products','action' => 'index'));
        }
        $this->set('id',$id);
    }

    public function admin_delete($id){
        $this->log('delete tuan product '.$id);
        $data_info  = $this->TuanProduct->find('first',array(
            'conditions' => array(
                'id'=>$id
            )
        ));
        if(!empty($data_info)){
            if($this->TuanProduct->updateAll(array('deleted' => 1),array('id' => $id))){
                $this->redirect(array('controller' => 'tuan_products','action' => 'index'));
            }
        }else{
            //error
        }
    }

    public function admin_api_tuan_products(){
        $this->autoRender=false;
        echo getTuanProductsAsJson();
    }
}