DROP TABLE IF EXISTS `cake_tags`;
CREATE TABLE IF NOT EXISTS `cake_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `priority` int(11) DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `value` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Tag', 'zh_cn', '11', 6, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'cate_id', '1', 'cateid', 'integer', 'Tag', 'zh_cn', '11', 5, '1', '1', 'Misccate', 'id', 'name', NULL, '1', '', '0', '', '', '', 'select', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <conditions>\r\n        <Misccate.model>Tag</Misccate.model>\r\n    </conditions>\r\n    <order>created desc</order>\r\n</options>'),
(NULL, 'name', '1', '名称', 'string', 'Tag', 'zh_cn', '200', 4, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'priority', '1', 'priority', 'integer', 'Tag', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'enabled', '1', 'enabled', 'boolean', 'Tag', 'zh_cn', '1', 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '1', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Tag', '标签', 'onetomany', 'default', '', 26, '2013-01-13 20:48:23', '2013-01-13 20:48:23', 'cake_tags', '', '', '', '0', 1, 0);
