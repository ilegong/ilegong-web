<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 9/28/14
 * Time: 6:06 PM
 */

class WxOauth extends AppModel {
    public $useDbConfig = 'WxOauth';
    public $useTable = false;

    public function __construct($id = false, $table = null, $ds = null) {
        $this->log('WxOauth init:'. $this->useDbConfig . ', useTable=' .$this->useDbConfig.', parameters:'. $id . ','. $table . ','. $ds);
        parent::__construct($id, $table, $ds);
        $this->log('WxOauth init2:'. $this->useDbConfig . ', useTable=' .$this->useDbConfig.', parameters:'. $id . ','. $table . ','. $ds);
    }
}