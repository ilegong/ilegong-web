<?php

class PlanHelperController extends AppController
{

    var $name = 'PlanHelper';

    var $uses = array('User', 'Order', 'Cart', 'Weshare');

    public function create_proxy()
    {
        $name = $this->getXing().$this->getMing();
        $proxy = array('username'=>$name, 'password'=>'', 'nickname'=> '', 'avatar'=>'', status=>2, 'created'=>'', 'updated'=> '');
        $this->User->save($proxy);

        $this->log('create a proxy '.json_encode($name) .' with id: '. $this->User->getLastInsertID(), LOG_DEBUG);

        echo json_encode(array('resulit' => 'ok'));
    }

    public function admin_orders()
    {
        $this->autoRender = false;

        $order_count = $_REQUEST['order_count'];
        $user_ids = explode(',', $_REQUEST['user_ids']);

        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            )
        ));
        if (empty($users)) {
            echo json_encode(array('result' => false, 'reason' => 'please provide at least 1 user'));
            return;
        }
        foreach ($users as $user) {
            if (!$this->_is_user_valid($user)) {
                echo json_encode(array('result' => false, 'reason' => 'invalid user ' . $user['User']['id']));
                return;
            }
        }

        $pys_products = $this->Product->find('all', array(
            'conditions' => array(
                'brand_id' => PYS_BRAND_ID,
                'published' => PUBLISH_YES
            )
        ));
        $pys_products = Hash::combine($pys_products, '{n}.Product.id', '{n}');

        $this->loadModel('ProductSpecGroup');
        $product_spec_groups = $this->ProductSpecGroup->find('all', array(
            'conditions' => array(
                'product_id' => Hash::extract($pys_products, '{n}.Product.id')
            )
        ));
        $product_spec_groups = Hash::extract($product_spec_groups, '{n}.ProductSpecGroup.product_id', '{n}');

        $offline_store = $this->OfflineStore->findById(54);

        $results = array();
        for ($i = 0; $i < $order_count; $i++) {
            $count = $this->_get_random_product_count();
            $products = $this->_get_random_products($pys_products, $product_spec_groups, $count);
            $user = $users[array_rand($users)];
            $order_id = $this->_try_to_create_order($i, $user, $products, $offline_store);

            if (!isset($results[$user['User']['id']])) {
                $results[$user['User']['id']] = array();
            }
            $results[$user['User']['id']][] = $order_id;
        }

        echo json_encode($results);
    }

    public function admin_order()
    {
        $this->autoRender = false;

        $user_id = $_REQUEST['user_id'];

        $user = $this->User->findById($user_id);
        if (empty($user) || !$this->_is_user_valid($user)) {
            echo json_encode(array('result' => false, 'reason' => 'user is invalid'));
            return;
        }

        $count = $this->_get_random_product_count();
        $products = $this->Product->find('all', array(
            'conditions' => array(
                'brand_id' => PYS_BRAND_ID,
                'published' => PUBLISH_YES,
                'deleted' => DELETED_NO
            )
        ));
        $product_spec_groups = $this->ProductSpecGroup->find('all', array(
            'conditions' => array(
                'product_id' => Hash::extract($products, '{n}.Product.id')
            )
        ));
        $product_spec_groups = Hash::combine($product_spec_groups, '{n}.ProductSpecGroup.product_id', '{n}');

        $products = $this->_get_random_products($products, $product_spec_groups, $count);

        $offline_store = $this->_get_random_offline_store($user_id);
        try {
            $order_id = $this->_try_to_create_order(0, $user, $products, $offline_store);
            echo json_encode(array('result' => true, "order_id" => $order_id));
        } catch (Exception $e) {
            echo json_encode(array('result' => false, 'reason' => $e->getMessage()));
        }
    }

    public function admin_order_shipped()
    {
        $this->autoRender = false;

        $order_ids = explode(",", $_REQUEST['order_ids']);
        if (empty($order_ids)) {
            echo json_encode(array('result' => false, 'reason' => 'please provide order ids'));
            return;
        }

        $orders = $this->Order->find('all', array(
            "conditions" => array(
                'id' => $order_ids
            )
        ));
        if (empty($orders)) {
            echo json_encode(array('result' => false, 'reason' => 'orders does not exist'));
            return;
        }

        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => array_unique(Hash::extract($orders, '{n}.Order.creator'))
            )
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}');

        foreach ($orders as $order) {
            if ($order['Order']['brand_id'] != 92) {
                echo json_encode(array('result' => false, 'reason' => 'order ' . $order['Order']['id'] . ' is not a pyshuo product'));
                return;
            }
            if ($order['Order']['status'] != ORDER_STATUS_PAID) {
                echo json_encode(array('result' => false, 'reason' => 'order ' . $order['Order']['id'] . ' is not in paid status'));
                return;
            }
            $user = $users[$order['Order']['creator']];
            if (!$this->_is_user_valid($user)) {
                echo json_encode(array('result' => false, 'reason' => 'invalid user id ' . $order['Order']['creator']));
                return;
            }
        }

        $users = $this->User->find('all', array(
            "conditions" => array(
                "id NOT IN (SELECT DISTINCT creator FROM cake_orders WHERE creator IS NOT NULL AND status > 0)",
                "nickname IS NOT NULL and nickname != ''",
                "mobilephone IS NOT NULL and mobilephone != ''"
            ),
            'limit' => max(count($orders) / 3, 1),
        ));

        foreach ($orders as &$order) {
            $user = $users[array_rand($users)];
            $this->Order->updateAll(array("creator" => "'" . $user['User']['id'] . "'", 'consignee_name' => "'" . $user['User']['nickname'] . "'", 'consignee_mobilephone' => "'" . $user['User']['nickname'] . "'", 'status' => "'" . ORDER_STATUS_SHIPPED . "'", 'published' => "'" . PUBLISH_NO . "'", 'flag' => "'7'"), array('id' => $order['Order']['id']));
            $this->Cart->updateAll(array('status' => "'" . ORDER_STATUS_SHIPPED . "'"), array('order_id' => $order['Order']['id']));
        }
        echo json_encode(array("order_ids" => $order_ids));
    }

    private function _try_to_create_order($index, $user, $products, $offline_store)
    {
        $send_date = date_format(get_send_date(6, "23:59:59", '2,4,6'), 'Y-m-d');
        $seconds = $index * 60 + rand(0, 59);
        $date = date('Y-m-d H:i:s', strtotime('+' . $seconds . ' seconds'));

        $order_id = $this->_insert_order($user, $products, $offline_store, $date);
        foreach ($products as &$product) {
            $this->_insert_cart($user, $product, $send_date, $order_id, $date);
        }

        return $order_id;
    }

    function _insert_order($user, $products, $offline_store, $date)
    {
        if (empty($user['User']['mobilephone'])) {
            $mobilephone = $this->_get_random_mobilephone();
            $res = $this->User->updateAll(array('mobilephone' => $mobilephone), array('User.id' => $user['User']['id']));
            if (!$res) {
                $this->log('failed to update user: ' . $this->User->validationErrors);
                throw new Exception($this->User->validationErrors);
            }
            $user['User']['mobilephone'] = $mobilephone;
        }
        $total_price = 0;
        foreach ($products as $product) {
            $price = empty($product['ProductSpecGroup']['price']) ? $products['Product']['price'] : $product['ProductSpecGroup']['price'];
            $total_price = $total_price + $price * $product['Product']['num'];
        }

        $data = array();
        $data['Order']['creator'] = $user['User']['id'];
        $data['Order']['status'] = 0;
        $data['Order']['ship_mark'] = 'ziti';
        $data['Order']['created'] = $date;
        $data['Order']['updated'] = $date;
        $data['Order']['consignee_name'] = empty($user['User']['nickname']) ? '李嘉' : $user['User']['nickname'];
        $data['Order']['consignee_mobilephone'] = $user['User']['mobilephone'];
        $data['Order']['consignee_id'] = $offline_store['OfflineStore']['id'];
        $data['Order']['consignee_address'] = $offline_store['OfflineStore']['name'];
        $data['Order']['coverimg'] = $product['Product']['coverimg'];
        $data['Order']['total_price'] = $total_price;
        $data['Order']['total_all_price'] = $total_price;
        $data['Order']['brand_id'] = $product['Product']['brand_id'];
        $data['Order']['type'] = 1;
        $data['Order']['published'] = PUBLISH_YES;

        $this->Order->id = null;
        if ($this->Order->save($data)) {
            $order_id = $this->Order->getLastInsertID();
            $this->log("plan helper create order successfully: " . $order_id);
            return $order_id;
        } else {
            $this->log($this->Order->validationErrors); //show validationErrors
            throw new Exception("plan helper create order failed");
        }
    }

    function _insert_cart($user, $product, $send_date, $order_id, $date)
    {
        $price = empty($product['ProductSpecGroup']['price']) ? $product['Product']['price'] : $product['ProductSpecGroup']['price'];

        $data = array();
        $data['Cart']['name'] = $product['Product']['name'];
        $data['Cart']['order_id'] = $order_id;
        $data['Cart']['creator'] = $user['User']['id'];
        $data['Cart']['type'] = 5;
        $data['Cart']['status'] = 0;
        $data['Cart']['product_id'] = $product['Product']['id'];
        $data['Cart']['spec_id'] = $product['ProductSpecGroup']['id'];
        $data['Cart']['coverimg'] = $product['Product']['coverimg'];
        $data['Cart']['price'] = $price * $product['Product']['num'];
        $data['Cart']['num'] = $product['Product']['num'];
        $data['Cart']['session_id'] = $this->Session->id();
        $data['Cart']['created'] = $date;
        $data['Cart']['updated'] = $date;
        $data['Cart']['modified'] = $date;
        $data['Cart']['send_date'] = $send_date;

        $this->Cart->id = null;
        if ($this->Cart->save($data)) {
            $cart_id = $this->Cart->getLastInsertID();
            $this->log("plan helper create cart successfully: " . $cart_id);
            return $cart_id;
        } else {
            $this->log($this->Cart->validationErrors); //show validationErrors
            throw new Exception("plan helper create cart failed");
        }
    }

    private function _is_user_valid($user)
    {

        return !empty($user) && $user['User']['username'];
    }

    private function _get_random_product_count()
    {
        $random_num = rand(1, 10);
        if ($random_num > 9) {
            $count = 4;
        } else if ($random_num > 7) {
            $count = 3;
        } else if ($random_num > 4) {
            $count = 2;
        } else {
            $count = 1;
        }
        return $count;
    }

    private function _get_random_products($pys_products, $product_spec_groups, $count)
    {
        $products = array();
        for ($i = 0; $i < $count; $i++) {
            $product = $pys_products[array_rand($pys_products)];
            $product_id = $product['Product']['id'];
            $spec_group = $this->_get_random_spec_group($product_spec_groups, $product_id);
            $price = empty($spec_group) ? $product['Product']['price'] : $spec_group['ProductSpecGroup']['price'];

            $products[$product_id] = $product;
            $products[$product['Product']['id']]['Product']['num'] = $this->_get_random_num($price);
            $products[$product['Product']['id']]['ProductSpecGroup'] = $spec_group['ProductSpecGroup'];
        }
        return $products;
    }

    private function _get_random_spec_group($product_spec_groups, $product_id)
    {
        $spec_groups = array_filter($product_spec_groups, function ($spec_group) use ($product_id) {
            return $spec_group['ProductSpecGroup']['product_id'] = $product_id;
        });
        $spec_groups = Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}');

        if (empty($spec_groups)) {
            return array();
        }
        return $spec_groups[array_rand($spec_groups)];
    }

    private function _get_random_offline_store($user_id)
    {
        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => array(
                'deleted' => DELETED_NO
            )
        ));
        $offline_stores = Hash::combine($offline_stores, '{n}.OfflineStore.id', '{n}');
        return $offline_stores[array_rand($offline_stores)];
    }

    private function _get_random_num($price)
    {
        if ($price < 10) {
            $rand_num = rand(3, 10);
        } else if ($price < 20) {
            $rand_num = rand(2, 10);
        } else if ($price < 30) {
            $rand_num = rand(1, 9);
        } else if ($price < 50) {
            $rand_num = rand(1, 9);
        } else if ($price < 60) {
            $rand_num = rand(1, 8);
        } else if ($price < 70) {
            $rand_num = rand(1, 7);
        } else if ($price < 80) {
            $rand_num = rand(1, 6);
        } else {
            $rand_num = rand(1, 5);
        }

        $num = 1;
        if ($rand_num > 10) {
            $num = 10;
        } else if ($rand_num > 9) {
            $num = 5;
        } else if ($rand_num > 8) {
            $num = 4;
        } else if ($rand_num > 7) {
            $num = 3;
        } else if ($rand_num > 5) {
            $num = 2;
        }
        return $num;
    }

    private function _get_random_mobilephone()
    {
        $second = array(3, 5, 8);
        return sprintf('1%d%d%04d%04d', $second[array_rand($second)], rand(0, 9), rand(0, 9999), rand(0, 9999));
    }

    private function getXing()
    {
        $arrXing = $this->getXingList();
        return $arrXing[mt_rand(0, count($arrXing))];
    }

    private function getMing()
    {
        $arrMing = $this->getMingList();
        return $arrMing[mt_rand(0, count($arrMing))];
    }

    private function getXingList()
    {
        $arrXing = array('赵', '钱', '孙', '李', '周', '吴', '郑', '王', '冯', '陈', '褚', '卫', '蒋', '沈', '韩', '杨', '朱', '秦', '尤', '许', '何', '吕', '施', '张', '孔', '曹', '严', '华', '金', '魏', '陶', '姜', '戚', '谢', '邹', '喻', '柏', '水', '窦', '章', '云', '苏', '潘', '葛', '奚', '范', '彭', '郎', '鲁', '韦', '昌', '马', '苗', '凤', '花', '方', '任', '袁', '柳', '鲍', '史', '唐', '费', '薛', '雷', '贺', '倪', '汤', '滕', '殷', '罗', '毕', '郝', '安', '常', '傅', '卞', '齐', '元', '顾', '孟', '平', '黄', '穆', '萧', '尹', '姚', '邵', '湛', '汪', '祁', '毛', '狄', '米', '伏', '成', '戴', '谈', '宋', '茅', '庞', '熊', '纪', '舒', '屈', '项', '祝', '董', '梁', '杜', '阮', '蓝', '闵', '季', '贾', '路', '娄', '江', '童', '颜', '郭', '梅', '盛', '林', '钟', '徐', '邱', '骆', '高', '夏', '蔡', '田', '樊', '胡', '凌', '霍', '虞', '万', '支', '柯', '管', '卢', '莫', '柯', '房', '裘', '缪', '解', '应', '宗', '丁', '宣', '邓', '单', '杭', '洪', '包', '诸', '左', '石', '崔', '吉', '龚', '程', '嵇', '邢', '裴', '陆', '荣', '翁', '荀', '于', '惠', '甄', '曲', '封', '储', '仲', '伊', '宁', '仇', '甘', '武', '符', '刘', '景', '詹', '龙', '叶', '幸', '司', '黎', '溥', '印', '怀', '蒲', '邰', '从', '索', '赖', '卓', '屠', '池', '乔', '胥', '闻', '莘', '党', '翟', '谭', '贡', '劳', '逄', '姬', '申', '扶', '堵', '冉', '宰', '雍', '桑', '寿', '通', '燕', '浦', '尚', '农', '温', '别', '庄', '晏', '柴', '瞿', '阎', '连', '习', '容', '向', '古', '易', '廖', '庾', '终', '步', '都', '耿', '满', '弘', '匡', '国', '文', '寇', '广', '禄', '阙', '东', '欧', '利', '师', '巩', '聂', '关', '荆', '司马', '上官', '欧阳', '夏侯', '诸葛', '闻人', '东方', '赫连', '皇甫', '尉迟', '公羊', '澹台', '公冶', '宗政', '濮阳', '淳于', '单于', '太叔', '申屠', '公孙', '仲孙', '轩辕', '令狐', '徐离', '宇文', '长孙', '慕容', '司徒', '司空');
        return $arrXing;
    }
    private function getMingList()
    {
        $arrMing = array('伟', '刚', '勇', '毅', '俊', '峰', '强', '军', '平', '保', '东', '文', '辉', '力', '明', '永', '健', '世', '广', '志', '义', '兴', '良', '海', '山', '仁', '波', '宁', '贵', '福', '生', '龙', '元', '全', '国', '胜', '学', '祥', '才', '发', '武', '新', '利', '清', '飞', '彬', '富', '顺', '信', '子', '杰', '涛', '昌', '成', '康', '星', '光', '天', '达', '安', '岩', '中', '茂', '进', '林', '有', '坚', '和', '彪', '博', '诚', '先', '敬', '震', '振', '壮', '会', '思', '群', '豪', '心', '邦', '承', '乐', '绍', '功', '松', '善', '厚', '庆', '磊', '民', '友', '裕', '河', '哲', '江', '超', '浩', '亮', '政', '谦', '亨', '奇', '固', '之', '轮', '翰', '朗', '伯', '宏', '言', '若', '鸣', '朋', '斌', '梁', '栋', '维', '启', '克', '伦', '翔', '旭', '鹏', '泽', '晨', '辰', '士', '以', '建', '家', '致', '树', '炎', '德', '行', '时', '泰', '盛', '雄', '琛', '钧', '冠', '策', '腾', '楠', '榕', '风', '航', '弘', '秀', '娟', '英', '华', '慧', '巧', '美', '娜', '静', '淑', '惠', '珠', '翠', '雅', '芝', '玉', '萍', '红', '娥', '玲', '芬', '芳', '燕', '彩', '春', '菊', '兰', '凤', '洁', '梅', '琳', '素', '云', '莲', '真', '环', '雪', '荣', '爱', '妹', '霞', '香', '月', '莺', '媛', '艳', '瑞', '凡', '佳', '嘉', '琼', '勤', '珍', '贞', '莉', '桂', '娣', '叶', '璧', '璐', '娅', '琦', '晶', '妍', '茜', '秋', '珊', '莎', '锦', '黛', '青', '倩', '婷', '姣', '婉', '娴', '瑾', '颖', '露', '瑶', '怡', '婵', '雁', '蓓', '纨', '仪', '荷', '丹', '蓉', '眉', '君', '琴', '蕊', '薇', '菁', '梦', '岚', '苑', '婕', '馨', '瑗', '琰', '韵', '融', '园', '艺', '咏', '卿', '聪', '澜', '纯', '毓', '悦', '昭', '冰', '爽', '琬', '茗', '羽', '希', '欣', '飘', '育', '滢', '馥', '筠', '柔', '竹', '霭', '凝', '晓', '欢', '霄', '枫', '芸', '菲', '寒', '伊', '亚', '宜', '可', '姬', '舒', '影', '荔', '枝', '丽', '阳', '妮', '宝', '贝', '初', '程', '梵', '罡', '恒', '鸿', '桦', '骅', '剑', '娇', '纪', '宽', '苛', '灵', '玛', '媚', '琪', '晴', '容', '睿', '烁', '堂', '唯', '威', '韦', '雯', '苇', '萱', '阅', '彦', '宇', '雨', '洋', '忠', '宗', '曼', '紫', '逸', '贤', '蝶', '菡', '绿', '蓝', '儿', '翠', '烟');
        return $arrMing;
    }
}