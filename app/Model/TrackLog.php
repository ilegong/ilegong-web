<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/20/14
 * Time: 5:38 PM
 */
class TrackLog extends AppModel {

    public function find_track_log($track_type, $current_uid, $friendUid) {
        return $this->find('first', array(
            'conditions' => array('type' => $track_type, 'from' => $current_uid, 'to' => $friendUid),
        ));
    }

    /**
     * @param $type
     * @param $uid
     * @return mixed
     */
    public function today_helped($type, $uid) {
        $datetime = new DateTime();
        $day = $datetime->format(FORMAT_DATE);
        $key = $this->key_today_helped($day, $type, $uid);
        $result = Cache::read($key);
        if (!$result) {
            $result = $this->find('count', array(
                'conditions' => array('date(award_time) ' => $day, 'type' => $type, 'from' => $uid)
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
     * @param $day
     * @param $type
     * @param $uid
     * @return string
     */
    protected function key_today_helped($day, $type, $uid) {
        return 'helped_' . $day.'_'.$type.'_'.$uid;
    }

    protected function clearCache() {
        $datetime = new DateTime($this->data['TrackLog']['award_time']);
        $day = $datetime->format(FORMAT_DATE);
        Cache::delete($this->key_today_helped($day, $this->data['TrackLog']['type'], $this->data['TrackLog']['from']));
    }
}