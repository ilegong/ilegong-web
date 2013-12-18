<?php
class Modelcate extends AppModel { 
       var $name = 'Modelcate';
       var $actsAs = array('Tree'=> array('left'=>'left','right'=>'right') );
} 