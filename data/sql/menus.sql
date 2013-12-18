DROP TABLE IF EXISTS `cake_menus`;
CREATE TABLE IF NOT EXISTS `cake_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT '0',
  `name` varchar(40) DEFAULT NULL,
  `slug` varchar(60) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '0',
  `rel` varchar(60) DEFAULT NULL,
  `target` varchar(60) DEFAULT NULL,
  `link` varchar(60) DEFAULT NULL,
  `left` int(11) DEFAULT '0',
  `right` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `locale` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Menu', 'zh_cn', '11', 13, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'parent_id', '1', '上级菜单', 'integer', 'Menu', 'zh_cn', '11', 12, '1', '1', 'Menu', 'id', 'name', NULL, '1', '', '0', '', '', 'treenode', 'select', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'name', '1', '标题', 'string', 'Menu', 'zh_cn', '40', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'slug', '1', '菜单标识符', 'string', 'Menu', 'zh_cn', '60', 10, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', '', '', '1', '', '', '', '', '菜单项名字可能变化，保持标识符不变。标识符可在程序中使用，通过标识符来确定分类及上下级关系', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'visible', '1', '是否可见', 'integer', 'Menu', 'zh_cn', '1', 9, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', '', '1', '1', '', '', '', '', '不可见时，界面上将隐藏无法看到', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'rel', '1', 'rel', 'string', 'Menu', 'zh_cn', '60', 8, '1', '1', '', '', '', NULL, '1', '=>选择\r\najaxAction=>ajax调用', '0', '', '', '', 'select', '', '1', '', '', '', '', '为ajaxAction时，调用ajaxAction处理，如清除缓存的操作', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'target', '1', 'target', 'string', 'Menu', 'zh_cn', '60', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'link', '1', '菜单链接', 'string', 'Menu', 'zh_cn', '60', 6, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'left', '1', '树左节点', 'integer', 'Menu', 'zh_cn', '11', 4, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'right', '1', '树右节点', 'integer', 'Menu', 'zh_cn', '11', 3, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Menu', 'zh_cn', NULL, 2, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Menu', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Menu', 'zh_cn', '1', NULL, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-12-05 12:06:00', '2010-12-05 12:06:00', NULL),
(NULL, 'locale', '1', '语言类型', 'string', 'Menu', 'zh_cn', '10', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', 'zh_cn', '1', '', NULL, '', '', '', 0, '2011-03-13 22:58:39', '2011-03-13 22:58:39', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Menu', '菜单', 'onetomany', 'tree', '', 26, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_menus', '', '', '', '0', NULL, 1);



