DROP TABLE IF EXISTS `cake_styles`;
CREATE TABLE IF NOT EXISTS `cake_styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `coverimg` varchar(200) DEFAULT '',
  `parent_id` int(11) DEFAULT '0',
  `creator` int(11) DEFAULT '0',
  `status` tinyint(4) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `slug` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Style', NULL, '11', 6, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'name', '1', '名称', 'string', 'Style', NULL, '200', 5, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'coverimg', '1', '封面图片', 'string', 'Style', NULL, '200', 5, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'parent_id', '1', '上级分类', 'integer', 'Style', NULL, '11', 6, '1', '1', '', '', '', 0, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', ''),
(NULL, 'creator', '1', '编创建者', 'integer', 'Style', NULL, '11', 6, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'status', '1', '状态', 'integer', 'Style', NULL, '11', 3, '1', '1', NULL, NULL, NULL, 0, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Style', NULL, '11', 3, '1', '1', NULL, NULL, NULL, 0, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Style', NULL, '11', 3, '1', '1', NULL, NULL, NULL, 0, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Style', NULL, NULL, 2, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Style', NULL, NULL, 1, '1', '1', NULL, NULL, NULL, 0, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-09-23 10:21:07', '2013-09-23 10:21:07', NULL),
(NULL, 'slug', '1', '英文名称', 'string', 'Style', 'zh_cn', '20', NULL, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2013-09-23 16:00:56', '2013-09-23 16:00:56', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Style', '风格', '', 'default', '', 27, '2013-09-23 10:21:07', '2013-09-23 10:21:07', 'cake_styles', '', '', '', '0', 100, NULL);



REPLACE INTO `cake_styles` (`id`, `name`, `coverimg`, `parent_id`, `creator`, `status`, `published`, `deleted`, `created`, `updated`, `slug`) VALUES (1, '橙色风格', '', 0, 0, 0, '1', '0', '2013-09-23 17:06:59', '2013-09-23 17:06:59', 'united'),
(2, '蓝色风格', '', 0, 0, 0, '1', '0', '2013-10-26 09:45:22', '2013-10-26 09:45:22', 'Cerulean');
