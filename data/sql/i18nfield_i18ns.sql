DROP TABLE IF EXISTS `cake_i18nfield_i18ns`;
CREATE TABLE IF NOT EXISTS `cake_i18nfield_i18ns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `cate_id` int(11) DEFAULT '0',
  `creator` int(13) DEFAULT '0',
  `lastupdator` int(13) DEFAULT '0',
  `remoteurl` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `foreign_key` int(11) DEFAULT '0',
  `translate` varchar(200) DEFAULT NULL,
  `locale` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'I18nfieldI18n', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'name', '1', '名称', 'string', 'I18nfieldI18n', 'zh_cn', '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'I18nfieldI18n', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'I18nfieldI18n', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'I18nfieldI18n', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'remoteurl', '1', '引用地址', 'string', 'I18nfieldI18n', 'zh_cn', '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'status', '1', '状态', 'integer', 'I18nfieldI18n', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'I18nfieldI18n', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'I18nfieldI18n', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'I18nfieldI18n', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'I18nfieldI18n', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-06 22:45:56', '2011-08-06 22:45:56', NULL),
(NULL, 'foreign_key', '1', '外键', 'integer', 'I18nfieldI18n', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-08-06 22:51:36', '2011-08-06 22:51:36', ''),
(NULL, 'translate', '1', '含义', 'string', 'I18nfieldI18n', 'zh_cn', '200', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-06 22:53:01', '2011-08-06 22:53:01', ''),
(NULL, 'locale', '1', '语言类型', 'string', 'I18nfieldI18n', 'zh_cn', '10', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-06 23:00:24', '2011-08-06 23:00:24', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'I18nfieldI18n', '字段多语言', '', 'default', '', 27, '2011-08-06 22:45:56', '2011-08-06 22:45:56', 'cake_i18nfield_i18ns', '', '', '', '0', 0, 0);
