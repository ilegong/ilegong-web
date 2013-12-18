DROP TABLE IF EXISTS `cake_crawl_title_lists`;
CREATE TABLE IF NOT EXISTS `cake_crawl_title_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `crawl_id` int(11) DEFAULT '0',
  `creator` int(13) DEFAULT '0',
  `lastupdator` int(13) DEFAULT '0',
  `remoteurl` varchar(200) DEFAULT NULL,
  `published` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `coverimg` varchar(255) DEFAULT NULL,
  `crawl_content_flag` tinyint(1) DEFAULT '0',
  `content` text,
  `publish_flag` tinyint(1) DEFAULT '0',
  `serialize_info` text,
  `refererurl` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'CrawlTitleList', 'zh_cn', '11', 14, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-19 17:08:39', '2011-08-19 20:50:14', NULL),
(NULL, 'name', '1', '名称', 'string', 'CrawlTitleList', 'zh_cn', '200', 12, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', NULL),
(NULL, 'crawl_id', '1', '所属采集规则', 'integer', 'CrawlTitleList', 'zh_cn', '11', 13, '1', '1', 'Crawl', 'id', 'title', NULL, '1', '', '0', '', '', 'equal', 'select', '', '1', '', NULL, '', '', '', 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', ''),
(NULL, 'creator', '1', '编创建者', 'integer', 'CrawlTitleList', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'CrawlTitleList', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', NULL),
(NULL, 'remoteurl', '1', '原文地址', 'string', 'CrawlTitleList', 'zh_cn', '200', 10, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', ''),
(NULL, 'published', '1', '是否发布', 'integer', 'CrawlTitleList', 'zh_cn', '11', 7, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', '', '', 'equal', 'select', '0', '1', '', NULL, '', '', '标识发布是否完成，仅针对与标记要发布的数据。很多数据质量不好，没有标记publish_flag的不需要发布', 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', ''),
(NULL, 'deleted', '1', '是否删除', 'integer', 'CrawlTitleList', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'CrawlTitleList', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'CrawlTitleList', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-19 17:08:39', '2011-08-19 17:08:39', NULL),
(NULL, 'coverimg', '1', '标题图片', 'string', 'CrawlTitleList', 'zh_cn', '255', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'coverimg', '', '1', '', NULL, '', '', '', 0, '2011-08-26 21:04:58', '2011-08-26 21:04:58', ''),
(NULL, 'crawl_content_flag', '1', '内容是否已抓取', 'integer', 'CrawlTitleList', 'zh_cn', '1', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '0', '1', '', NULL, '', '', '内容是否已抓取', 0, '2011-08-27 09:58:22', '2011-08-27 09:58:22', ''),
(NULL, 'content', '1', '内容', 'content', 'CrawlTitleList', 'zh_cn', '', 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'ckeditor', '', '1', '', NULL, '', '', '', 0, '2012-01-03 09:26:29', '2012-01-03 09:26:29', ''),
(NULL, 'publish_flag', '1', '发布标记', 'integer', 'CrawlTitleList', 'zh_cn', '1', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '0', '1', '', NULL, '', '', '标记采集的文章是否发布，做了标记的文章会通过cron加入发布队列来发布', 0, '2012-01-03 11:21:45', '2012-01-03 11:21:45', ''),
(NULL, 'serialize_info', '1', '其它信息', 'content', 'CrawlTitleList', 'zh_cn', '', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'textarea', '', '1', '', NULL, '', '', '', 0, '2012-01-07 16:00:37', '2012-01-07 16:00:37', ''),
(NULL, 'refererurl', '1', '来源引用页地址', 'string', 'CrawlTitleList', 'zh_cn', '200', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '标题链接所在页面的地址', 0, '2012-03-03 14:26:22', '2012-03-03 14:26:22', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'CrawlTitleList', '采集数据仓库', '', 'default', '', 27, '2011-08-19 17:08:39', '2011-08-19 17:08:39', 'cake_crawl_title_lists', '', '', '', '0', 0, 0);
