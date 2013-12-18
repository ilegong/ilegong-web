DROP TABLE IF EXISTS `cake_taskings`;
CREATE TABLE IF NOT EXISTS `cake_taskings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `organize_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_id` (`task_id`,`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Tasking', 'zh_cn', '11', 8, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'Tasking', 'zh_cn', '11', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Tasking', 'zh_cn', '11', 3, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Tasking', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Tasking', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'task_id', '1', '任务id', 'integer', 'Tasking', 'zh_cn', NULL, 7, '1', '1', 'Task', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '0', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'staff_id', '1', '职员id', 'integer', 'Tasking', 'zh_cn', '11', 5, '1', '1', 'Tenure', 'staff_id', 'staff_id', NULL, '1', '', '1', 'organize_id', 'organize_id', 'equal', 'checkbox', '', '0', '', '', '', 'explode', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'organize_id', '1', '职员部门', 'string', 'Tasking', 'zh_cn', '100', 6, '1', '1', 'Organization', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '0', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Tasking', '任务分配', 'onetomany', 'default', '<id>', 1, '2010-08-06 15:51:35', '2010-08-06 15:51:35', 'cake_taskings', NULL, NULL, NULL, '0', 0, 0);
