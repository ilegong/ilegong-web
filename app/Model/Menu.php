<?php

class Menu extends AppModel {

    var $name = 'Menu';
    
    var $actsAs = array('Tree'=> array('left'=>'left','right'=>'right') );

}
?>