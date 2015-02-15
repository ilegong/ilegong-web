<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 2/13/15
 * Time: 11:25 AM
 */

class ScoresController extends AppController {

    var $uses = array('Score');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->set('hideNav', true);
    }

    public function rules() {
        $this->pageTitle = '积分规则';
    }

    public function more_score() {
        $this->pageTitle = '赚积分，抵现金';

        $ranks = array();
        $this->set('ranks', $ranks);

        $uid = $this->currentUser['id'];
        $left_to_follow = (WX_STATUS_UNSUBSCRIBED == user_subscribed_pys($uid));
        $left_to_follow_score = $left_to_follow ?  50 : 0;

        $this->set('left_to_follow', $left_to_follow);
        $this->set('left_to_follow_score', $left_to_follow_score);


        $this->loadModel('Order');
        $left_to_comment = $this->Order->count_to_comments($uid);
        $score_to_comment = $left_to_comment * 100;

        $this->set('score_left_comment', $score_to_comment);
        $this->set('left_to_comment', $left_to_comment);

        $left_got = $score_to_comment + $left_to_follow_score;
        $this->set('left_got', $left_got);
    }

    public function detail() {
        $uid = $this->currentUser['id'];
        if (!empty($uid)) {
            $arr = $this->paged_details(PHP_INT_MAX, $uid);
            $this->set('scores', $arr);
        } else {
            $this->redirect('/users/login.html?referer=/scores/detail.html');
        }

        $this->pageTitle = '积分明细';
    }

    public function detail_lists($next) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $arr = empty($uid)? array('success' => false) : $this->paged_details($next, $uid);

        echo json_encode($arr);
    }

    /**
     * @param $next
     * @param $uid
     * @param int $limit
     * @return array
     */
    private function paged_details($next, $uid, $limit = 10) {
        if (empty($next)) {
            $next = PHP_INT_MAX;
        }
        $result = $this->Score->find_user_score_logs($uid, $next, $limit);
        $arr = array();
        $next = empty($result) || count($result) < $limit ? 0 : $next - 1;
        foreach ($result as $row) {
            $arr['scores'][] = array(
                'id' => $row['Score']['id'],
                'num' => $row['Score']['score'],
                'reason' => $row['Score']['desc'],
                'date' => friendlyDateFromStr($row['Score']['created']),
            );
            $next = min($next, $row['Score']['id']);
        }

        $arr['next'] = $next;
        $arr['success'] = true;
        return $arr;
    }
}