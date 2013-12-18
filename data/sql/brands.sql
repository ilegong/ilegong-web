DROP TABLE IF EXISTS `cake_brands`;
CREATE TABLE IF NOT EXISTS `cake_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `creator` bigint(11) DEFAULT '0',
  `lastupdator` int(13) DEFAULT '0',
  `website` varchar(200) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Brand', 'zh_cn', '11', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', NULL),
(NULL, 'name', '1', 'name', 'string', 'Brand', 'zh_cn', '200', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', NULL),
(NULL, 'creator', '1', 'creator', 'integer', 'Brand', 'zh_cn', '11', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '0', '1', '', NULL, '', '', '', 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', ''),
(NULL, 'lastupdator', '1', 'lastupdator', 'integer', 'Brand', 'zh_cn', '13', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', NULL),
(NULL, 'website', '1', 'website', 'string', 'Brand', 'zh_cn', '200', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', NULL),
(NULL, 'status', '1', 'status', 'integer', 'Brand', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', NULL),
(NULL, 'published', '1', '是否发布', 'boolean', 'Brand', 'zh_cn', '1', 4, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', NULL),
(NULL, 'deleted', '1', '是否删除', 'boolean', 'Brand', 'zh_cn', '1', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-03-25 15:15:46', '2011-03-25 15:15:46', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Brand', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-03-25 15:15:47', '2011-03-25 15:15:47', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Brand', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-03-25 15:15:47', '2011-03-25 15:15:47', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Brand', '产品品牌', '', 'default', '', 27, '2011-03-25 15:07:49', '2011-03-25 15:07:49', 'cake_brands', '', '', '', '0', 0, 0);
