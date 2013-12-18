<?php

class AjaxesController extends AppController {

    var $name = 'Ajaxes';
  // 对应的模块名为Ajaxis
    var $json = array();
    
//     var $uses = false;
    public $uses = array();

    // 加载某个模块的字段列表，在后台采集管理,Region区域编辑中使用
    function admin_loadschema($modelname='Article') {
        $this->autoRender = false;
        $this->loadModel($modelname);

        list($plugin, $modelClass) = pluginSplit($modelname, true);

        $fieldlist = array();
		$extschema = $this->{$modelClass}->getExtSchema();
        foreach ($extschema as $field => $type) {
            $fieldlist[$field] = $type['translate'] . '(' . $field . ')';
//			$fieldlist_str.='<option value="'.$field.'">'.$type['translate'].'('.$field.')</option>';
        }
        echo json_encode($fieldlist);
        exit;
//        $this->json[] = array(
//            'dotype' => 'html',
//            'selector' => '.model-schema-list',
//            'content' => $fieldlist_str
//        );
//        echo json_encode($this->json);
//        exit;
    }

    // 由模块名称，加载该模块的类别
    function admin_loadcate($modelname='Category', $selectid='', $selector='#CrawlCategoryId') {
        
        $modelClass = 'Modelcate';

        $this->loadModel($modelClass);
        $catelist = array();
        $fieldlist_str = '';
        //$categories = $this->{$modelname}->generatetreelist();
        if ($modelname == 'Article') {
            $categories = $this->{$modelClass}->generateTreeList();
        } else {
            $categories = $this->{$modelClass}->generateTreeList(array('model' => $modelname));
        }

        foreach ($categories as $cateid => $catename) {
            $catelist[$cateid] = $catename;
//            if ($selectid == $cateid) {
//                $fieldlist_str.='<option value="' . $cateid . '" selected="selected">' . $catename . '</option>';
//            } else {
//                $fieldlist_str.='<option value="' . $cateid . '">' . $catename . '</option>';
//            }
        }
        echo json_encode($catelist);
        exit;
//        $this->json['tasks'][] = array(
//            'dotype' => 'html',
//            'selector' => $selector,
//            'content' => $fieldlist_str
//        );
//
//        echo json_encode($this->json);
//        exit;
    }

    /**
     * 占用较小的内存，更适合网站空间php占用内存限制小的情况。
     * @param unknown_type $modelClass
     * @param unknown_type $searchoptions
     */
    private function _downloadxml($modelClass,$searchoptions){
    	@set_time_limit(0);
    	App::import('Vendor', 'Excel_XML', array('file' => 'phpexcel'.DS.'excel_xml.class.php'));
    	$xls = new Excel_XML('UTF-8', false, 'My Test Sheet');
    	
    	$extschema = $this->{$modelClass}->getExtSchema();    	
    	unset($extschema['creator'],$extschema['lastupdator'],
    			$extschema['updated'],$extschema['locale'],
    			$extschema['published'],$extschema['deleted'],
    			$extschema['favor_nums'],$extschema['point_nums'],$extschema['views_count'],
    			$extschema['seotitle'],$extschema['seodescription'],$extschema['seokeywords']);
    	$header = array();
    	foreach($extschema as $item){
    		$header[] = $item['translate'];
    	}
    	$xls->addRow($header);
    	unset($searchoptions['limit'],$searchoptions['page'],$searchoptions['fields']);
    	
    	$fields = array_keys($extschema);
    	$page = 1;
    	$pagesize = 500;
    	do{
    		$searchoptions['limit'] = $pagesize;
    		$searchoptions['page']=$page;
    		$datas = $this->{$modelClass}->find('all', $searchoptions);
    		$rows = count($datas);
    		foreach($datas as $item){
    			$row = array();
    			foreach($fields as $fieldname){
    				$row[] = $item[$modelClass][$fieldname];
    			}
    			$xls->addRow($row);
    		}
    		unset($datas);// 主动注销防止变量占用太多内存
    		++$page;
    	}while($rows==$pagesize);
    	
    	$xls->generateXML($modelClass.'_'.date('Y-m-d'));
    }
    
