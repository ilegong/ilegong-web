<?php
/**
 * User
 *
 * PHP version 5
 *
 * @category Model
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class Staff extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'Staff';
    
 
/**
 * Behaviors used by the Model
 *
 * @var array
 * @access public
 */
    var $actsAs = array(
        'Acl' => array('type' => 'requester'),
    );
/**
 * Model associations: belongsTo
 *
 * @var array
 * @access public
 */
//    var $belongsTo = array('Role');
/**
 * Validation
 *
 * @var array
 * @access public
 */
    function parentNode() {
        if (!$this->id && empty($this->data)) {
            return null;
        }
        $data = $this->data;
        if (empty($this->data)) {
            $data = $this->read();
        }
        if (!isset($data['Staff']['role_id']) || !$data['Staff']['role_id']) {
            return null;
        } else {
            return array('Role' => array('id' => $data['Staff']['role_id']));
        }
    }

    function afterSave($created) {
        if (!$created) {
            $parent = $this->parentNode();
            $parent = $this->node($parent);
            $node = $this->node();
            $aro = $node[0];
            $aro['Aro']['parent_id'] = $parent[0]['Aro']['id'];
            $this->Aro->save($aro);
        }
        //parent::afterSave($created) ;
    }


}
?>