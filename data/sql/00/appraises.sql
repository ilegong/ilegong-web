DROP TABLE IF EXISTS `cake_appraises`;
CREATE TABLE IF NOT EXISTS `cake_appraises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `cate_id` int(11) DEFAULT NULL,
  `questiontype` varchar(20) DEFAULT NULL,
  `columns` int(2) DEFAULT NULL,
  `is_require` tinyint(1) DEFAULT NULL,
  `minselect` int(2) DEFAULT NULL,
  `maxselect` int(2) DEFAULT NULL,
  `selectvalues` text,
  `published` tinyint(1) DEFAULT NULL,
  `weibo_id` int(13) DEFAULT NULL,
  `creator` varchar(60) DEFAULT NULL,
  `creator_id` int(13) DEFAULT NULL,
  `user_img` varchar(200) DEFAULT NULL,
  `favor_nums` int(11) DEFAULT NULL,
  `comment_nums` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Appraise', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 10:03:33', '2010-09-11 10:03:33', NULL),
(NULL, 'name', '1', '名称', 'string', 'Appraise', 'zh_cn', '200', 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '', '0', '', NULL, '', '', '', 0, '2010-09-11 10:03:33', '2010-09-11 10:03:33', NULL),
(NULL, 'status', '1', '数据状态', 'integer', 'Appraise', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 10:03:33', '2010-09-11 10:03:33', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Appraise', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 10:03:33', '2010-09-11 10:03:33', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Appraise', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 10:03:33', '2010-09-11 10:03:33', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Appraise', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-11 10:03:33', '2010-09-11 10:03:33', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Appraise', 'zh_cn', NULL, NULL, '1', '1', 'Misccate', 'id', 'name', 30, '1', '', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-09-11 10:18:22', '2010-09-11 10:18:22', NULL),
(NULL, 'questiontype', '1', '问题类型', 'string', 'Appraise', 'zh_cn', '20', NULL, '1', '1', '', NULL, NULL, NULL, '1', 'input=>填空\ncheckbox=>多选\nradio=>单选\nselect=>下拉选择\ntextarea=>问答', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-09-11 10:21:07', '2010-09-11 10:21:07', NULL),
(NULL, 'columns', '1', '每行显示列数', 'integer', 'Appraise', 'zh_cn', '2', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '1', '1', 'numeric', NULL, '', '', '', 0, '2010-09-11 10:23:53', '2010-09-11 10:23:53', NULL),
(NULL, 'is_require', '1', '是否必填', 'integer', 'Appraise', 'zh_cn', '1', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '1', '1', '', NULL, '', '', '', 0, '2010-09-11 10:25:17', '2010-09-11 10:25:17', NULL),
(NULL, 'minselect', '1', '最少选择项', 'integer', 'Appraise', 'zh_cn', '2', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '0', '1', '', NULL, '', '', '', 0, '2010-09-11 10:26:18', '2010-09-11 10:26:18', NULL),
(NULL, 'maxselect', '1', '最多选择型', 'integer', 'Appraise', 'zh_cn', '2', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '0', '1', 'numeric', NULL, '', '', '', 0, '2010-09-11 10:26:58', '2010-09-11 10:26:58', NULL),
(NULL, 'selectvalues', '1', '下拉选择值', 'content', 'Appraise', 'zh_cn', NULL, NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'textarea', '', '1', '', NULL, '', '', '', 0, '2010-09-11 10:34:50', '2010-09-11 10:34:50', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Appraise', 'zh_cn', '1', NULL, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-10-23 21:22:02', '2010-10-23 21:22:02', NULL),
(NULL, 'weibo_id', '1', '微博编号', 'integer', 'Appraise', 'zh_cn', '13', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-23 22:35:09', '2010-10-23 22:35:09', NULL),
(NULL, 'creator', '1', '创建人', 'string', 'Appraise', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-23 22:36:07', '2010-10-23 22:36:07', NULL),
(NULL, 'creator_id', '1', '创建者id', 'integer', 'Appraise', 'zh_cn', '13', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-23 22:36:28', '2010-10-23 22:36:28', NULL),
(NULL, 'user_img', '1', '用户头像', 'string', 'Appraise', 'zh_cn', '200', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-23 22:37:08', '2010-10-23 22:37:08', NULL),
(NULL, 'favor_nums', '1', '收藏数', 'integer', 'Appraise', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-23 23:02:12', '2010-10-23 23:02:12', NULL),
(NULL, 'comment_nums', '1', '参与评论数', 'integer', 'Appraise', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-23 23:02:37', '2010-10-23 23:02:37', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Appraise', '评价', '', 'default', '', 27, '2010-09-11 10:03:33', '2010-09-11 10:03:33', 'cake_appraises', '', 'branch', '', '0', 0, 0);



REPLACE INTO `cake_appraises` (`id`, `name`, `status`, `deleted`, `created`, `updated`, `cate_id`, `questiontype`, `columns`, `is_require`, `minselect`, `maxselect`, `selectvalues`, `published`, `weibo_id`, `creator`, `creator_id`, `user_img`, `favor_nums`, `comment_nums`) VALUES (1, '数据评价记录表[系统使用，勿删]', 0, 0, '2010-09-12 20:13:42', '2010-09-12 20:45:23', NULL, 'radio', 1, '0', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '数据Digg[系统使用，勿删]', 0, 0, '2010-09-12 20:46:19', '2010-09-12 20:46:19', NULL, 'radio', 1, '0', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '请选择您看到这篇内容时的心情', 0, 0, '2010-09-24 13:35:44', '2010-09-24 14:59:01', NULL, 'radio', 1, '0', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '早上几点起床', 0, 0, '2010-10-25 11:49:37', '2010-10-25 11:49:37', NULL, 'checkbox', 1, NULL, 1, 0, NULL, '1', 2147483647, '微博点名', 1835369353, 'http://tp2.sinaimg.cn/1835369353/50/0', NULL, NULL),
(17, 'test', 0, 0, '2010-10-25 11:57:57', '2010-10-25 11:57:57', NULL, 'checkbox', 1, NULL, 1, 0, NULL, '1', 2147483647, '微博点名', 1835369353, 'http://tp2.sinaimg.cn/1835369353/50/0', NULL, NULL);
