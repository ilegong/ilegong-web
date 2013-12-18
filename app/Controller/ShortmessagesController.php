<?php

class ShortmessagesController extends AppController {

    var $name = 'Shortmessages';
	
	function admin_add()
	{
		if (!empty($this->data) ) {
			if($this->data['Shortmessage']['receiver'])
			{
				$this->loadModel('Staff');
				$this->Staff->recursive = -1;
				$receiver = $this->Staff->findByName($this->data['Shortmessage']['receiver']);
				if($receiver)
				{
					$this->data['Shortmessage']['receiverid'] = $receiver['Staff']['id'];
					$this->data['Shortmessage']['name'] = 'staff'; // 记录类型是staff的短信，还是user的短信
					$this->data['Shortmessage']['msgfromid'] = $this->Auth->user('id');
					$this->data['Shortmessage']['msgfrom'] = $this->Auth->user('name');
				}
				else
				{
					echo json_encode(array('receiver'=> 'receiver not exists'));
					exit;
				}
			}
			else
			{
				echo json_encode(array('receiver'=> 'receiver not exists'));
				exit;
			}
			
			
			//print_r($this->data);exit;
		}
		parent::admin_add();
	}

}
?>