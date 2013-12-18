<?php
/**
 * 多语言内容互相独立行为，
 * 		多语言数据存放在一个表中。
 * 		使用locale字段区分内容属于哪一种语言
 * 		页面显示时，仅查询当前对应语言类型的数据。
 * @author Arlon
 *
 */

class IndLangBehavior extends ModelBehavior {
	
	public function setup($model, $config = array()) {
		$fields = array_keys($model->schema());
		if (!in_array('locale',$fields)) {
			trigger_error(
				__('Model %s.Table do not contain filed `locale`', $model->alias),
				E_USER_ERROR
			);
			return false;
		}		
		return true;
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
		if(empty($query['conditions'])) $query['conditions'] = array();
		/* 不包含locale字段时，添加locale字段*/
		if(empty($query['conditions']['locale']) && empty($query['conditions'][$model->alias . '.locale'])){
			$query['conditions'][$model->alias . '.locale'] = getLocal(Configure::read('Config.language'));
		}
		return $query;
	}
}