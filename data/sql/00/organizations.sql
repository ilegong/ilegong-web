DROP TABLE IF EXISTS `cake_organizations`;
CREATE TABLE IF NOT EXISTS `cake_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `left` int(11) DEFAULT '0',
  `right` int(11) DEFAULT '0',
  `sort` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `responsiblepositionid` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `responsibleposition_name` varchar(100) DEFAULT NULL,
  `deleted` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Organization', 'zh_cn', '11', 12, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '组织名称', 'string', 'Organization', 'zh_cn', '200', 11, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', 'explode', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'parent_id', '1', '上级组织id', 'integer', 'Organization', 'zh_cn', '11', 10, '1', '1', 'Organization', 'id', 'name', NULL, '1', '', '0', '', '', 'treenode', 'select', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'left', '1', '树左节点', 'integer', 'Organization', 'zh_cn', '11', 9, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'right', '1', '树右节点', 'integer', 'Organization', 'zh_cn', '11', 8, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'sort', '1', '排序', 'integer', 'Organization', 'zh_cn', '11', 5, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'Organization', 'zh_cn', '3', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'responsiblepositionid', '1', '管理职位id', 'integer', 'Organization', 'zh_cn', '11', 7, '1', '1', 'Position', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Organization', 'zh_cn', NULL, 2, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Organization', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'responsibleposition_name', '1', '管理职位', 'string', 'Organization', 'zh_cn', '100', 6, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Organization', 'zh_cn', '1', 3, '0', '1', '', '', '', NULL, '1', '0=>否\n1=>是', '0', '', '', 'equal', 'select', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Organization', '组织', 'onetomany', 'default', '', 26, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_organizations', NULL, NULL, NULL, '0', 0, 0);
