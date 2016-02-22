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
            $defaultDeliveryTemplate = $WeshareDeliveryTemplateM->find('first', array(
                'conditions' => array(
                    'weshare_id' => $weshare_id,
                    'is_default' => 1
                )
            ));
            return $this->get_ship_fee_by_default_template($good_num, $defaultDeliveryTemplate);
        }
        $region_id = $region['WeshareTemplateRegion']['delivery_template_id'];
        $template = $this->get_delivery_template_by_region($weshare_id, $region_id, $WeshareDeliveryTemplateM);
        return $this->get_ship_fee_by_template($good_num, $template);
    }

    private function get_ship_fee_by_default_template($good_num, $delivery_template){
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

    private function get_ship_fee_by_template($good_num, $delivery_template){
        return $this->get_ship_fee_by_default_template($good_num, $delivery_template);
    }

    private function get_delivery_template_by_region($weshare_id, $region_id, $deliveryTemplateM){
        $template = $deliveryTemplateM->find('first', array(
            'conditions' => array(
                'weshare_id' => $weshare_id,
                'region_id' => $region_id,
            )
        ));
        return $template;
    }

}