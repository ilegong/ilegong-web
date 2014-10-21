<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/20/14
 * Time: 5:38 PM
 */
class AwardResult extends AppModel {

    /**
     * @param $day string formatted with a date in FORMAT_DATE
     * @return mixed
     */
    public function todayAwarded($day) {
        $key = $this->key_day_awarded($day);
        $result = Cache::read($key);
        if (!$result) {
            $result = $this->find('count', array(
                'conditions' => array('date(finish_time) ' => $day)
            ));
            Cache::write($key, $result);
        }
        return $result;
    }

    public function userIsAwarded($uid) {
        $key = $this->key_user_is_awarded($uid);
        $result = Cache::read($key);
        if (!$result) {
            $result = $this->find('first', array(
                'conditions' => array('uid ' => $uid)
            ));
            Cache::write($key, $result);
        }
        return $result;
    }

    public function afterDelete() {
        $this->clearCache();
    }

    public function afterSave($created, $options = array()) {
        $this->clearCache();
    }

    /**
     * @param $uid
     * @return string
     */
    protected function key_user_is_awarded($uid) {
        return 'today_awarded_u_' . $uid;
    }

    /**
     * @param $day
     * @return string
     */
    protected function key_day_awarded($day) {
        return 'today_awarded_' . $day;
    }

    protected function clearCache() {
        Cache::delete($this->key_user_is_awarded($this->data['AwardResult']['id']));
        $day = (new DateTime($this->data['AwardResult']['finish_time']))->format(FORMAT_DATE);
        Cache::delete($this->key_day_awarded($day));
    }
}