DROP TABLE IF EXISTS `cake_tag_relateds`;
CREATE TABLE IF NOT EXISTS `cake_tag_relateds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL,
  `relatedid` int(11) DEFAULT NULL,
  `relatedmodel` varchar(60) DEFAULT NULL,
  `name` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `relatedmodel` (`relatedmodel`,`relatedid`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'TagRelated', 'zh_cn', '11', 9, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'TagRelated', 'zh_cn', '11', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'TagRelated', 'zh_cn', '11', 3, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'relatedmodel', '1', '相关模块', 'string', 'TagRelated', 'zh_cn', '60', 7, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '标题', 'string', 'TagRelated', 'zh_cn', '300', 5, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'tag_id', '0', 'tag_id', 'integer', 'TagRelated', NULL, '11', 0, '1', '1', '', '', '', 0, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2013-06-22 23:49:42', '2013-06-22 23:49:42', ''),
(NULL, 'relatedid', '0', 'relatedid', 'integer', 'TagRelated', NULL, '11', 0, '0', '0', NULL, NULL, NULL, 0, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-06-22 23:49:42', '2013-06-22 23:49:42', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'TagRelated', '相关标签', 'onetomany', 'default', '', 27, '2013-06-22 23:44:22', '2013-06-22 23:44:22', 'cake_tag_relateds', '', '', '', '0', NULL, 0);
