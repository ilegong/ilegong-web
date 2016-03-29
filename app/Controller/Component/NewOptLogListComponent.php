<?php

class NewOptLogListComponent extends Component {

    var $components = ['WeshareBuy'];

    public function get_all_logs($time, $limit, $type = 0, $followed = false)
    {
        $data = ClassRegistry::init('NewOptLog')->get_all_logs($time, $limit, $type, $followed);

        return $this->get_logs_fields($data);
    }

    private function get_logs_fields($data)
    {
        $ret = [];

        $uid = $_SESSION['Auth']['User']['id'];
        $my_proxys = ClassRegistry::init('User')->get_my_proxys($uid);

        foreach ($data as $v) {
            $ret[] = $this->map_fields($v, $uid, $my_proxys);
        }

        return $ret;
    }

    private function map_fields($v, $uid, $my_proxys)
    {
        $tmp = [];
        $level = $v['ProxyLevel']['data_value'];

        $tmp['share_id'] = $v['Weshare']['id'];
        $tmp['proxy_id'] = $v['Proxy']['id'];
        $tmp['proxy'] = $v['Proxy']['nickname'];
        $tmp['current_user'] = $uid;
        $tmp['check_relation'] = in_array($uid, $my_proxys);
        $tmp['avatar'] = $v['Proxy']['avatar'];
        $tmp['level'] = "L{$level}" . map_user_level($level);
        $tmp['title'] = $v['Weshare']['title'];

        $description = str_replace('<br />', '', $v['Weshare']['description']);
        if (mb_strlen($description) > 110) {
            $tmp['description'] = mb_substr($description, 0, 110) . "...";
            $tmp['description_more'] = true;
        } else {
            $tmp['description'] = $description;
            $tmp['description_more'] = false;
        }

        $image = explode('|', $v['Weshare']['images'])[0];
        $tmp['image'] = $image ? $image : "http://static.tongshijia.com/static/img/default_product_banner.png";

        // $tmp['baoming'] = $this->WeshareBuy->get_share_and_all_refer_share_count($v['Weshare']['id'], $v['Proxy']['id']);

        $tmp['liulan'] = $v['Weshare']['view_count'];

        $tmp['customer'] = $v['Customer']['nickname'];
        $tmp['time'] = strtotime($v['NewOptLog']['time']);
        $tmp['readtime'] = map_readable_date($tmp['time']);
        //check is pintuan success type
        if ($v['NewOptLog']['data_type_tag'] == OPT_LOG_PINTUAN_SUCCESS) {
            $tmp['data_url'] = get_pintuan_opt_log_url($v['Weshare']['id']);;
        } else {
            $tmp['data_url'] = '/weshares/view/' . $v['Weshare']['id'];
        }
        $tmp['data_type_tag'] = map_opt_log_data_type($v['NewOptLog']['data_type_tag']);

        return $tmp;
    }
}
