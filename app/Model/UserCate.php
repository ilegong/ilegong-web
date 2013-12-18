<?php

class UserCate extends AppModel {

    var $name = 'UserCate';
    
    var $actsAs = array('Tree'=> array('left'=>'left','right'=>'right') );

}
?>