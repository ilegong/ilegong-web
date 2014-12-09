<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/16/14
 * Time: 7:10 PM
 */

class AwardInfo extends AppModel {

    /**
     * @param $uid
     * @param $type
     * @return mixed
     */
    public function getAwardInfoByUidAndType($uid, $type) {
        $awardTimes = $this->find('first', array(
            'conditions' => array('uid' => $uid, 'type' => $type),
            'fields' => array('times', 'got', 'id', 'spent'),
        ));
        return $awardTimes ? $awardTimes['AwardInfo'] : false;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function top_list($type) {
        $key = $this->key_top_list($type);
        $result = Cache::read($key);

        $now = time();
        if (!empty($result)) {
            $decoded = json_decode($result, true);
            if ($now - $decoded[0] < 30) {
                return $decoded;
            }
        }

        $result = $this->find('list', array(
            'conditions' => array('type' => $type),
            'fields' => array('uid', 'got'),
            'order' => 'got desc',
            'limit' => 1000
        ));

        $r = array(time(), $result);
        $cacheJson = json_encode($r);
        $this->log("top_list db result:" . $cacheJson);
        Cache::write($key, $cacheJson);
        return $r;
    }

    public function afterDelete() {
    }

    public function afterSave($created, $options = array()) {
    }

    /**
     * @param $type
     * @return string
     */
    protected function key_top_list($type) {
        return 'top_list_'.$type;
    }

    protected function clearCache() {
        Cache::delete($this->key_top_list($this->data['AwardResult']['type']));
    }
} 