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

    public function admin_tuan_products(){
        $tuan_products = $this->TuanProduct->find('all',array(
            'conditions' => array(
                'deleted'=>DELETED_NO
            )
        ));
        $this->set('datas',$tuan_products);
    }

    public function admin_add_tuan_products(){
        if(!empty($this->data)){
            //add it
            if($this->TuanProduct->save($this->data)){
                //redirect
                $this->redirect(array('controller' => 'tuanProducts','action' => 'admin_tuan_products'));
            }else{
                //error
            }
        }
    }

    public function admin_edit_tuan_products($id){
        $data_info  = $this->TuanProduct->find('first',array(
            'conditions' => array(
                'id' => $id
            )
        ));
        if(!empty($data_info)){
            if(!empty($this->data)){
                $this->data['TuanProduct']['id'] = $id;
                if($this->TuanProduct->save($this->data)){
                    $this->redirect(array('controller' => 'tuanProducts','action' => 'admin_tuan_products'));
                }
            }else{
                $this->set('data',$data_info);
            }
            $this->set('id',$id);
        }else{
            //error
        }
    }

    public function admin_delete_tuan_products($id){
        $data_info  = $this->TuanProduct->find('first',array(
            'conditions' => array(
                'id'=>$id
            )
        ));
        if(!empty($data_info)){
            if($this->TuanProduct->updateAll(array('deleted' => 1),array('id' => $id))){
                $this->redirect(array('controller' => 'tuanProducts','action' => 'admin_tuan_products'));
            }
        }else{
            //error
        }

    }

}