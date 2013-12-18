<?php

class Note extends AppModel {

    var $name = 'Note';
    
    var $actsAs = array('Cipher'=> array('fields'=> array('name','content') ));

}
?>