DROP TABLE IF EXISTS `cake_templates`;
CREATE TABLE IF NOT EXISTS `cake_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `creator` bigint(13) DEFAULT '0',
  `content` text,
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `relatepath` varchar(60) DEFAULT NULL,
  `theme` varchar(60) DEFAULT NULL,
  `appname` varchar(60) DEFAULT NULL,
  `foldername` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `relatepath` (`relatepath`,`theme`,`appname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Template', 'zh_cn', '11', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-06-25 13:48:06', '2011-06-25 13:48:06', NULL),
(NULL, 'name', '1', 'name', 'string', 'Template', 'zh_cn', '200', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-06-25 13:48:06', '2011-06-25 13:48:06', NULL),
(NULL, 'creator', '1', 'creator', 'integer', 'Template', 'zh_cn', '13', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-06-25 13:48:06', '2011-06-25 13:48:06', NULL),
(NULL, 'content', '1', '内容', 'text', 'Template', 'zh_cn', NULL, 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-06-25 13:48:06', '2011-06-25 13:48:06', NULL),
(NULL, 'deleted', '1', '是否删除', 'boolean', 'Template', 'zh_cn', '1', 0, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-06-25 13:48:06', '2011-06-25 13:48:06', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Template', 'zh_cn', NULL, 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-06-25 13:48:06', '2011-06-25 13:48:06', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Template', 'zh_cn', NULL, 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-06-25 13:48:06', '2011-06-25 13:48:06', NULL),
(NULL, 'relatepath', '1', '相对路径', 'string', 'Template', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-17 09:03:51', '2011-07-17 09:03:51', NULL),
(NULL, 'theme', '1', '主题', 'string', 'Template', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-17 09:05:05', '2011-07-17 09:05:05', NULL),
(NULL, 'appname', '1', 'app目录名', 'string', 'Template', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-17 09:21:02', '2011-07-17 09:21:02', NULL),
(NULL, 'foldername', '1', '模块模板目录名', 'string', 'Template', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-17 13:07:29', '2011-07-17 13:07:29', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Template', 'Template', 'onetomany', 'default', NULL, 1, '2011-06-25 09:04:31', '2011-06-25 09:04:31', 'cake_templates', NULL, NULL, NULL, '0', 0, 0);
