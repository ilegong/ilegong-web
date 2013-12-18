DROP TABLE IF EXISTS `cake_regions`;
CREATE TABLE IF NOT EXISTS `cake_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `titleheight` varchar(10) DEFAULT NULL,
  `titleicon` varchar(200) DEFAULT NULL,
  `portletid` varchar(30) DEFAULT NULL,
  `model` varchar(60) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `titlelength` tinyint(3) DEFAULT NULL,
  `preimg` tinyint(1) DEFAULT NULL,
  `rows` tinyint(3) DEFAULT '6',
  `columns` tinyint(3) DEFAULT '1',
  `cate_id` tinyint(1) DEFAULT '0',
  `showphoto` tinyint(1) DEFAULT NULL,
  `showsummary` tinyint(1) DEFAULT NULL,
  `showpages` tinyint(1) NOT NULL,
  `titlebackimg` varchar(200) DEFAULT NULL,
  `template` varchar(60) NOT NULL DEFAULT 'regions/_list',
  `conditions` text,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `portlet_tpl` varchar(60) DEFAULT NULL,
  `custom_class` varchar(60) DEFAULT NULL,
  `creator_id` bigint(13) DEFAULT '0',
  `content` text,
  `attribute` text,
  `custom_style` text,
  `description` varchar(200) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `content_url` varchar(200) DEFAULT NULL,
  `ext_field1` varchar(200) DEFAULT NULL,
  `ext_field2` varchar(200) DEFAULT NULL,
  `ext_field3` varchar(200) DEFAULT NULL,
  `ext_field4` varchar(200) DEFAULT NULL,
  `ext_description` text,
  `pagelink_type` varchar(10) DEFAULT NULL,
  `auto_receive_param` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Region', 'zh_cn', '20', 34, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '标题', 'content', 'Region', 'zh_cn', '', 33, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'titleheight', '1', 'titleheight', 'string', 'Region', 'zh_cn', '10', 32, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'titleicon', '1', 'titleicon', 'string', 'Region', 'zh_cn', '200', 31, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'portletid', '1', 'portletid', 'string', 'Region', 'zh_cn', '30', 30, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'model', '1', 'model', 'string', 'Region', 'zh_cn', '60', 29, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'color', '1', 'color', 'string', 'Region', 'zh_cn', '10', 28, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'titlelength', '1', 'titlelength', 'integer', 'Region', 'zh_cn', '3', 27, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'preimg', '1', 'preimg', 'boolean', 'Region', 'zh_cn', '1', 26, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'rows', '1', 'rows', 'integer', 'Region', 'zh_cn', '3', 25, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '6', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'columns', '1', 'columns', 'integer', 'Region', 'zh_cn', '3', 23, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '1', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Region', 'zh_cn', '1', 22, '1', '1', 'Misccate', 'id', 'name', 79, '1', '', '0', '', '', 'equal', 'select', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'showphoto', '1', 'showphoto', 'boolean', 'Region', 'zh_cn', '1', 21, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'showsummary', '1', 'showsummary', 'boolean', 'Region', 'zh_cn', '1', 20, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'titlebackimg', '1', 'titlebackimg', 'string', 'Region', 'zh_cn', '200', 19, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Region', 'zh_cn', NULL, 15, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Region', 'zh_cn', NULL, 14, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'showpages', '1', 'showpages', 'boolean', 'region', 'zh_cn', '1', 16, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-09 09:14:25', '2010-10-09 09:14:25', NULL),
(NULL, 'template', '1', 'template', 'string', 'region', 'zh_cn', '60', 17, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, 'regions/_list', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-09 09:14:25', '2010-10-09 09:14:25', NULL),
(NULL, 'conditions', '1', 'conditions', 'text', 'region', 'zh_cn', NULL, 18, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-09 09:14:25', '2010-10-09 09:14:25', NULL),
(NULL, 'portlet_tpl', '1', 'portlet模板', 'string', 'Region', 'zh_cn', '60', 13, '1', '1', 'Template', 'relatepath', 'name', NULL, '1', '', '0', '', '', '', 'select', 'default', '1', '', NULL, '', '', '', 0, '2010-10-09 15:31:48', '2010-10-09 15:31:48', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <conditions>\r\n        <Template.foldername>portlets</Template.foldername>\r\n    </conditions>\r\n    <order>created desc</order>\r\n</options>'),
(NULL, 'custom_class', '1', '个性化css样式', 'string', 'Region', 'zh_cn', '60', 12, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 17:35:26', '2010-10-09 17:35:26', NULL),
(NULL, 'creator_id', '1', '添加人id', 'integer', 'Region', 'zh_cn', '13', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-12-01 15:54:23', '2010-12-01 15:54:23', NULL),
(NULL, 'content', '1', '内容', 'content', 'Region', 'zh_cn', NULL, 10, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'ckeditor', '', '1', '', NULL, '', '', '', 0, '2010-12-01 15:56:20', '2010-12-01 15:56:20', NULL),
(NULL, 'attribute', '1', 'html5 data属性', 'content', 'Region', 'zh_cn', NULL, 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'textarea', '', '1', '', NULL, '', '', '', 0, '2010-12-01 17:43:05', '2010-12-01 17:43:05', NULL),
(NULL, 'custom_style', '1', '自定义style样式', 'content', 'Region', 'zh_cn', NULL, 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'textarea', '', '1', '', NULL, '', '', '', 0, '2011-04-30 16:49:22', '2011-04-30 16:49:22', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Region', 'zh_cn', '1', 6, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '', 0, '2011-05-05 22:44:59', '2011-05-05 22:44:59', NULL),
(NULL, 'description', '1', '描述', 'string', 'Region', 'zh_cn', '200', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-05-05 20:24:40', '2011-05-05 20:24:40', NULL),
(NULL, 'content_url', '1', '内容网址', 'string', 'Region', 'zh_cn', '200', 24, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-12 21:08:11', '2011-07-12 21:08:11', NULL),
(NULL, 'ext_field1', '1', '扩展字段1', 'string', 'Region', 'zh_cn', '200', 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-17 08:55:56', '2011-07-17 08:55:56', NULL),
(NULL, 'ext_field2', '1', '扩展字段2', 'string', 'Region', 'zh_cn', '200', 4, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-17 08:56:23', '2011-07-17 08:56:23', NULL),
(NULL, 'ext_field3', '1', '扩展字段3', 'string', 'Region', 'zh_cn', '200', 3, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-07-17 08:56:42', '2011-07-17 08:56:42', NULL),
(NULL, 'ext_field4', '1', '扩展字段4', 'string', 'Region', 'zh_cn', '200', 2, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-07-17 08:56:58', '2011-07-17 08:56:58', NULL),
(NULL, 'ext_description', '1', '扩展字段用途描述', 'content', 'Region', 'zh_cn', NULL, 1, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'textarea', '', '1', '', NULL, '', '', '', 0, '2011-07-17 08:57:45', '2011-07-17 08:57:45', NULL),
(NULL, 'photo', '0', '文件上传', 'content', 'Region', 'zh_cn', NULL, NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'file', '', '1', '', NULL, '', '', '文件上传，用于上传文件。字段不保存到数据库，在uploadfile中', 0, '2011-08-12 17:21:49', '2011-08-12 17:21:49', ''),
(NULL, 'pagelink_type', '1', '分页链接类型', 'string', 'Region', 'zh_cn', '10', NULL, '1', '1', '', NULL, NULL, NULL, '1', 'pageurl=>本页链接\r\nregionurl=>Region页链接', '0', '', '', '', 'select', 'regionurl', '1', '', NULL, '', '', '', 0, '2011-09-02 23:45:43', '2011-09-02 23:45:43', ''),
(NULL, 'auto_receive_param', '1', '是否自动接收url参数', 'integer', 'Region', 'zh_cn', '1', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '0', '1', '', NULL, '', '', '是否自动接收参数，当为1时，自动接收url传递的参数，作为列表的搜索条件。\r\n参数名要求与模块的字段名相同。\r\n\r\n当参数为model.field_name带模块名的形式时，无论此值是否为1，都加为搜索字段。（model的值与本列表的模块名相同）', 0, '2012-02-26 10:33:51', '2012-02-26 10:33:51', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Region', '页面区域', 'onetomany', 'default', '', 27, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_regions', '', '', '', '0', NULL, 0);



REPLACE INTO `cake_regions` (`id`, `name`, `titleheight`, `titleicon`, `portletid`, `model`, `color`, `titlelength`, `preimg`, `rows`, `columns`, `cate_id`, `showphoto`, `showsummary`, `showpages`, `titlebackimg`, `template`, `conditions`, `updated`, `created`, `portlet_tpl`, `custom_class`, `creator_id`, `content`, `attribute`, `custom_style`, `description`, `deleted`, `content_url`, `ext_field1`, `ext_field2`, `ext_field3`, `ext_field4`, `ext_description`, `pagelink_type`, `auto_receive_param`) VALUES (5, '频道新闻', '30px', '', '', 'Article', 'red', 0, '0', 15, 1, '0', '0', '0', '1', '', 'regions/_list', '<options>\r\n	<recursive>-1</recursive>	\r\n	<fields>\r\n		<id>Article.id</id>\r\n		<title>Article.title</title>\r\n		<created>Article.created</created>\r\n		<slug>Article.slug</slug>\r\n		<views_count>Article.views_count</views_count>\r\n		<titleimg>Article.titleimg</titleimg>\r\n		<origin>Article.origin</origin>\r\n		<remoteurl>Article.remoteurl</remoteurl>\r\n		<summary>Article.summary</summary>\r\n	</fields>\r\n	<order>Article.id desc</order>\r\n	<withsubcategory>1</withsubcategory>\r\n</options>', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'notitle', 'digg-summary-list', 0, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(7, '最新投票', '30px', '', '', 'Appraise', 'red', 0, '0', 15, 1, '0', '0', '0', '1', '', 'appraises/_list', '<options>\r\n	<recursive>-1</recursive>	\r\n	<fields>\r\n		<id>Appraise.id</id>\r\n		<title>Appraise.name</title>\r\n		<created>Appraise.created</created>\r\n		<cate_id>Appraise.cate_id</cate_id>\r\n		<user_img>Appraise.user_img</user_img>\r\n		<creator>Appraise.creator</creator>	\r\n		<creator_id>Appraise.creator_id</creator_id>\r\n		<weibo_id>Appraise.weibo_id</weibo_id>\r\n		<favor_nums>Appraise.favor_nums</favor_nums>\r\n		<comment_nums>Appraise.comment_nums</comment_nums>\r\n	</fields>\r\n	<order>Appraise.id desc</order>	\r\n</options>', '2011-05-05 22:13:34', '0000-00-00 00:00:00', 'notitle', 'digg-summary-list', 0, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(9, '最新用户', '30px', '', '', 'User', 'red', 0, '0', 8, 1, '0', '0', '0', '0', '', 'users/_userheadlist', '<options>\r\n	<recursive>-1</recursive>	\r\n	<fields>\r\n		<id>User.id</id>\r\n		<sina_uid>User.sina_uid</sina_uid>\r\n		<image>User.image</image>\r\n		<screen_name>User.screen_name</screen_name>	\r\n		<sina_domain>User.sina_domain</sina_domain>\r\n	</fields>	\r\n	<order>User.id desc</order>\r\n</options>', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'default', '', 0, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(12, '日排行', '', '', '', 'Article', '', NULL, '0', 10, 1, '0', '0', '0', '0', '', 'regions/_hotlist', '<options>\r\n	<recursive>-1</recursive>	\r\n	<fields>\r\n		<id>Article.id</id>\r\n		<name>Article.name</name>\r\n		<created>Article.created</created>\r\n		<slug>Article.slug</slug>\r\n		<titleimg>Article.titleimg</titleimg>\r\n		<views_count>Article.views_count</views_count>\r\n		<summary>Article.summary</summary>\r\n		<view_nums>Stats.view_nums</view_nums>\r\n	</fields>\r\n	<joins>\r\n		<join>\r\n		<table>cake_stats_days</table>\r\n		<alias>Stats</alias>\r\n		<taye>right</taye>\r\n		<conditions>Stats.data_id = Article.id</conditions>\r\n		<conditions>Stats.model=\'Article\'</conditions>\r\n		<conditions>Stats.date=\'{$date}\'</conditions>\r\n		</join>\r\n	</joins>\r\n	<order>Stats.view_nums desc</order>\r\n</options>', '2011-06-04 10:08:32', '2010-10-09 11:32:38', 'default', 'portlet-content-top10', 0, '', NULL, NULL, '新闻访问日排行', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(13, '周排行', '', '', '', 'Article', '', NULL, '0', 10, 1, '0', '0', '0', '0', '', 'regions/_hotlist', '<options>\r\n	<recursive>-1</recursive>	\r\n	<fields>\r\n		<id>Article.id</id>\r\n		<name>Article.name</name>\r\n		<created>Article.created</created>\r\n		<slug>Article.slug</slug>\r\n		<titleimg>Article.titleimg</titleimg>\r\n		<views_count>Article.views_count</views_count>\r\n		<summary>Article.summary</summary>\r\n		<view_nums>Stats.view_nums</view_nums>\r\n	</fields>\r\n	<joins>\r\n		<join>\r\n		<table>cake_stats_weeks</table>\r\n		<alias>Stats</alias>\r\n		<taye>right</taye>\r\n		<conditions>Stats.data_id = Article.id</conditions>\r\n		<conditions>Stats.model=\'Article\'</conditions>\r\n		<conditions>Stats.week=\'{$week}\'</conditions>\r\n		<conditions>Stats.year=\'{$week_year}\'</conditions>\r\n		</join>\r\n	</joins>\r\n	<order>Stats.view_nums desc</order>\r\n</options>', '2011-06-04 11:32:54', '2010-10-09 11:32:38', 'default', 'serial-sort-list', 0, '', NULL, NULL, '新闻访问周排行', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(14, '月排行', '', '', '', 'Article', '', NULL, '0', 10, 1, '0', '0', '0', '0', '', 'regions/_hotlist', '<options>\r\n	<recursive>-1</recursive>	\r\n	<fields>\r\n		<id>Article.id</id>\r\n		<name>Article.name</name>\r\n		<created>Article.created</created>\r\n		<slug>Article.slug</slug>\r\n		<titleimg>Article.titleimg</titleimg>\r\n		<views_count>Article.views_count</views_count>\r\n		<summary>Article.summary</summary>\r\n		<view_nums>Stats.view_nums</view_nums>\r\n	</fields>\r\n	<joins>\r\n		<join>\r\n		<table>cake_stats_months</table>\r\n		<alias>Stats</alias>\r\n		<taye>right</taye>\r\n		<conditions>Stats.data_id = Article.id</conditions>\r\n		<conditions>Stats.model=\'Article\'</conditions>\r\n		<conditions>Stats.year_month=\'{$month_year}\'</conditions>\r\n		</join>\r\n	</joins>\r\n	<order>Stats.view_nums desc</order>\r\n</options>', '2011-06-04 11:33:42', '2010-10-09 11:32:38', 'default', 'serial-sort-list', 0, '', NULL, NULL, '新闻访问月排行', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(45, '产品列表', NULL, NULL, NULL, 'Product', NULL, NULL, NULL, 0, 1, '0', NULL, NULL, '0', NULL, 'regions/_list', '', '2011-07-12 21:11:16', '2011-07-06 00:18:17', 'default', '', 0, '{{template ajax_lists}}', NULL, NULL, '产品模块lists动作页面', '0', '/products/lists', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(24, '首页下中', NULL, NULL, '', '', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, 'regions/_list', '', '2011-05-01 22:09:35', '2011-05-01 10:32:01', 'notitle', '', 0, '<dl>\r\n        <dt>体验SAECMS</dt>\r\n        <dd>如果您希望整体而全面的把握SAECMS的特点与功能，我们推荐您进入产品中了解；如果您已经了解了有关SAECMS的软件介绍，需要进一步体验的话，您可以进入下面的体验流程。<br>\r\n        </dd>\r\n        <dd><a href=\"#\">软件下载</a> | <a href=\"#\">资料下载</a> | <a href=\"#\">立即体验&gt;&gt;</a> </dd>\r\n      </dl>', NULL, '', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(25, '首页左下', NULL, NULL, '', '', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, 'regions/_list', '', '2011-05-01 22:12:02', '2011-05-01 10:47:16', 'notitle', '', 0, '<dl>\r\n        <dt>网站建设、管理、营销、运营一体化解决方案</dt>\r\n        <dd>搜索引擎排名靠前---网站增访客<br>\r\n          访客需求智能分析---商机不放过 <br>\r\n          沟通访客主动及时---访客变客户<br>\r\n          营销过程时时监控---管理可把握<br>\r\n          运营数据深度挖掘---发展有方向<br>\r\n        </dd>\r\n      </dl>', NULL, '#portlet-25  dl,#portlet-24  dl,#portlet-29  dl{\r\n    color: #999999;\r\n    line-height: 24px;\r\n    margin: 15px;\r\n}\r\n#portlet-25 dl dt,#portlet-24 dl dt,#portlet-29 dl dt {\r\n    color: #4C4C4C;\r\n    font-weight: bold;\r\n}', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(42, 'google广告160*600', NULL, NULL, NULL, 'Advertise', NULL, NULL, NULL, 1, 1, '0', NULL, NULL, '0', NULL, 'regions/_singlecontent', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <recursive>-1</recursive>\r\n    <fields>Advertise.id</fields>\r\n    <fields>Advertise.name</fields>\r\n    <fields>Advertise.advertise_url</fields>\r\n    <fields>Advertise.content</fields>\r\n    <conditions>\r\n        <Advertise.id>4</Advertise.id>\r\n    </conditions>\r\n    <order>created desc</order>    \r\n</options>', '2011-07-05 20:36:50', '2011-06-26 19:29:52', 'notitle', '', 0, '', NULL, NULL, 'google广告160*600', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(28, '首页图片展示', NULL, NULL, '', 'Article', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, 'regions/_list', '', '2011-08-12 17:08:07', '2011-05-01 21:51:38', 'portlets/notitle.html', '', 0, '<div id=\"hot_pocket_wrapper\">\r\n	<ul id=\"hot_pocket_nav\">\r\n		<li>\r\n			<a class=\"active\" href=\"#hp_one\">1</a></li>\r\n		<li>\r\n			<a href=\"#hp_two\">2</a></li>\r\n		<li>\r\n			<a href=\"#hp_three\">3</a></li>\r\n	</ul>\r\n	<div id=\"hot_pocket\">\r\n		<div class=\"hp\" data-height=\"69\" data-img=\"/ui-themes/saecms/images/01_general.png\" data-width=\"350\" id=\"hp_one\">\r\n			<div class=\"text\">\r\n				<p>\r\n					Foxtie creates engaging, content-rich, custom websites that reflect your company&rsquo;s identity. However, our expertise doesn&rsquo;t stop there.</p>\r\n				<p>\r\n					Our team can help you create crisp business cards, glossy magazine ads and professional photography to promote your brand. Foxtie is your one-stop marketing solution.</p>\r\n			</div>\r\n			<a class=\"hp_button\" href=\"/services/\">Learn more about our marketing solutions</a></div>\r\n		<div class=\"hp\" data-height=\"69\" data-img=\"/ui-themes/saecms/images/02_marketing.png\" data-width=\"350\" id=\"hp_two\">\r\n			<div class=\"text\">\r\n				<p>\r\n					You&rsquo;ve spent months building your site, now what? Without an internet marketing plan your site could get lost in the shuffle. Foxtie can help. Our experts have the knowledge and resources to deliver clients to your site.</p>\r\n			</div>\r\n			<a class=\"hp_button\" href=\"/services/internet-marketing/\">Learn more about our internet marketing solutions</a></div>\r\n		<div class=\"hp\" data-height=\"69\" data-img=\"/ui-themes/saecms/images/03_print.png\" data-width=\"350\" id=\"hp_three\">\r\n			<div class=\"text\">\r\n				<p>\r\n					Direct mail, and print campaigns are among the most effective forms of advertising. Foxtie is an expert at promoting your brand both online and in print.</p>\r\n			</div>\r\n			<a class=\"hp_button\" href=\"/services/print-design/\">Learn more about our print solutions</a></div>\r\n	</div>\r\n	<div id=\"hot_pocket_tray\">\r\n		&nbsp;</div>\r\n	<div class=\"clear\">\r\n		&nbsp;</div>\r\n</div>\r\n', NULL, '#portlet-28 .ui-portlet-content{\r\nborder:none;padding:0;\r\n}', '', '0', '', '', '', '', '', '记录下各扩展字段添加后的用途，别时间一长都忘了是做什么用的了', NULL, '0'),
(29, '首页右下', NULL, NULL, '', '', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, 'regions/_list', '', '2011-05-01 22:09:44', '2011-05-01 22:09:23', 'notitle', '', 0, '<dl>\r\n        <dt>购买SAECMS</dt>\r\n        <dd>即日起实行特价优惠酬谢用户活动，在活动有限时间内购买SAECMS的用户，将享受更多折扣，更多服务，更多功能，更多享受，敬请关注。活动截止时间：2011年12月 <br>\r\n        </dd>\r\n        <dd><a href=\"#\">软件下载</a> | <a href=\"#\">资料下载</a> | <a href=\"#\">立即体验&gt;&gt;</a> </dd>\r\n      </dl>', NULL, '', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(30, '{{$Category[\'Category\'][\'name\']}}', NULL, NULL, '', 'Article', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, '', '', '2012-08-30 23:00:42', '2011-05-02 17:17:18', 'portlets/default.html', '', 0, '<div class=\"Content-body\">{{$Category[\'Category\'][\'content\']}} </div>\r\n<?PHP if($total>0){ ?>\r\n<ul class=\"clearfix portlet-content-list\">\r\n    <?PHP foreach($Category[\'datalist\'] as $item) {\r\n    ?>	\r\n		{{include $Category[\'Category\'][\'template\']}}			\r\n	<?php } ?>\r\n</ul>\r\n	{{$page_navi}}\r\n<?php } ?>', NULL, '', '频道栏目内容', '0', '', '', '', '', '', '', '', '0'),
(31, '{{$top_category_name}}', NULL, NULL, '', 'Article', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, 'regions/_list', '', '2012-08-21 17:52:48', '2011-05-02 18:29:17', 'default', '', 0, '{{$this->Section->getLeftMenu(\'Category\',array(\'parent_id\'=> $top_category_id,\'selectedid\'=>$current_cateid))}}\r\n', NULL, '', '频道子栏目', '0', '', '', '', '', '', '', '', '0'),
(32, 'Google广告 250*250', NULL, NULL, '', 'Advertise', NULL, NULL, NULL, 1, 1, '0', '0', '0', '0', NULL, 'regions/_singlecontent', '<options>\r\n	<recursive>-1</recursive>	\r\n	<fields>\r\n		<id>Advertise.id</id>\r\n		<title>Advertise.name</title>		\r\n		<content>Advertise.content</content>\r\n	</fields>\r\n	<conditions>Advertise.id = 1</conditions>\r\n	<conditions>Advertise.published = 1</conditions>\r\n	<conditions>Advertise.deleted = 0</conditions>\r\n</options>', '2011-05-22 22:16:31', '2011-05-22 22:09:49', 'notitle', '', 0, '', NULL, NULL, '', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(33, '产品信息展示', NULL, NULL, '', '', NULL, NULL, NULL, 1, 1, '0', '0', '0', '0', NULL, 'products/_view', '', '2011-05-24 23:18:06', '2011-05-24 23:11:57', 'notitle', '', 0, '{{template products/_view}}', NULL, NULL, '产品信息页，产品信息展示', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(34, 'Google文本广告 468*60', NULL, NULL, '', 'Advertise', NULL, NULL, NULL, 1, 1, '0', '0', '0', '0', NULL, 'regions/_singlecontent', '<options>\r\n    <recursive>-1</recursive>\r\n    <fields>\r\n        <item0>\r\n            <![CDATA[Advertise.id]]>\r\n        </item0>\r\n        <item1>\r\n            <![CDATA[Advertise.name]]>\r\n        </item1>\r\n        <item2>\r\n            <![CDATA[Advertise.content]]>\r\n        </item2>\r\n    </fields>\r\n    <conditions>       \r\n        <Advertise.id>3</Advertise.id>\r\n    </conditions>\r\n    <order>\r\n        <![CDATA[created desc]]>\r\n    </order>\r\n</options>', '2011-06-25 21:02:50', '2011-05-26 23:52:52', 'notitle', '', 0, '', NULL, NULL, 'Google文本广告 468*60', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(35, '博物馆场馆列表', NULL, NULL, '', 'Museum', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, 'regions/_list', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <recursive>-1</recursive>\r\n    <fields>Museum.id</fields>\r\n    <fields>Museum.name</fields>\r\n    <fields>Museum.created</fields>\r\n    <fields>Museum.ticketprice</fields>\r\n    <fields>Museum.titleimg</fields>\r\n    <fields>Museum.address</fields>\r\n    <order>created desc</order>\r\n</options>', '2011-06-13 20:19:50', '2011-06-03 18:25:46', 'default', '', 0, '', NULL, NULL, '', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(36, '产品列表', NULL, NULL, '', 'Product', NULL, NULL, NULL, 12, 1, '0', '0', '0', '1', NULL, 'products/_photolist', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <recursive>-1</recursive>\r\n    <fields>Product.id</fields>\r\n    <fields>Product.name</fields>\r\n    <fields>Product.coverimg</fields>\r\n    <fields>Product.slug</fields>\r\n    <fields>Product.price</fields>\r\n    <fields>Product.created</fields>\r\n    <order>created desc</order>\r\n</options>', '2011-06-18 12:23:17', '2011-06-04 09:45:08', 'default', 'portlet-content-list', 0, '', NULL, NULL, '产品图文列表', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(37, '新闻热门排行', NULL, NULL, '', 'Article', NULL, NULL, NULL, 8, 1, '0', '0', '0', '0', NULL, 'regions/_titlelist', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <recursive>-1</recursive>\r\n    <fields>Article.name</fields>\r\n    <fields>Article.slug</fields>\r\n    <fields>Article.created</fields>\r\n    <order>views_count desc</order>\r\n</options>', '2011-06-27 20:26:27', '2011-06-04 09:51:52', 'default', 'portlet-title-list', 0, '', NULL, NULL, '新闻热门排行', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(38, '产品类别列表', NULL, NULL, '', 'Article', NULL, NULL, NULL, NULL, 1, '0', '0', '0', '0', NULL, 'regions/_list', '', '2011-06-04 11:39:55', '2011-06-04 11:39:55', 'default', '', 0, '{{eval echo $this->Section->getLeftMenu(\'Modelcate\',array(\'parent_id\'=>\'\',\'selectedid\'=>\'\',\'conditions\'=>array(\'model\'=>\'Product\')));}}', NULL, NULL, '列表显示产品类别名称', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(39, 'banner', NULL, NULL, '', 'Article', NULL, NULL, NULL, 0, 1, '0', '0', '0', '0', NULL, 'regions/_singlecontent', '', '2012-09-01 13:37:58', '2011-06-04 11:54:37', '', '', 0, '<img src=\"{{$this->Html->url(\'/ui-themes/saecms/images/banner.jpg\')}}\">', NULL, '', '', '0', '', '', '', '', '', '', '', '0'),
(40, 'google广告160*600', NULL, NULL, '', 'Article', NULL, NULL, NULL, NULL, 1, '0', '0', '0', '0', NULL, 'regions/_singlecontent', '', '2011-06-04 14:19:06', '2011-06-04 14:19:06', 'notitle', '', 0, '<script type=\"text/javascript\">\r\n		<!--\r\ngoogle_ad_client = \"pub-1393746648850099\";\r\n/* 160x600, 创建于 10-4-18 */\r\ngoogle_ad_slot = \"7338009172\";\r\ngoogle_ad_width = 160;\r\ngoogle_ad_height = 600;\r\n//-->\r\n</script>\r\n<script type=\"text/javascript\"\r\nsrc=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">\r\n</script>', NULL, NULL, 'google广告160*600', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(41, '博物馆信息展示', NULL, NULL, '', 'Article', NULL, NULL, NULL, 0, 1, '0', '0', '0', '0', NULL, '', '', '2011-06-13 20:46:18', '2011-06-04 14:36:11', 'default', '', 0, '{{template name=\"museums/_view\" plugin=\"Museum\"}}', NULL, NULL, '博物馆信息展示', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(43, '新闻内容显示', NULL, NULL, NULL, 'Article', NULL, NULL, NULL, 8, 1, '0', NULL, NULL, '0', NULL, 'regions/_list', '', '2011-06-27 20:36:48', '2011-06-27 20:36:48', 'default', '', 0, '{{template articles/_view}}', NULL, NULL, '', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(46, '<ul class=\"nav nav-tabs\" style=\"margin-bottom:0px;\">\r\n<li class=\"{{if strpos($this->request->query[\'order\'],\'volume\')!==false || empty($this->request->query[\'order\'])}}active{{/if}}\"><a href=\"{{getSearchLinks($page_request,array(\'order\'=>\'Taobaoke.volume desc\'), array(\'TaobaoPromotion.discountValue\'))}}\">销量</a></li>\r\n<li class=\"{{if strpos($this->request->query[\'order\'],\'discountValue\')!==false}}active{{/if}}\"><a href=\"{{getSearchLinks($page_request,array(\'order\'=>\'TaobaoPromotion.discountValue asc\',\'TaobaoPromotion.discountValue >\'=> 0), array())}}\">促销</a></li>\r\n<li class=\"{{if strpos($this->request->query[\'order\'],\'price\')!==false}}active{{/if}}\"><a href=\"{{getSearchLinks($page_request,array(\'order\'=>\'Taobaoke.price desc\'), array(\'TaobaoPromotion.discountValue\'))}}\" toggle=\"{{getSearchLinks($page_request,array(\'order\'=>\'Taobaoke.price asc\'), array(\'TaobaoPromotion.discountValue\'))}}\">价格</a></li>\r\n</ul>', NULL, NULL, NULL, 'Taobao.Taobaoke', NULL, NULL, NULL, 20, 4, '0', NULL, NULL, '1', NULL, 'taobaokes/_photolist', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <fields>Taobaoke.id</fields>\r\n    <fields>Taobaoke.name</fields>\r\n    <fields>Taobaoke.volume</fields>\r\n    <fields>Taobaoke.created</fields>\r\n    <fields>Taobaoke.num_iid</fields>\r\n    <fields>Taobaoke.pic_url</fields>\r\n    <fields>Taobaoke.price</fields>\r\n    <fields>Taobaoke.click_url</fields>\r\n    <conditions>\r\n        <conditionskey/>\r\n        <conditionsval/>\r\n        <valid>always</valid>\r\n    </conditions>\r\n    <order>Taobaoke.volume desc</order>\r\n    <joins>\r\n        <join0>\r\n            <table>TaobaoCate</table>\r\n            <alias>TaobaoCate</alias>\r\n            <type>inner</type>\r\n            <conditions>\r\n                <conditionskey>TaobaoCate.id</conditionskey>\r\n                <conditionskey>TaobaoCate.left &gt;=</conditionskey>\r\n                <conditionskey>TaobaoCate.right &lt;=</conditionskey>\r\n                <conditionsval>Taobaoke.cate_id</conditionsval>\r\n                <conditionsval>$left</conditionsval>\r\n                <conditionsval>$right</conditionsval>\r\n                <valid>always</valid>\r\n                <valid>notempty</valid>\r\n                <valid>notempty</valid>\r\n            </conditions>\r\n        </join0>\r\n        <join1>\r\n            <table>TaobaoPromotion</table>\r\n            <alias>TaobaoPromotion</alias>\r\n            <type>left</type>\r\n            <conditions>\r\n                <conditionskey>TaobaoPromotion.num_iid</conditionskey>\r\n                <conditionsval>Taobaoke.num_iid</conditionsval>\r\n                <valid>always</valid>\r\n            </conditions>\r\n        </join1>\r\n    </joins>\r\n    <params>\r\n        <type>get</type>\r\n        <name>page</name>\r\n    </params>\r\n</options>', '2012-11-11 21:13:46', '2011-08-31 21:34:13', 'portlets/default.html', 'ui-tabs', 0, '', NULL, '', '淘宝产品列表2', '0', '', '', '', '', '', '记录下各扩展字段添加后的用途，别时间一长都忘了是做什么用的了', 'pageurl', '1'),
(47, '淘宝商品信息', NULL, NULL, NULL, 'Article', NULL, NULL, NULL, 8, 1, '0', NULL, NULL, '0', NULL, 'regions/_list', '', '2011-09-18 10:54:50', '2011-09-18 09:35:57', 'portlets/default.html', '', 0, '{{template name=\"taobaokes/_view\" plugin=\"Taobao\"}}', NULL, '#portlet-47 .Content-body img {\r\n    margin: 0px 0px;\r\n}\r\n#portlet-47  .Content-body {\r\n    padding: 0 0px;\r\n}\r\n#portlet-47 .ui-tabs .ui-tabs-panel {\r\n    margin: 0;\r\n    padding: 0;\r\n}\r\n\r\n#portlet-47 .ui-portlet-content {\r\n    padding: 0;\r\n}\r\n#portlet-47 table tr td {\r\n    border-bottom: 0;\r\n    padding: 5px 0;\r\n    vertical-align: top;\r\n}', '', '0', '', '', '', '', '', '', '', '0'),
(48, '{{$top_category_name}} - 商品筛选', NULL, NULL, NULL, 'Article', NULL, NULL, NULL, 8, 1, '0', NULL, NULL, '0', NULL, 'regions/_list', '', '2011-09-24 12:33:52', '2011-09-24 12:33:52', 'portlets/default.html', '', 0, '{{template name=\"taobaokes/_search\" plugin=\"Taobao\"}}', NULL, '', '', '0', '', '', '', '', '', '', '', '0'),
(49, '{{$top_category_name}}', NULL, NULL, NULL, 'Article', NULL, NULL, NULL, 8, 1, '0', NULL, NULL, '0', NULL, 'regions/_list', '', '2011-09-24 13:36:07', '2011-09-24 13:36:07', 'portlets/default.html', '', 0, '{{template name=\"taobaokes/_leftcates\" plugin=\"Taobao\"}}', NULL, '', '', '0', '', '', '', '', '', '', '', '0'),
(50, '秋装推荐', NULL, NULL, NULL, 'Taobao.Taobaoke', NULL, NULL, NULL, 12, 4, '0', NULL, NULL, '0', NULL, 'taobaokes/_photolist', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <fields>Taobaoke.id</fields>\r\n    <fields>Taobaoke.name</fields>\r\n    <fields>Taobaoke.volume</fields>\r\n    <fields>Taobaoke.created</fields>\r\n    <fields>Taobaoke.num_iid</fields>\r\n    <fields>Taobaoke.pic_url</fields>\r\n    <fields>Taobaoke.price</fields>\r\n    <fields>Taobaoke.click_url</fields>\r\n    <fields>Taobaoke.shop_type</fields>\r\n    <conditions>\r\n        <conditionskey>Taobaoke.name like</conditionskey>\r\n        <conditionsval>%秋装%</conditionsval>\r\n        <valid>always</valid>\r\n    </conditions>\r\n    <order>Taobaoke.volume desc</order>\r\n    <joins>\r\n        <join0>\r\n            <table>TaobaoPromotion</table>\r\n            <alias>TaobaoPromotion</alias>\r\n            <type>left</type>\r\n            <conditions>\r\n                <conditionskey>TaobaoPromotion.num_iid</conditionskey>\r\n                <conditionsval>Taobaoke.num_iid</conditionsval>\r\n                <valid>always</valid>\r\n            </conditions>\r\n        </join0>\r\n    </joins>\r\n    <params>\r\n        <type>get</type>\r\n        <name>page</name>\r\n    </params>\r\n</options>', '2011-10-13 22:35:54', '2011-10-05 10:31:11', 'portlets/default.html', 'ui-tabs', 0, '', NULL, '', '淘宝产品列表', '0', '', '', '', '', '', '记录下各扩展字段添加后的用途，别时间一长都忘了是做什么用的了', 'pageurl', '0'),
(51, '冬装推荐', NULL, NULL, NULL, 'Taobao.Taobaoke', NULL, NULL, NULL, 12, 4, '0', NULL, NULL, '0', NULL, 'taobaokes/_photolist', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <fields>Taobaoke.id</fields>\r\n    <fields>Taobaoke.name</fields>\r\n    <fields>Taobaoke.volume</fields>\r\n    <fields>Taobaoke.created</fields>\r\n    <fields>Taobaoke.num_iid</fields>\r\n    <fields>Taobaoke.pic_url</fields>\r\n    <fields>Taobaoke.price</fields>\r\n    <fields>Taobaoke.click_url</fields>\r\n    <conditions>\r\n        <conditionskey>Taobaoke.name like</conditionskey>\r\n        <conditionsval>%冬装%</conditionsval>\r\n        <valid>always</valid>\r\n    </conditions>\r\n    <order>Taobaoke.volume desc</order>\r\n    <joins>\r\n        <join0>\r\n            <table>TaobaoPromotion</table>\r\n            <alias>TaobaoPromotion</alias>\r\n            <type>left</type>\r\n            <conditions>\r\n                <conditionskey>TaobaoPromotion.num_iid</conditionskey>\r\n                <conditionsval>Taobaoke.num_iid</conditionsval>\r\n                <valid>always</valid>\r\n            </conditions>\r\n        </join0>\r\n    </joins>\r\n    <params>\r\n        <type>get</type>\r\n        <name>page</name>\r\n    </params>\r\n</options>', '2011-10-06 22:50:43', '2011-10-05 11:50:52', 'portlets/default.html', 'ui-tabs', 0, '', NULL, '', '淘宝产品列表', '0', '', '', '', '', '', '记录下各扩展字段添加后的用途，别时间一长都忘了是做什么用的了', 'pageurl', '0'),
(53, '淘宝分类列表', NULL, NULL, NULL, 'Article', NULL, NULL, NULL, 8, 1, '0', NULL, NULL, '0', NULL, 'regions/_list', '', '2012-11-09 21:39:45', '2011-10-05 14:49:16', 'portlets/default.html', '', 0, '{{$this->Section->getLeftMenu(\'TaobaoCate\',array(\'maxdepth\'=>1))}}', NULL, '', '淘宝分类列表，用于首页', '0', '', '', '', '', '', '', '', '0'),
(52, '内衣', NULL, NULL, NULL, 'Taobao.Taobaoke', NULL, NULL, NULL, 12, 4, '0', NULL, NULL, '0', NULL, 'taobaokes/_photolist', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <fields>Taobaoke.id</fields>\r\n    <fields>Taobaoke.name</fields>\r\n    <fields>Taobaoke.volume</fields>\r\n    <fields>Taobaoke.created</fields>\r\n    <fields>Taobaoke.num_iid</fields>\r\n    <fields>Taobaoke.pic_url</fields>\r\n    <fields>Taobaoke.price</fields>\r\n    <fields>Taobaoke.click_url</fields>\r\n    <conditions>\r\n        <conditionskey>Taobaoke.name like</conditionskey>\r\n        <conditionsval>%内衣%</conditionsval>\r\n        <valid>always</valid>\r\n    </conditions>\r\n    <order>Taobaoke.volume desc</order>\r\n    <joins>\r\n        <join0>\r\n            <table>TaobaoPromotion</table>\r\n            <alias>TaobaoPromotion</alias>\r\n            <type>left</type>\r\n            <conditions>\r\n                <conditionskey>TaobaoPromotion.num_iid</conditionskey>\r\n                <conditionsval>Taobaoke.num_iid</conditionsval>\r\n                <valid>always</valid>\r\n            </conditions>\r\n        </join0>\r\n    </joins>\r\n    <params>\r\n        <type>get</type>\r\n        <name>page</name>\r\n    </params>\r\n</options>', '2011-10-06 22:50:33', '2011-10-05 12:01:34', 'portlets/default.html', 'ui-tabs', 0, '', NULL, '', '淘宝产品列表', '0', '', '', '', '', '', '记录下各扩展字段添加后的用途，别时间一长都忘了是做什么用的了', 'pageurl', '0'),
(54, '房地产咨询', NULL, NULL, NULL, 'EstateArticle', NULL, NULL, NULL, 8, 1, '0', NULL, NULL, '1', NULL, 'regions/_list', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <recursive>-1</recursive>\r\n    <fields>EstateArticle.id</fields>\r\n    <fields>EstateArticle.name</fields>\r\n    <fields>EstateArticle.cate_id</fields>\r\n    <fields>EstateArticle.area_id</fields>\r\n    <fields>EstateArticle.property_status</fields>\r\n    <fields>EstateArticle.property_type</fields>\r\n    <fields>EstateArticle.enterprise_news_type</fields>\r\n    <fields>EstateArticle.policy_types</fields>\r\n    <fields>EstateArticle.view_party</fields>\r\n    <fields>EstateArticle.summary</fields>\r\n    <fields>EstateArticle.created</fields>\r\n    <conditions>\r\n        <conditionskey/>\r\n        <conditionsval/>\r\n        <valid>always</valid>\r\n    </conditions>\r\n    <order/>\r\n    <params>\r\n        <type/>\r\n        <name/>\r\n    </params>\r\n</options>', '2012-02-26 10:40:04', '2012-02-25 22:27:42', 'portlets/default.html', '', 0, '', NULL, '', '', '0', '', '', '', '', '', '', 'pageurl', '1');
