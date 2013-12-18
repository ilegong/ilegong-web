DROP TABLE IF EXISTS `cake_appraiseresults`;
CREATE TABLE IF NOT EXISTS `cake_appraiseresults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `model` varchar(60) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `option_id` varchar(60) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Appraiseresult', 'zh_cn', '11', 11, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-12 20:03:10', '2010-09-12 20:03:10', NULL),
(NULL, 'name', '1', '名称', 'string', 'Appraiseresult', 'zh_cn', '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-12 20:03:10', '2010-09-12 20:03:10', NULL),
(NULL, 'status', '1', '数据状态', 'integer', 'Appraiseresult', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-12 20:03:10', '2010-09-12 20:03:10', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Appraiseresult', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-12 20:03:10', '2010-09-12 20:03:10', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Appraiseresult', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-12 20:03:10', '2010-09-12 20:03:10', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Appraiseresult', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-12 20:03:10', '2010-09-12 20:03:10', NULL),
(NULL, 'model', '1', '模块', 'string', 'Appraiseresult', 'zh_cn', '60', 10, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-09-12 20:04:01', '2010-09-12 20:04:01', NULL),
(NULL, 'data_id', '1', '数据编号', 'integer', 'Appraiseresult', 'zh_cn', '11', 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-09-12 20:04:33', '2010-09-12 20:04:33', NULL),
(NULL, 'option_id', '1', '选项编号', 'string', 'Appraiseresult', 'zh_cn', '60', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '选项编号不同于选项表中的编号。对多维评价表选项编号可能为 z-y-z。\n选项编号为字符串型，而非整型', 0, '2010-09-12 20:06:51', '2010-09-12 20:06:51', NULL),
(NULL, 'question_id', '1', '问题编号', 'integer', 'Appraiseresult', 'zh_cn', '11', 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-09-12 20:07:29', '2010-09-12 20:07:29', NULL),
(NULL, 'value', '1', '选项选择次数', 'integer', 'Appraiseresult', 'zh_cn', '11', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-09-12 20:08:06', '2010-09-12 20:08:06', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Appraiseresult', '评价结果', '', 'default', '', 27, '2010-09-12 20:03:10', '2010-09-12 20:03:10', 'cake_appraiseresults', '', '', '', '0', 0, 0);
