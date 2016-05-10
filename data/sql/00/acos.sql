DROP TABLE IF EXISTS `cake_acos`;
CREATE TABLE IF NOT EXISTS `cake_acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT '0',
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT '0',
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT '0',
  `rght` int(10) DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Aco', 'zh_cn', '10', 8, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'parent_id', '1', '上级编号', 'integer', 'Aco', 'zh_cn', '10', 6, '1', '1', 'Aco', 'id', 'name', NULL, '1', '', '0', '', '', '', 'select', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'model', '1', 'model', 'string', 'Aco', 'zh_cn', '255', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'foreign_key', '1', '外键', 'integer', 'Aco', 'zh_cn', '10', 4, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'alias', '1', 'alias', 'string', 'Aco', 'zh_cn', '255', 3, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'lft', '1', '树左节点', 'integer', 'Aco', 'zh_cn', '10', 2, '0', '0', '', '', '', NULL, '1', '', '0', '', '', '', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'rght', '1', '树右节点', 'integer', 'Aco', 'zh_cn', '10', 1, '0', '0', '', '', '', NULL, '1', '', '0', '', '', '', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'name', '1', '权限名称', 'string', 'Aco', 'zh_cn', '100', 7, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2013-05-18 09:26:18', '2013-05-18 09:26:18', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Aco', '权限操作', 'onetomany', 'default', '', 27, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_acos', '', '', '', '0', 100, 0);



