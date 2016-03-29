<?php
define(CAROUSEL_TYPE, 1);
define(TOP_RANK_TYPE, 2);
/**
 * new find and its manage modal
 */
class NewFind extends AppModel
{

    public function get_all_item($type)
    {
        $data = $this->find('all', [
            'conditions' => [
                'type' => $type,
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
                    'type' => $type,
                    'sort' => 0,
                    'deleted' => 0,
                ],
            ];
        }

        return $data;
    }

    public function get_all_top_rank()
    {
        return $this->get_all_item(TOP_RANK_TYPE);
    }

    public function get_all_carousel()
    {
        return $this->get_all_item(CAROUSEL_TYPE);
    }

    public function save_all_item($data, $type)
    {
        foreach ($data as $item) {
            if ($this->validate($item)) {
                $item['type'] = $type;
                $this->save_item($item);
            }
        }
    }

    public function save_all_top_rank($data)
    {
        $this->save_all_item($data, TOP_RANK_TYPE);
    }

    public function save_all_carousel($data)
    {
        $this->save_all_item($data, CAROUSEL_TYPE);
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
