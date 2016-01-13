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
        $levels = $result[0]['src']['levels'];
        if ($levels > 0) {
            $paths = $result[0]['src']['paths'];
            $refer_share_ids = array_filter(explode(',', $paths));
            return array_values($refer_share_ids);
        }

        return array();

    }

}