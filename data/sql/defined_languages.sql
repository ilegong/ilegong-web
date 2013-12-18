DROP TABLE IF EXISTS `cake_defined_languages`;
CREATE TABLE IF NOT EXISTS `cake_defined_languages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `language_id` int(10) NOT NULL,
  `key` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'DefinedLanguage', 'zh_cn', '10', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:43', '2011-02-20 09:21:43', NULL),
(NULL, 'language_id', '1', 'language_id', 'integer', 'DefinedLanguage', 'zh_cn', '10', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:43', '2011-02-20 09:21:43', NULL),
(NULL, 'key', '1', 'key', 'string', 'DefinedLanguage', 'zh_cn', '200', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:43', '2011-02-20 09:21:43', NULL),
(NULL, 'value', '1', 'value', 'text', 'DefinedLanguage', 'zh_cn', NULL, 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:43', '2011-02-20 09:21:43', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'DefinedLanguage', 'zh_cn', NULL, 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:43', '2011-02-20 09:21:43', NULL),
(NULL, 'modified', '1', 'modified', 'datetime', 'DefinedLanguage', 'zh_cn', NULL, 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:43', '2011-02-20 09:21:43', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'DefinedLanguage', '自定义语言', 'onetomany', 'default', '', 27, '2011-03-05 20:26:57', '2011-03-05 20:26:57', 'cake_defined_languages', '', '', '', '0', NULL, 0);



REPLACE INTO `cake_defined_languages` (`id`, `language_id`, `key`, `value`, `created`, `modified`) VALUES (1, 2, 'hello', '你好啊', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 1, 'hello', 'hello', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
