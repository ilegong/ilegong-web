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
     * @param $type
     * @return mixed
     */
    public function todayAwarded($day, $type) {
        $key = $this->key_day_awarded($day, $type);
        $result = Cache::read($key);
        if (!$result) {
            $result = $this->find('count', array(
                'conditions' => array('date(finish_time) ' => $day, 'type' => $type)
            ));
            Cache::write($key, $result);
        }
        return $result;
    }

    public function userIsAwarded($uid, $type) {
        $key = $this->key_user_is_awarded($uid, $type);
        $result = Cache::read($key);
        if (!$result) {
            $result = $this->find('first', array(
                'conditions' => array('uid ' => $uid, 'type' => $type)
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
     * @param $type
     * @return string
     */
    protected function key_user_is_awarded($uid, $type) {
        return 'today_awarded_u_' . $uid . '_'.$type;
    }

    /**
     * @param $day
     * @param $type
     * @return string
     */
    protected function key_day_awarded($day, $type) {
        return 'today_awarded_' . $day.'_'.$type;
    }

    protected function clearCache() {
        Cache::delete($this->key_user_is_awarded($this->data['AwardResult']['id'], $this->data['AwardResult']['type']));
        $datetime = new DateTime($this->data['AwardResult']['finish_time']);
        $day = $datetime->format(FORMAT_DATE);
        Cache::delete($this->key_day_awarded($day, $this->data['AwardResult']['type']));
    }
}