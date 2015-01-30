<?php

class Comment extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'Comment';
/**
 * Behaviors used by the Model
 *
 * @var array
 * @access public
 */
    var $actsAs = array(
        'Tree',
    );
/**
 * Validation
 *
 * @var array
 * @access public
 */
    /**
    var $validate = array(
        'body' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
        'name' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
        'email' => array(
            'rule' => 'email',
            'required' => true,
            'message' => 'Please enter a valid email address.',
        ),
    );
 */


    /**
     * @param $total_price
     * @param $commentId_pids_map
     * @return array the score value and product ids with extra award ids
     */
    public function estimate_score_value($total_price, $commentId_pids_map) {
        $score = $this->base_comment_score($total_price);

        $extra_award_ids = array();
        foreach ($commentId_pids_map as $commentId => $data_id) {
            $commentsByProductId = $this->find('all', array(
                'conditions' => array(
                    'data_id' => $data_id,
                    'status' => COMMENT_SHOW_STATUS,
                ),
                'order' => array(
                    'created asc'
                ),
                'fields' => array('id'),
                'limit' => COMMENT_EXTRA_LIMIT
            ));
            $commentIds = Hash::extract($commentsByProductId, '{n}.Comment.id');
            if (in_array($commentId, $commentIds)) {
                $score += COMMENT_EXTRA_SCORE;
                $extra_award_ids[] = $data_id;
            }
        }
        return array($score, $extra_award_ids);
    }

    /**
     * @param $product_ids array
     * @return array  product ids could be extra awarded
     */
    public function comment_could_extra_award($product_ids) {
        $could_add = array();
        foreach ($product_ids as $data_id) {
            $count = $this->find('count', array(
                'conditions' => array(
                    'data_id' => $data_id,
                    'status' => COMMENT_SHOW_STATUS,
                ),
            ));
            if ($count < COMMENT_EXTRA_LIMIT) {
                $could_add[] = $data_id;
            }
        }
        return $could_add;
    }


    /**
     * @param $total_price
     * @return float|int
     */
    public function base_comment_score($total_price) {
        $score = 0;
        if ($total_price > COMMENT_AWARD_BASE_PRICE) {
            $score += floor($total_price);
            return $score;
        } else {
            $score += COMMENT_AWARD_SCORE;
            return $score;
        }
    }
}
?>