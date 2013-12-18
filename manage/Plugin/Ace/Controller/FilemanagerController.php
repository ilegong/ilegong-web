<?php

App::uses('Folder', 'Utility');
App::uses('File','Utility');
class FilemanagerController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Filemanager';
/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
    var $uses = array('Setting', 'User');
/**
 * Helpers used by the Controller
 *
 * @var array
 * @access public
 */
    var $helpers = array('Html', 'Form');

    var $deletablePaths = array();

    function beforeFilter() {
        parent::beforeFilter();

        $this->deletablePaths = array(
            APP.'views'.DS.'themed'.DS,
            WWW_ROOT.'css'.DS,
            WWW_ROOT.'img'.DS,
            WWW_ROOT.'js'.DS,
            WWW_ROOT.'themed'.DS,
        );
        $this->set('deletablePaths', $this->deletablePaths);
        
    }
    
    protected function _isDeletable($path) {
    	$path = realpath($path);
    	$regex = array();
    	for ($i = 0, $ii = count($this->deletablePaths); $i < $ii; $i++) {
    		$regex[] = '(^' . preg_quote(realpath($this->deletablePaths[$i]), '/') . ')';
    	}
    	$regex = '/' . join($regex, '|') . '/';
    	return preg_match($regex, $path) > 0;
    }
    
    protected function _isEditable($path) {
    	$path = realpath($path);
    	$regex = '/^' . preg_quote(realpath(APP), '/') . '/';
    	return preg_match($regex, $path) > 0;
    }
    
    public function admin_savefile(){
    	if (isset($this->request->query['path'])) {
    		$path = $this->request->query['path'];
    		$absolutefilepath = $path;
    	} else {
    		throw new BadRequestException(__('Error,this param path is needed.'));
    	}
    	$this->file = new File($absolutefilepath, true);
    	
    	if (!empty($this->request->data) ) {
    		if ($this->file->write($this->request->data['Filemanager']['content'])) {
    			$this->Session->setFlash(__('File saved successfully'), 'default', array('class' => 'success'));
    		}
    	}
    }

    function admin_upload() {
        $this->pageTitle = __('Upload', true);

        if (isset($this->params['url']['path'])) {
            $path = $this->params['url']['path'];
        } else {
            $path = APP;
        }

        $this->set('path',$path);

        if (isset($this->data) &&
            is_uploaded_file($this->data['Filemanager']['file']['tmp_name'])) {
            $destination = $path.$this->data['Filemanager']['file']['name'];
            move_uploaded_file($this->data['Filemanager']['file']['tmp_name'], $destination);
            $this->Session->setFlash(__('File uploaded successfully.', true));
            $redirectUrl = Router::url(array('controller' => 'filemanager', 'action' => 'browse'), true) . '?path=' . urlencode($path);

            $this->redirect($redirectUrl);
        }
    }

    function admin_delete_file() {
        if (isset($this->params['url']['path'])) {
            $path = $this->params['url']['path'];
            $path = ROOT.$path;
        } else {
            throw new BadRequestException(__('Error,this param path is needed.'));
        }
        $allowed = false;
        
        foreach($this->deletablePaths as $delablePath){
        	if(strpos($path,$delablePath)===0){
        		$allowed = true;
        		if (file_exists($path) && unlink($path)) {
        			$this->Session->setFlash(__('File deleted', true));
        		} else {
        			$this->Session->setFlash(__('An error occured', true));
        		}
        	}
        }
        if(!$allowed){
        	$this->Session->setFlash(__('not allowed to delete.', true));
        }        
        $successinfo = array('success' => __('Edit success', true), 'actions' => array('OK' => 'closedialog'));
        echo json_encode($successinfo);

        exit();
    }

    function admin_delete_directory() {
        if (isset($this->params['url']['path'])) {
            $path = $this->params['url']['path'];
            $path = ROOT.$path;
        } else {
            throw new BadRequestException(__('Error,this param path is needed.'));
        }
        $allowed = false;
        
        foreach($this->deletablePaths as $delablePath){
        	if(strpos($path,$delablePath)===0){
        		$allowed = true;
	        	if (is_dir($path) && rmdir($path)) {
		            $this->Session->setFlash(__('Directory deleted', true));
		        } else {
		            $this->Session->setFlash(__('An error occured', true));
		        }
        	}
        }
        if(!$allowed){
        	$this->Session->setFlash(__('not allowed to delete.', true));
        }
        $successinfo = array('success' => __('Edit success', true), 'actions' => array('OK' => 'closedialog'));
        echo json_encode($successinfo);
        exit();
    }

    function admin_rename() {
        if (isset($this->params['url']['path'])) {
            $path = $this->params['url']['path'];
        } else {
            $this->redirect(array('controller' => 'filemanager', 'action' => 'browse'));
        }

        if (isset($this->params['url']['newpath'])) {
            // rename here
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->redirect(array('controller' => 'filemanager', 'action' => 'index'));
        }
    }

    function admin_create_directory() {
        $this->pageTitle = __('New Directory', true);

        if (isset($this->params['url']['path'])) {
            $path = $this->params['url']['path'];
        } else {
            $this->redirect(array('controller' => 'filemanager', 'action' => 'browse'));
        }

        if (!empty($this->data)) {
            $this->folder = new Folder;
            if ($this->folder->create($path . $this->data['Filemanager']['name'])) {
                $this->Session->setFlash(__('Directory created successfully.', true));
                $redirectUrl = Router::url(array('controller' => 'filemanager', 'action' => 'browse'), true) . '?path=' . urlencode($path);
                $this->redirect($redirectUrl);
            } else {
                $this->Session->setFlash(__('An error occured', true));
            }
        }

        $this->set('path',$path);
    }

    function admin_create_file() {
        $this->pageTitle = __('New File', true);

        if (isset($this->params['url']['path'])) {
            $path = $this->params['url']['path'];
        } else {
            $this->redirect(array('controller' => 'filemanager', 'action' => 'browse'));
        }

        if (!empty($this->data)) {
            //$this->file = new File;
            //if ($this->file->create($path . $this->data['Filemanager']['name'])) {
            if (touch($path . $this->data['Filemanager']['name'])) {
                $this->Session->setFlash(__('File created successfully.', true));
                $redirectUrl = Router::url(array('controller' => 'filemanager', 'action' => 'browse'), true) . '?path=' . urlencode($path);
                $this->redirect($redirectUrl);
            } else {
                $this->Session->setFlash(__('An error occured', true));
            }
        }

        $this->set('path',$path);
    }

    function admin_chmod() {

    }

}
?>