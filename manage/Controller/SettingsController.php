<?php
/**
 * Settings Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class SettingsController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Settings';
/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
    var $uses = array('Setting');
/**
 * Helpers used by the Controller
 *
 * @var array
 * @access public
 */
    var $helpers = array('Html', 'Form');
	/**
	 * 显示指定前缀，且editable为1的所有设置项
	 */
    function admin_prefix($prefix = null) {
        $this->pageTitle = sprintf(__('Settings: %s'), $prefix);
        if(!empty($this->data) && $this->Setting->saveAll($this->data['Setting'])) {
            $this->Session->setFlash(__("Settings updated successfully"));
            $successinfo = array('success'=>__("Settings updated successfully"));
            echo json_encode($successinfo);
            exit;
        }

        $settings = $this->Setting->find('all', array(
            'order' => 'Setting.weight DESC',
            'conditions' => array(
                'Setting.key LIKE' => $prefix . '.%',
                'Setting.editable' => 1,
            ),
        ));
        $this->set('settings',$settings);
        if(count($settings) == 0 ) {
            $this->Session->setFlash(__("Invalid Setting prefix key"));
        }
        $this->set("prefix", $prefix);
    }

    function admin_sort() {
    	if(!empty($_POST['settings']) && is_array($_POST['settings'])){
    		$weight = count($_POST['settings']);
    		foreach($_POST['settings'] as $id){
    			$this->Setting->updateAll(array('weight'=>$weight),array('id'=>$id));
    			$weight--;
    		}
    		$successinfo = array('success' => __('ordered success'));
    	}
    	else{
    		$successinfo = array('error' => __('Error,no post data $_POST[settings]'));
    	}
    	$this->set('successinfo', $successinfo);
    	$this->set('_serialize', 'successinfo');
    }
	
    /**
     * 字段设置，列表显示字段，搜索显示字段等
     */
    function admin_fieldsetting($modelClass,$setting_key){
    	$this->loadModel($modelClass);
    	$model_setting = Configure::read($modelClass);
    	
    	$ext_schema = $this->{$modelClass}->getExtSchema();
    	$fileds = array_keys($ext_schema);
    	if(isset($model_setting[$setting_key])){
    		$listfields=explode(',',$model_setting[$setting_key]);
		}
		else{
			$listfields = $fileds;
		}
		$this->set('checked_fields',$listfields); // list_fields选中字段 
    	$this->set("modelClass", $modelClass);
    	$this->set("setting_key", $setting_key);
    	$this->set("_extschema",$ext_schema);
    }
	/**
	 *  ajax保存参数设置
	 */
	function admin_ajaxesave()
	{
		//$this->loadModel('Setting');

		foreach($_POST['setting'] as $key=>$value){
			if(is_array($value)){
				foreach($value as $k => $v){
					$data = $this->Setting->findByKey($key.'.'.$k);
					if(!empty($data)){
						$this->data=$data;
					}					
					else{
						$this->Setting->create();
						$this->data['Setting']['key']=$key.'.'.$k;
					}
					if(is_array($v))	$v=implode(',',$v);
					$this->data['Setting']['value']=$v;
					if(empty($this->data['Setting']['scope'])){
						$this->data['Setting']['scope']='manage';
					}					
					if($this->Setting->save($this->data)){
						Cache::delete("settings");
						$successinfo = array('success'=>__('Add success',true),'actions'=>array('OK'=>'closedialog'));
					}
					else{
						echo json_encode($this->Setting->validationErrors);
						exit;
					}
				}
			}		
		}
		echo json_encode($successinfo);	
		exit;
	}
}
?>