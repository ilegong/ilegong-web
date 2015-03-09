<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/8/15
 * Time: 13:37
 */
class ProductSpecGroupComponent extends Component{

    var $name = 'ProductSpecComponent';

    public function extract_spec_group_map($pid,$field='id'){
        $productSpecGroup = ClassRegistry::init('ProductSpecGroup');
        $result = $productSpecGroup->find('all',array(
            'conditions'=>array(
                'product_id'=>$pid,
                'deleted'=>0
            )
        ));
        $result = Hash::combine($result,'{n}.ProductSpecGroup.'.$field,'{n}.ProductSpecGroup');
        return $result;
    }

    public function get_spec_group_by_ids($pid,$ids){
        $productSpecGroup = ClassRegistry::init('ProductSpecGroup');
        $result = $productSpecGroup->find('first',array(
            'conditions'=>array(
                'product_id'=>$pid,
                'spec_ids'=>$ids,
                'deleted'=>0
            )
        ));
        return $result;
    }

    public function get_spec_group_by_names($pid,$names){
        $productSpecGroup = ClassRegistry::init('ProductSpecGroup');
        $result = $productSpecGroup->find('first',array(
            'conditions'=>array(
                'product_id'=>$pid,
                'spec_names'=>$names,
                'deleted'=>0
            )
        ));
        return $result;
    }

    /**
     * param example array(array('pid'=>6,'specId'=>6,'defaultPrice'=>190).......)
     * specId default 0
     * result example array('pid-specId'=>price)
     *
     * @param $pidSidMap
     * @return array
     */
    public function get_price_by_pid_and_sid($pidSidMap){
        $productSpecGroup = ClassRegistry::init('ProductSpecGroup');
        //有特惠价格或者商品没有specId 使用默认价格
        $result = array();
        foreach($pidSidMap as $item){
            $sid = $item['sepcId'];
            $pid = $item['pid'];
            $price = $item['defaultPrice'];
            if($sid!=0){
                $specGroup = $productSpecGroup->find('first',array(
                    'conditions'=>array(
                        'id'=>$sid
                    )
                ));
                if(!empty($specGroup)){
                    $price = $specGroup['ProductSpecGroup']['price'];
                }
            }
            $result[]=array($pid.'-'.$sid=>$price);
        }
        return $result;
    }

    public function get_product_spec_json($pid){
        $productSpec = ClassRegistry::init('ProductSpec');
        $productAttrs = json_decode(ProductSpeciality::get_product_attrs(),true);
        $productAttrs = Hash::combine($productAttrs,'{n}.id','{n}.name');
        //todo
        $allProductSpec = $productSpec->find('all',array(
            'conditions'=>array(
                'product_id'=>$pid,
                'deleted'=>0
            ),
            'fields'=>array(
                'id','name','attr_id',
            )
        ));
        $allProductSpec = Hash::combine($allProductSpec,'{n}.ProductSpec.id','{n}.ProductSpec');
        $mapArray = array();
        $choiceArray = array();
        foreach($allProductSpec as $id=>$item){
            $specName = $item['name'];
            $mapArray[$id]=$specName;
            $attr_id=$item['attr_id'];
            $attr_name = $productAttrs[$attr_id];
            if(array_key_exists($attr_name,$choiceArray)){
                $attrArray = &$choiceArray[$attr_name];
                $attrArray[]=$specName;
            }else{
                $choiceArray[$attr_name]=array($specName);
            }
        }
        if(!empty($mapArray)&&!empty($choiceArray)){
            $result = array('map'=>$mapArray,'choices'=>$choiceArray);
        }else{
            $result=array();
        }
        return $result;
    }

}