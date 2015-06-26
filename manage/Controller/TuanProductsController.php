<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/9/15
 * Time: 10:42
 */
class TuanProductsController extends AppController{

    var $name = 'TuanProducts';

    var $uses = array('TuanProduct', 'Product', 'TuanBuying', 'ProductTry');

    public function admin_index(){
        $tuan_products = $this->TuanProduct->find('all',array(
            'conditions' => array(
                'deleted'=>DELETED_NO
            ),
            'order' => 'priority DESC'
        ));
        $product_ids = Hash::extract($tuan_products, "{n}.TuanProduct.product_id");

        $products = $this->Product->find('all', array(
            'conditions' => array(
                'id'=> $product_ids
            ),
            'fields' => array('id', 'name', 'price', 'deleted', 'published')
        ));
        $products = Hash::combine($products, '{n}.Product.id', '{n}.Product');

        $tuan_buyings_count = $this->TuanBuying->query('select pid as id, count(pid) as c from cake_tuan_buyings WHERE STATUS IN ( 0, 1, 2 ) group by pid;');
        $tuan_buyings_count = Hash::combine($tuan_buyings_count, '{n}.cake_tuan_buyings.id', '{n}.0.c');

        $this->set('tuan_buyings_count', $tuan_buyings_count);
        $this->set('tuan_products', $tuan_products);
        $this->set('products', $products);
    }

    public function admin_new(){
    }
    public function admin_create(){
        if($this->TuanProduct->save($this->data)){
            Cache::delete('tuan_products');
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
            Cache::delete('tuan_products');
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
                Cache::delete('tuan_products');
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

    public function admin_api_products(){
        $this->autoRender=false;

        $tuan_products = $this->TuanProduct->find('all',array(
            'conditions' => array(
                'deleted' => DELETED_NO
            ),
            'order' => 'priority desc'
        ));

        $tuanSecKills = $this->ProductTry->find('all',array(
            'conditions' => array(
                'deleted' => DELETED_NO
            )
        ));
        $tuanSecKills = Hash::combine($tuanSecKills, "{n}.ProductTry.product_id", "{n}");

        $products = Hash::combine($tuan_products, "{n}.TuanProduct.product_id", "{n}");
        foreach($products as $product_id => &$product){
            $product['isTuanProduct'] = true;
            if(!empty($tuanSecKills[$product_id])){
                $product['ProductTry'] = $tuanSecKills[$product_id];
                $product['isProductTry'] = true;
            }
        }
        foreach($tuanSecKills as $product_id => &$tuanSecKill){
            if(empty($products[$product_id])){
                $product['isProductTry'] = true;
                $products[$product_id] = $tuanSecKill;
            }
        }

        echo json_encode($products);
    }

    public function admin_api_pys_products(){
        $this->autoRender=false;
        $products = $this->Product->find('all',array(
            'conditions' => array(
                'brand_id' => PYS_BRAND_ID
            )
        ));

        echo json_encode($products);
    }
}