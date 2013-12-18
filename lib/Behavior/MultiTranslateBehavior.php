<?php
/**
 * 多语言一对一对应模块行为
 * 		仅默认语言在一个表中，其他语言的数据在另外一个对应的_i18ns表中。
 * 
 * @author Arlon
 *
 */
class MultiTranslateBehavior extends ModelBehavior {
	
	public $runtime = array();
	
	private $saveover = false;
	
	public function setup($model, $config = array()) {
		$db = ConnectionManager::getDataSource($model->useDbConfig);
		if (!$db->connected) {
			trigger_error(
					__d('cake_dev', 'Datasource %s for TranslateBehavior of model %s is not connected', $model->useDbConfig, $model->alias),
					E_USER_ERROR
			);
			return false;
		}
		
		$this->settings[$model->alias] = array();
		$this->runtime[$model->alias]['fields'] = $config;
		$this->runtime[$model->alias]['table'] = Inflector::underscore($model->name).'_i18ns';
		$this->runtime[$model->alias]['class'] = $this->runtime[$model->alias]['name'] = Inflector::classify($this->runtime[$model->alias]['table']);
		$this->bindModel($model);
		return true;
	}

/**
 * Cleanup Callback unbinds bound translations and deletes setting information.
 *
 * @param Model $model Model being detached.
 * @return void
 */
	public function cleanup($model) {
		unset($this->settings[$model->alias]);
		unset($this->runtime[$model->alias]);
	}
/**
 * afterFind Callback
 * 将语言的model_I18ns中的data值替换到model中data上。使数据在模版中显示时，当作只有一个语言版本来处理
 *
 * @param Model $model Model find was run on
 * @param array $results Array of model results.
 * @param boolean $primary Did the find originate on $model.
 * @return array Modified results
 */
	public function afterFind($model, $results, $primary) {
		if (empty($results)) {
			return $results;
		}
		$locale = getLocal(Configure::read('Config.language'));		
		$trans_fields = $this->runtime[$model->alias]['fields'];
		$i18nmodel = $this->runtime[$model->alias]['name'];
		foreach ($results as $key => & $row) {
			if(is_array($row[$i18nmodel])){
				foreach($row[$i18nmodel] as $i18n){
					if($i18n['locale']==$locale){
						foreach ($trans_fields as $field) {
							if (!empty($i18n[$field])) {
								$row[$model->alias][$field] = $i18n[$field];
							}
							unset($row[$i18nmodel]);
						}
					}
				}
			}
		}
		return $results;
	}

/**
 * afterSave Callback
 *
 * @param Model $model Model the callback is called on
 * @param boolean $created Whether or not the save created a record.
 * @return void
 */
	public function afterSave($model, $created) {
		$locale = getLocal(Configure::read('Config.language'));		
		/*仅1对1时，关联保存*/
		if(!$this->saveover && $this->runtime[$model->alias]['fields'])
		{
			$modelClass = $this->runtime[$model->alias]['class'];
			$RuntimeModel = loadModelObject($modelClass);
            if(is_array($model->data[$modelClass]) && !empty($model->data[$modelClass])){
				foreach($model->data[$modelClass] as $lang => $data){  // $lang:zh_cn,en_us
					unset($data['locale']);
					$data = array_delete_value($data,'');
					if(empty($data)){
						continue; // 无输入数据，提交多语言时，跳过不保存。
					}
					$RuntimeModel->create();
					$data['foreign_key'] = $model->id;
					$data['locale'] = $lang;
					$RuntimeModel->save(array($RuntimeModel->alias => $data));
				}
				$this->saveover = true;
            }
		}
		return ;		
	}

/**
 * afterDelete Callback
 *
 * @param Model $model Model the callback was run on.
 * @return void
 */
	public function afterDelete($model) {
		$RuntimeModel = loadModelObject($this->runtime[$model->alias]['class']);
		$conditions = array('foreign_key' => $model->id);
		$RuntimeModel->deleteAll($conditions);
	}

/**
 * Get instance of model for translations.
 *
 * If the model has a translateModel property set, this will be used as the class
 * name to find/use.  If no translateModel property is found 'I18nModel' will be used.
 *
 * @param Model $model Model to get a translatemodel for.
 * @return object
 */
	public function bindModel($model) {
		// 没有初始化对应的多语言模型,对应的多语言表配置不存在
		$className = $this->runtime[$model->alias]['class'];
		$lang = Configure::read('Config.language');		
		if(APP_DIR =='app'){ // 前台仅当前语言不为默认语言时，才绑定。
			if($lang!=DEFAULT_LANGUAGE){
				$model->bindModel(array('hasOne' => 
					array(
						$className => array(
							'className' => $className,
							'foreignKey' => 'foreign_key',						
							'conditions' => array(
								//$className.'.deleted' => '0',
								'locale'	=> $locale = getLocal($lang),
							),
						)
					)
				),false);
			}
		}
		else{
// 			print_r($model->hasMany);
			$model->bindModel(array('hasMany' =>
					array(
							$className => array(
									'className' => $className,
									'foreignKey' => 'foreign_key',
									'conditions' => array(
// 											$className.'.deleted' => '0',
// 											'locale'	=> getLocal(Configure::read('Config.language')),
									),
							)
					)
			),false);
// 			print_r($model->hasMany);
		}
		
		return true;
	}
	
	function unbindModel($model){
		$className = $this->runtime[$model->alias]['class'];
		if(APP_DIR =='app'){
			$model->unbindModel( array('hasOne' => array($className)) );
		}
		else{
			$model->unbindModel( array('hasMany' => array($className)) );
		}
	}
}
