<?php
/**
 * Attachments Controller
 *
 * This file will take care of file uploads (with rich text editor integration).
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class AttachmentsController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Attachments';
/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
    //var $uses = array('Node');
/**
 * Helpers used by the Controller
 *
 * @var array
 * @access public
 */
    var $helpers = array('Filemanager', 'Text', 'Image');
/**
 * Node type
 *
 * If the Controller uses Node model,
 * this is, most of the time, the singular of the Controller name in lowercase.
 *
 * @var string
 * @access public
 */
    var $type = 'attachment';
/**
 * Uploads directory
 *
 * relative to the webroot.
 *
 * @var string
 * @access public
 */
    var $uploadsDir = 'uploads';

    function beforeFilter() {
        parent::beforeFilter();

        // Comment, Category, Tag not needed
        $this->Node->unbindModel(array('hasMany' => array('Comment'), 'hasAndBelongsToMany' => array('Category', 'Tag')));

        $this->Node->type = $this->type;
        $this->Node->Behaviors->attach('Tree', array('scope' => array('Node.type' => $this->type)));
        $this->set('type', $this->type);
    }

    function admin_index() {
        $this->pageTitle = __('Attachments', true);

        $this->Node->recursive = 0;
        $this->paginate['Node']['order'] = 'Node.created DESC';
        $this->set('attachments', $this->paginate());
    }

    function admin_add() {
        $this->pageTitle = __("Add Attachment", true);

        if (isset($this->params['named']['editor'])) {
            $this->layout = 'admin_full';
        }

        if (!empty($this->data)) {
            $file = $this->data['Node']['file'];
            unset($this->data['Node']['file']);

            // check if file with same path exists
            $destination = WWW_ROOT . $this->uploadsDir . DS . $file['name'];
            if (file_exists($destination)) {
                $newFileName = String::uuid() . '-' . $file['name'];
                $destination = WWW_ROOT . $this->uploadsDir . DS . $newFileName;
            } else {
                $newFileName = $file['name'];
            }

            // remove the extension for title
            if (explode('.', $file['name']) > 0) {
                $fileTitleE = explode('.', $file['name']);
                array_pop($fileTitleE);
                $fileTitle = implode('.', $fileTitleE);
            } else {
                $fileTitle = $file['name'];
            }

            $this->data['Node']['title'] = $fileTitle;
            $this->data['Node']['slug'] = $newFileName;
            $this->data['Node']['mime_type'] = $file['type'];
            //$this->data['Node']['guid'] = Router::url('/' . $this->uploadsDir . '/' . $newFileName, true);
            $this->data['Node']['path'] = '/' . $this->uploadsDir . '/' . $newFileName;

            $this->Node->create();
            if ($this->Node->save($this->data)) {
                // move the file
                move_uploaded_file($file['tmp_name'], $destination);

                $this->Session->setFlash(__('The Attachment has been saved', true));

                if (isset($this->params['named']['editor'])) {
                    $this->redirect(array('action' => 'browse'));
                } else {
                    $this->redirect(array('action'=>'index'));
                }
            } else {
                $this->Session->setFlash(__('The Attachment could not be saved. Please, try again.', true));
            }
        }
    }

    function admin_edit($id = null) {
        $this->pageTitle = __("Edit Attachment", true);

        if (!$id && empty($this->data)) {
            $this->Session->setFlash(__('Invalid Attachment', true));
            $this->redirect(array('action'=>'index'));
        }
        if (!empty($this->data)) {
            if ($this->Node->save($this->data)) {
                $this->Session->setFlash(__('The Attachment has been saved', true));
                $this->redirect(array('action'=>'index'));
            } else {
                $this->Session->setFlash(__('The Attachment could not be saved. Please, try again.', true));
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Node->read(null, $id);
        }
    }

    function admin_delete($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for Attachment', true));
            $this->redirect(array('action'=>'index'));
        }

        // get Node
        $attachment = $this->Node->find('first', array('conditions' => array('Node.id' => $id, 'Node.type' => $this->type)));
        if (isset($attachment['Node'])) {
            if ($this->Node->delete($id)) {
                // delete the file
                unlink(WWW_ROOT . $this->uploadsDir . DS . $attachment['Node']['slug']);

                $this->Session->setFlash(__('Attachment deleted', true));
                $this->redirect(array('action'=>'index'));
            }
        } else {
            $this->Session->setFlash(__('Invalid id for Attachment', true));
            $this->redirect(array('action'=>'index'));
        }
    }

    function admin_browse() {
        $this->layout = 'admin_full';
        $this->admin_index();
    }

}
?>