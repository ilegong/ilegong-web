<?php

class ProductsController extends AppController{
	
	var $name = 'Products';

    function admin_add() {
        parent::admin_add();
        $this->loadModel('ProductTag');
        $this->set('productTags', $this->ProductTag->find('list'));
    }

    function admin_edit($id = null,$copy = NULL) {
        $shouldSave = false;
        $productTag_id = array();
        if (empty($this->data) && !empty($this->data['Product']['productTag_id'])) {
            $shouldSave = true;
            $productTag_id = $this->data['Product']['productTag_id'];
        }
        parent::admin_edit($id, $copy);

        if ($shouldSave) {
            sort($this->data['Product']['productTag_id']);
            sort($productTag_id);
            if ($this->data['Product']['productTag_id'] != $productTag_id) {

//                $this->loadModel('ProductProductTag');
//                $this->ProductProductTag->save()
//                foreach($productTag_id as $tagId) {
//
//                }
            }
        }

        $this->loadModel('ProductTag');
        $this->set('productTags', $this->ProductTag->find('list'));
        $this->__viewFileName = 'admin_add';
    }
}