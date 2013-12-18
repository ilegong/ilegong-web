DROP TABLE IF EXISTS `cake_photos`;
CREATE TABLE IF NOT EXISTS `cake_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` bigint(11) DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `coverimg` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `authorname` varchar(200) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `thumbphoto` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `seotitle` varchar(255) DEFAULT NULL,
  `seodescription` varchar(255) DEFAULT NULL,
  `seokeywords` varchar(255) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `content` text,
  `remoteurl` varchar(255) DEFAULT NULL,
  `status` int(3) DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `comment_status` tinyint(1) DEFAULT '0',
  `comment_count` int(11) DEFAULT NULL,
  `views_count` bigint(11) DEFAULT '0',
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `crawl_id` bigint(11) DEFAULT '0',
  `favor_nums` bigint(11) DEFAULT '0',
  `point_nums` bigint(11) DEFAULT '0',
  `cate_id` int(10) DEFAULT '0',
  `locale` char(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Photo', 'zh_cn', '11', 30, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'creator', '1', '创建人', 'integer', 'Photo', 'zh_cn', '11', 27, '1', '1', 'Staff', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '标题', 'string', 'Photo', 'zh_cn', '255', 26, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'coverimg', '1', '标题图片', 'string', 'Photo', 'zh_cn', '255', 29, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'coverimg', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'subtitle', '1', '副标题', 'string', 'Photo', 'zh_cn', '255', 25, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'slug', '1', '链接文字', 'string', 'Photo', 'zh_cn', '255', 24, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'authorname', '1', '作者', 'string', 'Photo', 'zh_cn', '200', 23, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'origin', '1', '来源', 'string', 'Photo', 'zh_cn', '255', 22, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'thumbphoto', '1', 'thumbphoto', 'string', 'Photo', 'zh_cn', '255', 21, '0', '1', '', NULL, NULL, 0, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'photo', '0', '文章图片', 'string', 'Photo', 'zh_cn', '255', 20, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'file', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'keywords', '1', '关键字', 'string', 'Photo', 'zh_cn', '255', 19, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'seotitle', '1', 'SEO页面标题', 'string', 'Photo', 'zh_cn', '255', 18, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'seodescription', '1', 'SEO页面描述', 'string', 'Photo', 'zh_cn', '255', 17, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'seokeywords', '1', 'SEO页面关键字', 'string', 'Photo', 'zh_cn', '255', 16, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'summary', '1', '摘要', 'string', 'Photo', 'zh_cn', '500', 15, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'content', '1', '内容', 'content', 'Photo', 'zh_cn', NULL, 14, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'ckeditor', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'remoteurl', '1', '采集源地址', 'string', 'Photo', 'zh_cn', '255', 12, '1', '1', '', NULL, NULL, 0, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'status', '1', '发布状态', 'integer', 'Photo', 'zh_cn', '3', 8, '1', '1', 'Misccate', 'id', 'name', 25, '1', '', '0', '', '', 'treenode', 'radio', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'comment_status', '1', '是否允许评论', 'integer', 'Photo', 'zh_cn', '1', 11, '1', '1', '', NULL, NULL, 0, '1', '', '0', '', '', 'equal', '', '1', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'comment_count', '1', '总评论数', 'integer', 'Photo', 'zh_cn', '11', 10, '0', '0', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'views_count', '1', '阅读次数', 'integer', 'Photo', 'zh_cn', '11', 9, '0', '1', '', NULL, NULL, 0, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Photo', 'zh_cn', NULL, 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'created', '1', '创建时间', 'datetime', 'Photo', 'zh_cn', NULL, 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'deleted', '1', '是否删除', '', 'Photo', 'zh_cn', '1', 5, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', '', '', 'equal', 'select', '0', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'published', '1', '是否发布', '', 'Photo', 'zh_cn', '1', 4, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', '', '', 'equal', 'select', '0', '1', '', NULL, '', '', '', 0, '2010-09-05 17:19:14', '2010-09-05 17:19:14', ''),
