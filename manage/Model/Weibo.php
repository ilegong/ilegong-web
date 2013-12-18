<?php

class Weibo extends AppModel {

    var $name = 'Weibo';
    
    var $actsAs = array('Tree'=> array('left'=>'left','right'=>'right') );

    function getWeiboByQid($q_id,$page=1,$num=20){
    	
    	return $weibolist = $this->find('all',array(
			'conditions' => array(
				'data_id'=>$q_id,
				'model'=>'Question',
    			'Weibo.published' => 1,
			),
			'limit'=>$num,
    		'page'=>$page,
			'order'=> 'Weibo.id desc'
		));
    }
    /**
     * 获取父级类和下级类
     * @param $parent_id
     * @param $page
     * @param $num
     */
	function getWeiboByWid($parent_id,$page=1,$num=20){
    	$data = $this->read(null,$parent_id);
//		print_r($data);
		// 获取父级类和下级类
    	$weibolist = $this->find('all',array(
    		
			'conditions' => array(
    			array(
						'left <' => $data['Weibo']['left'],
		    			'right >' => $data['Weibo']['right'],
						'model'=>'Question',
						'Weibo.published' => 1,
    			),
//    			'OR'=>array(
//    				array(
//						'left <=' => $data['Weibo']['left'],
//		    			'right >=' => $data['Weibo']['right'],
//						'model'=>'Question',
//    				),
//    				array(
//	    				'left >'=>$data['Weibo']['left'],
//	    				'right <' => $data['Weibo']['right'],
//    				)
//    			),
			),
			'limit' => $num,
    		'page' => $page,
    		'order'=> 'Weibo.id desc'
		));
		array_unshift($weibolist,$data); //  Prepend one or more elements to the beginning of an array		
		return array('current'=>$data,'parentlist'=>$weibolist);
    }
    

	function getWeiboMale($q_id,$page=1,$num=20){
    	$data = $this->read(null,$weibo_id);
//		print_r($data);
		// 获取父级类和下级类
    	$weibolist = $this->find('all',array(
			'conditions' => array(
				'data_id'=>$q_id,
				'model'=>'Question',
    			'limit'=>$num,
    			'page'=>$page,
    			'order'=> 'Weibo.id desc'
			),
			'joins'=>array(
				array('table'=>''),
			),
			'limit'=>$num,
		));
		return $weibolist;
    }
     // 男生回答，女生回答，好友回答。
    
}
?>