<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 1/8/15
 * Time: 11:54
 */
class ProductTagsController extends AppController{
    var $name = "ProductTags";
    function admin_list_products(){
        $this->autoRender=false;
        $tagId= $_REQUEST['tagId'];
        $this->loadModel('ProductProductTag');
        $query = array(
            'conditions' => array('tag_id' => $tagId),
        );
        $tagProducts = $this->ProductProductTag->find('all',$query);
        $tagProducts= Hash::extract($tagProducts,'{n}.ProductProductTag');
        $tagProductIds=Hash::map($tagProducts,'',function($newArr){
            return Hash::get($newArr,'product_id');
        });
        if(!empty($tagProductIds)){
            $this->loadModel('Product');
            $products =$this->Product->find('all',array(
                'conditions' => array('id'=>$tagProductIds),
                'recursive'=>-1,
                'fields'=>array('id','name'),
            ));
            $products = Hash::combine($products,'{n}.Product.id','{n}.Product.name');
        }
        foreach($tagProducts as &$item){
             $item['product_name'] = $products[$item['product_id']];
        }
        echo json_encode($tagProducts);
        return;
    }

    function admin_edit_recommend(){
        $this->autoRender=false;
        $id = $_REQUEST['id'];
        $currentRecommend =$_REQUEST['currentRecommend'];
        $this->loadModel('ProductProductTag');
        $this->ProductProductTag->id=$id;
        $isSave = $this->ProductProductTag->saveField('recommend',$currentRecommend);
        if($isSave){
            echo '{"success":true}';
        }else{
            echo '{"success":false}';
        }

        return;
    }
}
