<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/12/2
 * Time: 下午3:08
 */

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
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application)){
            if($application['Openstore']['status'] == 1){
                $this->Session->setFlash('继续未完成的认证');
                $this->redirect('/openstores/base');
            }else if($application['Openstore']['status'] == 2 || $application['Openstore']['status'] == 3 ){
                $this->redirect('/openstores/complete');
            }else{
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
                        $this->data['Openstore']['status'] = 1;
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
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application)){
            if(!empty($this->data)&& $this->request->is('post')){
                $person_pattern = 0;
                $this->data['Openstore']['pattern'] = $person_pattern;
                $this->data['Openstore']['id'] = $application['Openstore']['id'];
                $this->data['Openstore']['status'] = 2;
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
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application) ){
            $company_pattern = 1;
            $this->data['Openstore']['pattern'] = $company_pattern;
            $this->data['Openstore']['id'] = $application['Openstore']['id'];
            $this->data['Openstore']['status'] = 2;
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
                $this->set('store_name', $application['Openstore']['store_name']);


            }
        }else{
            $this->__message('您需要先通过上一步验证', '/openstores/apply');
        }
    }
    public function complete(){
        $id = $this->currentUser['id'];
        $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
        if(!empty($application)){
            if($application['Openstore']['status'] == 2){
                $this->set('wait', true);
            }else if($application['Openstore']['status'] == 3){
                $this->set('not_pass', true);
                $this->set('reason', $application['Openstore']['reason']);
            }
        }

    }
}