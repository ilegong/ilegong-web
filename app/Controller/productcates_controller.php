<?php
class ProductcatesController extends AppController{
	
	var $name = 'Productcates';
	var $actsAs = array('Tree'); 
	
	function admin_index($parent_id=null){
		$this->pageTitle = __('Categories', true);
        //$this->User->recursive = 0;
        //echo $parent_id;
        $modelClass = $this->modelClass;
        $datas = $this->{$modelClass}->find('all', array(
            'conditions' => array(
                $modelClass .'.parent_id' => $parent_id,
            ),
            'order' => $modelClass .'.id ASC',
            'fields' => array(
                'id','title','parent_id','created','updated'
            ),
        ));
        $this->set('Categories', $datas);
        
        $currentdata =  $this->{$modelClass}->findById($parent_id);
        $this->set('currentdata', $currentdata);
        $this->set('parent_id', $parent_id);
        
	}	
	
	function view($slug,$page=1)
	{
//		echo $page;

		$pagesize = 15;
		$this->Productcate->bindModel(
			array(
			'hasAndBelongsToMany' => array(
				'Product' => array(
		            'className' => 'Product',
		            'joinTable' => 'category_products',
		            'foreignKey' => 'category_id',
		            'associationForeignKey' => 'product_id',
		            'unique' => true,
		            //'conditions' => "CategoryData.type='Article'",
		            'fields' => 'id,title,created,slug',
		            'order' => 'product_id desc',
		            'limit' => $pagesize,
		            'offset' => $pagesize*($page-1),
		            'finderQuery' => '',
		            'deleteQuery' => '',
		            'insertQuery' => '',
		        ),
		       )
		      )
		  );
		
		$this->Productcate->recursive = 1;
		//$Category = $this->Category->find('first',array('conditions' => array('Category.id' => $id)));
		if(!empty($slug) && $slug != strval(intval($slug)))
		{
			$Category = $this->Productcate->findBySlug($slug);
			//$this->paginate[$this->modelClass]['limit'] = Configure::read('Reading.nodes_per_page');
			//$Category = $this->paginate($this->modelClass);
			//$Category = $this->Category->find('first',array('conditions'=>array('slug'=>$slug)));
			//exit;
		}
		elseif(intval($slug))
		{
			$Category = $this->Productcate->findById($slug);
		}
		else
		{
			$this->redirect('/');
		}
		
		if(count($Category['Product'])==1)
		{
			
		}
//		print_r($Category);
		$id = $Category[$this->modelClass]['id'];
		
		$total_article = $this->{$this->modelClass}->CategoryProduct->find('count',
			array('conditions'=>array('category_id'=>$id))); 
		if($total_article > 1)
		{
			require_once ROOT.'/app/libs/page.php';
			$pagelinks = new Page($total_article,$pagesize,$Category[$this->modelClass]['slug'],$page);
			$page_navi = $pagelinks->renderNav(10);
			if($Category['Productcate']['slug']=='/')
			{
				$this->set('page_navi', $page_navi,'/index/');
			}
			else
			{
				$this->set('page_navi', $page_navi,'/'.$Category[$this->modelClass]['slug'].'/');
			}
		}
		elseif($total_article == 1)
		{
			$this->Productcate->CategoryProduct->recursive = 1;
			$array = $this->Productcate->CategoryProduct->find('first',
			array(
				
				'conditions'=>array('category_id'=>$id),
				'fields' => 'Product.*',
				'joins'=>array(
					array(
						'table' => 'products',
						'alias' => 'Product',
						'type' => 'LEFT',
						'conditions' => array('CategoryProduct.product_id=Product.id ') 
					),
				))
			);
			$Category['Product']=$array['Product'];
			
		}
		$this->set('total_article', $total_article);
		
		
		// 设置顶级栏目，与栏目名称。不为顶级栏目时，找出本栏目的所有父栏目。
		if($Category['Productcate']['parent_id']=='')
		{
			$top_cateid = 	$Category['Productcate']['id'];
			$top_catename = $Category['Productcate']['title'];	
		}
		else
		{
			$left = 	$Category['Productcate']['left'];
			$right = 	$Category['Productcate']['right'];
			$this->{$this->modelClass}->recursive = -1;
			$options = array(
				'conditions' => array('Productcate.left <' => $left,'Productcate.right >' => $right),
				'order' => array('Productcate.left asc'),
				);
			$guid = guid_string($options);
			$parents = Cache::read($guid.'category_parentids');
			//print_r($parents);
			if ($parents === false) {
				$parents = $this->Category->find('all',$options);
				Cache::write($guid.'category_parentids',$parents); 
			}
			$top_cateid = $parents[0][$this->modelClass]['id'];
			$top_catename = $parents[0][$this->modelClass]['title'];
		}
		// 设置页面SEO标题、关键字、内容描述
		if(!empty($Category[$this->modelClass]['seotitle']))
		{
			$this->set('pageTitle', $Category[$this->modelClass]['seotitle']);
		}
		else
		{
			$this->set('pageTitle', $Category[$this->modelClass]['title']);
		}
		if($Category[$this->modelClass]['seodescription'])
		{
			$this->set('seodescription',$Category['Category']['seodescription']);
		}
		if($Category[$this->modelClass]['seokeywords'])
		{
			$this->set('seokeywords',$Category['Category']['seokeywords']);
		}
		$this->set('top_cateid', $top_cateid);
		$this->set('top_catename', $top_catename);
		$this->set('current_cateid', $id);
		$this->set('Category', $Category);
	}
}