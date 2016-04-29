<?php

class Weshare extends AppModel
{
    var $name = 'Weshare';

    /**
     * @param $share_id
     * @param $uid
     * @return array
     * 获取关联的分享id
     */
    function get_relate_share($share_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $share =$weshareM->find('first', array(
            'fields' => array('Weshare.id', 'Weshare.root_share_id', 'Weshare.creator'),
            'conditions' => [
                'Weshare.id' => $share_id
            ]
        ));

        $uid = $share['Weshare']['creator'];
        $cache_key = SHARE_REFER_SHARE_IDS_CACHE_KEY.'_'.$share_id.'_'.$uid;
        $cache_data = Cache::read($cache_key);
        if(empty($cache_data)){
            $root_share_id = $share['Weshare']['root_share_id'] > 0 ? $share['Weshare']['root_share_id'] : $share['Weshare']['id'];
            $related_share_ids =$weshareM->find('all', array(
                'fields' => array('Weshare.id'),
                'conditions' => [
                    'OR'=>['Weshare.root_share_id' => $root_share_id, 'Weshare.id'=>$root_share_id],
                    'Weshare.creator' => $share['Weshare']['creator'],
                ]
            ));
            $related_share_ids = Hash::extract($related_share_ids, '{n}.Weshare.id');
            Cache::write($cache_key, json_encode($related_share_ids));
            return $related_share_ids;
        }
        return json_decode($cache_data, true);
    }


    function get_root_share_id($share_id){
        $sql = "SELECT T2.id
FROM (
	SELECT
		@r AS _id,
		(SELECT @r := refer_share_id FROM cake_weshares WHERE id = _id) AS refer_share_id,
		@l := @l + 1 AS lvl
	FROM
		(SELECT @r := ".$share_id.", @l := 0) vars,
		cake_weshares m
	WHERE @r <> 0) T1
JOIN cake_weshares T2
ON T1._id = T2.id WHERE T2.type=0 AND T2.refer_share_id=0";
        $weshareM = ClassRegistry::init('Weshare');
        $result = $weshareM->query($sql);
        $root_share_id = Hash::extract($result, '{n}.T2.id');
        return $root_share_id[0];
    }

}