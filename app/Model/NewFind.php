<?php
/**
 * new find and its manage modal
 */
class NewFind extends AppModel
{
    const CAROUSEL_TYPE = 1;
    const TOP_RANK_TYPE = 2;

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


//        if (!$data) {
//            // 当后台没有数据的时候, 初始化一个, 方便前台显示
//            $data = [
//                [
//                    'id' => 1,
//                    'banner' => '',
//                    'link' => '',
//                    'title' => '',
//                    'description' => '',
//                    'type' => $type,
//                    'sort' => 0,
//                    'deleted' => 0,
//                ],
//            ];
//        }

        return array_filter($data);
    }

    public function get_all_top_rank()
    {
        return $this->get_all_item(self::TOP_RANK_TYPE);
    }

    public function get_all_carousel()
    {
        return $this->get_all_item(self::CAROUSEL_TYPE);
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
        $this->save_all_item($data, self::TOP_RANK_TYPE);
    }

    public function save_all_carousel($data)
    {
        $this->save_all_item($data, self::CAROUSEL_TYPE);
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
