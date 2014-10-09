<?php

class ProductsController extends AppController{
	
	var $name = 'Products';

    function admin_add() {
        parent::admin_add();

        $productTag_id = $this->data['Product']['productTag_id'];
        if (is_array($productTag_id) && !empty($productTag_id)) {
            $this->loadModel('ProductProductTag');
            foreach ($productTag_id as $tagId) {
                $this->ProductProductTag->save(array('ProductProductTag' => array('product_id' => $this->data['Product']['id'], 'tag_id' => $tagId)));
            }
        }

        $this->loadModel('ProductTag');
        $this->set('productTags', $this->ProductTag->find('list'));
    }

    function admin_edit($id = null,$copy = NULL) {
        parent::admin_edit($id, $copy);

        $this->loadModel('ProductProductTag');
        $inDb = $this->ProductProductTag->find('all', array(
            'conditions' => array('product_id' => $id),
            'fields' => array('tag_id')
        ));

        $tagIdsInDb = array();
        foreach($inDb as $i) {
            $tagIdsInDb[] = $i['ProductProductTag']['tag_id'];
        }
        $productTag_id = $this->data['Product']['productTag_id'];
        if (is_array($productTag_id) && !empty($productTag_id)) {
            sort($tagIdsInDb);
            sort($productTag_id);
            if ($tagIdsInDb != $productTag_id) {
                foreach ($productTag_id as $tagId) {
                    if (array_search($tagId, $tagIdsInDb) === false) {
                        $this->ProductProductTag->save(array('ProductProductTag' => array('product_id' => $id, 'tag_id' => $tagId)));
                    }
                }

                foreach($tagIdsInDb as $ppt) {
                    if (array_search($ppt, $productTag_id) === false) {
                        $this->ProductProductTag->deleteAll(array('product_id' => $id, 'tag_id' => $ppt));
                    }
                }
            }

            $this->set('selectedProductTags', $productTag_id);
        } else {
            $this->set('selectedProductTags', $tagIdsInDb);
        }

        $this->loadModel('ProductTag');
        $this->set('productTags', $this->ProductTag->find('list'));
        $this->__viewFileName = 'admin_add';
    }

    protected function _custom_list_option(&$searchoptions) {

        $tagId = intval($_REQUEST['filter']);
        if ($tagId) {
            /*连接Order表，获取收获人信息。 */
            if ($searchoptions['conditions']){
                $searchoptions['conditions']['Tag.tag_id'] = $tagId;
            } else {
                $searchoptions['conditions'] = array(
                    'Tag.tag_id' => $tagId
                );
            }
            $searchoptions['joins'][] = array(
                'table' => 'product_product_tags',
                'alias' => 'Tag',
                'type' => 'left',
                'conditions' => array('Product.id=Tag.product_id'),
            );
        }
        //print_r($searchoptions);
        return $searchoptions;
    }
}