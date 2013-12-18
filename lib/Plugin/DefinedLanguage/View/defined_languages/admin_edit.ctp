<?php
	echo $this->Form->create('DefinedLanguage', array('id' => 'contentform', 'action' => '/admin/DefinedLanguage/defined_languages/edit/'.$defined_key, 'url' => '/admin/DefinedLanguage/defined_languages/edit/'.$defined_key));
	
	echo $this->MForm->input('DefinedLanguage.key',array(
   				   		'label' => __('Alias'),				   
   						'value' => $defined_key
	               ));
	
	foreach($languages AS $language)
	{
		$language_key = $language['Language']['id'];
		if(!isset($keyed_definitions[$language_key]['DefinedLanguage']['value']))
			$keyed_definitions[$language_key]['DefinedLanguage']['value'] = "";
		echo $this->MForm->input(
				'DefinedLanguage.'.$language['Language']['id'].'.value',
				 array(
			   		'label' => $language['Language']['native'],
					'value' => $keyed_definitions[$language_key]['DefinedLanguage']['value']
            	  ));
	}
	
	echo $this->Form->submit(__('Submit'), array('name' => 'submit'));
	echo '<div class="clear"></div>';
	echo $this->Form->end();
	
?>