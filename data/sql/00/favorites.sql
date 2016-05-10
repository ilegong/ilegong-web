DROP TABLE IF EXISTS `cake_favorites`;
CREATE TABLE IF NOT EXISTS `cake_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `cate_id` int(11) DEFAULT '0',
  `creator` varchar(60) DEFAULT NULL,
  `lastupdator` int(13) DEFAULT '0',
  `remoteurl` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `model` varchar(60) DEFAULT NULL,
  `data_id` int(13) DEFAULT NULL,
  `creator_id` int(13) DEFAULT NULL,
  `weibo_id` int(13) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Favorite', 'zh_cn', '11', 14, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'name', '1', '名称', 'string', 'Favorite', 'zh_cn', '200', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Favorite', 'zh_cn', '11', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'creator', '1', '编创建者', 'string', 'Favorite', 'zh_cn', '60', 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Favorite', 'zh_cn', '11', 8, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'remoteurl', '1', '引用地址', 'string', 'Favorite', 'zh_cn', '200', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'status', '1', '状态', 'integer', 'Favorite', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Favorite', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Favorite', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Favorite', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Favorite', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-18 16:27:21', '2010-10-18 16:27:21', NULL),
(NULL, 'model', '1', '模块', 'string', 'Favorite', 'zh_cn', '60', 13, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-18 16:31:18', '2010-10-18 16:31:18', NULL),
(NULL, 'data_id', '1', '数据编号', 'integer', 'Favorite', 'zh_cn', '13', 12, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-18 16:31:39', '2010-10-18 16:31:39', NULL),
(NULL, 'creator_id', '1', '创建者id', 'integer', 'Favorite', 'zh_cn', '13', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-18 16:32:21', '2010-10-18 16:32:21', NULL),
(NULL, 'weibo_id', '1', '微博编号', 'integer', 'Favorite', 'zh_cn', '13', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-18 17:42:23', '2010-10-18 17:42:23', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Favorite', '收藏夹', '', 'default', '', 27, '2010-10-18 16:27:21', '2010-10-18 16:27:21', 'cake_favorites', '', '', '', '0', 0, 0);