<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/12/2
 * Time: 下午3:08
 */
define('APPLY_STATUS_WAIT_AUTH', 1);         //待认证
define('APPLY_STATUS_WAIT_VERIFY', 2);      //待审核
define('APPLY_STATUS_NEED_FIX', 3);     //返回修改
define('APPLY_STATUS_PASSED', 4);  //已通过


class OpenstoresController extends AppController{
    var $name = 'Openstores';

    public function beforeFilter(){
        parent::beforeFilter();
        if(empty($this->currentUser['id'])){
            $this->__message('您需要先登录才能操作', '/users/login');
            exit;
        }
    }
    public function apply(){
        $this->pageTitle = '店铺申请';
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application)){
            if($application['Openstore']['status'] == APPLY_STATUS_WAIT_AUTH){
                $this->Session->setFlash('继续未完成的认证');
                $this->redirect('/openstores/base');
            }else if($application['Openstore']['status'] == APPLY_STATUS_WAIT_VERIFY || $application['Openstore']['status'] == APPLY_STATUS_NEED_FIX ){
                $this->redirect('/openstores/complete');
            }else if($application['Openstore']['status'] == APPLY_STATUS_PASSED) {
                $this->redirect('/stores/index');
            }
            else{
                $this->redirect('/');
            }
        }else{
            if(!empty($this->data)){
                $msgCode = $this->Session->read('messageCode');
                //$msgCode ="222";
                if ($msgCode) {
                    $codeLog = json_decode($msgCode, true);
                    if ($codeLog && is_array($codeLog) && $codeLog['code'] == $this->data['Openstore']['msg_code'] && (time() - $codeLog['time'] < 30 * 60)){
                    //if(1){
                        $this->data['Openstore']['creator'] = $id;
                        $this->data['Openstore']['status'] = APPLY_STATUS_WAIT_AUTH;
                        if($this->Openstore->save($this->data)){
                            $this->redirect('/openstores/base');
                        }
                    } else {
                        $this->Session->setFlash('短信验证码错误');
                    }
                }else{
                    $this->Session->setFlash('短信验证未成功，请重新获取');
                }
            }
        }
    }
    public function base(){
        $this->pageTitle = '店铺个人认证';
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application) && $application['Openstore']['status'] != APPLY_STATUS_PASSED){
            if(!empty($this->data)&& $this->request->is('post')){
                $person_pattern = 0;
                $this->data['Openstore']['pattern'] = $person_pattern;
                $this->data['Openstore']['id'] = $application['Openstore']['id'];
                $this->data['Openstore']['status'] = APPLY_STATUS_WAIT_VERIFY;
                if($this->Openstore->save($this->data)){
                    $this->redirect('/openstores/complete');
                }
            }else{
                $this->set('person_name', $application['Openstore']['person_name']);
                $this->set('person_id', $application['Openstore']['person_id']);
                $this->set('workplace', $application['Openstore']['workplace']);
                $this->set('store_name', $application['Openstore']['store_name']);
                if(!empty($application['Openstore']['person_id_pic'])){
                    $this->set('pic_show', true);
                    $this->set('person_id_pic', $application['Openstore']['person_id_pic']);
                }
            }
        }else{
            $this->__message('您需要先通过上一步验证', '/openstores/apply');
        }
    }
    public function company(){
        $this->pageTitle = '店铺企业认证';
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application) && $application['Openstore']['status'] != APPLY_STATUS_PASSED ){
            $company_pattern = 1;
            $this->data['Openstore']['pattern'] = $company_pattern;
            $this->data['Openstore']['id'] = $application['Openstore']['id'];
            $this->data['Openstore']['status'] = APPLY_STATUS_WAIT_VERIFY;
            $this->data['Openstore'] = array_filter($this->data['Openstore']);
            if(!empty($this->data)&&$this->request->is('post')){
                if($this->Openstore->save($this->data)){
                    $this->redirect('/openstores/complete');
                }
            }else{
                if(!empty($application['Openstore']['business_licence'])){
                    $this->set('business_licence_show', true);
                    $this->set('business_licence', $application['Openstore']['business_licence']);
                }
                if(!empty($application['Openstore']['food_licence'])){
                    $this->set('food_licence_show', true);
                    $this->set('food_licence', $application['Openstore']['food_licence']);
                }
                if(!empty($application['Openstore']['id_front'])){
                    $this->set('id_front_show', true);
                    $this->set('id_front', $application['Openstore']['id_front']);
                }
                if(!empty($application['Openstore']['id_back'])){
                    $this->set('id_back_show', true);
                    $this->set('id_back', $application['Openstore']['id_back']);
                }
                if(!empty($application['Openstore']['food_product_licence'])){
                    $this->set('food_product_licence_show', true);
                    $this->set('food_product_licence', $application['Openstore']['food_product_licence']);
                }
                if(!empty($application['Openstore']['food_health_licence'])){
                    $this->set('food_health_licence_show', true);
                    $this->set('food_health_licence', $application['Openstore']['food_health_licence']);
                }
                $this->set('store_name', $application['Openstore']['store_name']);


            }
        }else{
            $this->__message('您需要先通过上一步验证', '/openstores/apply');
        }
    }
    public function complete(){
        $this->pageTitle = '店铺审核';
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application)){
            if($application['Openstore']['status'] == APPLY_STATUS_WAIT_VERIFY){
                $this->set('wait', true);
            }else if($application['Openstore']['status'] == APPLY_STATUS_NEED_FIX){
                $this->set('not_pass', true);
                $this->set('reason', $application['Openstore']['reason']);
                if($application['Openstore']['pattern'] == 0) {
                    $this->set('person_apply', true);
                }
            }
        }

    }
    public function agreement(){
        $this->layout = null;
    }
}