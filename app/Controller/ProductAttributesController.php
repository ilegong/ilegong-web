<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/5/15
 * Time: 16:23
 */

class ProductAttributesController  extends Controller{


    public function getAllProductAttribute(){
        $this->autoRender=false;
        //TODO cache it
        $this->loadModel('ProductAttribute');
        $allAttrs = $this->ProductAttribute->find('all',array(
            'conditions'=>array(
                'deleted'=>0
            )
        ));
        $allAttrs = Hash::extract($allAttrs,'{n}.ProductAttribute');
        echo json_encode($allAttrs);
    }

}