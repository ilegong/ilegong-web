DROP TABLE IF EXISTS `cake_idioms`;
CREATE TABLE IF NOT EXISTS `cake_idioms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(2000) DEFAULT NULL,
  `cate_id` int(11) DEFAULT '0',
  `creator` bigint(13) DEFAULT '0',
  `lastupdator` bigint(13) DEFAULT '0',
  `remoteurl` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Idiom', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'name', '1', '名称', 'string', 'Idiom', 'zh_cn', '2000', 5, '1', '1', '', NULL, NULL, 0, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', NULL, '', '', '', 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Idiom', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'Idiom', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Idiom', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'remoteurl', '1', '引用地址', 'string', 'Idiom', 'zh_cn', '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'status', '1', '状态', 'integer', 'Idiom', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Idiom', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Idiom', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Idiom', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Idiom', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-11-06 20:48:44', '2010-11-06 20:48:44', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Idiom', '习语', '', 'default', '', 27, '2010-11-06 20:48:43', '2010-11-06 20:48:43', 'cake_idioms', '', '', '', '0', 0, 0);
