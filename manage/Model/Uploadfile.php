<?php
class Uploadfile extends AppModel { 

    var $name = 'Uploadfile'; 

    function findByPath ($path, $name) { 
        return $this->find("name = '$name' and path = '$path'"); 
    } 

} 
?>