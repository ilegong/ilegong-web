DROP TABLE IF EXISTS `cake_links`;
CREATE TABLE IF NOT EXISTS `cake_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `link_img` varchar(200) DEFAULT NULL,
  `cate_id` int(11) DEFAULT '0',
  `creator` int(11) DEFAULT '0',
  `lastupdator` int(11) DEFAULT '0',
  `link_url` varchar(200) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `locale` char(5) DEFAULT '',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Link', NULL, '11', 6, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'name', '1', '名称', 'string', 'Link', NULL, '200', 5, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'link_img', '1', '图片', 'string', 'Link', NULL, '200', 5, '1', '1', '', '', '', 0, '1', '', '0', '', '', '', 'file', '', '1', '', NULL, '', '', '', 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', ''),
(NULL, 'cate_id', '1', '分类', 'integer', 'Link', NULL, '11', 6, '1', '1', 'Misccate', 'id', 'name', 0, '1', '', '0', '', '', 'treenode', 'select', '', '1', '', NULL, '', '', '', 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <conditions>\r\n        <Misccate.model>Link</Misccate.model>\r\n    </conditions>\r\n    <order/>\r\n</options>'),
(NULL, 'creator', '1', '编创建者', 'integer', 'Link', NULL, '11', 6, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Link', NULL, '11', 6, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'link_url', '1', '链接地址', 'string', 'Link', NULL, '200', 5, '1', '1', '', '', '', 0, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', ''),
(NULL, 'status', '1', '状态', 'integer', 'Link', NULL, '11', 3, '1', '1', NULL, NULL, NULL, 0, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Link', NULL, '11', 3, '1', '1', NULL, NULL, NULL, 0, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Link', NULL, '11', 3, '1', '1', NULL, NULL, NULL, 0, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'locale', '1', '语言版本', 'char', 'Link', NULL, '5', 3, '1', '1', NULL, NULL, NULL, 0, '1', 'zh_cn', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Link', NULL, NULL, 2, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Link', NULL, NULL, 1, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-01-13 14:04:27', '2013-01-13 14:04:27', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Link', '链接管理', '', 'default', '', 27, '2013-01-13 14:04:27', '2013-01-13 14:04:27', 'cake_links', '', '', '', '0', NULL, NULL);



