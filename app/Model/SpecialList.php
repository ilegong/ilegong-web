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
            'conditions' => array('product_id' => $pid, 'published' => PUBLISH_YES, '(show_day = "0000-00-00" or show_day = "'. date(FORMAT_DATE) .'")'),
        ));
        if (!empty($specials)) {
            $special_ids = Hash::extract($specials, '{n}.ProductSpecial.special_id');
            $specialLists = $this->find('all', array(
                'conditions' => array('id' => $special_ids,
                    '(start = null || start <= NOW())',
                    '(end = null || end > NOW())'
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

    public function find_daily_special($day = null) {
        if ($day == null) {
            $day = date(FORMAT_DATE, mktime());
        }

        $psM = ClassRegistry::init('ProductSpecial');

        $specials = $psM->find('first', array(
            'conditions' => array('show_day' => $day, 'special_id' => SPEICAL_LIST_DAILY_ID, 'published' => PUBLISH_YES),
        ));

        return $specials;
    }
}
?>