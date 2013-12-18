<?php

class Misccate extends AppModel {
    var $name = 'Misccate';
    
    var $actsAs = array('Tree'=> array('left'=>'left','right'=>'right') );

}
?>