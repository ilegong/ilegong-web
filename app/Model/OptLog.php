<?php

/**
 * opt log modal
 */
class OptLog extends AppModel {

    public function __get($key)
    {
        if ($key == 'uid') {
            return $_SESSION['Auth']['User']['id'];
        }
        return parent::__get($key);
    }

    /**
     * @param $format_date
     * @param $limit
     * @param $type
     * @return array
     */
    public function fetch_by_time_limit_type($format_date, $limit, $type) {
        $fetch_option = array(
            'conditions' => array(
                'created < ' => $format_date,
                'deleted' => DELETED_NO
            ),
            'limit' => $limit,
            'order' => array('created DESC'),
            // 'group' => array('obj_id'),
        );
        if ($type != 0) {
            $fetch_option['conditions']['obj_type'] = $type;
        }
        $opt_logs = $this->find('all', $fetch_option);
        return $opt_logs;
    }

    /**
     * @param $format_date
     * @param $limit
     * @param $type
     * @return array
     */
    public function new_fetch_by_time_limit_type($format_date, $limit, $type, $followed = false) {

        $conditions = [
            'created < ' => $format_date,
            'deleted' => DELETED_NO
        ];
        if ($followed && $this->uid) {
            // 当用户选定只看fllowed的团长的东西时, 我们需要做一些过滤.
            $userRelationM = ClassRegistry::init('UserRelation');
            $info = $userRelationM->find('all', [
                'conditions' => [
                    'follow_id' => $this->uid,
                    'deleted' => DELETED_NO,
                ],
                'fields' => ['user_id'],
            ]);
            $info = Hash::extract($info, '{n}.UserRelation.user_id');
            if (!$info) {
                return false;
            }
            // 先找到自己关注的团长, 在进行查询.
            $conditions['obj_creator'] = $info;
        }

        $fetch_option = array(
            'conditions' => $conditions,
            // 'fields' => ['id', 'obj_id', 'created'],
            'limit' => $limit,
            'order' => array('created DESC'),
            'group' => array('obj_id'),
        );
        if ($type != 0) {
            $fetch_option['conditions']['obj_type'] = $type;
        }
        $opt_logs = $this->find('all', $fetch_option);
        return $opt_logs;
    }

    /**
     * @param $time
     * @param int $type
     * @return int
     * get not read count by limit time
     */
    public function fetch_count_by_time($time, $type = 0) {
        $fetch_option = array(
            'conditions' => array(
                'created > ' => $time,
                'deleted' => DELETED_NO
            ),
            'order' => array('created DESC')
        );
        if ($type != 0) {
            if ($type != 0) {
                $fetch_option['conditions']['obj_type'] = $type;
            }
        }
        $no_read_count = $this->find('count', $fetch_option);
        return $no_read_count;
    }

    /**
     * @return int|mixed
     */
    public function get_oldest_update_time() {
        $timeStamp = Cache::read(OPT_LOG_OLDEST_TIME_CACHE_KEY, 0);
        if ($timeStamp == 0) {
            $oldestLog = $this->find('first', array(
                'order' => array('created ASC')
            ));
            $oldestDate = $oldestLog['OptLog']['created'];
            $timeStamp = strtotime($oldestDate);
            Cache::write(OPT_LOG_OLDEST_TIME_CACHE_KEY, $timeStamp);
        }
        return $timeStamp;
    }

    /**
     * @return int|mixed
     */
    public function get_last_update_time() {
        $timeStamp = Cache::read(OPT_LOG_LAST_TIME_CACHE_KEY, 0);
        if ($timeStamp == 0) {
            $lastLog = $this->find('first', array(
                'order' => array('created DESC')
            ));
            $lastDate = $lastLog['OptLog']['created'];
            $timeStamp = strtotime($lastDate);
            Cache::write(OPT_LOG_LAST_TIME_CACHE_KEY, $timeStamp);
        }
        return $timeStamp;
    }


}