DROP TABLE IF EXISTS `cake_appraiseoptions`;
CREATE TABLE IF NOT EXISTS `cake_appraiseoptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `qid` int(11) DEFAULT NULL,
  `withinput` tinyint(1) DEFAULT NULL,
  `optiontype` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Appraiseoption', 'zh_cn', '11', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 17:28:01', '2010-09-11 17:28:01', NULL),
(NULL, 'name', '1', '名称', 'string', 'Appraiseoption', 'zh_cn', '200', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 17:28:01', '2010-09-11 17:28:01', NULL),
(NULL, 'status', '1', '数据状态', 'integer', 'Appraiseoption', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 17:28:01', '2010-09-11 17:28:01', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Appraiseoption', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 17:28:01', '2010-09-11 17:28:01', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Appraiseoption', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 17:28:01', '2010-09-11 17:28:01', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Appraiseoption', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 17:28:01', '2010-09-11 17:28:01', NULL),
(NULL, 'qid', '1', '问题编号', 'integer', 'Appraiseoption', 'zh_cn', '11', 5, '1', '1', 'Appraise', 'id', 'name', NULL, '1', '', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-09-11 17:29:32', '2010-09-11 17:29:32', NULL),
(NULL, 'withinput', '1', '带文本框', 'integer', 'Appraiseoption', 'zh_cn', '1', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2010-09-11 17:49:28', '2010-09-11 17:49:28', NULL),
(NULL, 'optiontype', '1', '选项类型', 'string', 'Appraiseoption', 'zh_cn', '10', NULL, '1', '1', '', NULL, NULL, NULL, '1', 'x=>x\ny=>y\nz=>z', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-09-11 17:50:39', '2010-09-11 17:50:39', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Appraiseoption', '评价选项', '', 'default', '', 27, '2010-09-11 17:28:00', '2010-09-11 17:28:00', 'cake_appraiseoptions', '', '', '', '0', 0, 0);



REPLACE INTO `cake_appraiseoptions` (`id`, `name`, `status`, `deleted`, `created`, `updated`, `qid`, `withinput`, `optiontype`) VALUES (1, '好评', 0, 0, '2010-09-12 20:13:42', '2010-09-12 20:45:23', 1, '0', 'x'),
(2, '中评', 0, 0, '2010-09-12 20:13:42', '2010-09-12 20:45:23', 1, '0', 'x'),
(3, '差评', 0, 0, '2010-09-12 20:13:42', '2010-09-12 20:45:23', 1, '0', 'x'),
(4, '顶上去', 0, 0, '2010-09-12 20:46:19', '2010-09-12 20:46:19', 2, '0', 'x'),
(5, '踩下去', 0, 0, '2010-09-12 20:46:19', '2010-09-12 20:46:19', 2, '0', 'x'),
(6, '感动', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', 3, '0', 'x'),
(7, '同情', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', 3, '0', 'x'),
(8, '无聊', 0, 1, '2010-09-24 13:35:44', '2010-10-10 23:15:47', 3, '0', 'x'),
(9, '愤怒', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', 3, '0', 'x'),
(10, '搞笑', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', 3, '0', 'x'),
(11, '难过', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', 3, '0', 'x'),
(12, '高兴', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', 3, '0', 'x'),
(13, '打酱油路过', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', 3, '0', 'x'),
(14, '相见恨晚', 0, 0, '2010-09-24 14:59:01', '2010-09-24 14:59:01', 3, NULL, 'x'),
(69, '6点', 0, 0, '2010-10-25 11:49:37', '2010-10-25 11:49:37', 16, '0', 'x'),
(70, '7点', 0, 0, '2010-10-25 11:49:37', '2010-10-25 11:49:37', 16, '0', 'x'),
(71, '8点', 0, 0, '2010-10-25 11:49:37', '2010-10-25 11:49:37', 16, NULL, 'x'),
(72, '[空白选项]', 0, 0, '2010-10-25 11:57:57', '2010-10-25 11:57:57', 17, '0', 'x'),
(73, '[空白选项]', 0, 0, '2010-10-25 11:57:57', '2010-10-25 11:57:57', 17, '0', 'x');