    /**
     * PHPExcel examples
     * https://github.com/PHPOffice/PHPExcel/tree/develop/Examples
     * @param unknown_type $conditions
     */
    private function _downloadPHPExcel($modelClass,$searchoptions){
    	@set_time_limit(0);
    	App::import('Vendor', 'PHPExcel', array('file' => 'phpexcel'.DS.'PHPExcel.php'));
    	/** PHPExcel_Writer_Excel2007 */
    	$objPHPExcel = new PHPExcel();
    	
    	$objPHPExcel->getProperties()->setCreator("MiaoMiaoXuan");
    	$objPHPExcel->getProperties()->setTitle($modelClass.'_'.date('H:i:s'));
    	$objPHPExcel->setActiveSheetIndex(0);
    	
    	$extschema = $this->{$modelClass}->getExtSchema();
    	$cells = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N',
    			'O','P','Q','R','S','T','U','V','W','X','Y','Z',
    			'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN',
    			'AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
    			'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN',
    			'BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
    			);
    	$tcols = count($cells);
    	unset($extschema['creator'],$extschema['lastupdator'],
    			$extschema['updated'],$extschema['locale'],
    			$extschema['published'],$extschema['deleted'],
    			$extschema['favor_nums'],$extschema['point_nums'],$extschema['views_count'],
    			$extschema['seotitle'],$extschema['seodescription'],$extschema['seokeywords']);
    	$i=0;
    	foreach($extschema as $item){
    		$objPHPExcel->getActiveSheet()->SetCellValue($cells[$i].'1',$item['translate']);
    		$i++;
    		if($i>=$tcols){break;}
    	}
    	
    	unset($searchoptions['limit'],$searchoptions['page'],$searchoptions['fields']);
    	
    	$fields = array_keys($extschema);
    	$page = 1;
    	$pagesize = 500;
    	$line = 2;//表头为第一行，内容从第二行开始。
    	do{
    		$searchoptions['limit'] = $pagesize;
    		$searchoptions['page']=$page;
	    	$datas = $this->{$modelClass}->find('all', $searchoptions);
	    	$rows = count($datas);
	    	foreach($datas as $item){
	    		$i = 0;
	    		foreach($fields as $fieldname){
	    			$objPHPExcel->getActiveSheet()->SetCellValue($cells[$i].$line,$item[$modelClass][$fieldname]);
	    			$i++;
	    			if($i>=$tcols){break;}
	    		}
	    		++$line;
	    	}
	    	unset($datas);// 主动注销防止变量占用太多内存
	    	++$page;
    	}while($rows==$pagesize);
    	//$objPHPExcel->getActiveSheet()->SetCellValue('B2', 'world!');
    	//$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Hello');
    	//$objPHPExcel->getActiveSheet()->SetCellValue('D2', 'world!');
    	// Rename sheet
    	$objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    	
    	// Save Excel 2007 file
    	//echo date('H:i:s') . " Write to Excel2007 format\n";
    	//App::import('Vendor', 'PHPExcel_Writer_Excel2007', array('file' => 'phpexcel/PHPExcel/Writer/Excel2007.php'));
    	//include 'PHPExcel/Writer/Excel2007.php'; 
    	//$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    	//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
    	
