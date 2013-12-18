DROP TABLE IF EXISTS `cake_keyword_relateds`;
CREATE TABLE IF NOT EXISTS `cake_keyword_relateds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `keyword_id` int(11) DEFAULT NULL,
  `relatedid` int(11) DEFAULT NULL,
  `relatedmodel` varchar(60) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'KeywordRelated', 'zh_cn', '11', 9, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'KeywordRelated', 'zh_cn', '11', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'KeywordRelated', 'zh_cn', '11', 3, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'KeywordRelated', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'KeywordRelated', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'keyword_id', '1', '关键字id', 'integer', 'KeywordRelated', 'zh_cn', NULL, 8, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'relatedid', '1', '相关id', 'integer', 'KeywordRelated', 'zh_cn', NULL, 6, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'relatedmodel', '1', '相关模块', 'string', 'KeywordRelated', 'zh_cn', '60', 7, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'title', '1', '标题', 'string', 'KeywordRelated', 'zh_cn', '300', 5, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'KeywordRelated', '相关关键词', 'onetomany', 'default', '', 1, '2010-08-03 23:08:37', '2010-08-03 23:08:37', 'cake_keyword_relateds', NULL, NULL, NULL, '0', 0, 0);
