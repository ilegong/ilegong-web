<?php

class ProductSpecialsController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'ProductSpecials';

    var $uses = array('ProductSpecials');

    function admin_index() {
//        $this->Comment->recursive = 0;
//        $this->paginate['Comment']['order'] = 'Comment.created DESC';
//        $this->paginate['Comment']['conditions'] = array();
//        $this->paginate['Comment']['conditions']['Comment.status'] = 1;
//        $this->paginate['Comment']['comment_type'] = 'comment';
//
//        if (isset($this->params['named']['filter'])) {
//        	$filters = $this->params['named']['filter'];
//        	$filters=explode(';',$filters);
//
//            foreach ($filters AS $filterKey => $filterValue) {
//            	if(empty($filterValue))
//            		continue;
//            	list($filterKey , $filterValue) = explode(':',$filterValue);
//                if (strpos($filterKey, '.') === false) {
//                    $filterKey = 'Comment.' . $filterKey;
//                }
//                $this->paginate['Comment']['conditions'][$filterKey] = $filterValue;
//            }
//        }
//
//        if ($this->paginate['Comment']['conditions']['Comment.status'] == 1) {
//            $this->pageTitle .= ': ' . __('Published', true);
//        } else {
//            $this->pageTitle .= ': ' . __('Approval', true);
//        }
//
//        $comments = $this->paginate();
//        $this->set('comments',$comments);
    }

//    function admin_edit($id = null) {
//        $this->pageTitle = __("Edit Comment", true);
//
//        if (!$id && empty($this->data)) {
//            $this->Session->setFlash(__('Invalid Comment', true));
//            $this->redirect(array('action'=>'index'));
//        }
//        if (!empty($this->data)) {
//            if ($this->Comment->save($this->data)) {
//                $this->Session->setFlash(__('The Comment has been saved', true));
//                $this->redirect(array('action'=>'index'));
//            } else {
//                $this->Session->setFlash(__('The Comment could not be saved. Please, try again.', true));
//            }
//        }
//        if (empty($this->data)) {
//            $this->data = $this->Comment->read(null, $id);
//        }
//        parent::admin_edit($id);
//    }


//    function admin_process() {
//        $action = $this->data['Comment']['action'];
//        $ids = array();
//        foreach ($this->data['Comment'] AS $id => $value) {
//            if ($id != 'action' && $value['id'] == 1) {
//                $ids[] = $id;
//            }
//        }
//
//        if (count($ids) == 0 || $action == null) {
//            $this->Session->setFlash(__('No items selected.', true));
//            $this->redirect(array('action' => 'index'));
//        }
//
//        if ($action == 'delete' &&
//            $this->Comment->deleteAll(array('Comment.id' => $ids), true, true)) {
//            $this->Session->setFlash(__('Comments deleted.', true));
//        } elseif ($action == 'publish' &&
//            $this->Comment->updateAll(array('Comment.status' => 1), array('Comment.id' => $ids))) {
//            $this->Session->setFlash(__('Comments published', true));
//        } elseif ($action == 'unpublish' &&
//            $this->Comment->updateAll(array('Comment.status' => 0), array('Comment.id' => $ids))) {
//            $this->Session->setFlash(__('Comments unpublished', true));
//        } else {
//            $this->Session->setFlash(__('An error occurred.', true));
//        }
//
//        $this->redirect(array('action' => 'index'));
//    }
  

}
?>