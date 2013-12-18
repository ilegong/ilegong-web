DROP TABLE IF EXISTS `cake_advertises`;
CREATE TABLE IF NOT EXISTS `cake_advertises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `cate_id` int(11) DEFAULT '0',
  `creator` int(13) DEFAULT '0',
  `lastupdator` int(13) DEFAULT '0',
  `remoteurl` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `advertise_url` varchar(300) DEFAULT NULL,
  `content` text,
  `adwidth` smallint(3) DEFAULT '0',
  `adheight` smallint(3) DEFAULT '0',
  `photo` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Advertise', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'name', '1', '名称', 'string', 'Advertise', 'zh_cn', '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Advertise', 'zh_cn', '11', 6, '1', '1', 'Misccate', 'id', 'name', 34, '1', '', '0', '', '', 'equal', 'select', '', '1', '', NULL, '', '', '', 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'Advertise', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Advertise', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'remoteurl', '1', '引用地址', 'string', 'Advertise', 'zh_cn', '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 1, '2011-05-22 18:27:59', '2011-05-22 18:29:26', NULL),
(NULL, 'status', '1', '状态', 'integer', 'Advertise', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 1, '2011-05-22 18:27:59', '2011-05-22 19:13:54', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Advertise', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Advertise', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Advertise', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Advertise', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-05-22 18:27:59', '2011-05-22 18:27:59', NULL),
(NULL, 'advertise_url', '1', '广告链接', 'string', 'Advertise', 'zh_cn', '300', 3, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', NULL, '', '', '', 0, '2011-05-22 18:31:51', '2011-05-22 18:31:51', NULL),
(NULL, 'content', '1', '内容', 'content', 'Advertise', 'zh_cn', '65535', NULL, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'textarea', '', '1', '', NULL, '', '', '', 0, '2011-05-22 19:20:48', '2011-05-22 19:20:48', ''),
(NULL, 'adwidth', '1', '广告(图片)宽度', 'integer', 'Advertise', 'zh_cn', '3', NULL, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-05-22 21:23:44', '2011-05-22 21:23:44', ''),
(NULL, 'adheight', '1', '广告(图片)高度', 'integer', 'Advertise', 'zh_cn', '3', NULL, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2012-12-27 23:37:23', '2012-12-27 23:37:23', ''),
(NULL, 'photo', '1', '广告图片', 'string', 'Advertise', 'zh_cn', '200', NULL, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'file', '', '1', '', NULL, '', '', '', 0, '2012-12-27 23:38:19', '2012-12-27 23:38:19', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Advertise', '广告', '', 'default', '', 27, '2011-05-22 18:27:59', '2011-05-22 18:27:59', 'cake_advertises', '', '', '', '0', NULL, 0);



REPLACE INTO `cake_advertises` (`id`, `name`, `cate_id`, `creator`, `lastupdator`, `remoteurl`, `status`, `published`, `deleted`, `created`, `updated`, `advertise_url`, `content`, `adwidth`, `adheight`, `photo`) VALUES (1, 'Google文本广告 250*250', 35, NULL, NULL, '', 0, '1', '0', '2011-05-22 21:23:52', '2011-05-22 21:23:52', '', '<script type=\"text/javascript\">\ngoogle_ad_client = \"pub-1393746648850099\";\ngoogle_ad_slot = \"8668539340\";\ngoogle_ad_width = 250;\ngoogle_ad_height = 250;\n</script>\n<script type=\"text/javascript\" src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">\n</script>', 250, 0, NULL),
(3, 'Google文本广告 468*60', 35, NULL, NULL, '', 0, '1', '0', '2011-05-26 20:14:40', '2011-05-26 20:15:45', '', '<script type=\"text/javascript\">\r\n	    <!--\r\ngoogle_ad_client = \"pub-1393746648850099\";\r\n/* 468x60, 创建于 10-4-18 */\r\ngoogle_ad_slot = \"1851151709\";\r\ngoogle_ad_width = 468;\r\ngoogle_ad_height = 60;\r\n//-->\r\n</script>\r\n<script type=\"text/javascript\"\r\nsrc=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">\r\n</script>', 468, 0, NULL),
(4, 'Google广告 600*160', 35, NULL, NULL, '', 0, '1', '0', '2011-06-26 19:26:10', '2011-06-26 19:27:32', '', '<script type=\"text/javascript\">\r\n		<!--\r\ngoogle_ad_client = \"pub-1393746648850099\";\r\n/* 160x600, 创建于 10-4-18 */\r\ngoogle_ad_slot = \"7338009172\";\r\ngoogle_ad_width = 160;\r\ngoogle_ad_height = 600;\r\n//-->\r\n</script>\r\n<script type=\"text/javascript\"\r\nsrc=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">\r\n</script>', 600, 0, NULL);
