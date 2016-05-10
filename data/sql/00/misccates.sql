DROP TABLE IF EXISTS `cake_misccates`;
CREATE TABLE IF NOT EXISTS `cake_misccates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT '0',
  `name` varchar(200) DEFAULT NULL,
  `slug` varchar(60) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '1',
  `rel` varchar(60) DEFAULT NULL,
  `target` varchar(60) DEFAULT NULL,
  `link` varchar(60) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `left` int(11) DEFAULT '0',
  `right` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `model` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `left_right_index` (`left`,`right`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Misccate', 'zh_cn', '11', 13, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'parent_id', '1', '父级分类', 'integer', 'Misccate', 'zh_cn', '11', 12, '1', '1', 'Misccate', 'id', 'name', NULL, '1', '', '0', '', '', 'treenode', 'select', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'name', '1', '标题', 'string', 'Misccate', 'zh_cn', '200', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', 'explode', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'slug', '1', '链接文字', 'string', 'Misccate', 'zh_cn', '60', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'visible', '1', 'visible', 'boolean', 'Misccate', 'zh_cn', '1', 9, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '1', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'rel', '1', 'rel', 'string', 'Misccate', 'zh_cn', '60', 8, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'target', '1', 'target', 'string', 'Misccate', 'zh_cn', '60', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'link', '1', 'link', 'string', 'Misccate', 'zh_cn', '60', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'sort', '1', 'sort', 'integer', 'Misccate', 'zh_cn', '11', 5, '0', '0', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'left', '1', '树左节点', 'integer', 'Misccate', 'zh_cn', '11', 4, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'right', '1', '树右节点', 'integer', 'Misccate', 'zh_cn', '11', 3, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Misccate', 'zh_cn', NULL, 2, '1', '0', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Misccate', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'model', '1', '所在模块', 'string', 'Misccate', 'zh_cn', '30', NULL, '1', '1', 'Modelextend', 'name', 'cname', NULL, '1', '', '0', '', '', '', 'select', '', '1', '', NULL, '', 'none', '', 0, '2011-06-08 21:35:04', '2011-06-08 21:35:04', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Misccate', '分类杂项', 'onetomany', 'default', '', 26, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_misccates', NULL, NULL, NULL, '0', 0, 0);



