<?php
App::uses('FormHelper', 'View/Helper');
class MFormHelper extends FormHelper {
	
	var $helpers = array('Html','Js','Swfupload','Layout');
	var $_extschema = array();
	
	public function create($model = null, $options = array()) {
		
		$model_obj = loadModelObject ( $model );
		$this->_extschema[$model] = $model_obj->getExtSchema();
		
		$defaultOptions = array(
				'inputDefaults' => array(
						'div' => array(
								'class' => 'form-group'
						),
						'label' => array(
								'class' => 'col-sm-2 control-label'
						),						
						'class' => 'form-control',
				),
				'class' => 'form-horizontal',
				'role' => 'form',
		);
		
		if(!empty($options['inputDefaults'])) {
			$options = array_merge($defaultOptions['inputDefaults'], $options['inputDefaults']);
		} else {
			$options = array_merge($defaultOptions, $options);
		}
		return parent::create($model, $options);
	}
	
	public function input($fieldName, $options = array()) {
		 
		$this->setEntity($fieldName);
		$modelKey = $this->model();
		$fieldKey = $this->field();  // 获取模块和字段，可修改到FormHelper中去
		
		if(!isset($options['div'])){
			$options['div'] = 'form-group';
		}
		elseif(is_array($options['div'])){
			$options['div']['class'] .= ' form-group';
		}
		elseif(!empty($options['div'])){
			$options['div'] .= ' form-group';
		}
		if(!empty($this->_extschema[$modelKey][$fieldKey])){
			$fieldinfo = $this->_extschema[$modelKey][$fieldKey];
			
			if(!empty($fieldinfo['allownull'])){
				$options['required'] = false;
			}
			
			if(empty($options['type']) && !empty($fieldinfo['formtype']) && $fieldinfo['formtype']!='input' && !$options['is_editor']){
				// input时，为默认的text，不能指定type值为input
				// is_editor为true时,为编辑器ckeditor函数中调用，不能再将type设为ckeditor，防止重复调用，陷入死循环
				$options['type'] = $fieldinfo['formtype'];
			}
			
			if($options['type']=='checkbox'){
				$options ['multiple'] = 'checkbox';
			}
			
			if(!isset($options['after'])){
				$options['after'] = nl2br($fieldinfo['explain']);
			}
			if(!isset($options['label'])){
				$options['label'] = array('text'=>$fieldinfo['translate'],'class'=>'col-sm-2 control-label');
			}
			if ($fieldinfo ['formtype'] == 'select' && $fieldinfo ['selectmodel'] && $fieldinfo ['selecttxtfield'] && $fieldinfo ['selecttxtfield']) {
				//$fieldid = Inflector::camelize ( $modelClass . '_' . $key . '_associate' );
				//$options ['after'] = '&nbsp;<span for="' . $fieldid . '">' . __ ( 'Filter', true ) . '</span>&nbsp;<input class="associate-text" style="width: 80px;" type="text" value="" id="' . $fieldid . '" />';
				if ($modelClass != $fieldinfo ['selectmodel']) {
					$relateurl = $this->url ( '/admin/' . Inflector::tableize ( $fieldinfo ['selectmodel'] ) . '/add?model=' . $modelKey . '&select_parent_id=' . $fieldinfo ['selectparentid'] );
					// $relateurl = $this->Html->url($relateurl);
					$options ['after'] .= '&nbsp;<a href="' . $relateurl . '" onclick="return open_dialog({title:\'' . __ ( 'New ' ) .__d('modelextend', 'Model_' . $fieldinfo ['selectmodel'] ) . '\'},this.href);">' . __ ( 'New ' ) . __d('modelextend',  'Model_' . $fieldinfo ['selectmodel'] ) . '</a>';
				}
			}
		}
		if(!isset($options['label'])){
			// 此值在生成的语言缓存中，ToolsController::admin_updateLanCache
			$options['label'] = __d('i18nfield','Field_'.$modelKey.'_'.$fieldKey);
		}
		if(!is_array($options['label']) && $options['label']!=false){
			$options['label'] = array('text'=> $options['label'],'class'=>'col-sm-2 control-label');
		}
		if(!isset($options['class'])){
			$options['class'] = 'form-control';
		}
		
		if($options['type'] == 'textarea' || isset($options['rows'])){
			if($options['use_editor']!==false){
				$txt = __('Use WYSIWYG Editor.');
				if($options['is_editor']){
					$txt = __('Destory WYSIWYG Editor.');
				}
				//before
				$options['after'].='<div class="clearfix"></div><div class="btn btn-default use_editor clearfix">'.$txt.'</div>';
			}
			if(!isset($options['cols'])){
				$options['cols']=60;
			}
			if(!isset($options['rows'])){
				$options['rows']=2;
			}
		}
		
// 		print_r();
		$options = $this->_parseOptions($options);
		if ($options['label'] !== false && $options['type']!='radio') {
			$options['between'] .='<div class="col-sm-10 controls '.$options['type'].'">';
			$options['after'] .='</div>';
		}
		if($options['type']=='checkbox'){ // 重设checkbox的格式
			$options ['format'] = array('before', 'label', 'between','input',  'after', 'error');
		}
		if($options['type']=='radio'){ // 重设checkbox的格式
			$options['before'] .='<div class="col-sm-10 controls '.$options['type'].'">';
			$options['after'] .='</div>';
		}
		return parent::input($fieldName,$options)."\n";
	}
	
