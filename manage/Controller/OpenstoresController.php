<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/12/6
 * Time: 下午1:24
 */

class OpenstoresController extends AppController{
    var $name = 'Openstores';
    protected function _list() {
        $this->autoRender = false;
        $modelClass = $this->modelClass;
        $control_name = Inflector::tableize($modelClass);
        $page = $_GET['page']; // get the requested page
        $limit = $_GET['rows']; // get how many rows we want to have into the grid
        $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
        $sord = $_GET['sord']; // get the direction
        $options = $_GET['q'];
        $status = intval($options);
        if(!$sidx) $sidx =1;
        $count = $this->{$modelClass}->find('count', array('conditions'=> array('status'=>$status)));
        if( $count >0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) $page=$total_pages;

        $results = $this->{$modelClass}->find('all', array(
            'conditions'=>array('status' => $status
            ),
            'fields' => array('id', 'creator', 'store_name', 'contact_name', 'mobile', 'qq', 'person_id', 'pattern','created', 'updated'),
            'order' => $sidx.' ' .$sord,
            'limit' => $limit,
            'page' => $page
        ));
        $responce = new stdClass();
        $responce->page = $page;
        $responce->total = $total_pages;
        $responce->records = $count;
        $i=0;
        foreach($results as $item){
            /*******重要： action的li之间要一个紧连着一个 ，不要有换行或空格，否则会引起格式错乱。****** */
            $actions = '';
            if($status == 2){
                $actions .= '<li class="ui-state-default grid-row-edit"><a title="通过" href="' . Router::url(array('controller' => $control_name, 'action' => 'add',  'admin' => true, $item[$modelClass]['id'])) . '"><span class="glyphicon glyphicon-ok"></span></a></li>';
                $actions .= '<li class="ui-state-default grid-row-delete"><a title="拒绝" href="' . Router::url(array('controller' => $control_name, 'action' => 'reject',  'admin' => true, $item[$modelClass]['id'])) . '"><span class="glyphicon glyphicon-remove "></span></a></li>';

            }
            $actions .= '<li class="ui-state-default grid-row-edit"><a href="' . Router::url(array('controller' => $control_name, 'action' => 'view', 'admin' => true, $item[$modelClass]['id'])) . '" title="查看"><span class="glyphicon glyphicon-file"></span></a></li>';
            $actions .= $this->Hook->call('gridDataAction', array($modelClass, $item[$modelClass]));
            $item[$modelClass]['actions'] = $actions;


            $params = array($modelClass, &$item[$modelClass]);
            $this->Hook->call('gridList', $params);
            $item[$modelClass]['actions'] = '<ul class="ui-grid-actions">' . $item[$modelClass]['actions'] . '</ul>';

            $responce->rows[$i]['id']=$item[$modelClass]['id'];
            if($item[$modelClass]['pattern'] == '0'){
                $item[$modelClass]['pattern'] = '个人';
            }else{
                $item[$modelClass]['pattern'] = '企业';
            }
            $responce->rows[$i]['cell']= $item[$modelClass];
            //$responce->rows[$i]['cell']['person_id_pic']= '<img src="' . $responce->rows[$i]['cell']['person_id_pic'] . '" width="80"/>';
            $i++;
        }
        echo json_encode($responce);
    }
    public function admin_list(){
        if($this->request->params['ext']=='json'){
            $this->_list();
            return;
        }
    }

