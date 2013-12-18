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

	/**
     * 将settings表中的记录，写入DATA_PATH.'settings.php'配置文件
     * @param boolean $force_write 是否强制更新Configure配置文件。after_save,after_delete中强制更新文件
     */
    function writeConfiguration($force_write=false) {
        if ($force_write || !file_exists(DATA_PATH.'settings.php')){
//         	echo "-====writeConfiguration====";
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