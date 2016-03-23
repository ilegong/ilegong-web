<?php
define(CAROUSEL_TYPE, 1);
define(TOP_RANK_TYPE, 2);
/**
 * new find and its manage modal
 */
class NewFind extends AppModel {

    public function get_all_carousel()
    {
        $data = $this->find('all', [
            'conditions' => [
                'type' => CAROUSEL_TYPE,
                'deleted' => DELETED_NO
            ],
            'order' => 'sort ASC'
        ]);

        if (!$data) {
            // 当后台没有数据的时候, 初始化一个, 方便前台显示
            $data = [
                'Carousel' => [
                    [
                        'id' => 1,
                        'banner' => '',
                        'link' => '',
                        'type' => CAROUSEL_TYPE,
                        'sort' => 0,
                        'deleted' => 0,
                    ],
                ]
            ];
        }

        return $data;
    }

    public function save_all_carousel($data)
    {
        print_r($data);
    }
}
