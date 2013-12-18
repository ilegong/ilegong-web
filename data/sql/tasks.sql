DROP TABLE IF EXISTS `cake_tasks`;
CREATE TABLE IF NOT EXISTS `cake_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `content` text,
  `starttime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `finishcondition` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Task', 'zh_cn', '11', 11, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'Task', 'zh_cn', '11', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', '', '0', '', '', 'treenode', 'select', '0', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Task', 'zh_cn', '11', 3, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Task', 'zh_cn', NULL, 2, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Task', 'zh_cn', NULL, 1, '0', '0', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '任务名称', 'string', 'Task', 'zh_cn', '200', 10, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'content', '1', '内容', 'content', 'Task', 'zh_cn', NULL, 7, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'starttime', '1', '任务开始时间', 'datetime', 'Task', 'zh_cn', NULL, 6, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'endtime', '1', '结束时间', 'datetime', 'Task', 'zh_cn', NULL, 5, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'quantity', '1', '任务数量', 'integer', 'Task', 'zh_cn', NULL, 9, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'finishcondition', '1', '完成条件', 'string', 'Task', 'zh_cn', '100', 8, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Task', '任务', 'onetomany', 'default', '<id>', 1, '2010-08-06 16:01:00', '2010-08-06 16:01:00', 'cake_tasks', NULL, NULL, NULL, '0', 0, 0);
