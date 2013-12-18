<?php

class Setting extends AppModel {

    var $name = 'Setting';
    var $actsAs = array(
        'Ordered' => array(
            'field' => 'weight',
            'foreign_key' => false,
        ),
    );

    var $validate = array(
        'key' => array(
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This key has already been taken.',
            ),
            'minLength' => array(
                'rule' => array('minLength', 1),
                'message' => 'Key cannot be empty.',
            ),
        ),
    );

    function afterSave() {
        $this->writeConfiguration(true);
    }

    function afterDelete() {
        $this->writeConfiguration(true);
    }

    function write($key, $value, $options = array()) {
        $_options = array(
            'editable' => 0,
        );
        $options = array_merge($_options, $options);

        $setting = $this->findByKey($key);
        if (isset($setting['Setting']['id'])) {
            $setting['Setting']['id'] = $setting['Setting']['id'];
            $setting['Setting']['value'] = $value;
            $setting['Setting']['editable'] = $options['editable'];
        } else {
            $setting = array();
            $setting['key'] = $key;
            $setting['value'] = $value;
            $setting['editable'] = $options['editable'];
        }

        $this->id = false;
        if ($this->save($setting)) {
            Configure::write($key, $value);
            return true;
        } else {
            return false;
        }
    }

    function deleteKey($key) {
        $setting = $this->findByKey($key);
        if (isset($setting['Setting']['id']) &&
            $this->delete($setting['Setting']['id'])) {
            return true;
        }
        return false;
    }
    
    /**
     * 将settings表中的记录，写入DATA_PATH.'settings.php'配置文件
     * @param boolean $force_write 是否强制更新Configure配置文件。after_save,after_delete中强制更新文件
     */
    function writeConfiguration($force_write=false) {
        if ($force_write || !file_exists(DATA_PATH.'settings.php')){
            $settings = $this->find('all', array(
                'fields' => array(
                    'Setting.key',
                    'Setting.value',
                ),
            ));
            $settings_array = array();
	        foreach($settings AS $setting) {
	        	if($setting['Setting']['key']){
		            $settings_array[$setting['Setting']['key']] = $setting['Setting']['value'];
	        	}
	        }
        	Configure::write($settings_array);
        	write_configure_setting();
        }
    }
}
?>