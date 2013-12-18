DROP TABLE IF EXISTS `cake_notes`;
CREATE TABLE IF NOT EXISTS `cake_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `cate_id` int(11) DEFAULT '0',
  `creator` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'content', '1', '内容', 'content', 'Note', NULL, '', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'textarea', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-20 22:40:01', '2012-10-20 22:40:01', NULL),
(NULL, 'id', '1', '编号', 'integer', 'Note', NULL, '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL),
(NULL, 'name', '1', '名称', 'string', 'Note', NULL, '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Note', NULL, '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'Note', NULL, '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Note', NULL, '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Note', NULL, '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Note', NULL, NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Note', NULL, NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2012-10-04 08:51:57', '2012-10-04 08:51:57', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Note', '记事本', '', 'default', '', 27, '2012-10-04 08:51:57', '2012-10-04 08:51:57', 'cake_notes', '', 'self', '', '0', NULL, NULL);
