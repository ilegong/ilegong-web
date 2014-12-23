<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/18/14
 * Time: 6:57 PM
 */

class ShipSetting extends AppModel {

    public function find_by_pids($pids, $provinceId) {
        $shipSettings = $this->find('all', array(
            'conditions' => array('product_id' => $pids)
        ));
        if (!empty($provinceId)) {
            //filter by provinceId
        }
        return $shipSettings;
    }
}