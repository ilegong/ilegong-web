<?php

class SpecialList extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'SpecialList';

    public function has_special_list($pid) {
        $specialM = ClassRegistry::init('ProductSpecial');
        $specials = $specialM->find('all', array(
            'conditions' => array('product_id' => $pid),
        ));
        if (!empty($specials)) {
            $special_ids = Hash::extract($specials, '{n}.ProductSpecial.product_id');
            $specialLists = $this->find('all', array(
                'conditions' => array('id' => $special_ids,
                    'start = null || start <= NOW()',
                    'end = null || end > NOW()'
                )
            ));
            $specialListsM = Hash::combine($specialLists, '{n}.SpecialList.id', '{n}.SpecialList');
            foreach($specials as &$special) {
                $list = &$specialListsM[$special['ProductSpecial']['special_id']];
                if (!empty($list)) {
                    $list['special'] = $special['ProductSpecial'];
                }
            }

            foreach($specialListsM as &$specialList) {
                if (empty($specialList['special'])) {
                    unset($specialList);
                }
            }
            return $specialListsM;
        }

        return false;
    }
}
?>