    private function __loadFormStore($id = null){
        $modelClass = $this->modelClass;
        $result = $this->{$modelClass}->find('first', array('conditions' => array('id' => $id), 'fields' => array('store_name', 'creator')));
        $this->loadModel('Brand');
        if($this->Brand->hasany(array('Brand.name' => $result[$modelClass]['store_name']))){
            $this->set('name_repeat', true);
        }
        $result[$modelClass]['slug'] = generate_slug($result[$modelClass]['store_name']);
        if($this->Brand->hasany(array('Brand.slug' => $result[$modelClass]['slug']))){
            $this->set('slug_repeat', true);
        }
        $this->request->data = $result;
        $this->set('id', $id);
    }
    public function admin_add($id = null) {
        $modelClass = $this->modelClass;
        $this->pageTitle = __("Add " . $this->modelClass, true);
        if (!empty($_POST)) {
            $this->autoRender = false;
            $brand= array();
            $brand['Brand']['name'] = $this->data[$modelClass]['store_name'];
            $brand['Brand']['slug'] = $this->data[$modelClass]['slug'];
            $brand['Brand']['creator'] = $this->data[$modelClass]['creator'];
            $brand['Brand']['cate_id'] = '121';
            $brand['Brand']['published'] = $this->data[$modelClass]['published'];
            $this->data = $brand;
            $this->loadModel('Brand');
            $this->loadModel('User');
            if($this->Brand->hasany(array('Brand.slug' => $brand['Brand']['slug']))){
                $successinfo = array(
                    'success' => __('数据冲突，链接重复'),
                    );
                echo json_encode($successinfo);
            }else{
                if($this->Brand->save($this->data)){
                    $this->{$modelClass}->updateAll(array('status' => '4'), array('id' => $id));
                    $this->User->updateAll(array('is_business' => 1), array('User.id' => $brand['Brand']['creator']));
                    $successinfo = array(
                        'success' => __('Add success'),
                        'actions' => array(
                            'nexturl' => Router::url(array('action'=>'verify'))
                        ));
                    echo json_encode($successinfo);
                }
            }
        } else {
            // 无提交值，生成表单。加载选项
            $this->__loadFormStore($id);
        }
    }
    public function admin_view($id = null){
        $modelClass = $this->modelClass;
        $this->pageTitle = __("查看 " . $this->modelClass, true);
        $result = $this->{$modelClass}->find('first', array(
            'conditions' => array('id' => $id),
                'fields' => array('store_name', 'pattern', 'person_id', 'person_name', 'person_id_pic', 'workplace', 'business_licence', 'food_licence','id_front', 'id_back', 'reason'),
            )
        );
        if(!empty($result[$modelClass]['reason'])){
            $this->set('reason_show', true);
            $this->set('reason', $result[$modelClass]['reason']);
        }
        if($result[$modelClass]['pattern'] == '0'){
            $this->set('person_id', $result[$modelClass]['person_id']);
            $this->set('store_name', $result[$modelClass]['store_name']);
            $this->set('person_name', $result[$modelClass]['person_name']);
            $this->set('person_id_pic', $result[$modelClass]['person_id_pic']);
            $this->set('workplace', $result[$modelClass]['workplace']);
            $this->set('person', true);
        }else if($result[$modelClass]['pattern'] == '1'){
            $this->set('business_licence', $result[$modelClass]['business_licence']);
            $this->set('store_name', $result[$modelClass]['store_name']);
            $this->set('food_licence', $result[$modelClass]['food_licence']);
            $this->set('id_front', $result[$modelClass]['id_front']);
            $this->set('id_back', $result[$modelClass]['id_back']);
            $this->set('person', false);
        }

    }
    public function admin_reject($id = null){
        $this->set('id', $id);
        if (!empty($_POST)) {
            $this->autoRender = false;
            $modelClass = $this->modelClass;
            $reason = $_POST['remark'];
            $is_send = $_POST['is_send'];
            if($this->{$modelClass}->updateAll(array('reason' => '\''.$reason.'\'', 'status' => 3), array('id' => $id))){
                $successinfo = array(
                    'success' => __('审核未通过已确认'),
                    'actions' => array(
                        'nexturl' => Router::url(array('action'=>'list'))
                    ));
                if($is_send == 1){
                    $temp = $this->{$modelClass}->find('first',array('conditions' =>$id,'fields' => 'mobile'));
                    $mobilephone = $temp[$modelClass]['mobile'];
                    $this->message_send($reason, $mobilephone);
                }
                echo json_encode($successinfo);
            };

        }
    }
    private function message_send($message = null, $mobilephone = null){
        if(!empty($message)){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, 'api:key-fdb14217a00065ca1a47b8fcb597de0d');
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobilephone, 'message' => $message.'【朋友说】'));
            $res = curl_exec($ch);
            curl_close($ch);
        }
    }
}