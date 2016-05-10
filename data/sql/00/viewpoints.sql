DROP TABLE IF EXISTS `cake_viewpoints`;
CREATE TABLE IF NOT EXISTS `cake_viewpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `cate_id` int(11) DEFAULT '0',
  `creator` bigint(13) DEFAULT '0',
  `lastupdator` bigint(13) DEFAULT '0',
  `remoteurl` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `model` varchar(60) DEFAULT NULL,
  `data_id` bigint(11) DEFAULT NULL,
  `support_nums` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Viewpoint', 'zh_cn', '11', 13, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'name', '1', '名称', 'string', 'Viewpoint', 'zh_cn', '200', 12, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Viewpoint', 'zh_cn', '11', 9, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'Viewpoint', 'zh_cn', '11', 8, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Viewpoint', 'zh_cn', '11', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'remoteurl', '1', '引用地址', 'string', 'Viewpoint', 'zh_cn', '200', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'status', '1', '状态', 'integer', 'Viewpoint', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Viewpoint', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Viewpoint', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Viewpoint', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Viewpoint', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:22:55', '2010-11-14 13:22:55', NULL),
(NULL, 'model', '1', '模块', 'string', 'Viewpoint', 'zh_cn', '60', 11, '1', '1', 'Modelextend', 'name', 'cname', NULL, '1', '', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-11-14 13:26:22', '2010-11-14 13:26:22', NULL),
(NULL, 'data_id', '1', '数据id', 'integer', 'Viewpoint', 'zh_cn', '11', 10, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-11-14 13:26:46', '2010-11-14 13:26:46', NULL),
(NULL, 'support_nums', '1', '支持人数', 'integer', 'Viewpoint', 'zh_cn', '1', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-11-14 15:08:45', '2010-11-14 15:08:45', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Viewpoint', '观点', '', 'default', '', 27, '2010-11-14 13:22:54', '2010-11-14 13:22:54', 'cake_viewpoints', '', '', '', '0', 0, 0);
