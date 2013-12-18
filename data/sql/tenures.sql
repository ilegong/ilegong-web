DROP TABLE IF EXISTS `cake_tenures`;
CREATE TABLE IF NOT EXISTS `cake_tenures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `closure_time` datetime DEFAULT NULL,
  `organize_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Tenure', 'zh_cn', '11', 10, '0', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'hidden', '', '0', '', '', '', '', '', 0, '0000-00-00 00:00:00', '2010-08-29 18:48:55', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'Tenure', 'zh_cn', '11', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Tenure', 'zh_cn', '11', 3, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Tenure', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Tenure', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'closure_time', '1', '任职结束时间', 'datetime', 'Tenure', 'zh_cn', NULL, 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'datetime', '', '1', '', NULL, '', '', '', 0, '2010-08-29 20:14:16', '2010-08-29 20:14:16', NULL),
(NULL, 'position_id', '1', '担任职位', 'integer', 'Tenure', 'zh_cn', '11', 7, '1', '1', 'Position', 'id', 'name', NULL, '0', '', '1', 'organize_id', 'organize_id', 'equal', 'select', '', '0', '', '', 'var txt = $(\"#TenurePositionId\").find(\"option:selected\").text(); \ntxt = txt.replace(/_/g,\'\');\n$(\"#TenurePositionName\").val(txt);', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'start_time', '1', '任职开始时间', 'datetime', 'Tenure', 'zh_cn', NULL, 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'datetime', '{$now}', '1', '', NULL, '', '', '', 0, '2010-08-29 20:11:02', '2010-08-29 20:11:02', NULL),
(NULL, 'staff_id', '1', '员工编号', 'integer', 'Tenure', 'zh_cn', '11', 9, '1', '1', 'Staff', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '0', '', '', 'var txt = $(\"#TenureStaffId\").find(\"option:selected\").text(); \ntxt = txt.replace(/_/g,\'\');\n$(\"#TenureStaffName\").val(txt);', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'organize_id', '1', '所在部门', 'integer', 'Tenure', 'zh_cn', '11', 8, '1', '1', 'Organization', 'id', 'name', NULL, '1', '', '0', '', '', '', 'select', '', '0', '', NULL, '', '', '', 0, '2010-08-29 20:22:10', '2010-08-29 20:22:10', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Tenure', '任职', 'onetomany', 'default', '', 26, '2010-08-03 10:44:31', '2010-08-03 10:44:31', 'cake_tenures', NULL, NULL, NULL, '0', 0, 0);
