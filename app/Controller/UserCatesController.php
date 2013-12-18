<?php
/**
 * 用户分类管理
 * @author arlonzou
 *
 */
class UserCatesController extends AppController {

	var $name = 'UserCate';
	
/**
     * 列表
     * @param $slug  为所在类别的slug
     */
    public function add($model='Note') {
    	
    	$option_result = $this->UserCate->generateTreeList(array(
    			'creator'=> $this->currentUser['id'],
    			'model'=>'Note'
    			));
    	$this->set('parents', $option_result);
    	
    	if (!empty($this->data)) {
    		$this->data['UserCate']['model']= $model;
    	}
    	parent::add();
    }
	


}
?>