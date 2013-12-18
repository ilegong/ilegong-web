DROP TABLE IF EXISTS `cake_user_cates`;
CREATE TABLE IF NOT EXISTS `cake_user_cates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `coverimg` varchar(200) DEFAULT '',
  `cate_id` int(11) DEFAULT '0',
  `creator` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `model` varchar(60) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `left` int(10) DEFAULT '0',
  `right` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'UserCate', NULL, '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'name', '1', '名称', 'string', 'UserCate', NULL, '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'coverimg', '1', '封面图片', 'string', 'UserCate', NULL, '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'UserCate', NULL, '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'UserCate', NULL, '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'UserCate', NULL, '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'UserCate', NULL, '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'UserCate', NULL, NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'UserCate', NULL, NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 09:33:03', '2012-10-04 09:33:03', NULL),
(NULL, 'model', '1', '所属模块', 'string', 'UserCate', NULL, '60', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 13:50:37', '2012-10-04 13:50:37', NULL),
(NULL, 'parent_id', '1', '上级分类', 'integer', 'UserCate', NULL, '', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 13:50:37', '2012-10-04 13:50:37', NULL),
(NULL, 'left', '1', '树左节点', 'integer', 'UserCate', NULL, '', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 13:50:37', '2012-10-04 13:50:37', NULL),
(NULL, 'right', '1', '树右节点', 'integer', 'UserCate', NULL, '', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 13:50:37', '2012-10-04 13:50:37', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'UserCate', '用户分类', '', 'default', '', 27, '2012-10-04 09:33:03', '2012-10-04 09:33:03', 'cake_user_cates', '', '', '', '0', NULL, NULL);
