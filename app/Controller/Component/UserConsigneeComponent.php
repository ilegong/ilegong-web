<?php

class UserConsigneeComponent extends Component
{

    public function get_consignee_list($uid)
    {
        $key = USER_CONSIGNEES_CACHE_KEY . '_' . $uid;
        $cacheData = Cache::read($key);
        if (!empty($cacheData)) {
            return $cacheData;
        }
        $orderConsigneeM = ClassRegistry::init('OrderConsignee');
        $consignees = $orderConsigneeM->find('all', [
            'conditions' => [
                'creator' => $uid,
                'type' => TYPE_CONSIGNEES_SHARE
            ]
        ]);
        $consignees = Hash::extract($consignees, '{n}.OrderConsignee');
        $result = json_encode(['consignees' => $consignees, 'success' => true]);
        Cache::write($key, $result);
        return $result;
    }

    public function save_consignee($data, $uid)
    {
        $data['creator'] = $uid;
        $data['status'] = PUBLISH_YES;
        $data['area'] = get_address_location($data);
        $orderConsigneeM = ClassRegistry::init('OrderConsignee');
        $orderConsigneeM->updateAll(['status' => PUBLISH_NO], ['creator' => $uid, 'type' => TYPE_CONSIGNEES_SHARE]);
        $consignee = $orderConsigneeM->save($data);
        Cache::write(USER_CONSIGNEES_CACHE_KEY . '_' . $uid, '');
        return $consignee;
    }

    public function select_consignee($consignee_id, $uid)
    {
        $orderConsigneeM = ClassRegistry::init('OrderConsignee');
        $orderConsigneeM->updateAll(['status' => PUBLISH_NO], ['creator' => $uid, 'type' => TYPE_CONSIGNEES_SHARE]);
        $orderConsigneeM->update(['status' => PUBLISH_YES], ['id' => $consignee_id]);
        Cache::write(USER_CONSIGNEES_CACHE_KEY . '_' . $uid, '');
    }

}