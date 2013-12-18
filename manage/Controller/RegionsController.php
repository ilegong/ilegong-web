<?php
class RegionsController extends AppController{
	
	var $name = 'Regions';
	var $layout = 'regions_layout';
	
	function admin_getDialog(){
		$this->__viewFileName = 'admin_add';
		$this->__loadFormValues($this->modelClass);
		$this->__loadAssocValues();
	}
	
	function admin_edit($id = null,$copy=null){
		parent::admin_edit($id,$copy);
		
		// 将xml内容的条件转换为数组	
		if(!empty($this->data['Region']['conditions'])){
			$xmlarray = xml_to_array($this->data['Region']['conditions']);	 
			
			if(isset($xmlarray['options']['params']) && isset($xmlarray['options']['params']['type'])){
				$xmlarray['options']['params'] = array($xmlarray['options']['params']);
			}
			foreach($xmlarray['options']['joins'] as $key => &$join){
				if(!is_array($join['conditions']['conditionskey'])){
					/**
					 * conditionskey若不为数组，则将其转换成数组
					 */
					$join['conditions']['conditionskey'] = array($join['conditions']['conditionskey']);
					$join['conditions']['conditionsval'] = array($join['conditions']['conditionsval']);
					$join['conditions']['valid'] = array($join['conditions']['valid']);
				}
			}
                        // 将$xmlarray['options']['joins']的索引的数字设为从0开始
                        $i=0;
                        $tmp_joins = array();
                        foreach($xmlarray['options']['joins'] as $key => $join){
				$tmp_joins['join'.$i] = $join;
                                $i++;
			}
                        $xmlarray['options']['joins'] = $tmp_joins;
                        
			
			if(!is_array($xmlarray['options']['conditions']['conditionskey'])){
				/**
				 * conditionskey若不为数组，则将其转换成数组
				 */
				$xmlarray['options']['conditions']['conditionskey'] = array($xmlarray['options']['conditions']['conditionskey']);
				$xmlarray['options']['conditions']['conditionsval'] = array($xmlarray['options']['conditions']['conditionsval']);
				$xmlarray['options']['conditions']['valid'] = array($xmlarray['options']['conditions']['valid']);
			}
			
			$this->data = array_merge($this->data,$xmlarray);
		}
	}
	/**
	 * 设置属性的索引与值，attribute为serialize的数组
	 * @param $id region的id
	 */
	function admin_dataattri($id,$type='')
	{
		$key = $_POST['key'];
		$value = $_POST['value'];
		$this->autoRender = false;
		if(empty($this->currentUser['User'])){
			$this->__message('no permission');
		}
		$region = $this->Region->find('first',array(
			'conditions'=>array(
				'id'=>$id,
				'creator_id' => $this->currentUser['User']['id']
			),
		));
		if(empty($region)){
			$this->__message('no permission');
		}
		
		$attributes = unserialize($region['Region']['attribute']);
		if(!empty($attributes)){
			if($type=='append'){
				if(!is_array($attributes[$key])){
					$attributes[$key] = array($attributes[$key]);
				}
				$attributes[$key][] = $value;
				$attributes[$key] = array_unique($attributes[$key]);
			}
			else{
				$attributes[$key] = $value;
			}
		}
		else{
			$attributes = array($key=>$value);
		}
		$this->Region->update(array('attribute'=> serialize($attributes)),array('id'=>$id));
		Cache::delete('regioninfo_'.$id);		
		$successinfo = array('success'=>__('Update success'));
		echo json_encode($successinfo);
        return ;
	}
	
	function admin_generateoptions(){
		
		$array = array('options' => $this->data['options']);		
		$xml = array_to_xml($array);		
//		print_r($xml); print_r(xml_to_array($xml));
		$successinfo = array('success'=>__('success'),'data'=>$xml);
		echo json_encode($successinfo);
		exit;
	}
}