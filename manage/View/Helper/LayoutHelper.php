<?php

/**
 * Layout Helper
 *
 * PHP version 5
 *
 * @category Helper
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class LayoutHelper extends AppHelper {

    /**
     * Other helpers used by this helper
     *
     * @var array
     * @access public
     */
    var $helpers = array(
        'Html',
        'MForm',
        'Session', 'Ckeditor',
        'Javascript',
    );
    /**
     * Current Node
     *
     * @var array
     * @access public
     */
    var $node = null;
    /**
     * Hook helpers
     *
     * @var array
     * @access public
     */
    var $hooks = array();

    /**
     * Constructor
     *
     * @param array $options options
     * @access public
     */
    function __construct($options = array()) {
        //$this->View = ClassRegistry::getObject('view');
        $this->__loadHooks();
        $objct = parent::__construct($options);
        return $objct;
    }

    /**
     * Load hooks as helpers
     *
     * @return void
     */
    function __loadHooks() {
        //$hooks = 'Oauth.OauthHook,Communicate.ViewpointsHook';
        //$hooks = Configure::read('Hook.helpers')
        if ($hooks) {
            // Set hooks
            $hooksE = explode(',', $hooks);
            foreach ($hooksE AS $hook) {
                $hookE = explode('.', $hook);
                $plugin = $hookE['0'];
                $hookHelper = $hookE['1'];
                $loaded = CakePlugin::load($plugin);
                $this->hooks[] = $hook;
            }
            // Set hooks as helpers
            foreach ($this->hooks AS $hook) {
                $this->helpers[] = $hook;
            }
        }
    }

    function getLanguageTabHead($modelClass) {
        $fields = $GLOBALS['model_behaviors'][$modelClass]['MultiTranslate'];
        if (empty($fields)) {
            return '';
        }
        $languages = Configure::read('System.ActiveLanguage'); //Cache::read
        $cn_langs = array('zh-cn','zh-tw');
        $lis = '';
        $jumpflag = true;
        if(is_array($languages) && !empty($languages)){
	        foreach ($languages as $key => $val) {
	        	if (DEFAULT_LANGUAGE == $val['alias']) {
	                continue; //等于系统默认语言时跳过
	            }
	            elseif(in_array(DEFAULT_LANGUAGE,$cn_langs) && in_array($val['alias'],$cn_langs)){
	        		continue;//默认语言为中文，当前语言也为中文时，跳过。中文时，不出现设置繁体语言tab
	        	}
	            $lis .= '<li><a href="#' . $key . '-lang-content" data-toggle="tab"><span>' . $val['native'] . '</span></a></li>';
	        }
        }
        return $lis;
    }

    function getLanguageTabContent($modelClass, $suffix='') {
        $fields = $GLOBALS['model_behaviors'][$modelClass]['MultiTranslate'];
        if (empty($fields)) {
            return '';
        }
        $languages = Configure::read('System.ActiveLanguage'); //Cache::read
        $contents = '';

        if (!empty($this->data[$modelClass . 'I18n'])) {
            $lang_data = $this->data;
            foreach ($lang_data[$modelClass . 'I18n'] as $key => $value) {
                unset($lang_data[$modelClass . 'I18n'][$key]);
                $lang_data[$modelClass . 'I18n'][$value['locale']] = $value;
            }
            $this->data = $lang_data;
        }
        $cn_langs = array('zh-cn','zh-tw');
        $model =  loadModelObject($modelClass);
        $extSchema = $model->getExtSchema();
        if(is_array($languages) && !empty($languages)){
	        foreach ($languages as $language => $languageinfo) {
	        	if(in_array(DEFAULT_LANGUAGE,$cn_langs) && in_array($languageinfo['alias'],$cn_langs)){
	        		continue;//默认语言为中文，当前语言也为中文时，跳过。中文时，不出现设置繁体语言tab
	        	}
	            $lang_index = $locale = $languageinfo['locale'];
	            if (DEFAULT_LANGUAGE == $languageinfo['alias']) {
	                continue; //等于系统默认语言时跳过
	            }
	
	            $contents .= '<div id="' . $language . '-lang-content" class="tab-pane"><fieldset>';
	            $contents .= $this->MForm->input($modelClass . 'I18n.' . $lang_index . '.id', array('type' => 'hidden'));
	            $contents .= $this->MForm->input($modelClass . 'I18n.' . $lang_index . '.locale', array('type' => 'hidden', 'value' => $locale));
	            foreach ($extSchema as $key => $value) {
	                if (in_array($key, $fields)) {
	                    $options = array();
	                    $options['label'] = $value['translate'] . '(' . $languageinfo['locale'] . ')';
	                    if (in_array($value['formtype'], array('input', 'datetime'))) {
	                        $options['type'] = 'text';
	                    } elseif ($value['formtype'] == 'checkbox') {
	                        $options['type'] = 'select';
	                        if (!($params['form_type'] == 'edit' && $value['explodeimplode'] == 'explode')) {
	                            $options['multiple'] = 'checkbox';
	                            $options['div'] = array('id' => Inflector::camelize($modelClass . '_' . $key . '_Checkboxs') . $suffix);
	                        }
	                    } elseif ($value['formtype']) {
	                        if ('ckeditor' == $value['formtype']) {
	                            $options['rows'] = '5';
	                            $options['cols'] = '100';
	                            $options['between'] = '<div style="clear: both;"></div>';
	                        }
	                        $options['type'] = $value['formtype'];
	                    }
	                    $options['id'] = $language . Inflector::camelize($modelClass . '_' . $key) . $suffix;
	                    $contents .= $this->MForm->input($modelClass . 'I18n.' . $lang_index . '.' . $key, $options);
	
	                    if ('ckeditor' == $value['formtype']) {
	                        $contents .= $this->Ckeditor->load($options['id']);
	                    }
	                }
	            }
	            $contents .= '</fieldset></div>';
	//			$lang_index++;
	        }
        }
        

        return $contents;
    }

    /**
     * Hook
     *
     * Used for calling hook methods from other HookHelpers
     *
     * @param string $methodName
     * @return string
     */
    function hook($methodName, $params=array()) {
        $output = '';
        foreach ($this->hooks AS $hook) {
            if (strstr($hook, '.')) {
                $hookE = explode('.', $hook);
                $hook = $hookE['1'];
            }

            if (method_exists($this->{$hook}, $methodName)) {
                //$output .= $this->{$hook}->$methodName();
                $output .= call_user_func_array(array(&$this->{$hook}, $methodName), $params);
            }
        }

        return $output;
    }

}

?>