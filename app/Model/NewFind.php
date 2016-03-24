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
        $data = Hash::extract($data, '{n}.NewFind');


        if (!$data) {
            // 当后台没有数据的时候, 初始化一个, 方便前台显示
            $data = [
                [
                    'id' => 1,
                    'banner' => '',
                    'link' => '',
                    'title' => '',
                    'description' => '',
                    'type' => CAROUSEL_TYPE,
                    'sort' => 0,
                    'deleted' => 0,
                ],
            ];
        }

        return $data;
    }

    public function save_all_carousel($data)
    {
        foreach ($data as $item) {
            if ($this->validate($item)) {
                $item['type'] = CAROUSEL_TYPE;
                $this->save_item($item);
            }
        }
    }

    private function validate($item)
    {
        extract($item);
        return !empty($banner) && !empty($link) && !empty($sort);
    }

    public function save_item($item)
    {
        if (!isset($item['id'])) {
            // 新加
            $item['id'] = null;
        }

        $this->save($item);
    }
}
