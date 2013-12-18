DROP TABLE IF EXISTS `cake_taskexecutes`;
CREATE TABLE IF NOT EXISTS `cake_taskexecutes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `executer_id` int(11) DEFAULT NULL,
  `executer_name` varchar(60) DEFAULT NULL,
  `achieve_num` varchar(20) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(60) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Taskexecute', 'zh_cn', '11', 12, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'Taskexecute', 'zh_cn', '11', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Taskexecute', 'zh_cn', '11', 3, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Taskexecute', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Taskexecute', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'executer_id', '1', '执行人编号', 'integer', 'Taskexecute', 'zh_cn', NULL, 11, '1', '1', 'Staff', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '0', '', '', 'var txt = $(\"#TaskexecuteExecuterId\").find(\"option:selected\").text(); \ntxt = txt.replace(/_/g,\'\');\nif($(\"#TaskexecuteExecuterId\").val()!=\"\")\n{\n    $(\"#TaskexecuteExecuterName\").val(txt);\n}\nelse\n{\n    $(\"#TaskexecuteExecuterName\").val(\"\");\n}', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'executer_name', '1', '执行人员姓名', 'string', 'Taskexecute', 'zh_cn', '60', 10, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'hidden', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'achieve_num', '1', '完成数', 'string', 'Taskexecute', 'zh_cn', '20', 8, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'task_id', '1', '具体参与任务', 'integer', 'Taskexecute', 'zh_cn', NULL, 9, '1', '1', 'Tasking', 'task_id', 'task_id', NULL, '0', '', '1', 'executer_id', 'staff_id', 'equal', 'select', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'customer_id', '1', '相关客户id', 'integer', 'Taskexecute', 'zh_cn', NULL, 7, '1', '1', 'Customer', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '1', '', '', 'var txt = $(\"#TaskexecuteCustomerId\").find(\"option:selected\").text(); \ntxt = txt.replace(/_/g,\'\');\nif($(\"#TaskexecuteCustomerId\").val()!=\"\")\n{\n    $(\"#TaskexecuteCustomerName\").val(txt);\n}\nelse\n{\n    $(\"#TaskexecuteCustomerName\").val(\"\");\n}', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'customer_name', '1', '客户姓名', 'string', 'Taskexecute', 'zh_cn', '60', 6, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'hidden', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'content', '1', '内容', 'content', 'Taskexecute', 'zh_cn', NULL, 5, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Taskexecute', '任务执行记录', 'onetomany', 'default', '<id>', 1, '2010-08-08 19:22:40', '2010-08-08 19:22:40', 'cake_taskexecutes', NULL, NULL, NULL, '0', 0, 0);
