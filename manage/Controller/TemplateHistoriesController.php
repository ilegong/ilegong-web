<?php
class TemplateHistoriesController extends AppController{
	
	var $name = 'TemplateHistories';
	
// 编辑模板，保存文件历史版本入库

	function admin_edit() {
		
		$modelClass = $this->modelClass;
		if(empty($this->data[$modelClass]['name']) || empty($this->data[$modelClass]['content'])){
			throw new BadRequestException('Need Post Params');
		}	
		
        $this->autoRender = false;
        $filepath = ROOT.$this->data[$modelClass]['name'];
//         $file_content = file_get_contents($filepath);
        $content = trim($this->data[$modelClass]['content']);
        
        $content = preg_replace_callback("/<div\s+([^\>]*?)class=\"ui-portlet\"([^\>]*?)><\/div>/is",array($this,'_parsePortletAttributes'),$content);
        
        $htmldom = str_get_html(stripslashes($content));
        $new_container = $htmldom->find('.container', 0);
       
        
        App::uses('File','Utility');
        $file = new File($filepath); 
        $old_content = $file->read();
        
        $htmldom = str_get_html(stripslashes($old_content));
        $old_container = $htmldom->find('.container', 0);
        
        $new_tpl_content = str_replace($old_container->outertext(),$new_container->outertext(),$old_content);
        
        if($file->exists()){
        	$successinfo = array('success'=>__('Add success'));
        	if($file->write(trim($new_tpl_content))){ // 写文件
        		$this->data[$modelClass]['creator'] = $this->currentUser['Staff']['id'];
        		$this->data[$modelClass]['content'] = $old_content; // 保存当前文件的内容为历史版本
		        if($this->{$modelClass}->save($this->data)){
		            $successinfo['success'] = __('history saved');
		        }
        	}
        }
        else{
        	$successinfo = array('error'=>__('save error'));
        }
        echo json_encode($successinfo);
        exit;
        
    }
        
    function _parsePortletAttributes($matches){
//     	print_r($matches); // 0为全部内容，1,2 为属性
    	$ret = array();
    	if(preg_match_all('/(\S+)="([^"]+?)"/',$matches[1],$match)){
    		foreach($match[1] as $n => $k){
    			$ret[$k] = urldecode($match[2][$n]);
    		}
    	}
    	if(preg_match_all('/(\S+)="([^"]+?)"/',$matches[2],$match)){
    		foreach($match[1] as $n => $k){
    			$ret[$k] = urldecode($match[2][$n]);
    		}
    	}
//     	print_r($ret);
    	if(!empty($ret['region_info'])){ // 从列表模板中插入的列表
    		$region_str = $ret['region_info'];
    		$region_str = str_replace('&amp;','&',$region_str);
    		parse_str($region_str,$regioninfo);
    		$attributes = '';
    		if(!empty($regioninfo['name'])){
    			$attributes.='title="'.$regioninfo['name'].'" ';
    		}
    		if(!empty($regioninfo['template'])){
    			$attributes.='list_tpl="'.$regioninfo['template'].'" ';
    		}
    		if(!empty($regioninfo['portlet']) && $regioninfo['portlet']!='default'){
    			$attributes.='portlet="'.$regioninfo['portlet_tpl'].'" ';
    		}
    		if(empty($regioninfo['conditions'])){
    			// 当无conditions时，为内容型
    			return '<portlet '.$attributes.'>'.trim($regioninfo['content']).'</portlet>';
    		}
    		else{
    			// 否则为插入模块数据列表型
    			$arr = xml_to_array($regioninfo['conditions']);
    			$attributes.='info="'.http_build_query($arr['options']).'" ';    			
    		}
    		if(!empty($regioninfo['rows'])){
    			$attributes.='limit="'.$regioninfo['rows'].'" ';
    		}
    		if(!empty($regioninfo['custom_class'])){
    			$attributes.='custom_class="'.$regioninfo['custom_class'].'" ';
    		}
    		if(!empty($regioninfo['model'])){
    			$attributes.='model="'.$regioninfo['model'].'" ';
    		}
    		return '<portlet '.$attributes.'>'.trim($regioninfo['content']).'</portlet>';
    		
    	}
    	elseif(!empty($ret['innertext'])){
    		$attributes = '';
    		foreach($ret as $k=>$v){
    			if($k!='innertext'){
    				if($k=='portlet' && $v=='default'){
    					continue;
    				}
    				$attributes.= ' '.$k.'="'.urldecode($v).'"';
    			}
    		}
    		return '<portlet'.$attributes.'>'.urldecode($ret['innertext']).'</portlet>';
    	}
    	else{
    		return '<portlet'.$matches[1].' '.$matches[2].'></portlet>';
    	}
    }
  
  
    
}