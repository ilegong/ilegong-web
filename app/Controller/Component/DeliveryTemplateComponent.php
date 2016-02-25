<?php

class DeliveryTemplateComponent extends Component{

    public function calculate_ship_fee($good_num, $province_id, $weshare_id){
        $WeshareDeliveryTemplateM = ClassRegistry::init('WeshareDeliveryTemplate');
        $WeshareTemplateRegionM = ClassRegistry::init('WeshareTemplateRegion');
        $region = $WeshareTemplateRegionM->find('first', array(
            'conditions' => array(
                'province_id' => $province_id,
                'weshare_id' => $weshare_id,
            )
        ));
        if (empty($region)) {
            //default
            $defaultDeliveryTemplate = $this->get_default_delivery_template($weshare_id, $WeshareDeliveryTemplateM);
            return $this->get_ship_fee_by_template($good_num, $defaultDeliveryTemplate);
        }
        $template_id = $region['WeshareTemplateRegion']['delivery_template_id'];
        $template = $this->get_delivery_template_by_region($weshare_id, $template_id, $WeshareDeliveryTemplateM);
        return $this->get_ship_fee_by_template($good_num, $template);
    }

    private function get_ship_fee_by_template($good_num, $delivery_template){
        $start_units = $delivery_template['WeshareDeliveryTemplate']['start_units'];
        $start_fee = $delivery_template['WeshareDeliveryTemplate']['start_fee'];
        $add_units = $delivery_template['WeshareDeliveryTemplate']['add_units'];
        $add_fee = $delivery_template['WeshareDeliveryTemplate']['add_fee'];
        $gap_num = $good_num - $start_units;
        if ($gap_num <= 0) {
            return $start_fee;
        }
        return $start_fee + (ceil($gap_num / $add_units) * $add_fee);
    }

    private function get_delivery_template_by_region($weshare_id, $template_id, $deliveryTemplateM){
        $template = $deliveryTemplateM->find('first', array(
            'conditions' => array(
                'weshare_id' => $weshare_id,
                'id' => $template_id,
            )
        ));
        return $template;
    }

    private function get_default_delivery_template($weshare_id, $WeshareDeliveryTemplateM){
        $defaultDeliveryTemplate = $WeshareDeliveryTemplateM->find('first', array(
            'conditions' => array(
                'weshare_id' => $weshare_id,
                'is_default' => 1
            )
        ));
        return $defaultDeliveryTemplate;
    }

    public function save_share_default_delivery_template($weshare_id, $user_id, $ship_fee){
        $WeshareDeliveryTemplateM = ClassRegistry::init('WeshareDeliveryTemplate');
        if ($WeshareDeliveryTemplateM->hasAny(array('weshare_id' => $weshare_id, 'is_default' => 1))) {
            //update
            $WeshareDeliveryTemplateM->updateAll(array('start_fee' => $ship_fee), array('weshare_id' => $weshare_id, 'is_default' => 1));
        } else {
            //save
            $WeshareDeliveryTemplateM->save(array('user_id' => $user_id, 'weshare_id' => $weshare_id, 'start_units' => 1, 'start_fee' => $ship_fee, 'add_units' => 1, 'add_fee' => 0, 'is_default' => 1, 'created' => date('Y-m-d H:i:s')));
        }
    }

    /**
     * @param $deliveryTemplates
     * save regions
     */
    public function save_all_delivery_template($deliveryTemplates){
        $WeshareDeliveryTemplateM = ClassRegistry::init('WeshareDeliveryTemplate');
        $WeshareTemplateRegions = ClassRegistry::init('WeshareTemplateRegions');
        foreach ($deliveryTemplates as $itemTemplate) {
            //$WeshareDeliveryTemplateM->saveAll($deliveryTemplates);
            $is_default = $itemTemplate['is_default'];
            $weshare_id = $itemTemplate['weshare_id'];
            $regions = $itemTemplate['regions'];
            unset($itemTemplate['regions']);
            //process default delivery template default template only one
            if ($is_default == 1) {
                if ($WeshareDeliveryTemplateM->hasAny(array('weshare_id' => $weshare_id, 'is_default' => 1))) {
                    $old_data = $WeshareDeliveryTemplateM->find('first', array(
                        'conditions' => array(
                            'weshare_id' => $weshare_id,
                            'is_default' => 1
                        )
                    ));
                    $old_data = $old_data['WeshareDeliveryTemplate'];
                    $itemTemplate = array_merge($old_data, $itemTemplate);
                    $WeshareDeliveryTemplateM->save($itemTemplate);
                }
            } else {
                $itemTemplate = $WeshareDeliveryTemplateM->save($itemTemplate);
                foreach ($regions as &$region_item) {
                    $region_item['weshare_id'] = $itemTemplate['weshare_id'];
                    $region_item['creator'] = $itemTemplate['user_id'];
                    $region_item['delivery_template_id'] = $WeshareDeliveryTemplateM->id;
                }
                $WeshareTemplateRegions->saveAll($regions);
            }
        }
    }

}