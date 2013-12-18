<?php

class AutoSign extends AppModel {

    var $name = 'AutoSign';
    
    var $actsAs = array('Cipher'=> array('fields'=> array('name','password') ));

}
?>