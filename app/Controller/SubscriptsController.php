<?php
/**
 * Created by PhpStorm.
 * User: algdev
 * Date: 15/1/7
 * Time: 下午2:25
 */



class SubscriptsController extends AppController {
    public $name ='Subscripts';


    public function chu_niang_bang_shou(){
        $this->_find_article(chu_niang_bang_shou);
        $this->pageTitle=__('厨娘帮手');
    }

    public function peng_you_gu_shi(){
        $this->_find_article(peng_you_gu_shi);
        $this->pageTitle=__('朋友故事');
    }

    public function chi_huo_ju_hui(){
        $this->_find_article(chi_huo_ju_hui);
        $this->pageTitle=__('吃货聚会');
    }

    public function shi_chi_information(){
        $this->_find_article(shi_chi_information);
        $this->pageTitle=__('吃货早知道');
    }

    public function about_us(){
        $this->_find_article(about_us);
        $this->pageTitle=__('关于我们');
    }

    public function ha_ha_ha_ha(){
        $this->_find_article(ha_ha_ha_ha);
        $this->pageTitle=__('哈哈哈哈');
    }

    public function xian_shang_huo_dong(){
        $this->_find_article(xian_shang_huo_dong);
        $this->pageTitle=__('线上活动');
    }

    public function peng_you_recommend(){
        $this->_find_article(peng_you_recommend);
        $this->pageTitle=__('朋友推荐');
    }

    public function _find_article($slug){

      $data = $this->Subscript->find('all',array(
          'conditions' => array('slug' => $slug,'deleted' => 0,'published' =>1),
          'fileds' => array('title','summary','pictures','published','deleted','slug','priority','link','name','content','coverimg'),
          'order' => 'priority desc'
      ));

        $this->set('datas',$data);
        $this->set('_serialize', array('datas'));

    }


}