<?php
/**
 * Helper for AJAX operations.
 *
 * Helps doing AJAX using the jQuery library.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2009, Damian Jóźwiak (http://www.cakephp.bee.pl)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 */
/**
 * AjaxHelper helper library.
 *
 * Helps doing AJAX using the Prototype library.
 *
 * @package cake
 * @subpackage cake.cake.libs.view.helpers
 */
class AjaxHelper extends AppHelper {
	/**
	 * Included helpers.
	 *
	 * @var array
	 */
	var $helpers = array (
			'Html',
			'Js',
			'Form',
			'Swfupload',
			'Ckeditor',
			'Layout' 
	);
	/**
	 * Output buffer for Ajax update content
	 *
	 * @var array
	 */
	var $__ajaxBuffer = array ();	
	/**
	 * Creates JavaScript function for remote AJAX call
	 *
	 * This function creates the javascript needed to make a remote call
	 * it is primarily used as a helper for AjaxHelper::link.
	 *
	 * @param array $options
	 *        	options for javascript
	 * @return string html code for link to remote action
	 * @see AjaxHelper::link() for docs on options parameter.
	 */
	function remoteFunction($options, $suffix = '') {
		$ajaxoption = array();
		if (isset ( $options ['position'] )) {
			$position = $options ['position'];
			unset ( $options ['position'] );
		} else {
			$position = 'html';
		}
		if ($options ['id']) {
			$formselector = "$('#" . $options ['id'] . "')";
		} else {
			$formselector = "$('#form$suffix')";
		}
		
		
		$ajaxoption ['success'] = "
			$('[id^=\"error_" . $options ['model'] . "\"]').remove(); // 删除错误提示信息 
			if(request.success){
				showDialogMessage(request);					
			}
			else{
				$formselector.data('validator').invalidate(request);
				//showValidateErrors(request,'" . $options ['model'] . "','" . $suffix . "');					
			}
			" . $options ['success'] . '
			if(typeof(after_submit_callback)=="function"){
				after_submit_callback(request);
			}';
		$options ['url'] = $this->url ( isset ( $options ['url'] ) ? $options ['url'] : "" );
		$func = '$.ajax({
			type: "POST",		
			url:"'.$options ['url'].'",
			data:'.$formselector.'.serialize(),
			success:function(request, textStatus){
				'.$ajaxoption ['success'].'
			},
			dataType:"json"
		});';
		
		if (isset ( $options ['before'] )) {
			$func = "{$options['before']}; $func";
		}
		if (isset ( $options ['after'] )) {
			$func = "$func; {$options['after']};";
		}
		if (isset ( $options ['condition'] )) {
			$func = "if ({$options['condition']}) { $func; }";
		}
		
		if (isset ( $options ['confirm'] )) {
			$func = "if (confirm('" . $options ['confirm'] . "')) { $func; } else { return false; }";
		}
		return $func . " ;\r\n return false;\r\n";
	}
	
	function form($options = array(), $type = 'post') {
		
		$suffix = '_' . intval ( mt_rand () ); // 随机数作为表单各id的后缀，防止出现两个同模块的表单时，id相同。
		$htmlDefaults = array (
				'id' => 'form' . $suffix,
				'type' => $type,
				'class' => 'form-horizontal'
		);		
		$options = array_merge ( $htmlDefaults, $options );
		if (empty ( $options ['form_type'] )){
			$options ['form_type'] = 'add';
		}
		/*
			$callback = 'setCKEditorVal();' . "\r\n";
			$callback .= $this->remoteFunction ( $options, $suffix );
			// juery tools form validate,bootstrap			 
			$script = '$(function(){
				$("form#'.$options ['id'].'").validator().bind("onSuccess", function (e, ok) {
                    $.each(ok, function() {
                        var input = $(this);
                        remove_validation_markup(input);
                        // uncomment next line to highlight successfully
                        // validated fields in green
                        add_validation_markup(input, "success");
                    }); 
                }).bind("onFail", function (e, errors) {
                	var msg = "";
                    $.each(errors, function() {
                        var err = this;
                        var input = $(err.input);
                        remove_validation_markup(input);
                        msg+=err.messages.join(" ")+"<br/>";
                        add_validation_markup(input, "error",
                            err.messages.join(" "));
                    });
                    showErrorMessage(msg);
                    return false;
                }).submit(
				function(e){
					var form = $(this);
					if (!e.isDefaultPrevented()) {
						'.$callback.'
						e.preventDefault();
						return false;
					}
					return false;
				})
			})'; //
			$script = $this->Html->scriptBlock($script);
		*/
				
		$form = $this->Form->create ( $options ['model'], $options );
		$formhtml = $advancedhtml = $seohtml = '';
		if (Configure::read ( $options ['model'] . '.advancedfield' )) {
			$advancedfields = explode ( ',', Configure::read ( $options ['model'] . '.advancedfield' ) );
		} else {
			$advancedfields = explode ( ',', Configure::read ( 'Modelextend.advancedfield' ) );
		}
		
		$seofields = explode ( ',', Configure::read ( 'Modelextend.seofield' ) );
		
		$modelClass = Inflector::classify ( $options ['model'] );
		
		$ui_tab_nav = '';
		if (! empty ( $options ['auto_form'] )) {
			$formhtml = '';
			$advancedhtml = '';			
			// print_r($this);exit;
			// print_r($this->params['_flowstep_edit_fields']);
			$model_obj = loadModelObject ( $modelClass );
			if ($model_obj->hasAndBelongsToMany) {
				foreach ( $model_obj->hasAndBelongsToMany as $key => $val ) {
					$i18ninfo = $model_obj->{$val ['className']}->_extschema ['id'];
					$modelinfo = $model_obj->{$val ['className']}->getModelInfo ();
					$i18ninfo ['translate'] = $modelinfo ['cname'];
					$i18ninfo ['formtype'] = 'select';
					$formhtml .= $this->autoFormElement ( $val ['className'], 'id', $i18ninfo, $options);
				}
			}
			foreach ( $this->params ['_extschema'] as $key => $value ) {
				$field_html = $this->autoFormElement ( $modelClass, $key, $value, $options);
				if (in_array ( $key, $advancedfields ))
					$advancedhtml .= $field_html;
				elseif (in_array ( $key, $seofields ))
					$seohtml .= $field_html;
				else
					$formhtml .= $field_html;
			}
			if (isset ( $this->params ['_flowstep_edit_fields'] )) {
				$ui_tab_nav .= '<li><a href="#' . $modelClass . 'basic-info"><span>' . __ ( 'Basic', true ) . '</span></a></li>';
				// 流程中的增加与修改表单
				$formhtml = '<div id="' . $modelClass . 'basic-info" class="tab-pane"><fieldset>' . $formhtml . $advancedhtml . $seohtml . '</fieldset></div>';
			
			} else {
				$ui_tab_nav .= '<li><a href="#' . $modelClass . 'basic-info" data-toggle="tab" ><span>' . __ ( 'Basic Info' ) . '</span></a></li>';
				
				$ui_tab_nav .= '<li><a href="#' . $modelClass . 'advanced-info" data-toggle="tab" ><span>' . __ ( 'Advanced Options' ) . '</span></a></li>';
				$ui_tab_nav .= '<li><a href="#' . $modelClass . 'seo-info" data-toggle="tab" ><span>' . __ ( 'SEO' ) . '</span></a></li>';
				
				$formhtml = '<div id="' . $modelClass . 'basic-info" class="tab-pane"><fieldset>' . $formhtml . '</fieldset></div>';
				$advancedhtml = '<div id="' . $modelClass . 'advanced-info" class="tab-pane"><fieldset>' . $advancedhtml . '</fieldset></div>';
				$seohtml = '<div id="' . $modelClass . 'seo-info" class="tab-pane"><fieldset>' . $seohtml . '</fieldset></div>';
				
				
				$formhtml .= $advancedhtml . $seohtml;
				$ui_tab_nav .= $this->Layout->getLanguageTabHead ( $modelClass );
				$formhtml .= $this->Layout->getLanguageTabContent ( $modelClass );
			}
			if ($ui_tab_nav)
				$ui_tab_nav = '<ul class="nav nav-tabs">' . $ui_tab_nav . '</ul>';
			
			$formhtml .= $this->Form->end ( __ ( 'Submit', true ) );
			// echo '---------------------';print_r($params);
		}
		if (! isset ( $options ['form_type'] ))
			$options ['form_type'] = 'add';
		
		$formhtml .= $this->buildAssociate ( $modelClass, $options ['form_type'] ); // 生成级联操作的js代码
		
		return $form.$ui_tab_nav.'<div class="tab-content">'.$formhtml.'</div>'.$script;
	}
	/**
	 *
	 * @param <type> $modelClass
	 *        	模块的名字
	 * @param <type> $key
	 *        	字段名
	 * @param <type> $value
	 *        	extschema中字段的描述及值
	 * @param <type> $params
	 *        	生成form时传入的参数
	 * @return string
	 */
	function autoFormElement($modelClass, $key, $value, $params) {
		if ($params ['form_type'] == 'add' && ! $value ['allowadd']) {
			return;
		} elseif ($params ['form_type'] == 'edit' && ! $value ['allowedit']) {
			return;
		}
		if ($key != 'id' && isset ( $this->params ['_flowstep_edit_fields'] ) && ! in_array ( $key, $this->params ['_flowstep_edit_fields'] )) {
			return;
		}
		
		$options = array ();
		$options ['label'] = $value ['translate'];
		if (in_array ( $value ['formtype'], array (
				'input',
				'datetime' 
		) )) {
			$options ['type'] = 'text';
		} elseif ($value ['formtype'] == 'checkbox') {
			$options ['type'] = 'select';
			if (! ($params ['form_type'] == 'edit' && $value ['explodeimplode'] == 'explode')) {
				$options ['multiple'] = 'checkbox';
				$options ['div'] = array (
						'id' => Inflector::camelize ( $modelClass . '_' . $key . '_Checkboxs' ) 
				);
			}
		} elseif ($value ['formtype']) {
			$options ['type'] = $value ['formtype'];
		}
		
		$options ['id'] = Inflector::camelize ( $modelClass . '_' . $key );
		$options ['after'] = '';
		if (isset ( $value ['default'] )) {
			$options ['default'] = $value ['default'];
		}
		if ($value ['formtype'] == 'select' && $value ['selectmodel'] && $value ['selecttxtfield'] && $value ['selecttxtfield']) {
			$fieldid = Inflector::camelize ( $modelClass . '_' . $key . '_associate' );
			$options ['after'] = '&nbsp;<span for="' . $fieldid . '">' . __ ( 'Filter', true ) . '</span>&nbsp;<input class="associate-text" style="width: 80px;" type="text" value="" id="' . $fieldid . '" />';
			if ($modelClass != $value ['selectmodel']) {
				$relateurl = $this->url ( '/admin/' . Inflector::tableize ( $value ['selectmodel'] ) . '/add/model:' . $modelClass . '/parent_id:' . $value ['selectparentid'] );
				// $relateurl = $this->Html->url($relateurl);
				$options ['after'] .= '&nbsp;<a href="' . $relateurl . '" onclick="return open_dialog({title:\'' . __ ( 'New ' ) . __ ( 'Model_' . $value ['selectmodel'] ) . '\'},this.href);">' . __ ( 'New ' ) . __ ( 'Model_' . $value ['selectmodel'] ) . '</a>';
			}
		}
		
		// append field explain text
		if (! empty ( $value ['explain'] )) {
			$options ['after'] .= '<span>' . $value ['explain'] . '</span>';
		}
		
		if($value['formtype']=='coverimg'){
			$field_html ='<div class="input text" style="float:right;width:120px;height:120px;">';
			$field_html .= $this->Form->input ( $key, array (
					'type' => 'hidden'
			));
			$img = $options['value']?$options['value']:('nophoto.gif');
			$field_html .= $this->Html->image($img,array('width'=>120,'id'=>$options['id'].'Preview'));
			$field_html .= '</div>';
			return $field_html;
		}
		elseif ($value ['formtype'] == 'datetime') {
			// 生成时间类型的字段的表单内容
			$fieldid = Inflector::camelize ( $modelClass . '_' . $key );
			
			if ('{$now}' == $value ['default'] || in_array ( $key, array (
					'created',
					'updated' 
			) )) {
				$ymd = date ( 'Y-m-d' );
			} else {
				$ymd = '';
			}
			$his = date ( 'H:i:s' );
			if (isset ( $this->data [$modelClass] [$key] ) && ! is_array ( $this->data [$modelClass] [$key] )) {
				$time_array = explode ( ' ', $this->data [$modelClass] [$key] );
				$ymd = $time_array [0];
				$his = $time_array [1];
			} elseif (is_array ( $this->data [$modelClass] [$key] )) {
				$ymd = $this->data [$modelClass] [$key] ['ymd'];
				$his = $this->data [$modelClass] [$key] ['his'];
			}
			$on_select = '';
			if ($key == 'starttime') {
				$on_select .= '$("[id$=\'EndtimeYmd\']").datepicker("option", "minDate", date);';
			} elseif ($key == 'endtime') {
				$on_select .= '$("[id$=\'StarttimeYmd\']").datepicker("option", "maxDate", date);';
			} elseif ($key == 'created') {
				$on_select .= '$("[id$=\'UpdatedYmd\']").datepicker("option", "minDate", date);';
			} elseif ($key == 'updated') {
				$on_select .= '$("[id$=\'CreatedYmd\']").datepicker("option", "maxDate", date);';
			}
			$datetimehtml = '<div class="input text">
					<label>' . $value ['translate'] . '</label>
					<input class="datepicker" type="text" id="' . $fieldid . 'Ymd" name="data[' . $modelClass . '][' . $key . '][ymd]" value="' . $ymd . '">
					<input type="text" id="' . $fieldid . 'His" name="data[' . $modelClass . '][' . $key . '][his]" value="' . $his . '">
					</div>
					<script>
					$(document).ready(function(){ 
						$("#' . $fieldid . 'Ymd").datepicker({
							showButtonPanel: true,dateFormat:\'yy-mm-dd\',
							changeMonth: true,
							changeYear: true,
							onSelect: function(selectedDate) {
								var instance = $(this).data("datepicker");
								var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
								' . $on_select . '
							}
								
						});
						$("#ui-datepicker-div").css("zIndex",100000);
					});</script>';
			return $datetimehtml;
			
		} elseif ($value ['formtype'] == 'ckeditor') {
			$fieldid = Inflector::camelize ( $modelClass . '_' . $key ) . $suffix;
			$field_html = $this->Form->input ( $key, array (
					'div' => 'wygiswys',
					'id' => $fieldid,
					'label' => false 
			) );
			$field_html .= $this->Ckeditor->load ( $fieldid );
			return $field_html;
		} else {
			$field_html = '';
			if ($value ['formtype'] == 'file') {
				$field_html = $this->Swfupload->load ( $key, $modelClass ); // 文件上传类型
			} else {
				if ($key == 'id') {
					$varName = Inflector::tableize ( $modelClass );
					$varOptions = $this->_View->getVar ( $varName );
					if (is_array ( $varOptions )) {
						if ($options ['type'] !== 'radio') {
							$options ['type'] = 'select';
						}
						$options ['options'] = $varOptions;
					}
					$options ['selected'] = $this->_View->getVar ( 'selected_' . $varName );
				}
				$field_html = $this->Form->input ( $modelClass . '.' . $key, $options );
			}
			return $field_html;
		}
	}
	/**
	 * 创建级联下拉的选项加载js代码，
	 * 关联其它模块的数据搜索
	 */
	function buildAssociate($modelClass, $form_type = 'add') {
		// echo '<pre>'; print_r($this->data);
		// $modelClass = Inflector::classify($this->params['controller']);
		$js_str = '';
		$onload_trigger_changefields = array (); // 页面加载完成后，自动触发onchange事件的字段
		$model_obj = loadModelObject ( $modelClass );
		$_extschema = $model_obj->getExtSchema(); // $this->params['_extschema']
		
		foreach ( $_extschema as $key => $value ) {
			// echo "associateelement";
			if ($value ['onchange']) {
				$fieldid = Inflector::camelize ( $modelClass . '_' . $value ['name'] ); // .$suffix;
				$js_str .= '
					$("#' . $fieldid . '").change(function(){
						' . $value ['onchange'] . '
					});
					';
				if ($form_type == 'edit') {
					$js_str .= '$("#' . $fieldid . '").trigger("change");'; // 编辑页面加载时，触发onchange事件
				}
			}
			if (in_array ( $value ['formtype'], array (
					'select',
					'checkbox' 
			) )) {
				if (! $value ['selectmodel'] || ! $value ['selecttxtfield'] || ! $value ['selecttxtfield']) {
					continue;
				}
				
				if ($value ['associateflag'] && $value ['associatefield']) {
					// 选项的级联效果
					$fieldid = Inflector::camelize ( $modelClass . '_' . $value ['associateelement'] ); // .$suffix;
					$target_id = Inflector::camelize ( $modelClass . '_' . $value ['name'] ); // .$suffix;
					$successjs = '';
					// if(!($params['form_type'] == 'edit' &&
					// $value['explodeimplode']=='explode'))
					// 表单类型为checkbox，explodeimplode值为explode，表示批量添加内容。不支持批量修改。修改时作为select来处理
					if ($value ['formtype'] == 'checkbox' && ! ($form_type == 'edit' && $value ['explodeimplode'] == 'explode')) {
						$div_id = Inflector::camelize ( $modelClass . '_' . $key . '_Checkboxs' ); // .$suffix;
						$successjs = '
							$("#' . $div_id . ' .checkbox").remove();
							$(data).each(function(i){
								if(data[i].value=="")
								{
									data[i].text = "' . __ ( 'Select All', true ) . '"
								}
								$(\'<div class="checkbox"><input type="checkbox" id="' . $modelClass . $key . '_\'+i+\'" value="\'+data[i].value+\'" name="data[' . $modelClass . '][' . $key . '][]"><label for="' . $modelClass . $key . '_\'+i+\'">\'+data[i].text+\'</label></div>\').appendTo("#' . $div_id . '");
							});
						';
						if ($form_type == 'edit') {
							
							if (isset ( $this->data [$modelClass] [$key] ) && is_array ( $this->data [$modelClass] [$key] ) && ! empty ( $this->data [$modelClass] [$key] )) {
								$selectvalue_jsarray = '[';
								foreach ( $this->data [$modelClass] [$key] as $val ) {
									$selectvalue_jsarray .= '"' . str_replace ( '"', '\"', $val ) . '",';
								}
								$selectvalue_jsarray = substr ( $selectvalue_jsarray, 0, - 1 ) . ']';
							} else {
								$selectvalue_jsarray = '[]';
							}
							
							// echo $selectvalue_jsarray;
							// print_r($modelClass);echo
							// "======";print_r($value['associateelement']);
							$successjs .= '
								if($("#' . $fieldid . '").val()=="' . $this->data [$modelClass] [$value ['associateelement']] . '")
								{
									var select_values = ' . $selectvalue_jsarray . ';
									//alert($("#' . $div_id . ' input[type=checkbox]").size());
									$("#' . $div_id . ' input[type=checkbox]").each(function(i){
										if($.inArray(this.value,select_values)!=-1)
										{
											$(this).attr("checked",true);
										}
										return ;
									});
								}
							';
						}
					} else {
						$successjs = '
								$("#' . $target_id . '").html("");
								$(data).each(function(i){	
									var opt=document.createElement("OPTION"); 
									$("#' . $target_id . '").get(0).options.add(opt); 
									opt.value = data[i].value; 
									opt.text = data[i].text; 
									
								});
								
								';
						if ($form_type == 'edit') {
							if (is_array ( $this->data [$modelClass] [$key] )) {
								// 为数组时，表示字段允许批量添加的，值被转换成了数组。
								// 修改单条时，数组只有一个元素。不提供批量修改的checkbox，取数组的第一个即可。
								$this->data [$modelClass] [$key] = array_shift ( $this->data [$modelClass] [$key] );
							}
							$successjs .= '
								if($("#' . $fieldid . '").val()=="' . $this->data [$modelClass] [$value ['associateelement']] . '")
								{
									$("#' . $target_id . '").val("' . $this->data [$modelClass] [$key] . '");
								}
							';
						}
						$successjs .= '
							$("#' . $target_id . '").trigger("change");
							';
					}
					
					$onload_trigger_changefields [] = $fieldid;
					// 当为编辑时的表单时，自动触发级联上级的onchange事件，使触发下级的动作
					
					$js_str .= '
					$("#' . $fieldid . '").change(function(){
						if(this.value==""){
							$("#' . $target_id . '").html("");
							$("#' . $target_id . '").trigger("change");
							return false;
						}
						$.ajax({
							type:"post", 
							dataType: "json",
							data:{"valuefield":"' . $value ['selectvaluefield'] . '","ass_val":this.value,"selectmodel":"' . $value ['selectmodel'] . '","txtfield":"' . $value ['selecttxtfield'] . '","associatefield":"' . $value ['associatefield'] . '","associatetype":"' . $value ['associatetype'] . '"},
							url:"' . $this->url ( '/admin/ajaxes/associate' ) . '",
							success: function(data){								
				                	' . $successjs . '
							}
						});
					});
					';
				
				}
				
				if ($value ['associateflag'] && $value ['associatefield']) {
					$data_str = '{"txt_filter":this.value,"valuefield":"' . $value ['selectvaluefield'] . '","ass_val":$("#' . $fieldid . '").val(),"selectmodel":"' . $value ['selectmodel'] . '","txtfield":"' . $value ['selecttxtfield'] . '","associatefield":"' . $value ['associatefield'] . '","associatetype":"' . $value ['associatetype'] . '"}';
					// 级联上级的条件，和筛选条件一起传入
				} else {
					// $data_str =
					// '{"valuefield":"'.$value['selectvaluefield'].'","ass_val":this.value,"selectmodel":"'.$value['selectmodel'].'","txtfield":"'.$value['selecttxtfield'].'","associatefield":"'.$value['selecttxtfield'].'","associatetype":"like"}';
					$data_str = '{"txt_filter":this.value,"valuefield":"' . $value ['selectvaluefield'] . '","ass_val":this.value,"selectmodel":"' . $value ['selectmodel'] . '","txtfield":"' . $value ['selecttxtfield'] . '"}';
				}
				// 为选项加一个文本的筛选框，输入文本内容即可搜索筛选。筛选框js事件keyup
				$fieldid = Inflector::camelize ( $modelClass . '_' . $value ['name'] . '_associate' ); // .$suffix;
				$target_id = Inflector::camelize ( $modelClass . '_' . $value ['name'] ); // .$suffix;
				                                                                  
				// 追加字段其他的查询条件
				$xam_array = xml_to_array ( $value ['conditions'] );
				$condition_url = '';
				if (! empty ( $xam_array ['options'] ['conditions'] )) {
					foreach ( $xam_array ['options'] ['conditions'] as $ck => $cv ) {
						$condition_url .= 'conditions[' . rawurlencode ( $ck ) . ']=' . rawurlencode ( $cv ) . '&';
					}
					$condition_url = substr ( $condition_url, 0, - 1 );
				}
				
				$js_str .= '
					$("#' . $fieldid . '").keyup(function(){
						$.ajax({
							type:"post", 
							dataType: "json",
							data:' . $data_str . ',
							url:"' . $this->url ( '/admin/ajaxes/associate' ) . '?' . $condition_url . '",
							success: function(data){
								$("#' . $target_id . '").html("");
								$(data).each(function(i){
				                	//alert(data[i].text);
				                	var opt=document.createElement("OPTION"); 
									$("#' . $target_id . '").get(0).options.add(opt); 
									opt.value = data[i].value; 
									opt.text = data[i].text; 
								});
								$("#' . $target_id . '").trigger("change");
							}
						});
					});
				';
			}
		}
		if (! empty ( $onload_trigger_changefields )) {
			$onload_trigger_changefields = array_unique ( $onload_trigger_changefields );
			foreach ( $onload_trigger_changefields as $fieldid ) {
				// 当为编辑时的表单时，自动触发级联上级的onchange事件，使触发下级的动作
				if ($form_type == 'edit') {
					$js_str .= '
					$("#' . $fieldid . '").trigger("change");';
				}
			}
		}
		$js_str = '
				$(document).ready(function(){
					' . $js_str . '
				});
		';
		return $this->Html->scriptBlock($js_str);
	}	
}

?>