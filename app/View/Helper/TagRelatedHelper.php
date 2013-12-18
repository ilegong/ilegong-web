<?php
class TagRelatedHelper extends AppHelper {

    /**
     * 加载标签的数据
     * @param $modelClass
     * @param $parent_id
     */
    function loadTagData($modelClass='Article', $tag_id,$limit,$recursive) {
    	if(empty($recursive)){
    		$recursive = -1;
    	}
        $guid = $modelClass . '_tag_data_' . $tag_id.'_'.$recursive;
        
        $datas = Cache::read($guid);
            //if ($datas === false) {
                $model_obj = loadModelObject($modelClass);
                $model_obj->recursive = $recursive;
                $datas = $model_obj->find('all',
                                array(
                                	'conditions' => array('tag_id'=>$tag_id),
                                	'filed'=>'*',
                                	'limit'=> $limit,
                                	'joins' => array(array(
                                			'table' => 'tag_relateds',
                                			'alias' => 'TagRelated',
                                			'type' => 'left',
                                			'conditions' => array('relatedmodel'=>$modelClass,'relatedid= '.$modelClass.'.id'),
                                	)),
                        ));

                Cache::write($guid, $datas);
            //}
            return $datas;
    }

}

?>