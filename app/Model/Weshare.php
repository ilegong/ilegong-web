<?php

class Weshare extends AppModel
{
    var $name = 'Weshare';

    /**
     * @param $share_id
     * @param $uid
     * @return array
     * 获取关联的分享
     */
    function get_relate_share($share_id, $uid)
    {
        $cache_key = SHARE_REFER_SHARE_IDS_CACHE_KEY.'_'.$share_id.'_'.$uid;
        $cache_data = Cache::read($cache_key);
        if(empty($cache_data)){
            $refer_share_ids = array();
            $sql = "SELECT id, refer_share_id, levels, paths
FROM (SELECT id, refer_share_id, @le := IF(refer_share_id = 0, 0, IF(LOCATE(CONCAT('|', refer_share_id, ':'), @pathlevel) > 0, SUBSTRING_INDEX(SUBSTRING_INDEX(@pathlevel, CONCAT('|', refer_share_id, ':'), -1), '|', 1) + 1, @le + 1)) AS levels, @pathlevel := CONCAT(@pathlevel, '|', id, ':', @le, '|') AS pathlevel, @pathnodes := IF(refer_share_id = 0, ',0', CONCAT_WS(',', IF(LOCATE(CONCAT('|', refer_share_id, ':'), @pathall) > 0, SUBSTRING_INDEX(SUBSTRING_INDEX(@pathall, CONCAT('|', refer_share_id, ':'), -1), '|', 1), @pathnodes), refer_share_id)) AS paths
		, @pathall := CONCAT(@pathall, '|', id, ':', @pathnodes, '|') AS pathall
	FROM cake_weshares, (SELECT @le := 0, @pathlevel := NULL, @pathall := NULL, @pathnodes := NULL
		) vv WHERE creator=" . $uid . " and type=0
	ORDER BY refer_share_id, id
	) src
WHERE id = " . $share_id . "
ORDER BY id";
            $weshareM = ClassRegistry::init('Weshare');
            $result = $weshareM->query($sql);
            $this->log('query refer share ids ' . json_encode($result));
            $levels = $result[0]['src']['levels'];
            if ($levels > 0) {
                $paths = $result[0]['src']['paths'];
                $refer_share_ids = array_filter(explode(',', $paths));
                $refer_share_ids =  array_values($refer_share_ids);
            }
            Cache::write($cache_key, json_encode($refer_share_ids));
            return $refer_share_ids;
        }
        return json_decode($cache_data, true);
    }

}