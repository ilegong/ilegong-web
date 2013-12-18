<?php

class Organization extends AppModel {

    var $name = 'Organization';
    
    var $actsAs = array('Tree'=> array('left'=>'left','right'=>'right') );

}
?>