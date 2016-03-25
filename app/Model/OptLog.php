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

        $exclude_obj_ids = $this->get_exclude_obj_ids($format_date);
        $conditions = [
            'created < ' => $format_date,
            'obj_id <> ' => $exclude_obj_ids,
            'deleted' => DELETED_NO
        ];
        if ($followed && $this->uid) {
            // 当用户选定只看fllowed的团长的东西时, 我们需要做一些过滤.
            // 我决定在这里给用户显示它关注的非团长信息, 都关注了,
            // 不显示不够意思
            $info = ClassRegistry::init('User')->get_my_proxys();
            if (!$info) {
                return false;
            }
            // 先找到自己关注的团长, 在进行查询.
            $conditions['obj_creator'] = $info;
        } else {
            // 获取团长的分享
            // 这个排除策略太蠢了, 我想不出来好办法啦...
            $all_proxy = ClassRegistry::init('UserLevel')->find('all', [
                'conditions' => [
                    'data_value > ' => 0,
                ],
                'fields' => 'data_id',
            ]);

            $conditions['obj_creator'] = Hash::extract($all_proxy, '{n}.UserLevel.data_id');
        }

        $fetch_option = array(
            'conditions' => $conditions,
            'limit' => $limit,
            'order' => array('created DESC'),
        );
        if ($type != 0) {
            $fetch_option['conditions']['obj_type'] = $type;
        }
        // 先找出来符合条件的最小的distinct id
        $distinct_min_obj_id = $this->get_min_distinct_obj_id($fetch_option, $limit);
        // 再找到这个distinct obj_id对应的id, 下面取大于这个id的值,
        // 排序去重即可
        $min_id = $this->get_min_distinct_id($fetch_option, $limit, $distinct_min_obj_id);

        $fetch_option['conditions']['id >'] = $min_id;
        unset($fetch_option['limit']);

        $opt_logs = $this->find('all', $fetch_option);

        return $this->filter_data($opt_logs);
    }

    private function get_exclude_obj_ids($time)
    {
        $data = $this->find('all', [
            'conditions' => [
                'created > ' => $time,
            ],
            'fields' => 'DISTINCT obj_id',
        ]);
        $data = Hash::extract($data, '{n}.OptLog.obj_id');

        return $data;
    }

    private function filter_data($opt_logs)
    {
        $data = [];
        $aobj_id = [];
        foreach($opt_logs as $key => $val) {
            $item = $val['OptLog'];
            $obj_id = $item['obj_id'];
            if (!in_array($obj_id, $aobj_id)) {
                $aobj_id[] = $obj_id;
                $data[]['OptLog'] = $item;
            }
        }

        return $data;
    }

    private function get_min_distinct_id($option, $limit, $obj_id)
    {
        $option['conditions']['obj_id'] = $obj_id;
        $option['fields'] = 'max(id) as maxid';
        $data = $this->find('first', $option);

        return $data[0]['maxid'];
    }

    private function get_min_distinct_obj_id($option, $limit)
    {
        $option['fields'] = 'DISTINCT obj_id';
        $from = $limit - 1;
        $option['limit'] = 1;
        $option['offset'] = $from;
        $data = $this->find('first', $option);

        return $data['OptLog']['obj_id'];
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
