<?php
/**
 * 多站点数据筛选行为，
 * 		根据当前站点id的栏目类别，获取子类的数据。当选择了子类时，忽略不处理。
 * @author Arlon
 *
 */

class MultiSiteBehavior extends ModelBehavior {
	
	protected $multi_models = array();
	
	public function setup($model, $config = array()) {
		$schema = $model->schema();
		if(is_array($schema)){
			$fields = array_keys($schema);
		}
		else{
			$fields = array();
		}
		if ($model->isContentModel() && in_array('cate_id',$fields)) {
			$this->multi_models[$model->alias] = true; 
		}
// 		$this->multi_models['Category'] = true;
	}

/**
 * beforeFind Callback,在没有绑定hasMany时，使用join连接多语言表查询出当前语言的值。
 * 指定了绑定hasMany时，不处理。系统会自动查询出所有语言的值。
 *
 * @param Model $model Model find is being run on.
 * @param array $query Array of Query parameters.
 * @return array Modified query
 */
	public function beforeFind($model, $query) {
		if( empty($this->multi_models[$model->alias])){
			return $query;
		}
		if(empty($query['conditions'])) $query['conditions'] = array();
		/* 不包含locale字段时，添加locale字段*/
		if(empty($query['conditions']['cate_id']) && empty($query['conditions'][$model->alias . '.cate_id'])){
			//
			if($model->alias != 'Category'){
				$cate_model = loadModelObject('Category');
				$subcates = $cate_model->children($GLOBALS['site_cate_id']);
			}
			else{
				$subcates = $model->children($GLOBALS['site_cate_id']);
			}
			$cates = array();
			foreach($subcates as $c){
				$cates[] = $c['Category']['id'];
			}
			$query['conditions'][$model->alias . '.cate_id'] = $cates;
		}
		return $query;
	}
}