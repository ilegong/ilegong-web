<?php

class Misccate extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'Misccate';
    
    var $actsAs = array('Tree'=> array('left'=>'left','right'=>'right') );

}
?>