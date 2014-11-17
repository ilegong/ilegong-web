<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/15/14
 * Time: 4:27 PM
 */

/**
 * Class ShareOffer
 */
class ShareOffer extends AppModel {

    public function findByBrandId($brandId) {
        return $this->_find_by_brandId($brandId, false, null);
    }

    /**
     * @param $brandId
     * @param $actionTime
     * @return mixed
     */
    public function findValidByBrandId($brandId, $actionTime) {
        if ($actionTime == null) {
            return null;
        };
        return $this->_find_by_brandId($brandId, true, $actionTime);
    }

    private function _find_by_brandId($brandId, $onlyValid , $actionTime) {

        $cond = array(
            'brand_id' => $brandId,
            'published' => 1,
            'deleted' => 0,
        );

        if ($onlyValid && $actionTime != null) {
            $cond['start <'] = $actionTime;
            $cond['end > '] = $actionTime;
        }

        return $this->find("first", array('conditions' => $cond));
    }
}