    	header('Content-Type: application/vnd.ms-excel');
    	header('Content-Disposition: attachment;filename="'.$modelClass.'_'.date('Y-m-d').'.xls"');
    	header('Cache-Control: max-age=0');
    	
    	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); //'Excel2007'
    	$objWriter->save('php://output');
    }
    
    function admin_loaddata($modelClass,$id){
    	$this->loadModel($modelClass);
        list($plugin, $modelClass) = pluginSplit($modelClass, false);
        $this->autoRender = false;
        $this->data = $this->{$modelClass}->read(null, $id);
        echo json_encode($this->data);
    }
    /**
     * 处理搜索统计的请求，生成所admin_groupby模板的报表
     */
    function admin_groupby() {
        $this->autoRender = true;

        $modelClass = $_GET['model'];
        $this->loadModel($modelClass);

        $options = $this->params['form'];
        $_options = array(
            'rows' => 300,
            'page' => 1,
            'sidx' => 'id',
            'sord' => 'DESC',
        );
        $options = array_merge($_options, $options);

        $_schema_keys = array_keys($this->{$modelClass}->schema());
        $sort_order = '';
        $extSchema = $this->{$modelClass}->getExtSchema();
        $search_fields = $fields = $search_groupby = $search_groupby_fields = $search_sum = $conditions = array();
        $has_sum_field = false;
        if (!empty($_GET['conditions'])) {
            foreach ($_GET['conditions'] as $key => $val) {
                if (empty($val)) {
                    unset($_GET['conditions'][$key]);
                } elseif (substr($key, -8) == 'ymdstart') {
                    $newkey = substr($key, 0, -9);
                    $conditions[$newkey . ' >='] = $val;
                } elseif (substr($key, -6) == 'ymdend') {
                    $newkey = substr($key, 0, -7);
                    $conditions[$newkey . ' <='] = $val;
                } elseif (substr($key, -8) == '.groupby' && is_array($val)) {
                    $hasgroup = false;
                    foreach ($val as $gf) {
                        if ($gf) {
                            $hasgroup = true;
                            $search_groupby_fields[] = $gf;
                            $search_groupby[] = $modelClass . '.' . $gf;
                            $search_fields[] = $modelClass . '.' . $gf; // search_fields记录搜索sql中的字段名与模块名，防止在sql中多模块出现相同的字段，造成sql中无法区分属于哪个模块
                            $fields[] = $gf; // fields数组，只记录单独的字段名
                        }
                    }
                    if ($hasgroup) {
                        $search_fields[] = 'count(*) as groupnum';
                        $sort_order = 'groupnum desc,';
                    }
                    unset($_GET['conditions'][$key]);
                    continue;
                } elseif (substr($key, -4) == '.sum' && !empty($val) && is_array($val)) {
                    $sum_sort = false;
                    foreach ($val as $gf) {
                        if ($gf) {
                            $has_sum_field = true;
                            $search_sum[] = $gf;
                            $search_fields[] = 'sum(' . $modelClass . '.' . $gf . ') as ' . $gf; // sum() 字段，求和的字段
                            // search_fields记录搜索sql中的字段名与模块名，防止在sql中多模块出现相同的字段，造成sql中无法区分属于哪个模块
                            $fields[] = $gf; // fields数组，只记录单独的字段名
                            if (!$sum_sort) {
                                $sum_sort = true;
                                $sort_order = $gf . ' desc,'; // 按求和的第一个字段排序，其他求和字段略过
                            }
                        }
                    }

                    unset($_GET['conditions'][$key]);
                    continue;
                } else {
                    $conditions[$key] = $val;
                }
            }
            //			print_r($conditions);
        }
        

        if ((!isset($conditions['deleted']) && !isset($conditions[$modelClass . '.deleted'])) && in_array('deleted', $_schema_keys)) {
            $conditions[$modelClass . '.deleted'] = 0;
        }

        $searchoptions = array(
            'conditions' => $conditions,
            'order' => $sort_order . $modelClass . '.' . $options['sidx'] . ' ' . $options['sord'],
            'limit' => $options['rows'],
            'page' => $options['page'],
            'fields' => $search_fields,
            'group' => $search_groupby,
        );
        $joinmodel_fields = array();
        $alias = 0;
        if (!in_array($modelClass, array('I18nfield', 'Modelextend'))) {
            foreach ($extSchema as $k => $fieldinfo) {
                if (in_array($k, $fields) && $fieldinfo['selectmodel'] && $fieldinfo['selectvaluefield'] && $fieldinfo['selecttxtfield'] && in_array($fieldinfo['formtype'], array('select', 'checkbox', 'radio'))) {
                    $alias++;
                    $join_model = $fieldinfo['selectmodel'];
                    $model_alias = $join_model . '_' . $alias;
                    $selectvaluefield = $fieldinfo['selectvaluefield'];
                    $selecttxtfield = $fieldinfo['selecttxtfield'];
                    $searchoptions['fields'][] = $model_alias . '.' . $selecttxtfield . ' as ' . $k . '_txt';
                    //$searchoptions['order'] = $join_model.'.'.$selecttxtfield;
                    $joinmodel_fields[$k] = $model_alias;

                    $joinconditions = array($model_alias . '.' . $selectvaluefield . ' = ' . $modelClass . '.' . $k);

                    if ($fieldinfo['associateflag'] && $fieldinfo['associateelement'] && $fieldinfo['associatefield']) {
                        //将级联操作的字段也作为表单连接的条件，否则会包含不符合条件的多余的记录
                        $joinconditions[] = $model_alias . '.' . $fieldinfo['associatefield'] . '=' . $modelClass . '.' . $fieldinfo['associateelement'];
                    }
                    $searchoptions['joins'][] = array(
                        'table' => Inflector::tableize($join_model),
                        'alias' => $model_alias,
                        'type' => 'left',
                        'conditions' => $joinconditions,
                    );
                }
            }
        }
        $datas = $this->{$modelClass}->find('all', $searchoptions);

        $tableheader = $rows = array();
        $col_models = '';
        $control_name = Inflector::tableize($modelClass);
        if (count($search_groupby_fields) == 2) {
            $field1 = $search_groupby_fields[0];
            $field2 = $search_groupby_fields[1];
            $field1_values = $field2_values = array();
            foreach ($datas as $key => $item) {
                foreach ($joinmodel_fields as $joinfield => $joinmodel) {
                    if ($item[$joinmodel][$joinfield . '_txt']) {
                        $item[$modelClass][$joinfield] = $item[$joinmodel][$joinfield . '_txt'];
                    }
                }
                if (!in_array($item[$modelClass][$field1], $field1_values)) {
                    $field1_values[] = $item[$modelClass][$field1];
                }
                if (!in_array($item[$modelClass][$field2], $field2_values)) {
                    $field2_values[] = $item[$modelClass][$field2];
                }
                $item[$modelClass]['groupnum'] = $item[0]['groupnum'];
                foreach ($search_sum as $sumfield) {
                    $item[$modelClass][$sumfield] = $item[0][$sumfield];
                }
                //$item[$modelClass]['groupnum'] = $item[0]['groupnum'];
                $rows[] = $item[$modelClass];
            }
            $header_field = $left_field = '';
            $header_array = array();
            if (count($field1_values) < count($field2_values)) {
                $header_array = $field1_values;
                $header_field = $field1;
                $left_field = $field2;
            } else {
                $header_array = $field2_values;
                $header_field = $field2;
                $left_field = $field1;
            }
            $showtable = array();
            foreach ($rows as $row) {
                if ($has_sum_field) {
                    $showtable[$row[$left_field]][$row[$header_field]] = $row[$search_sum[0]];
                } else {
                    $showtable[$row[$left_field]][$row[$header_field]] = $row['groupnum'];
                }
                $showtable[$row[$left_field]][$left_field] = $row[$left_field];
            }
            $default_values = array();
            $col_names = "'',";
            $col_models = "{name:'$left_field',index:'$left_field',width:60,align:'right'},";
            foreach ($header_array as $headname) {
                $default_values[$headname] = '0';
                $col_names .= "'" . $headname . "',";
                $col_models .= "{name:'$headname',index:'$headname',width:60,align:'right', sorttype:'int'},";
            }
            $col_names = substr($col_names, 0, -1);
            $col_models = substr($col_models, 0, -1);
            $this->set('col_names', $col_names);
            $this->set('col_models', $col_models);

            $rows = array();
            $jsonrows = arrayToJson($showtable);

            $this->set('jsonrows', $jsonrows);
        } else {
            foreach ($datas as $key => $item) {
                foreach ($joinmodel_fields as $joinfield => $joinmodel) {
                    if ($item[$joinmodel][$joinfield . '_txt']) {
                        $item[$modelClass][$joinfield] = $item[$joinmodel][$joinfield . '_txt'];
                    }
                }
                if ($key == 0) {
                    foreach ($item[$modelClass] as $k => $v) {
                        $tableheader[] = $extSchema[$k]['translate'];
                        $col_models .= "{name:'$k',index:'$k', width:60},";
                    }
                    foreach ($search_sum as $sumfield) {
                        $tableheader[] = $extSchema[$sumfield]['translate'];
                        $col_models .= "{name:'$sumfield',index:'$sumfield',width:60,align:'right',sorttype:'float'},";
                    }
                    $tableheader[] = __('groupnum', true);
                    $col_models .= "{name:'groupnum',index:'groupnum',width:60,align:'right',sorttype:'float'}";
                    $this->set('col_names', "'" . implode("','", $tableheader) . "'");
                    //				echo $col_models;
                    $this->set('col_models', $col_models);
                }

                $item[$modelClass]['groupnum'] = $item[0]['groupnum'];
                foreach ($search_sum as $sumfield) {
                    $item[$modelClass][$sumfield] = $item[0][$sumfield];
                }
                //$item[$modelClass]['groupnum'] = $item[0]['groupnum'];
                $rows[] = $item[$modelClass];
            }
            $jsonrows = json_encode($rows);
            $this->set('jsonrows', $jsonrows);
        }
    }

    /**
     * 树形模块加载数据
     */
    function admin_jqgridtree() {
        $modelClass = $_GET['model'];
        $this->loadModel($modelClass);

        list($plugin, $modelClass) = pluginSplit($modelClass, true);
        $options = $this->params['form'];
		
        $extSchema = $this->{$modelClass}->getExtSchema();
        $_schema_keys = array_keys($this->{$modelClass}->schema());

        $fields = array('*');

        $conditions = array();
        $level = 0;
        if (!empty($_POST['nodeid'])) {
            $conditions = array($modelClass . '.parent_id' => $_POST['nodeid']);
            $parents = $this->{$modelClass}->getPath($_POST['nodeid']);
            $level = count($parents);
        }
        // 无nodeid时，加载所有数据，自动展示3层。其余通过ajax动态加载。
//         else{
//         	$_POST['nodeid'] = null;
//         	$conditions[$modelClass . '.parent_id'] = null;
//         }
        if (in_array('deleted', $_schema_keys)) {            
	        if (!empty($_GET['conditions'][$modelClass . '.deleted'])) {
	            $conditions[$modelClass . '.deleted'] = 1;
	            // 对已删除的内容，设置parent_id条件。
	            unset($conditions[$modelClass . '.parent_id']);
	        }
	        else{
	        	$conditions[$modelClass . '.deleted'] = 0;
	        }
        }

        if (empty($options['rows'])){
            $options['rows'] = 1000; //设置一个最大值，防止数据量过多时，造成卡死
        }
        
        
        $extSchema = $this->{$modelClass}->getExtSchema();
        $fields = array_keys($extSchema);
        $model_setting = Configure::read($modelClass);
        if (isset($model_setting['list_fields'])) {
        	$listfields = explode(',', $model_setting['list_fields']);
        } else {
        	$listfields = $fileds;
        }
        
        $ext_options = array();
        if (!in_array($modelClass, array('I18nfield', 'Modelextend'))) {
        	foreach ($extSchema as $k => $fieldinfo) {
        		if($fieldinfo['selectvalues'] && in_array($fieldinfo['formtype'], array('select', 'checkbox', 'radio'))){
        			$ext_options[$fieldinfo['name']] = optionstr_to_array($fieldinfo['selectvalues']);
        		}
        	}
        }
        
 		$searchoptions = array(
            'conditions' => $conditions,
            'limit' => $options['rows'],
            'page' => $options['page'],
            'fields' => $fields,
        );
 		// 树形结构时，必需已left，从小到大排序
 		if(in_array('left', $_schema_keys)){
 			$searchoptions['order'] = $modelClass . '.left asc'; 
 		}
 		elseif(in_array('lft', $_schema_keys)){
 			$searchoptions['order'] = $modelClass . '.lft asc'; 
 		}
//  		print_r($searchoptions);
        $datas = $this->{$modelClass}->find('all', $searchoptions);
        
//         $datas = $this->{$modelClass}->children($_POST['nodeid'], false, null, null, null, 1, 0);
//         print_r($datas);exit;
        $rows = array();
        $control_name = Inflector::tableize($modelClass);
        /**
         * 将id指向parengtid，用于计算level
         * @var array
         */
        $trunks = array();
        $tree_fields = array('parent_id','left','lft','right','rght');

        foreach ($datas as $item) {        	
        	foreach ($fields as $field_name) {
        		if (!in_array($field_name,$tree_fields) && !in_array($field_name, $listfields)) {
        			unset($item[$modelClass][$field_name]);
        			continue;
        		}
                if($extSchema[$field_name]['selectvalues'] && in_array($extSchema[$field_name]['formtype'], array('select', 'checkbox', 'radio'))){
                	$tmpval = $item[$modelClass][$field_name];
                	$item[$modelClass][$field_name] = $ext_options[$field_name][$tmpval];
                }
                $item[$modelClass][$field_name] = htmlspecialchars($item[$modelClass][$field_name]); 
            }
            if ($item[$modelClass]['deleted'] == 1) {
                $actions = '<li class="ui-state-default grid-row-restore"><a href="#" onclick="ajaxAction(\'' . Router::url('/admin/' . $control_name . '/restore/' . $item[$modelClass]['id']) . '.json\',null,null,\'deleteGridRow\',this)" title="' . __('Restore') . '"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span></li>';
                $actions .= '<li class="ui-state-default grid-row-delete"><a href="#" onclick="ajaxAction(\'' . Router::url('/admin/' . $control_name . '/delete/' . $item[$modelClass]['id']) . '.json\',null,null,\'deleteGridRow\',this)" title="' . __('Delete') . '"><span class="ui-icon ui-icon-close"></span></li>';
            } else {
            	$actions = '<li class="ui-state-default grid-row-edit"><a title="' . __('Edit') . '" href="' . Router::url(array('controller' => $control_name, 'action' => 'edit', 'plugin' => $plugin, 'admin' => true, $item[$modelClass]['id'])) . '"><span class="ui-icon ui-icon-pencil"></span></a></li>';
                $actions .= '<li class="ui-state-default grid-row-trash"><a href="#" onclick="ajaxAction(\'' . Router::url('/admin/' . $control_name . '/trash/' . $item[$modelClass]['id']) . '.json\',null,null,\'deleteGridRow\',this)"  title="' . __('Trash') . '"><span class="ui-icon ui-icon-trash"></span></a></li>';
                
                $actions .=  '<li class="ui-state-default grid-row-action"><a href="#"  onclick="ajaxAction(\''.Router::url('/admin/' . $control_name . '/treesort.json').'\',{id:'.$item[$modelClass]['id'].',\'type\':\'up\'})" title="'.__('Tree Sort Up').'"><span class="ui-icon ui-icon-arrowthick-1-n"></span></a></li>';
				$actions .=  '<li class="ui-state-default grid-row-action"><a href="#"  onclick="ajaxAction(\''.Router::url('/admin/' . $control_name . '/treesort.json').'\',{id:'.$item[$modelClass]['id'].',\'type\':\'down\'})"  title="'.__('Tree Sort Down').'"><span class="ui-icon ui-icon-arrowthick-1-s"></span></a></li>';
				
				/*若tree表结构中含model，则创建的下级默认model与上级保持一致*/
				$named_model = $item[$modelClass]['model']? $item[$modelClass]['model'] : ($this->params['named']['model'] ? $this->params['named']['model'] : $modelClass);
				$actions .=  '<li class="ui-state-default grid-row-action"><a href="'.Router::url('/admin/' . $control_name . '/add?model='.$named_model.'&parent_id='.$item[$modelClass]['id']).'" title="'.__('Add Sub').'"><span class="ui-icon ui-icon-document"></span></a></li>';
	
                //树形结构，删除时，不重新加载数据。（按层级加载的）,reloadGrid
            }
            //$actions = '<a class="ui-grid-edit"  href="javascript:void(0);" onclick="return open_dialog(this,\''.Router::url('/admin/'.$control_name.'/edit/'.$item[$modelClass]['id']).'\');"><span class="ui-icon ui-icon-pencil"></span>'.__('Edit',true).'</a>&nbsp;';
            $actions .= $this->Hook->call('gridDataAction', array($modelClass, $item[$modelClass]));
            $item[$modelClass]['actions'] = '<ul class="ui-grid-actions">' . $actions . '</ul>';

            $level_currentid = $item[$modelClass]['id'];
            if (empty($_POST['nodeid'])) {
                $level = 0;
                // 将id指向parengtid，用于计算level
                $trunks[$level_currentid] = $item[$modelClass]['parent_id'];
                while ($level_currentid && $trunks[$level_currentid]) {
                    $level++;
                    $level_currentid = $trunks[$level_currentid];
                }
                if ($level > 2)
                    continue; // 初始化加载时，只加载到第三层。0,1,2
            }

            $item[$modelClass]['level'] = $level;
            if ($item[$modelClass]['right'] == $item[$modelClass]['left'] + 1) {
                $item[$modelClass]['isLeaf'] = true;
            }
        	elseif ($item[$modelClass]['rght'] == $item[$modelClass]['lft'] + 1) {
        		$item[$modelClass]['right'] = $item[$modelClass]['rght'];
        		$item[$modelClass]['left'] = $item[$modelClass]['lft'];
                $item[$modelClass]['isLeaf'] = true;
            } else {
                $item[$modelClass]['isLeaf'] = false;
            }

// 			if($level>3 || $item[$modelClass]['isLeaf']){
// 				$item[$modelClass]['expanded'] = false;
// 			}
// 			else{
// 				$item[$modelClass]['expanded'] = true;
// 			}

            $item[$modelClass]['expanded'] = false;
            if (!$item[$modelClass]['parent_id'])
                $item[$modelClass]['parent_id'] = 'NULL';
            //unset($item[$modelClass]['parent_id']);
            $rows[] = $item[$modelClass];
        }


        $this->json = array(
            'records' => 1,
            'page' => 1,
            'total' => 1,
            'rows' => $rows,
        );

        echo json_encode($this->json);
        exit;
    }

    /**
     * 对字段进行排序，设置sort值，从admin/I18nfields/sortfield动作中提交过来
     */
    function admin_sortfield() {
        $this->loadModel('I18nfield');
        foreach ($_POST as $modelClass => $order) {
            $count = count($order);
            foreach ($order as $k => $i18nfield_id) {
                // save 函数多进行了一次数据库 select操作，不如update节省数据库连接
                $this->I18nfield->updateAll(array('sort' => ($count - $k)), array('id' => $i18nfield_id));
            }
            Cache::delete('extschema_' . $modelClass);
        }
        echo 'over';
        exit;
    }

    /**
     * 由本表单元素某一项的值变化时，决定此字段的值属性。
     * 本字段为级联的目标
     * selectmodel.本字段级联选择的数据模块
     * 
     * Associateelement级联的元素，字段的值作为associatefield对应的字段的值的筛选条件,$_POST['ass_val']
     * associatefield级联的数据库字段，字段的值为Associateelement元素对应的值,$_POST['ass_val']
     * 
     * Selectmodel 选择的模块
     * 
     * 加载下拉选项
     */
    function admin_associate() {
        $cacheKey = serialize($_REQUEST);
        $content = Cache::read($cacheKey);
        if ($content === false) {
            $modelClass = $_REQUEST['selectmodel'];
            $this->loadModel($modelClass);
            $conditions = array();
            if(!empty($_REQUEST['conditions']) && is_array($_REQUEST['conditions'])){
            	$conditions = $_REQUEST['conditions'];
            }
            if ($_REQUEST['associatefield'] && $_REQUEST['ass_val']) {
                switch ($_REQUEST['associatetype']) {
                    // 级联下拉
                	case 'like':
                		$conditions = array($modelClass . '.' . $_REQUEST['associatefield'] . ' like' => '%' . $_REQUEST['ass_val'] . '%');
                		break;
                	case 'equal':
                    case 'treenode':
                    default:
                        $conditions = array($modelClass . '.' . $_REQUEST['associatefield'] => $_REQUEST['ass_val']);
                        break;
                }
            }
            $extSchema = $this->{$modelClass}->getExtSchema();
            $model_fields = array_keys($extSchema);
            if (in_array('deleted', $model_fields)) {
                $conditions[$modelClass . '.deleted'] = 0; // 只加载未删除的数据
            }
            if (!empty($_REQUEST['txt_filter'])) {
                // 文本框筛选
                $conditions[$modelClass . '.' . $_REQUEST['txtfield'] . ' like'] = '%' . $_REQUEST['txt_filter'] . '%';
            }
            $searchoptions = array(
                'conditions' => $conditions,
                'order' => $modelClass . '.' . $_REQUEST['txtfield'],
                'limit' => 100,
                'page' => 1,
                'fields' => array($_REQUEST['valuefield'], $_REQUEST['txtfield']),
            );
            if ($_REQUEST['txtfield'] == $_REQUEST['valuefield'] && substr($_REQUEST['txtfield'], -3) == '_id') {
                $join_model = $extSchema[$_REQUEST['txtfield']]['selectmodel'];
                $selectvaluefield = $extSchema[$_REQUEST['txtfield']]['selectvaluefield'];
                $selecttxtfield = $extSchema[$_REQUEST['txtfield']]['selecttxtfield'];
                $searchoptions['fields'] = array($join_model . '.' . $selectvaluefield, $join_model . '.' . $selecttxtfield);
                $searchoptions['order'] = $join_model . '.' . $selecttxtfield;


                $searchoptions['joins'] = array(
                    array(
                        'table' => Inflector::tableize($join_model),
                        'alias' => $join_model,
                        'type' => 'right',
                        'conditions' => array($join_model . '.' . $selectvaluefield . ' = ' . $modelClass . '.' . $_REQUEST['valuefield'])
                    ),
                );
            }
            $datas = $this->{$modelClass}->find('all', $searchoptions);
            $json = array(array('text' => __('Please select'), 'value' => ''));
            foreach ($datas as $val) {
                if (isset($selectvaluefield)) {
                    $json[] = array('text' => $val[$join_model][$selecttxtfield], 'value' => $val[$join_model][$selectvaluefield]);
                } else {
                    $json[] = array('text' => $val[$modelClass][$_REQUEST['txtfield']], 'value' => $val[$modelClass][$_REQUEST['valuefield']]);
                }
            }
            $content = json_encode($json);
            Cache::write($cacheKey, $content);
        }
        $this->autoRender = false;
        echo $content;
    }

}