<?php

class OaHookComponent extends Component {
	
	/**
	 * 后台列表中数据处理钩子
	 * @param string $modelClass 模块
	 * @param array $item 数据
	 */
	public function gridList($modelClass,$item){
		
		if($modelClass=='Flowstep'){ //操作流步骤时，重设编辑按钮
			$item['actions'] = '';
			$flowstep_id = $_GET['flowstep'];
			// 工作流的操作按钮
			if (isset($step_info['Flowstep']['allowactions'])) {
				$allow_actions = explode(',', $step_info['Flowstep']['allowactions']);
			
				if (!empty($item[$modelClass]['deleted'])) {
					if (in_array('restore', $allow_actions)) {
						$actions .= '<li class="ui-state-default">
						<a href="#" href="'.Router::url('/admin/flowsteps/datarestore/' . $flowstep_id . '/' . $item[$modelClass]['id']).'" data-callback="reloadGrid" title="' . __('Restore') . '"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span></li>';
					}
					if (in_array('delete', $allow_actions)) {
						$actions .= '<li class="ui-state-default" onclick="if(confirm(\'' . __('Are you sure to delete', true) . '\')) open_dialog({title:\'' . __('Delete') . '\'},\'' . Router::url('/admin/flowsteps/datadelete/' . $flowstep_id . '/' . $item[$modelClass]['id']) . '\',reloadGrid);" title="' . __('Delete') . '"><span class="ui-icon ui-icon-close"></span></li>';
					}
				} else {
					if (in_array('edit', $allow_actions)) {
						$actions = '<li class="ui-state-default" onclick="open_dialog({title:\'' . __('Edit') . '\'},\'' . Router::url('/admin/flowsteps/dataedit/' . $flowstep_id . '/' . $item[$modelClass]['id']) . '\',reloadGrid);" title="' . __('Edit') . '"><span class="ui-icon ui-icon-pencil"></span></li>';
						// 行内编辑，保存，取消编辑
						$actions .= '<li id="edit_grid_row_' . $item[$modelClass]['id'] . '" class="ui-state-default" onclick="editGridRow(\'' . $item[$modelClass]['id'] . '\',\'' . $flowstep_id . '\');" title="' . __('Inline Edit') . '"><span class="ui-icon ui-icon-circle-arrow-w"></span></li>';
						$actions .= '<li id="save_grid_row_' . $item[$modelClass]['id'] . '" style="display:none" class="ui-state-default" onclick="SaveGridRow(\'' . $item[$modelClass]['id'] . '\');" title="' . __('Save') . '"><span class="ui-icon ui-icon-disk"></span></li>';
						$actions .= '<li id="canceledit_grid_row_' . $item[$modelClass]['id'] . '" style="display:none" class="ui-state-default" onclick="CancelEditGridRow(\'' . $item[$modelClass]['id'] . '\');" title="' . __('Cancel') . '"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span></li>';
					}
					if (in_array('trash', $allow_actions)) {
						$actions .= '<li class="ui-state-default" onclick="if(confirm(\'' . __('Are you sure to trash') . '\')) open_dialog({title:\'' . __('Trash') . '\'},\'' . Router::url('/admin/flowsteps/datatrash/' . $flowstep_id . '/' . $item[$modelClass]['id']) . '\',reloadGrid);" title="' . __('Trash') . '"><span class="ui-icon ui-icon-trash"></span></li>';
					}
				}
				if (in_array('view', $allow_actions)) {
					$actions .= '<li class="ui-state-default" onclick="open_dialog({title:\'' . __('View') . '\'},\'' . Router::url('/admin/flowsteps/dataview/' . $flowstep_id . '/' . $item[$modelClass]['id']) . '\');" title="' . __('View') . '"><span class="ui-icon ui-icon-image"></span></li>';
				}
			}
		}
	}
	
}