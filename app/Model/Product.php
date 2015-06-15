<?php
class Product extends AppModel {

        const NO_VISIBLE_SIMPLE_FIELDS = 'id, name, created, brand_id, coverimg, promote_name, comment_nums, price, original_price, slug, specs, published, listimg';
        const PRODUCT_PUBLIC_FIELDS = 'Product.id, Product.name, Product.created, Product.brand_id, Product.coverimg, Product.promote_name,
                        Product.comment_nums, Product.price, Product.original_price, Product.slug,Product.published,Product.listimg,Product.limit_area';

       var $name = 'Product';
       
       var $actsAs = array(
	    	'ModelSplit',
		);
       var $belongsTo = array(
       		'Brand' => array(
       				'className'     => 'Brand',
       				'foreignKey'    => 'brand_id',
       				//'conditions'    => array('Brand.brand_id' => 'Product','Uploadfile.trash' => '0'),
       		)
       );
		
       var $hasMany = array(
	       'Uploadfile' => array(
		       'className'     => 'Uploadfile',
		       'foreignKey'    => 'data_id',
		       'conditions'    => array('Uploadfile.modelclass' => 'Product','Uploadfile.trash' => '0'), 
		       'order'    => 'Uploadfile.sortorder asc,Uploadfile.created ASC',
		       'limit'        => '',
		       'dependent'=> true
	       )
       );
       /*
       var $hasAndBelongsToMany = array(
					'Keyword' => array(
							'className'              => 'Keyword',
							'joinTable'              => 'keyword_relateds',
							'foreignKey'             => 'relatedid', // 对应本模块的id
							'associationForeignKey'  => 'keyword_id', // 对应keyword的id
							'conditions'             => array('KeywordRelated.relatedmodel' => 'Product'),
							'unique'                 => true,//'keepExisting'
							'dependent'            => true,
							'exclusive'            => true,
					)
			);*/
    var $validate = array(
    	'title' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
        'slug' => array(
        	'notEmpty'=> array(
	            'rule' => 'notEmpty',
	            'message' => 'This field cannot be left blank.',
	        ),
	        'isUnique'=>array(
	            'rule' => 'isUnique',
	            'message' => 'The slug has already been taken.',
	        ),
        ),
    );

    public function find_shichi_products($limit = 10) {
        $results = $this->find('all', array('conditions' => array(
            'status' => PRODUCT_STATUS_EXPR,
            'published' => PUBLISH_YES,
            'shichi_time >= NOW()',
            ),
            'fields' => array_merge(array('id'), explode(',', self::NO_VISIBLE_SIMPLE_FIELDS), array('shichi_time', 'shichi_price', 'shichi_number')),
            'order' => 'shichi_time desc',
            'limit' => $limit,
            'recursive' => -1
        ));
        return $results;
    }

    /**
     * @param $product_ids
     * @param array $extra_fields
     * @return array  keyed with product id, valued with product items (1 dimension)
     */
    public function find_published_products_by_ids($product_ids, $extra_fields = array()) {
        return $this->find_products_by_ids($product_ids, $extra_fields, true);
    }

    public function update_storage_saled($pid, $num) {
        if ($num != 0) {
            $tries = 10;
            while ($tries-- > 0) {
                $product = $this->find('first', array('conditions' => array('id' => $pid), 'fields' => array('storage', 'saled', 'id')));
                if (empty($product)) {
                    $this->log("skip updateStorageSaled for not found $pid, $num, tries=".$tries);
                    break;
                }
                $this->updateAll(array('saled' => 'saled + ' . $num, 'storage' => 'storage - ' . $num),
                    array('id' => $pid, 'storage' => $product['Product']['storage'], 'saled' => $product['Product']['saled']));
                if ($this->getAffectedRows() > 0) {
                    $this->log("successfully updateStorageSaled: $pid, $num, based: ".json_encode($product['Product']).", tries=".$tries);
                    break;
                };
            }
        }
    }

    /**
     * @param $product_ids
     * @param $extra_fields
     * @param $only_published
     * @return array
     */
    public function find_products_by_ids($product_ids, $extra_fields = null, $only_published = true) {
        if (empty($product_ids)) return array();

        if ($extra_fields == null) $extra_fields = array();

        $cond = array(
            'id' => $product_ids,
        );
        if ($only_published) {
            $cond['published'] = PUBLISH_YES;
        }
        $results = $this->find('all', array('conditions' => $cond,
            'fields' => array_merge(array('id'), explode(',', self::NO_VISIBLE_SIMPLE_FIELDS), $extra_fields),
            'recursive' => -1
        ));
        return Hash::combine($results, '{n}.Product.id', '{n}.Product');
    }
}