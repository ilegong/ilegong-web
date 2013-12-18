DROP TABLE IF EXISTS `cake_pointsupports`;
CREATE TABLE IF NOT EXISTS `cake_pointsupports` (
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
  `data_id` bigint(11) DEFAULT NULL,
  `model` varchar(60) DEFAULT NULL,
  `viewpoint_id` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Pointsupport', 'zh_cn', '11', 11, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'name', '1', '名称', 'string', 'Pointsupport', 'zh_cn', '200', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Pointsupport', 'zh_cn', '11', 9, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'Pointsupport', 'zh_cn', '11', 8, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Pointsupport', 'zh_cn', '11', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'remoteurl', '1', '引用地址', 'string', 'Pointsupport', 'zh_cn', '200', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'status', '1', '状态', 'integer', 'Pointsupport', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Pointsupport', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Pointsupport', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Pointsupport', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Pointsupport', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-14 13:35:50', '2010-11-14 13:35:50', NULL),
(NULL, 'data_id', '1', '模块数据编号', 'integer', 'Pointsupport', 'zh_cn', '11', 0, '1', '1', '', NULL, NULL, 0, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-11-14 13:47:56', '2010-11-14 13:47:56', NULL),
(NULL, 'model', '1', '模块', 'string', 'Pointsupport', 'zh_cn', '60', NULL, '1', '1', 'Modelextend', 'name', 'cname', NULL, '1', '', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2010-11-14 16:35:44', '2010-11-14 16:35:44', NULL),
(NULL, 'viewpoint_id', '1', '观点id', 'integer', 'Pointsupport', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-11-14 16:36:22', '2010-11-14 16:36:22', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Pointsupport', '观点支持', '', 'default', '', 27, '2010-11-14 13:35:50', '2010-11-14 13:35:50', 'cake_pointsupports', '', '', '', '0', 0, 0);