(NULL, 'crawl_id', '1', '采集规则id', 'integer', 'Photo', 'zh_cn', '11', 13, '0', '1', '', NULL, NULL, 0, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-12 18:08:41', '2010-10-12 18:08:41', NULL),
(NULL, 'favor_nums', '1', '分享次数', 'integer', 'Photo', 'zh_cn', '11', 2, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-11-13 23:01:50', '2010-11-13 23:01:50', ''),
(NULL, 'point_nums', '1', '发表观点人数', 'integer', 'Photo', 'zh_cn', '11', 3, '1', '1', '', NULL, NULL, 0, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-11-14 15:10:59', '2010-11-14 15:10:59', ''),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Photo', 'zh_cn', '', 28, '1', '1', 'Category', 'id', 'name', NULL, '1', '', '0', '', '', 'treenode', 'select', '', '1', '', NULL, '', '', '', 0, '2012-01-29 17:45:14', '2012-01-29 17:45:14', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <conditions>\r\n        <Category.model>Photo</Category.model>\r\n    </conditions>\r\n    <order>created desc</order>\r\n</options>'),
(NULL, 'locale', '1', '语言类型', 'char', 'Photo', 'zh_cn', '5', 1, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2012-08-18 22:24:01', '2012-08-18 22:24:01', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Photo', '图片', 'onetomany', 'default', '', 26, '2012-08-31 16:20:25', '2012-08-31 16:20:25', 'cake_photos', '', '', '', '0', 1, 2);



REPLACE INTO `cake_photos` (`id`, `creator`, `name`, `coverimg`, `subtitle`, `slug`, `authorname`, `origin`, `thumbphoto`, `keywords`, `seotitle`, `seodescription`, `seokeywords`, `summary`, `content`, `remoteurl`, `status`, `published`, `deleted`, `comment_status`, `comment_count`, `views_count`, `updated`, `created`, `crawl_id`, `favor_nums`, `point_nums`, `cate_id`, `locale`) VALUES (51, 1, '图片测试', '/SaeProj/ideacms/3/files/201311/thumb_m/21493f15278_1117.jpg', '', 'test是', '', '', '', '', '', '', '', '', '', '', NULL, '1', '0', '1', NULL, 0, '2013-11-03 21:30:48', '2013-11-03 21:30:48', 0, NULL, NULL, 109, 'zh_cn'),
(52, 1, 'test1', '/SaeProj/ideacms/3/files/201311/thumb_s/1ad311ebe24_1116.jpg', '', 'test12', '', '', '', '收到', '', '', '', '', '<p>d发的</p>\r\n\r\n<p>sdfsd&nbsp;</p>\r\n\r\n<p>&nbsp;sds</p>\r\n\r\n<p>是的s sdfsd撒旦法 你好是否送到家看的说法</p>\r\n', '', NULL, '1', '0', '1', NULL, 0, '2013-11-16 20:23:16', '2013-11-16 20:23:16', 0, NULL, NULL, 109, 'zh_cn'),
(53, 1, '收到', '/SaeProj/ideacms/3/files/201311/thumb_m/4633aa0d583_1116.jpg', '', '', '', '', NULL, '', '', '', '', '', '<p>f撒大厦的撒旦法 &nbsp;十点多方法的</p>\r\n', '', NULL, '1', '0', '1', NULL, 0, '2013-11-16 20:25:07', '2013-11-16 20:25:07', 0, NULL, NULL, 109, 'zh_cn'),
(54, 1, '接口', '/SaeProj/ideacms/3/files/201311/thumb_m/175295e4337_1116.jpg', '', 'tuo', '', '', NULL, '', '', '', '', '', '<p>uyglihlkj</p>\r\n', '', NULL, '1', '0', '1', NULL, 0, '2013-11-16 20:32:39', '2013-11-16 20:32:39', 0, NULL, NULL, 109, 'zh_cn'),
(55, 1, 'hjhj', '/SaeProj/ideacms/3/files/201311/thumb_m/04c91f2bcb6_1116.jpg', '', 'oippo', '', '', NULL, '', '', '', '', '', '', '', NULL, '1', '0', '1', NULL, 0, '2013-11-16 20:33:05', '2013-11-16 20:33:05', 0, NULL, NULL, 109, 'zh_cn'),
(56, 1, 'uyy', '/SaeProj/ideacms/3/files/201311/thumb_m/965d6989569_1116.jpg', '', 'yytt', '', '', NULL, '', '', '', '', '', '<p>ujijoijoi</p>\r\n', '', NULL, '1', '0', '1', NULL, 0, '2013-11-16 20:33:32', '2013-11-16 20:33:32', 0, NULL, NULL, 109, 'zh_cn'),
(57, 1, 'juhua', '/SaeProj/ideacms/3/files/201311/thumb_m/35a86a9850a_1116.jpg', '', 'sdfsdf', '', '', NULL, '', '', '', '', '', '<p>sdfsdfsfd</p>\r\n', '', NULL, '0', '0', '1', NULL, 0, '2013-11-16 21:40:32', '2013-11-16 21:40:32', 0, NULL, NULL, 109, 'zh_cn');
