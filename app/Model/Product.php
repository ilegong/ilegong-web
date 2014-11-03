<?php
class Product extends AppModel { 
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


    /**
     * @param $product_ids
     * @return array
     */
    public function findPublishedProductsByIds($product_ids) {
        return $this->find('all',array('conditions'=>array(
            'id' => $product_ids,
            'published' => 1
        )));
    }
    
}