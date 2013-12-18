<?php
class SectionComponent extends Component{
	var $modelClass;
	var $controller;
	function startup(&$controller)
	{
		$this->controller = $controller;
		
	}
	function shutdown()
	{
		
	}
	function getNavMenu()
	{
		// 判断有模板缓存，不处理
		// 判断有变量缓存，直接返回变量缓存
		// 若两个缓存都没有，则从数据库查出内容，返回
		$channels = $this->controller->Channel->find('threaded');
		//print_r($channels);
		//echo "========";
	}
	
/**
 * beforeRender
 *
 * @param object $controller instance of controller
 * @return void
 */
    function beforeRender(&$controller) {
        $this->controller =& $controller;
        $this->controller->set('blocks_for_layout', $this->blocks_for_layout);
        $this->controller->set('menus_vars', $this->menus_for_layout);
        $this->controller->set('vocabularies_for_layout', $this->vocabularies_for_layout);
        $this->controller->set('types_for_layout', $this->types_for_layout);
        $this->controller->set('nodes_for_layout', $this->nodes_for_layout);

    }
	
	function nestedLinks($links, $options = array(), $classname='',$depth = 1) {
        $_options = array();
        $options = array_merge($_options, $options);
        
        $output = '';
        foreach ($links AS $link) {
            $linkAttr = array(
                'id' => 'link-' . $link['Channel']['id'],
                'rel' => $link['Channel']['rel'],
                'target' => $link['Channel']['target'],
                'title' => $link['Channel']['description'],
            );

            foreach ($linkAttr AS $attrKey => $attrValue) {
                if ($attrValue == null) {
                    unset($linkAttr[$attrKey]);
                }
            }

            // if link is in the format: controller:contacts/action:view
            if (strstr($link['Channel']['link'], 'controller:')) {
                $link['Channel']['link'] = $this->linkStringToArray($link['Channel']['link']);
            }

            if (Router::url($link['Channel']['link']) == Router::url('/' . $this->params['url']['url'])) {
                $linkAttr['class'] = $options['selected'];
            }

            $linkOutput = $this->Html->link($link['Channel']['title'], $link['Channel']['link'], $linkAttr);
            if (isset($link['children']) && count($link['children']) > 0) {
                $linkOutput .= $this->nestedLinks($link['children'], $options, $depth + 1);
            }
            $linkOutput = $this->Html->tag('li', $linkOutput);
            $output .= $linkOutput;
        }
        if ($output != null) {
            $tagAttr = array();
            if ($options['dropdown'] && $depth == 1) {
                $tagAttr['class'] = $options['dropdownClass'];
            }
            $output = $this->Html->tag($options['tag'], $output, $tagAttr);
        }

        return $output;
    }
}