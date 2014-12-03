<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/12/2
 * Time: 下午3:08
 */

class OpenstoresController extends AppController{
    var $name = 'Openstores';
    public function apply(){
        if(empty($this->currentUser['id'])){
            $this->__message('您需要先登录才能操作', '/users/login');
        }else{
            $id = $this->currentUser['id'];
            $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
            if(!empty($application)){
                $this->Session->setFlash('继续未完成的认证');
                $this->redirect('/openstores/base');
            }else{
                if(!empty($this->data)){
                    $msgCode = $this->Session->read('messageCode');
                    //$msgCode ="222";
                    if ($msgCode) {
                        $codeLog = json_decode($msgCode);
                        if ($codeLog && is_array($codeLog) && $codeLog['code'] == $this->data['Openstore']['msg_code'] && (time() - $codeLog['time'] < 30 * 60)){
                        //if(1){
                            $this->data['Openstore']['creator'] = $id;
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
    }
    public function base(){
        if(empty($this->currentUser['id'])){
            $this->__message('您需要先登录才能操作', '/users/login');
        }else{
            $id = $this->currentUser['id'];
            $application =$this->Openstore->find('first', array('conditions' => array('creator' => $id)));
            if(!empty($application)){
                if(!empty($this->data)){
                    if($this->Openstore->save($this->data)){
                        $this->redirect('/openstores/');
                    }
                }
            }else{
                $this->__message('您需要先通过上一步验证', '/openstores/apply');
            }
        }
    }
    public function company(){

    }
    public function complete(){

    }
}