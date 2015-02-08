<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 2/8/15
 * Time: 8:44 PM
 */

class seckilling extends AppModel {

    /**
     * @param $uid
     * @param $type
     * @param $sub_type
     * @param $time int unix timestamp
     * @return bool|array false on fails, the updated record on success
     */
    public function seckilling($uid, $type, $sub_type, $time) {

        $conds = array('uid' => 0,
            'type' => $type,
            'sub_type' => $sub_type,
            'deleted' => DELETED_NO,
            'published' => PUBLISH_YES,
            'valid_begin<=' => $time,
            'valid_end>=' => $time
        );
        $found = $this->find('first', array(
            'conditions' => $conds,
        ));
        if (!empty($found)) {
            $id = $found['Seckilling']['id'];
            if ($this->updateAll(array('uid' => $uid, 'occupied_at' => '\''.date(FORMAT_DATETIME).'\''), array_merge($conds, array('id' => $id,)))) {
                $affected = $this->getAffectedRows();
                if ($affected > 0) {
                    return $this->findById($id);
                }
            }
        }

        return false;
    }

} 