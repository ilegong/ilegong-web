<?php
/* -----------------------------------------------------------------------------------------
   VaM Cart
   http://vamcart.com
   http://vamcart.ru
   Copyright 2009-2010 VaM Cart
   -----------------------------------------------------------------------------------------
   Portions Copyright:
   Copyright 2007 by Kevin Grandon (kevingrandon@hotmail.com)
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

//echo $admin->ShowPageHeaderStart($current_crumb, 'defined.png');

echo '<table class="contentTable">';
echo $this->Html->tableHeaders(array( __('Title', true), __('Call (Template Placeholder)', true),__('Action', true)));

foreach ($defined_languages AS $defined_language)
{
	echo $this->Html->TableCells(
		  array(
			$this->Html->link($defined_language['DefinedLanguage']['key'],'/admin/DefinedLanguage/defined_languages/edit/' . $defined_language['DefinedLanguage']['key']),
			'{lang}' . $defined_language['DefinedLanguage']['key'] . '{/lang}',
			)
		   );
}
echo '</table>';
//echo $admin->EmptyResults($defined_languages);

//echo $admin->CreateNewLink();

//echo $admin->ShowPageHeaderEnd();

?>