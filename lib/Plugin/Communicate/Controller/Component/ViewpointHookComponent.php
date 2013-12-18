<?php
class ViewpointHookComponent extends Component {

    function onActivate($controller) {
       
    }
/**
 * Called after deactivating the hook in ExtensionsHooksController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
    function onDeactivate($controller) {
    }
/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param object $controller Controller with components to startup
 * @return void
 */
    function startup($controller) {

    }
    
    function viewPoint($controller,$modelName,$dataid)
    {
		$controller->loadModel('Viewpoint');
//		echo $this->current_data_id;
		$Viewpoints = $controller->Viewpoint->find('all',array(
				'conditions'=>array(
					'Viewpoint.model'=> $modelName,
					'Viewpoint.data_id'=> $dataid
				),
				'fields' =>array('Viewpoint.*'),
				'limit'=>12,
				'order'=>'support_nums desc'
			));
//		print_r($Viewpoints);
		$controller->set('Viewpoints', $Viewpoints);
		$controller->set('points_model', $modelName);
		$controller->set('points_dataid', $dataid);
    }
    
	function nextItems($controller,$modelName,$dataid)
    {
		$controller->loadModel('Viewpoint');
		//select top 1 * from table_name where id < "&id&" order by id desc
//select top 1 * from table_name where id > "&id&" order by id asc
//		echo $this->current_data_id;
		$next_item = $controller->{$modelName}->find('first',array(
				'conditions'=>array(
					$modelName.'.id >'=> $dataid,
					$modelName.'.deleted'=> 0,
					$modelName.'.published'=> 1,
				),
				'limit'=>1,
				'order'=> $modelName.'.'.'id asc'
			));
		$last_item = $controller->{$modelName}->find('first',array(
				'conditions'=>array(
					$modelName.'.id <'=> $dataid,
					$modelName.'.deleted'=> 0,
					$modelName.'.published'=> 1,
				),
				'limit'=>1,
				'order'=>$modelName.'.'.'id desc'
			));
		$controller->set('next_item', $next_item);
		$controller->set('last_item', $last_item);
		$controller->set('next_item_model', $modelName);
		$controller->set('next_item_controller', Inflector::tableize($modelName));
    }
   
}
?>