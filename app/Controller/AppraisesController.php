<?php

class AppraisesController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Appraises';
	function beforeFilter()
	{
		$this->set('top_category_id',35);
		parent::beforeFilter();
	}
	function publish()
	{
		$modelClass = $this->modelClass;
		if (!empty($this->data)) {
			if (empty($this->data['Appraiseoption']))
	        {
	        	$successinfo = array('error'=>__('Empty Appraiseoption',true));
				echo json_encode($successinfo);
        		return ;
	        }
			$this->data[$modelClass]['published'] = 1;
			$this->data[$modelClass]['deleted'] = 0;
			$this->data[$modelClass]['columns'] = 1;
			
			$this->autoRender = false;			
			$this->{$modelClass}->create();
			if ($this->{$modelClass}->save($this->data)) {
				$last_insert_id = $this->{$modelClass}->getLastInsertID();
				
				$this->loadModel('Appraiseoption');
	        	$options = $this->data['Appraiseoption'];
	        	foreach($options as $option)
	        	{
	        		$this->Appraiseoption->create();
	        		$option['qid'] = $last_insert_id;
	        		$this->Appraiseoption->save($option); // 保存选项
	        	}
	        	
				$weibo_content = "我发起了一个#点名投票#：“ ";
				$weibo_content .= preg_replace('/[“|”]/','"',$this->data[$modelClass]['name']);
				$weibo_content .= " ”，快来投票吧， 点击链接进入".DIANMING_HOST."/questions/$last_insert_id.html";
				
				$send_status = $this->WeiboUtil->new_weibo($weibo_content);
		
				if(empty($send_status['id']))
				{
					$this->{$modelClass}->deleteAll(array('id'=>$last_insert_id)); // 删除信息，提示错误
					$this->Appraiseoption->deleteAll(array('qid'=>$last_insert_id));
					echo json_encode( array( 'name'=> __($send_status['error'],true)) );
					return ;
				}
				$this->data[$modelClass]['id'] = $last_insert_id;
				
				$this->data[$modelClass]['weibo_id'] = $send_status['id'];
				$this->data[$modelClass]['creator'] = $this->currentUser['User']['nickname'];
				$this->data[$modelClass]['creator_id'] = $this->currentUser['User']['sina_uid'];
				$this->data[$modelClass]['user_img'] = $this->currentUser['User']['image'];
				
				$this->{$modelClass}->save($this->data);
				
				if(!empty($_POST['inviteusers']))
				{
					//邀请用户参与回答
					$inviteusers = '';
					$i=0;
					foreach($_POST['inviteusers'] as $key=>$value)
					{
						$i++;
						$inviteusers = '@'.$value.',';
						if($i==10)
						{
							break; // 限制最多转发10个
						}
					}
					$inviteusers = substr($inviteusers,0,-1);
					
					$weibo_content = '我#点名#邀请'.$inviteusers.' 来参与投票。 点击链接进入'.DIANMING_HOST.'/questions/'.$last_insert_id.'.html';
//					$repost_status = $this->WeiboUtil->repost($send_status['id'],$weibo_content);
//					print_R($repost_status);
					//连续调用接口发送错误，将邀请微博内容放入队列中，过一会再发送。
					//邀请的微博内容，不记录缓存					
					$this->loadModel('TransQueue');
					$this->data['TransQueue']['name'] = $weibo_content;
					$this->data['TransQueue']['weibo_id'] = $send_status['id'];
					$this->data['TransQueue']['creator'] = $this->currentUser['User']['sina_uid'];
					$this->TransQueue->save($this->data);
				}
				
				$successinfo = array('success'=>__('Add success',true));
				echo json_encode($successinfo);
        		return ;
        		
			} else {
				// 保存发生错误，删除对应的微博
				// $this->WeiboUtil->delete_weibo($send_status['id']);
				echo json_encode($this->{$this->modelClass}->validationErrors);
		        return ;
			}
		}
		else
		{
			$this->__loadFormValues($this->modelClass);
		}
	}
    
	function view($id)
	{
		$this->loadModel('Appraiseoption');
    	$question  = $this->Appraise->find('first',array(
    		'conditions' => array(
    			'deleted' => 0,
    			'published' => 1,
    			'id' => $id,
    		)
    	));
    	
//    	$listhtml =$this->__showsingle_question($question['Appraise'],1);  

    	$options  = $this->Appraiseoption->find('all',array('conditions'=>array('qid'=>$id,'deleted'=>0)));
		
    	$option_table = $this->__drewOptionTable($options,$question['Appraise']);
    	
//    	$this->set('question_options', $listhtml);
    	$this->set('option_table', $option_table);
    	$this->set('question', $question['Appraise']);
	}
    
    function load($qid,$data_id='1',$model='Article')
    {
    	// 加载显示投票表单
    	$modelClass = $this->modelClass;
    	$this->data = $this->{$modelClass}->read(null, $qid);
    	
    	$this->loadModel('Appraiseoption');
    	$options  = $this->Appraiseoption->find('all',array(
    		'conditions' => array(
    			'deleted' => 0,
    			'qid' => $qid,
    		)
    	));
//    	print_r($options);
    	$this->set('options', $options);
    	$this->set('model', $model);
    	$this->set('data_id', $data_id);
    	$this->set('qid', $qid);
    }

	function __showsingle_question($value,$widthedit=1)
	{
		global $db,$tablepre,$l_site;
		if($value['is_require'] || $value['minselect'])
		{
			$is_require='(必填 ';	
			if($value['minselect'])
			{
				$is_require.='至少填'.$value['minselect'].'项 ';
			}
			if($value['maxselect'])
			{
				$is_require.='至多填'.$value['maxselect'].'项';
			}
			$is_require.=')';
		}
		
		//printarray($value);
		$qustionlist ='<div id="question_'.$value['id'].'" style="margin-bottom:5px">
		<h4 class="ui-state-default ui-helper-clearfix"  style="margin:0;">
		<div style="float:left;"> <span>'.$value['id'].'. '.$value['name'].'</span><span class="notice"> '.$is_require.'</span></div>';
		
		if($widthedit)
		{
			$qustionlist .='<div style="float:right;"><span onclick="open_dialog({title:"'.__('edit').'"},\'/admin/appraises/edit/'.$value['id'].'\');">'.__('edit').'</span>
			&nbsp;<span onclick="if(confirm(\'您确定要删除\')) open_dialog({title:"'.__('delete').'"},\'/admin/appraises/trash/'.$value['id'].'\',deletequestion,{id:\''.$value['id'].'\'});">'.__('delete').'</span></div>';
		}
		//if(confirm('您确认要删除这条数据到回收站吗？')) open_dialog(this,'/admin/i18nfields/trash/509',reloadGrid);
		$qustionlist .='</h4>';
		$table_html = '';
		if(in_array($value['questiontype'],array('radio','checkbox','select')))
		{
			$options  = $this->Appraiseoption->find('all',array('conditions'=>array('qid'=>$value['id'],'deleted'=>0)));
			$table_html .= $this->__drewOptionTable($options,$value);
		}
		else
		{
			if($value['questiontype']=='textarea')
			{
				$table_html .='<tr><td><textarea name="textarea_'.$value['id'].'"  id="textarea_'.$value['id'].'" cols="60" rows="5" style="overflow-x:hidden;overflow-y:auto" ></textarea></td></tr>';
			}
			else
			{
				$table_html .='<tr><td><input type="text" name="input_'.$value['id'].'" id="input_'.$value['id'].'"/></td></tr>';
			}
			$table_html = '<table class="options"><tbody>'.$table_html.'</tbody></table>';
		}
		return $qustionlist .='
		<div class="ui-widget-content question-options"  style="margin:0;padding-left:15px;">'.$table_html.'</div>';	
		
	}
 	
	function __drewOptionTable($options,$question_info){
		$optionx = $optiony = $optionz = array();
		foreach($options as $k => $option)
		{
			if($option['Appraiseoption']['optiontype']=='x')
			{
				$optionx[] = $option['Appraiseoption'];
			}
			elseif($option['Appraiseoption']['optiontype']=='y')
			{
				$optiony[] = $option['Appraiseoption'];
			}
			elseif($option['Appraiseoption']['optiontype']=='z')
			{
				$optionz[] = $option['Appraiseoption'];
			}
		}
		if(!empty($optionz))
		{
			$tabs = '';
			$divs = '';
			foreach($optionz as $kz => $valz)
			{
				$tabs .= '<li><a href="#tab-question-'.$valz['id'].'">'.$valz['name'].'</a></li>';
				$table_html ='<table>';
				foreach($optiony as $ky => $valy)
				{
					if($ky==0)
					{
						$table_html .='<thead><th></th>';
						foreach($optionx as $kx => $valx)
						{
							$table_html .='<th>'.$valx['name'].'</th>';
						}
						$table_html .='</thead>';
					}
					
					
					$table_html .='<tr><td>'.$valy['name'].'</td>';
					$option_name = 'options['.$question_info['id'].']['.$valz['id'].'_'.$valy['id'].']';
					foreach($optionx as $kx => $valx)
					{
						$optionid= 'options_'.$question_info['id'].'_'.$valz['id'].'_'.$valy['id'].'_'.$valx['id'];
						if($question_info['questiontype']=='radio')
						{
							$table_html .='<td><input id="'.$optionid.'" type="radio" name="'.$option_name.'" value="'.$valx['id'].'" /></td>';
						}
						elseif($question_info['questiontype']=='checkbox')
						{
							$option_name = 'options['.$question_info['id'].']['.$valz['id'].'_'.$valy['id'].'_'.$valx['id'].']';
							$table_html .='<td><input id="'.$optionid.'" type="checkbox" name="'.$option_name.'" value="'.$valx['id'].'" /></td>';
						}
					}
					$table_html .='</tr>';
					
				}
				$table_html .='</table>';
				$divs .= '<div id="tab-question-'.$valz['id'].'" class="question-options" >'.$table_html.'</div>';
			}
			return $qustionlist .'<div id="tab-question-'.$question_info['id'].'" class="3d-tab-question"><ul>'.$tabs.'</ul>'.$divs.'</div>';
		}
		else if(!empty($optiony))
		{
			$table_html ='<table>';
			foreach($optiony as $ky => $valy)
			{
				if($ky==0)
				{
					$table_html .='<thead><th></th>';
					foreach($optionx as $kx => $valx)
					{
						$table_html .='<th>'.$valx['name'].'</th>';
					}
					$table_html .='</thead>';
				}
				
				$table_html .='<tr><td>'.$valy['name'].'</td>';
				$option_name = 'options['.$question_info['id'].']['.$valy['id'].']';
				foreach($optionx as $kx => $valx)
				{
					
					if($question_info['questiontype']=='radio')
					{
						$table_html .='<td><input type="radio" name="'.$option_name.'" value="'.$valx['id'].'" /></td>';
					}
					elseif($question_info['questiontype']=='checkbox')
					{
						$option_name = 'options['.$question_info['id'].']['.$valy['id'].'_'.$valx['id'].']';
						$table_html .='<td><input type="checkbox" name="'.$option_name.'" value="'.$valx['id'].'" /></td>';
					}
					//$table_html .='<td>-</td>';
				}
				$table_html .='</tr>';
			}
			$table_html .='</table>';
			return $qustionlist .'<div class="question-options" >'.$table_html.'</div>';
		}
		else
		{
			//$value['columns']=5;
			if($question_info['columns'] < 1 )
			{
				$question_info['columns']=1;
			}
			$optionname= 'options['.$question_info['id'].']';
			//print_r($options);
			$k = 0;
			foreach($options as $k => $val)
			{
				$val = $val['Appraiseoption'];
				if($question_info['questiontype']=='checkbox')
					$optionname= 'options['.$val['qid'].']['.$val['id'].']';
				$optionid= 'options_'.$val['qid'].'_'.$val['id'];
				
				if($val['withinput'])
				{
					$hasinput='<input type="text" size="20" name="'.('input_'.$val['qid'].'_'.$val['id']).'" id="'.('input_'.$val['qid'].'_'.$val['id']).'" />';
				}
				else
				{
					$hasinput='';
				}
				if( $k%$question_info['columns']==0)
				{
					$table_html .='<tr><td align="left"><input type="'.$question_info['questiontype'].'" value="1" name="'.$optionname.'" id="'.$optionid.'"/><label for="'.$optionid.'">'.$val['name'].'</label>'.$hasinput.'</td>
				';
				}
				else
				{
					
					$table_html .='<td align="left"><input type="'.$question_info['questiontype'].'" value="1" name="'.$optionname.'" id="'.$optionid.'"/><label for="'.$optionid.'">'.$val['id'].'. '.$val['name'].'</label>'.$hasinput.'</td>
					';
					
				}
				if( $k % $question_info['columns']== ($question_info['columns']-1))
				{
					$table_html .='</tr>';
				}
			}
			if( $k % $question_info['columns']!= ($question_info['columns']-1))
			{
				$table_html .='<td colspan="'.($question_info['columns'] - $k % $question_info['columns']-1).'"></td></tr>';
			}
			
			$table_html = '<div class="question-options" ><table class="options"><tbody>'.$table_html.'</tbody></table></div>';
		}
		return $table_html;
	}

}
?>