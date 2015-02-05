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

    public function count_ge_no_spent_50($type) {
        return $this->count_ge_no_spent($type, 50);
    }

    private function count_ge_no_spent($type, $mark) {
        $key = $this->key_ge_than($type, $mark);
        $result = Cache::read($key);

        if (empty($result)) {
            $result = $this->find('count', array(
                'conditions' => array('type' => $type, 'got >=' => $mark, 'spent' => 0),
            ));
            Cache::write($key, $result);
        }
        return $result;
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

        $now2 = time();
        $r = array($now2, $result);
        $cacheJson = json_encode($r);
        $cost = $now2 - $now;
        if ($cost >= 1) {
            $this->log("too long ($cost) to loading top_list db result:" . $cacheJson);
        }
        Cache::write($key, $cacheJson);
        return $r;
    }

    public function afterDelete() {
        $this->clearCache();
    }

    public function afterSave($created, $options = array()) {
        $this->clearCache();
    }

    /**
     * @param $type
     * @return string
     */
    protected function key_top_list($type) {
        return 'top_list_'.$type;
    }

    protected function clearCache() {
        //Cache::delete($this->key_top_list($this->data['AwardResult']['type']));

        $type = $this->data['AwardResult']['type'];
        $got = $this->data['AwardResult']['got'];
        if ($got > 50) {
            Cache::delete($this->key_ge_than($type, 50));
        }
    }

    private function key_ge_than($type, $mark) {
        return '_game_ge_'.$type.'_'.$mark;
    }
} 