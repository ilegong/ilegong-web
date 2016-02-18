<?php 

class ProductRecomComponent extends Component {
	
	/* component configuration */
	var $name = 'ProductRecomComponent';
	var $params = array();
	/**
	 * Contructor function
	 * @param Object &$controller pointer to calling controller
	 */
	function startup(&$controller) {
		$this->params = $controller->params;
	}

    public function recommend($pid) {
        $MAX_SAME_KIND = 2;
        $MAX_RECOMMEND = 4;

        $items = array();
        $productModel = ClassRegistry::init('Product');
        if ($pid == 0) {
            $recomm_hottest = $this->rand_recommend_pids(RECOMMEND_TAG_ID, $MAX_RECOMMEND * 2, $productModel, 0);
            $this->fill_recomm_items($recomm_hottest, $items, $MAX_RECOMMEND, $productModel);
        } else {

            $tag = $productModel->query("select tag_id from cake_product_product_tags where product_id = $pid limit 1");
            $recomm_same_kind = empty($tag) ? array() : $this->rand_recommend_pids($tag[0]['cake_product_product_tags']['tag_id'], $MAX_SAME_KIND * 2, $productModel, $pid);
            $recomm_hottest = $this->rand_recommend_pids(RECOMMEND_TAG_ID, ($MAX_RECOMMEND - $MAX_SAME_KIND) * 2, $productModel, $pid);

            $this->fill_recomm_items($recomm_same_kind, $items, $MAX_SAME_KIND, $productModel);
            $this->fill_recomm_items($recomm_hottest, $items, $MAX_RECOMMEND, $productModel);
        }
        return $items;
    }

    /**
     * @param $tag
     * @param $max
     * @param $productModel
     * @param $pid
     * @return mixed array keyed with the product id
     */
    private function rand_recommend_pids($tag, $max, &$productModel, $pid) {
        $recommend = array();
        if (!empty($tag) && $max > 0) {
            $pid_candidates = $productModel->query('select distinct product_id from cake_product_product_tags where tag_id = ' . $tag . ' and product_id != ' . $pid);
            $candidates_len = count($pid_candidates);

            $randTimes = 0;
            while (count($recommend) < min($max, $candidates_len)) {
                $idx = rand(0, $candidates_len - 1);
                $id = $pid_candidates [$idx]['cake_product_product_tags']['product_id'];
                $randTimes++;
                $recommend[$id] = null;
                if ($randTimes > 100) {
                    break;
                }
            }
            $this->log("random times for $tag: ". $randTimes, LOG_DEBUG);
        }
        return $recommend;
    }

    /**
     * @param $recomm_ids array indexed with product id
     * @param $items
     * @param $max_item_counts
     * @param $productModel
     */
    private function fill_recomm_items($recomm_ids, &$items, $max_item_counts, &$productModel) {
        $products = $productModel->find_published_products_by_ids(array_keys($recomm_ids));
        if (!empty($products)) {
            foreach ($recomm_ids as $pid => $val) {
                $item = $products[$pid];
                if (!empty($item)) {
                    $items[$pid] = $item;
                    if (count($items) >= $max_item_counts) {
                        break;
                    }
                }
            }
        }
    }

}
?>