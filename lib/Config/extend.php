<?php
$GLOBALS['model_behaviors'] = array();
/* 内容一一对应的多语言模块 */

// I18nfield 在这里会有问题，不设置。 在模型的初始化时，会调用I18nfield模型查询模型的字段。出现嵌套造成错误。
/*
$GLOBALS['model_behaviors']['I18nfield']['MultiTranslate'] = array('translate',);
$GLOBALS['model_behaviors']['Menu']['MultiTranslate'] = array('name',);
*/
/* 内容互相对立的多语言模块 */
/*
$GLOBALS['model_behaviors']['Article']['IndLang'] = array();
$GLOBALS['model_behaviors']['Category']['IndLang'] = array();

*/