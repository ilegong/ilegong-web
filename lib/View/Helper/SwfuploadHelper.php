<?php
class SwfuploadHelper extends FormHelper {
	
	var $helpers = array('Html','Form');
	
	function load($file_post_name = 'Filedata',$param = array(),$select=false) {
		
		$this->setEntity($file_post_name);
		$size=10;
		if(!is_array($param)){
			$param = array('modelClass'=>$param);
		}
// 		var_dump($this->Form);exit;
// 		print_r($this->model());
		
		if(strpos($file_post_name,'.')===false){
			$fieldid = $param['modelClass'].'.'.$file_post_name;
		}
		
		$fieldid = Inflector::camelize (str_replace('.','_',$file_post_name));
		
		$fieldname = $this->field(); //$file_post_name可能含“.”,经过field函数处理后，不含‘.’，得到name值
		$hidden = $this->Form->hidden($file_post_name,array('id'=>$fieldid,'value'=>$param['value']));
		
		//no_db支持从外部方法中传入，即在模板中指定值。其他都保存至uploadfiles表
		$param = array_merge(array(
				'modelClass'=> 'Article',
				'isadmin' => false,
				'label' => __d('i18nfield','Field_'.$param['modelClass'].'_'.$fieldname),
				'after' => '',//描述
				'upload_limit'=> 0, // 最多允许上传的文件数，0为不限制
				'file_types'=> "*.*",
				'file_types_description'=> 'All Files',
				'button_image_url'=> '/img/uploadbutton.png', // 上传按钮描述
				'button_width'=> 100, // 上传按钮宽度
				'button_height'=> 24, // 上传按钮高度
				'no_db' =>0, // 是否不保存到数据库
				'no_thumb'=>0,//图片不生成缩略图
				'save_folder' => '', // 保存地址	
				'fieldid' => '',
				'upload_success_handler'=>'uploadSuccess', //回调函数
				'return_type'=> 'json',// html or json
				'withprogress'=>true // 是否显示上传进度
				),$param);
		//extract($param); // 变量混淆方法不支持extract，直接用数组来使用变量
		
		if($param['no_db'] && $param['upload_success_handler']=='uploadSuccess'){
			$param['upload_success_handler'] = 'uploadSuccess_'.$fieldid;
		}
		
		
        $listfile = '';
        if(isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile']) && !empty($this->data['Uploadfile'])){
	        foreach($this->data['Uploadfile'] as $uploadfile){
	        	if($uploadfile['fieldname']==$file_post_name){
	        		$listfile.='<li class="upload-fileitem pull-left" id="upload-file-'.$uploadfile['id'].'">';
                    if(substr($uploadfile['fspath'],0,7) != 'http://'){
                        $file_url = str_replace('//','/',UPLOAD_FILE_URL.($uploadfile['fspath']));
                    }
                    else{
                        $file_url = $uploadfile['fspath'];
                    }
	        		if('image' == substr($uploadfile['type'],0,5)){
	        			if(substr($uploadfile['thumb'],0,7) != 'http://'){
                        	//$thumb_url = str_replace('//','/',UPLOAD_FILE_URL.($uploadfile['thumb']));
							$thumb_url=UPLOAD_FILE_URL.($uploadfile['thumb']);
	                    }
	                    else{
	                        $thumb_url = $uploadfile['thumb'];
	                    }
						$listfile.='<img src="'.$thumb_url.'"/>';
					}
	        		$listfile.='<input type="hidden" name="data[Uploadfile]['.$uploadfile['id'].'][id]" value="'.$uploadfile['id'].'">
	        		<p><input type="text" readonly name="data[Uploadfile]['.$uploadfile['id'].'][name]" value="'.urldecode($uploadfile['name']).'"/></p>
	        		 <p><a href="'.$file_url.'" target="_blank">' . __ ( '预览') . '</a>
	        		<a class="upload-file-delete" onclick="deleteImg(this);" rel="'.$uploadfile['id'].'" data-url="'.$this->url('/uploadfiles/delete/'.$uploadfile['id'].'.json').'">删除</a>
	        		<a href="javascript:void(0);" onclick="setCoverImg(\''.$param['modelClass'].'\',\''.$thumb_url.'\');">设置为产品首图</a></p>
	        		';
	        		$listfile.='</li>';
	        	}
	        }
        }elseif($param['modelClass'] == 'Weshare' && !empty($this->data[$param['modelClass']][$file_post_name])){
            $this->log('share edit');
            $images_str = $this->data[$param['modelClass']][$file_post_name];
            $images = explode('|', $images_str);
            foreach($images as $img){
                $listfile .='<div class="ui-upload-filelist" style="float:left;"><img src="'.$img.'" width="100px" height="100px"><br><p><a href="'.$img.'" target="_blank">预览</a>&nbsp;&nbsp;&nbsp;<a class="upload-file-delete" onclick="deleteImg(this);">删除</a></p></div>';
            }
        } elseif((isset($this->data[$param['modelClass']][$fieldname]) && !empty($this->data[$param['modelClass']][$file_post_name])) || !empty($param['value'])){
        	// 上传文件的地址保存到本模块对应的字段中，而非保存在Uploadfile里
        	if($param['value']){
        		if(substr($param['value'],0,7) != 'http://'){
        			$file_url = UPLOAD_FILE_URL.($param['value']);
        		}
        		else{
        			$file_url = ($param['value']);
        		}
        		//$file_url = str_replace('//','/',UPLOAD_FILE_URL.$param['value']);
        	}
        	else{
        		if(substr($this->data[$param['modelClass']][$file_post_name],0,7) != 'http://'){
        			$file_url = UPLOAD_FILE_URL.($this->data[$param['modelClass']][$file_post_name]);
        		}
        		else{
        			$file_url = $this->data[$param['modelClass']][$file_post_name];
        		}
        	}
        	if(is_image($file_url)){
        		$listfile = '<a href="'.$file_url.'" title="'.__( '预览').'" target="_blank"><img src="'.$file_url.'" style="max-height:120px"/></a>';
        	}
        	else{
        		$listfile = '<a href="'.$file_url.'" target="_blank">'.__( '预览').'</a>';
        	}
        }
        if($param['isadmin']){
        	$upload_url = $this->Html->url('/uploadfiles/admin_upload');
        }
        else{
        	$upload_url = $this->Html->url('/uploadfiles/upload');
        }
		if($select){
			$upload_url.='?select=true';
            if($param['modelClass'] == 'Weshare'){
                $upload_url .= '&no_db=1';
            }
		}
        $script = '<script>
'.(($param['upload_limit']==1)?
'function '.$param['upload_success_handler'].'(file, serverData) {
				try {
					var progress = progress_list[file.id] ;
					progress.setComplete();
					if (serverData === " ") {
						this.customSettings.upload_successful = false;
					} else {
						var data=eval("(" + serverData + ")");
						if(data.status==1){
							this.customSettings.upload_successful = true;
							$("#'.$fieldid.'").val(data.fspath);
							$("#fileuploadinfo_'.$fieldname.'").html(data.message);
						}
					}
				} catch (e) {
					alert("上传失败");
				}
			}':'').
'var swfu_'.$fieldid.';
$(function () {
	swfu_'.$fieldid.' = new SWFUpload({
		upload_url: "'.$upload_url.'",	
		file_post_name: "'.$fieldname.'",
		file_size_limit : "'.$size.' MB",
		file_types : "'.$param['file_types'].'",
		file_types_description : "'.$param['file_types_description'].'",
		file_upload_limit : 0,
		file_queue_limit : '.$param['upload_limit'].',
		post_params : {
			"PHP_SESSION_ID" : "'.session_id().'",
			"file_post_name" : "'.$fieldname.'",
			"file_model_name":"'.$param['modelClass'].'",
			"no_db":"'.$param['no_db'].'",
			"save_folder":"'.$param['save_folder'].'",
			"return_type":"'.$param['return_type'].'"
		},
		
		button_image_url : "'.$this->Html->assetUrl($param['button_image_url']).'",
		button_placeholder_id : "spanButtonPlaceholder_'.$fieldid.'",
		button_width: '.$param['button_width'].',
		button_height: '.$param['button_height'].',
		
		flash_url : "'.$this->Html->url('/js/swfupload/swfupload.swf').'",
		flash9_url : "'.$this->Html->url('/js/swfupload/swfupload_fp9.swf').'",
		
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		
		swfupload_preload_handler : preLoad,
		swfupload_load_failed_handler : loadFailed,
		swfupload_loaded_handler : loadSuccess,
		
		upload_start_handler : uploadStart,'.($param['withprogress']?
		'upload_progress_handler : uploadProgress,':'').
		'upload_error_handler : uploadError,
		upload_success_handler : '.$param['upload_success_handler'].',
		upload_complete_handler : uploadComplete,
		custom_settings : {
			progress_target : "fsUploadProgress",
			upload_successful : false,
			auto_start : true
		},
		debug: '.($_GET['debug']?'true':'false').'
	});
	swfu_array[swfu_array.length] = swfu_'.$fieldid.';
});
</script>';
        $this->_View->append('bottomscript',$script);
        if($param['modelClass'] == 'Weshare'){
            $return_text = $hidden.'
        <div class="form-group swfupload-control" ><div class="col-sm-12 controls"><span id="spanButtonPlaceholder_'.$fieldid.'"></span>(10MB 最大)'.$param['after'].'</div>
				<div class="clearfix"></div>
				<ul class="col-sm-12 upload-filelist" id="fileuploadinfo_'.$fieldname.'">'.$listfile.'</ul>
		</div>';
            return $return_text;
        }
        $return_text = $hidden.'
        <div class="form-group swfupload-control" >'.
            ($param['label']?'<label class="col-sm-2 control-label">'.$param['label'].'</label>':'').
            '<div class="col-sm-10 controls"><span id="spanButtonPlaceholder_'.$fieldid.'"></span>(10MB 最大)'.$param['after'].'</div>
				<div class="clearfix"></div>
				<ul class="col-sm-10 col-sm-offset-2 upload-filelist" id="fileuploadinfo_'.$fieldname.'">'.$listfile.'</ul>
		</div>';
        return $return_text;
    }


}
?>