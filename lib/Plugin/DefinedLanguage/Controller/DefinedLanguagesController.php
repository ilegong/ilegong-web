<?php

/**
 * 自定义语言管理
 * @author arlonzou
 *
 */
class DefinedLanguagesController extends DefinedLanguageAppController {
	var $name = 'DefinedLanguages';
	
	function admin_delete($key){
		$definitions = $this->DefinedLanguage->find('all', array('conditions' => array('key' => $key)));
		foreach($definitions AS $definition){
			$this->DefinedLanguage->del($definition['DefinedLanguage']['id']);
		}
		$this->Session->setFlash(__('Record deleted.'));
		$this->redirect('/defined_languages/admin/');
	}
	
	function admin_add(){
		$this->__viewFileName = 'admin_edit';
		$this->admin_edit();
	}
	
	function admin_edit ($defined_language_key = ""){
		$this->pageTitle = __('Defined Language Details');
		if(empty($this->data)){
			$data = $this->DefinedLanguage->find('all', array('conditions' => array('key like' => '%'.$defined_language_key.'%')));
			$lang_definitions = array();
			foreach($data AS $key => $value){
				$array_key = $value['DefinedLanguage']['language_id'];
				$lan_key = $value['DefinedLanguage']['key'];
				$lang_definitions[$lan_key][$array_key] = $value;
			}
			$this->set('lang_definitions', $lang_definitions);
			$this->set('defined_key',$defined_language_key);
			$this->set('languages', $this->DefinedLanguage->Language->find('all', array('conditions' => array('locale !='=>'zh_tw'), 'order' => array('Language.id ASC'))));
		}
		else{
			$language_descriptions = $this->DefinedLanguage->find('all', array('conditions' => array('key' => $defined_language_key)));
			foreach($language_descriptions AS $language_description){
				$this->DefinedLanguage->del($language_description['DefinedLanguage']['id']);
			}
			foreach($this->data['DefinedLanguage']['DefinedLanguage'] AS $id => $value){
				$new_definition = array();
				$new_definition['DefinedLanguage']['language_id'] = $id;
				$new_definition['DefinedLanguage']['key'] = $this->data['DefinedLanguage']['key'];
				$new_definition['DefinedLanguage']['value'] = $value['value'];				
				$this->DefinedLanguage->create();
				$this->DefinedLanguage->save($new_definition);
			}
			$this->redirect('/defined_languages/admin/list');	
		}
	}
}
?>