	public function form($options = array(), $type = 'post') {
		
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
				
		$form_start = $this->create ( $options ['model'], $options );
		$formhtml = $advancedhtml = $seohtml = '';
		if (Configure::read ( $options ['model'] . '.advancedfield' )) {
			$advancedfields = explode ( ',', Configure::read ( $options ['model'] . '.advancedfield' ) );
		} else {
			$advancedfields = explode ( ',', Configure::read ( 'Modelextend.advancedfield' ) );
		}
		
		$seofields = explode ( ',', Configure::read ( 'Modelextend.seofield' ) );
		
		$modelClass = Inflector::classify ( $options ['model'] );
		
		$ui_tab_nav = $ui_tab_content = '';
		if (! empty ( $options ['auto_form'] )) {
			$formhtml = $advancedhtml = $seohtml =  '';
			// print_r($this);exit;
			// print_r($this->params['_flowstep_edit_fields']);
			$_extschema = $this->_extschema[$modelClass];
			if(empty($_extschema)){
				$model_obj = loadModelObject ( $modelClass );
				$this->_extschema[$modelClass] = $_extschema = $model_obj->getExtSchema();
			}
			
			foreach ($_extschema as $key => $value ) {
				if(in_array($key,array('created','updated','creator','lastupdator','deleted'))){
					continue;//跳过不允许修改的字段
				}
				$field_html = $this->autoFormElement ( $modelClass, $key, $value, $options);
				if (in_array ( $key, $advancedfields ))
					$advancedhtml .= $field_html."\n";
				elseif (in_array ( $key, $seofields ))
					$seohtml .= $field_html."\n";
				else
					$formhtml .= $field_html."\n";
			}
			
			/*
			if ($model_obj->hasAndBelongsToMany) {
				foreach ( $model_obj->hasAndBelongsToMany as $key => $val ) {
					$formhtml .= $this->selectAssoc($val ['className']);
				}
			}*/
			if (isset ( $this->params ['_flowstep_edit_fields'] )) {
				$ui_tab_nav .= '<li class="active"><a href="#' . $modelClass . 'basic-info"><span>' . __ ( 'Basic', true ) . '</span></a></li>'."\n";
				// 流程中的增加与修改表单
				$ui_tab_content = '<div id="' . $modelClass . 'basic-info" class="tab-pane active"><fieldset>' . $formhtml . $advancedhtml . $seohtml . '</fieldset></div>';
			
			} else {
				$ui_tab_nav .= '<li class="active"><a href="#' . $modelClass . '-basic-info" data-toggle="tab" ><span>' . __ ( 'Basic Info' ) . '</span></a></li>'."\n";
				
				$ui_tab_nav .= '<li><a href="#' . $modelClass . '-advanced-info" data-toggle="tab" ><span>' . __ ( 'Advanced Options' ) . '</span></a></li>'."\n";
				$ui_tab_nav .= '<li><a href="#' . $modelClass . '-seo-info" data-toggle="tab" ><span>' . __ ( 'SEO' ) . '</span></a></li>'."\n";
				
				$formhtml = "\n".'<div id="' . $modelClass . '-basic-info" class="tab-pane active"><fieldset>' . $formhtml . '</fieldset></div>'."\n";
				$advancedhtml = "\n".'<div id="' . $modelClass . '-advanced-info" class="tab-pane"><fieldset>' . $advancedhtml . '</fieldset></div>'."\n";
				$seohtml = "\n".'<div id="' . $modelClass . '-seo-info" class="tab-pane"><fieldset>' . $seohtml . '</fieldset></div>'."\n";
				
				$ui_tab_content = $formhtml.$advancedhtml . $seohtml;
				$ui_tab_nav .= $this->Layout->getLanguageTabHead ( $modelClass );
				$ui_tab_content .= $this->Layout->getLanguageTabContent ( $modelClass );
			}
			if ($ui_tab_nav)
				$ui_tab_nav = '<ul class="nav nav-tabs">' . $ui_tab_nav . '</ul>'."\n";
			
			//$formhtml .= $this->submit ( __ ( 'Submit') );
			// echo '---------------------';print_r($params);
		}
		if (! isset ( $options ['form_type'] ))
			$options ['form_type'] = 'add';
		
// 		$formhtml .= $this->buildAssociate ( $modelClass, $options ['form_type'] ); // 生成级联操作的js代码
		$form_end = $this->submit ( __ ( 'Submit') ).$this->end();
		return $form_start.$ui_tab_nav.'<div class="tab-content">'.$ui_tab_content.'</div>'.$form_end.$script;
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
		
		if ($value ['formtype']=='select' && !empty($value ['selectmodel'])) {
// 			print_r($value);
			$props = array();			
			if(!empty($value['conditions'])){
				$query = xml_to_array($value['conditions']);
				$props = $query['options'];
			}
			if(!empty($value['selectparentid'])){
				if ($value['associatetype'] == 'treenode') {
					$selectmodel_name = Inflector::classify($value['selectmodel']);
					if (!$this->{$selectmodel_name} || !($this->{$selectmodel_name} instanceof AppModel)) {
						$this->{$selectmodel_name} = loadModelObject($selectmodel_name);
					}
					$rootcate = $this->{$selectmodel_name}->findById($value['selectparentid']);
					$props['conditions']['left >'] = $left = $rootcate[$selectmodel_name]['left'];
					$props['conditions']['right <'] = $right = $rootcate[$selectmodel_name]['right'];
				}
				else{
					$props['conditions']['parent_id'] = $value['selectparentid'];
				}
			}
			
			$conditions = http_build_query($props);
			
			$options ['class'] = 'chzn-select select';
			$options ['data-rel'] = 'chosen';
			$options ['data-url'] = $this->url ( '/admin/ajaxes/associate' ).
				'?valuefield=' . $value ['selectvaluefield'].
				'&selectmodel=' . $value ['selectmodel'] . 
				'&txtfield='. $value ['selecttxtfield'] . 
				'&associatefield=' . $value ['associatefield'] . 
				'&associatetype=' . $value ['associatetype'].
				'&'.$conditions;
		}
		
		$options ['id'] = Inflector::camelize ( $modelClass . '_' . $key );
		$options ['after'] = '';
		if (isset ( $value ['default'] )) {
			$options ['default'] = $value ['default'];
		}
		
		// append field explain text
		if (! empty ( $value ['explain'] )) {
			$options ['after'] .= '<span>' . $value ['explain'] . '</span>';
		}
		
		if($value['formtype']=='coverimg'){			
			$img = $options['value']?$options['value']:('nophoto.gif');			
			$options['after'] = $this->Html->image($img,array('width'=>120,'id'=>$options['id'].'Preview'));
			//$options['type'] = 'hidden';
			$options['class'] = 'form-control hidden';
			return $field_html = $this->input($key, $options);;
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
			$datetimehtml = '<div class="form-group">
					<label class="col-sm-2 control-label">' . $value ['translate'] . '</label>
					<div class="col-sm-10 controls text">
					<input class="datepicker" type="text" id="' . $fieldid . 'Ymd" name="data[' . $modelClass . '][' . $key . '][ymd]" value="' . $ymd . '">
					<input type="text" id="' . $fieldid . 'His" name="data[' . $modelClass . '][' . $key . '][his]" value="' . $his . '">
					</div>
					</div>';
			$script = '$(document).ready(function(){ 
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
					});';
			//$this->_View->append('bottomscript',$script);
			return $datetimehtml."<script>$script</script>";
			
		} elseif ($value ['formtype'] == 'ckeditor') {
			$field_html = $this->ckeditor( $key, array (
					'div' => false,
					'label' => false 
			) );
			return $field_html;
		} else {
			$field_html = '';
			if ($value ['formtype'] == 'file') {
				$field_html = $this->swfupload($key);
				//$field_html = $this->Swfupload->load ( $key, $modelClass ); // 文件上传类型
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
				$field_html = $this->input ( $modelClass . '.' . $key, $options );
			}
			return $field_html;
		}
	}
	/**
	 * 创建级联下拉的选项加载js代码，
	 * 关联其它模块的数据搜索
	 */
	function buildAssociate($modelClass, $form_type = 'add') {
		if(empty($modelClass)){
			return false;
		}
		// echo '<pre>'; print_r($this->data);
		// $modelClass = Inflector::classify($this->params['controller']);
		$js_str = '';
		$onload_trigger_changefields = array (); // 页面加载完成后，自动触发onchange事件的字段
		$_extschema = $this->_extschema[$modelClass];
		if(empty($_extschema)){
			$model_obj = loadModelObject ( $modelClass );
			$this->_extschema[$modelClass] = $_extschema = $model_obj->getExtSchema(); // $this->params['_extschema']
		}
		
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

	/**
	 * 新增的标题图片表单项
	 * @param string $fieldName 字段名
	 * @return string form表单项
	 */
	public function titleImage($fieldName){
		
		$this->setEntity($fieldName);
		$options = array();
		$options = $this->_initInputField($fieldName, $options);
		$id = $options['id'].'Preview';		
		$modelKey = $this->model();
		if(empty($options['value'])){
			$options['value']='nophoto.gif';/*默认图片*/
		}
		
		$img = $options['value']?$options['value']:('nophoto.gif');
		$options['after'] = $this->Html->image($img,array('width'=>120,'id'=>$options['id'].'Preview'));
		$options['class'] = 'form-control hidden';
		$html = $this->input($fieldName, $options);;
		return $html;
	}
	
	function ckeditor($fieldName,$options=array()){
		$options = $this->_initInputField($fieldName, $options);
		$id = $options['id'];		
		$model = $this->defaultModel;		
		$script = "<script type=\"text/javascript\">
		$(function(){
			if(typeof CKEDITOR.instances['$id']!='undefined'){
				//CKEDITOR.instances['$id'].destroy(); // 取消编辑器效果，替换为之前的textarea
				CKEDITOR.remove(CKEDITOR.instances['$id']); // 销毁删除对象
			}
			ckeditors['$id'] = CKEDITOR.replace('$id');
			ckeditors['$id'].on('focus',function(e){
				current_ckeditor_instance = e.editor;
			});
		
			CKEDITOR.on( 'instanceReady', function( e ){
				e.editor.document.appendStyleSheet( '".Router::url('/../stylevars/getcss.css')."' );
				e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/ui-customer.css')."' );
				//e.editor.setMode( 'source' );
				e.editor.on( 'mode', function( e ){
					if(e.editor.mode == 'wysiwyg'){
						e.editor.document.appendStyleSheet( '".Router::url('/../stylevars/getcss.css')."' );
						e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/ui-customer.css')."' );
					}
				});
				//if($('.cke_top').parent('ui-dialog-content').size()<1){
				//	$('.cke_top').addClass('clearfix').affix();
				//}
			});
			
		});
		</script>
		";
		$this->_View->append('bottomscript',$script);
		$options['type'] = 'textarea';
		$options['is_editor'] = true;
		$html = $this->input($fieldName,$options);
		return $html;
	}
	
	function swfupload($file_post_name = 'Filedata',$param = array()) {
	
		$this->setEntity($file_post_name);
		$size=10;
		if(!is_array($param)){
			$param = array('modelClass'=>$param);
		}
		if(empty($param['modelClass'])){
			$param['modelClass']=$this->model();
		}
		if(strpos($file_post_name,'.')===false){
			$file_post_name = $param['modelClass'].'.'.$file_post_name;
		}
	
		$fieldid = Inflector::camelize (str_replace('.','_',$file_post_name));
	
		$fieldname = $this->field(); //$file_post_name可能含“.”,经过field函数处理后，不含‘.’，得到name值
		$hide_options = array('id'=>$fieldid);
		if($param['value']){
			$hide_options['value']= $param['value'];
		}
		$hidden = $this->hidden($file_post_name,$hide_options);
	
		if(!empty($param['modelClass'])){
			$ext_schemas = $this->_extschema[$param['modelClass']];
			if(empty($ext_schemas)){
				$obj = loadModelObject($param['modelClass']);
				$this->_extschema[$param['modelClass']] = $ext_schemas = $obj->getExtSchema();
			}
			if($ext_schemas[$fieldname] && !empty($ext_schemas[$fieldname]['savetodb'])){
				// 字段选择的是savetodb时，字段值只保存到本模块的数据
				$param['no_db'] = 1;
				$param['upload_limit'] = 1;
				$param['upload_success_handler'] = 'uploadSuccess_'.$fieldname;
			}
		}
		$param = array_merge(array(
				'modelClass'=> 'Article',
				'isadmin' => true,
				'label' => __d('i18nfield','Field_'.$param['modelClass'].'_'.$fieldname),
				'after' => '',//描述
				'upload_limit'=> 0, // 最多允许上传的文件数，0为不限制
				'file_types'=> "*.*",
				'file_types_description'=> 'All Files',
				'button_image_url'=> '/img/uploadbutton.png', // 上传按钮描述
				'button_width'=> 100, // 上传按钮宽度
				'button_height'=> 24, // 上传按钮高度
				'no_db' =>0, // 是否保存到数据库
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
		if(!empty($this->data[$param['modelClass']][$fieldname]) || !empty($param['value'])){
			// 上传文件的地址保存到本模块对应的字段中，而非保存在Uploadfile里
			if($param['value']){
				$file_url = UPLOAD_FILE_URL.$param['value'];
				//$file_url = str_replace('//','/',UPLOAD_FILE_URL.$param['value']);
			}
			else{
				$file_url = UPLOAD_FILE_URL.($this->data[$param['modelClass']][$fieldname]);
			}
			if(is_image($file_url)){
				$listfile = '<a href="'.$file_url.'" title="'.__( 'Preview').'" target="_blank"><img src="'.$file_url.'" style="max-height:120px"/></a>';
			}
			else{
				$listfile = '<a href="'.$file_url.'" target="_blank">'.__( 'Preview').'</a>';
			}
		}
		elseif(isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile']) && !empty($this->data['Uploadfile'])){
			foreach($this->data['Uploadfile'] as $uploadfile){
				if($uploadfile['fieldname']==$fieldname){
					$listfile.='<li class="upload-fileitem clearfix" id="upload-file-'.$uploadfile['id'].'">';
					if(substr($uploadfile['fspath'],0,7) != 'http://'){
						$file_url = UPLOAD_FILE_URL.($uploadfile['fspath']);
					}
					else{
						$file_url = $uploadfile['fspath'];
					}
					if('image' == substr($uploadfile['type'],0,5)){
						if(substr($uploadfile['thumb'],0,7) != 'http://'){
							$thumb_url = UPLOAD_FILE_URL.($uploadfile['thumb']);
						}
						else{
							$thumb_url = $uploadfile['thumb'];
						}
						$listfile.='<img style="float:left;" src="'.$thumb_url.'" height="100px" width="100px"/>';
					}
					$listfile.='<input type="hidden" name="data[Uploadfile]['.$uploadfile['id'].'][id]" value="'.$uploadfile['id'].'">
	        		<p><label>'.__ ( 'Uploadfile Name').'</label>: <input type="text" name="data[Uploadfile]['.$uploadfile['id'].'][name]" value="'.urldecode($uploadfile['name']).'"/>
	        			<label>'.__ ( 'Sort Order').'</label>: <input style="width:30px;" type="text" name="data[Uploadfile]['.$uploadfile['id'].'][sortorder]" value="'.$uploadfile['sortorder'].'"/>
	        			<label>'.__ ( 'File Version').'</label>: <input style="width:60px;" type="text" name="data[Uploadfile]['.$uploadfile['id'].'][version]" value="'.$uploadfile['version'].'"/>
	        		</p>
	        		<p><label>'.__ ( 'Uploadfile Comment').'</label>:<textarea name="data[Uploadfile]['.$uploadfile['id'].'][comment]" row="2" cols="60">'.$uploadfile['comment'].'</textarea></p>
	        		 <p><a href="'.$file_url.'" target="_blank">' . __ ( 'Preview') . '</a>
	        		<a href="javascript:void(0);" onclick="insertHTML(\'&lt;img id=&#34;file_'.$uploadfile['id'].'&#34; src=&#34;'.($file_url).'&#34; >\')">'.__('Insert').'</a>
		
	        		<a class="upload-file-delete" rel="'.$uploadfile['id'].'" href="#" data-url="'.$this->url('/admin/uploadfiles/delete/'.$uploadfile['id'].'.json').'">'.__('Delete').'</a>
	        		<a href="javascript:void(0);" onclick="setCoverImg(\''.$param['modelClass'].'\',\''.$thumb_url.'\');">' . __ ( 'Set as title img') . '</a></p>
	        		';
					$listfile.='</li>';
				}
			}
		}
		if($param['isadmin']){
			$upload_url = $this->Html->url('/admin/uploadfiles/upload');
		}
		else{
			$upload_url = $this->Html->url('/uploadfiles/upload');
		}
		/*仅上传单个文件时，覆盖upload_success_handler函数*/
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
					alert(serverData);
				}
			}
':'').
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
	
		button_image_url : "'.$this->Html->url($param['button_image_url']).'",
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
		return $hidden.'
		<div class="form-group swfupload-control" >
			<label class="col-sm-2 control-label">'.$param['label'].'</label>'.
			'<div class="col-sm-10 controls"><span id="spanButtonPlaceholder_'.$fieldid.'"></span>'.$param['after'].'</div>
			<div class="clearfix"></div>
			<div class="col-sm-2">&nbsp;</div>
			<ul class="col-sm-10 upload-filelist" id="fileuploadinfo_'.$fieldname.'">'.$listfile.'</ul>
			<div class="clearfix"></div>
		</div>';
		
	}
	
	/**
	 * 选择相关模块的数据
	 * @param string $modelClass 相关模块
	 * @return string
	 */
	function selectAssoc($modelClass) {
		$targetid = $modelClass.'-'.time();
		$controller_name = Inflector::tableize($modelClass);
		$associd = Inflector::underscore($modelClass).'_id';
		$listword = '<div class="control-group required success"><label class="col-sm-2 control-label">'.__('Select').__d('modelextend','Model_'.$modelClass).'</label><div class="col-sm-10 controls" id="'.$targetid.'">';
		$listword.='<button onclick="open_dialog({title:\''.__('Select').'\'},\''.Router::url('/admin/'.$controller_name.'/list?type=select&targetid='.$targetid.'&m='.$this->defaultModel).'\')" class="btn btn-warning pull-left" style="margin:5px 5px;" type="button">'.__('Select').'</button>';
		if(is_array($this->data[$modelClass]) && !empty($this->data[$modelClass])){
			foreach($this->data[$modelClass] as $item){
				$listword.='<div class="btn-group">
				<input type="hidden" name="data['.$modelClass.']['.$item['id'].']['.$associd.']" value="'.$item['id'].'">
				<input type="hidden" name="data['.$modelClass.']['.$item['id'].'][relatedmodel]" value="'.$this->defaultModel.'">
				<button class="btn btn-primary">'.$item['name'].'</button>
				<button class="btn btn-primary btn-remove"><i class="glyphicon glyphicon-remove"></i></button></div>';
			}
		}
		$listword.='</div><div class="clearfix"></div></div>
		<script>
		$("#'.$targetid.'").on("click",".btn-remove",function(){
		$(this).closest(".btn-group").remove();
	});
	</script>
	';
		return $listword;
	}
	
	/**
	 * 选择关键字标签
	 * @return string
	 */
	function keyword() {
		$targetid = 'keywords-'.time();
		$listword = '<div class="control-group required success"><label class="col-sm-2 control-label">选择标签</label><div class="controls" id="'.$targetid.'">';
		if(is_array($this->data['Keyword']) && !empty($this->data['Keyword'])){
			foreach($this->data['Keyword'] as $word){
				$listword.='<div class="btn-group">
				<input type="hidden" name="data[Keyword]['.$word['id'].'][keyword_id]" value="'.$word['id'].'">
				<input type="hidden" name="data[Keyword]['.$word['id'].'][relatedmodel]" value="'.$word['KeywordRelated']['relatedmodel'].'">
				<button class="btn btn-primary">'.$word['value'].'</button>
				<button class="btn btn-primary btn-remove"><i class="icon-remove"></i></button></div>';
			}
		}
		$listword.='<button onclick="open_dialog({title:\'Select\'},\''.Router::url('/admin/keywords/select?targetid='.$targetid.'&m='.$this->defaultModel).'\')" class="btn btn-warning pull-left" style="margin:5px 5px;" type="button">'.__('Select').'</button>';
		$listword.='</div></div>
		<script>
		$("#'.$targetid.'").on("click",".btn-remove",function(){
			$(this).closest(".btn-group").remove();
		});
		</script>
		';
		return $listword;
	}
}

?>