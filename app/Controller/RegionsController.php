<?php
class RegionsController extends AppController{
	
	var $name = 'Regions';
	
	function beforeFilter() {
		parent::beforeFilter();
		if($this->layout != 'ajax'){
			$this->layout = 'region';
		}
	}
	
	function index($regionid)
	{
		
		$page= intval($_GET['page'])?intval($_GET['page']) :(intval($this->params['named']['page']) ? intval($this->params['named']['page']):1);
		
		$regionContent = $this->Region->getRegionContentById($regionid,$page);
    	
		$datas = $regionContent['datalist'];
		$page_navi = $regionContent['page_navi'];
		$regioninfo = $regionContent['regioninfo'];
		
		list($plugin, $modelname) = pluginSplit($regioninfo['Region']['model'], false);
		$tplname = $regioninfo['Region']['template'];
		
		$this->set('page_navi',$page_navi);
		$this->set('region_plugin',$plugin);
		$this->set('tplname',$tplname);
		$this->set('regionid',$regionid);
		$this->set('datas',$datas);
		$this->set('modelname',$modelname);
	}
	
	function lists(){ //$page=1
		$searchoptions =  array(
            'conditions' => array('deleted'=>0),
			'order' =>  'id desc',
        );
        $this->Region->schema();
        $fields = array_keys($this->Region->_schema);
        foreach($this->params['named'] as $key => $val){
        	if(in_array($key,$fields)){
        		$searchoptions['conditions'][$key] = $val;
        	}
        }
        $page= intval($_GET['page'])?intval($_GET['page']) :(intval($this->params['named']['page']) ? intval($this->params['named']['page']):1);
        
        $total_num  = $this->Region->find('count',$searchoptions);
        $searchoptions['page'] = $page;
        $searchoptions['limit'] = 10;
        $datas = $this->Region->find('all',$searchoptions);
       
        
        $page_navi = getPageLinks($total_num, 10, $this->request, $page);

        $this->set('regions',$datas);
        $this->set('page',$page);
        $this->set('page_navi',$page_navi);
	}
	
	function load($id){
		$this->layout = 'ajax';
		$this->set('portlet_id',$id);
	}

}