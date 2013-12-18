DROP TABLE IF EXISTS `cake_crawl_release_sites`;
CREATE TABLE IF NOT EXISTS `cake_crawl_release_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `cate_id` int(11) DEFAULT '0',
  `creator` int(13) DEFAULT '0',
  `lastupdator` int(13) DEFAULT '0',
  `apiurl` varchar(200) DEFAULT NULL,
  `model_api_url` varchar(200) DEFAULT NULL,
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `site_type` varchar(20) DEFAULT NULL,
  `sec_code` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'CrawlReleaseSite', 'zh_cn', '11', 13, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', NULL),
(NULL, 'name', '1', '站点名称', 'string', 'CrawlReleaseSite', 'zh_cn', '200', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', ''),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'CrawlReleaseSite', 'zh_cn', '11', 12, '1', '1', 'Modelcate', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '1', '', NULL, '', '', '', 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', '<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<options>\r\n    <conditions>\r\n        <Modelcate.model>CrawlReleaseSite</Modelcate.model>\r\n    </conditions>\r\n</options>'),
(NULL, 'creator', '1', '编创建者', 'integer', 'CrawlReleaseSite', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'CrawlReleaseSite', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', NULL),
(NULL, 'apiurl', '1', '发布接口地址', 'string', 'CrawlReleaseSite', 'zh_cn', '200', 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', ''),
(NULL, 'model_api_url', '1', '模块接口地址', 'string', 'CrawlReleaseSite', 'zh_cn', '200', 8, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\r\n1=>是', '0', '', '', 'equal', 'input', '0', '1', '', NULL, '', '', '此接口地址同时返回模块及模块下的分类。如discuzx的论坛->论坛版块，门户->门户新闻分类', 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', ''),
(NULL, 'published', '1', '是否发布', 'integer', 'CrawlReleaseSite', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'CrawlReleaseSite', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'CrawlReleaseSite', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'CrawlReleaseSite', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-12-25 16:18:17', '2011-12-25 16:18:17', NULL),
(NULL, 'site_type', '1', '站点类型', 'string', 'CrawlReleaseSite', 'zh_cn', '20', 10, '1', '1', '', NULL, NULL, NULL, '1', 'local=>本站\r\ndiscuzx=>discuzx\r\nphpcms=>phpcms', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '一个站点类型对应一个发布方法，发布时根据站点类型的值选择调用方法', 0, '2011-12-25 22:55:12', '2011-12-25 22:55:12', ''),
(NULL, 'sec_code', '1', '发布密钥', 'string', 'CrawlReleaseSite', 'zh_cn', '60', 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-12-25 22:59:46', '2011-12-25 22:59:46', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'CrawlReleaseSite', '采集发布站点', '', 'default', '', 27, '2011-12-25 16:18:17', '2011-12-25 16:18:17', 'cake_crawl_release_sites', '', '', '', '0', 0, 0);



REPLACE INTO `cake_crawl_release_sites` (`id`, `name`, `cate_id`, `creator`, `lastupdator`, `apiurl`, `model_api_url`, `published`, `deleted`, `created`, `updated`, `site_type`, `sec_code`) VALUES (1, 'sae开发者社区', 43, NULL, NULL, 'http://www.d.com/api/post/post.php?action=newthread', 'http://www.d.com/api/post/post.php?action=getmodule', '0', '0', '2011-12-28 23:45:17', '2011-12-28 23:45:17', 'discuzx', 'dfjIUbdfsKJsd832^7*sd@s'),
(2, '本站', 42, NULL, NULL, '', '0', '0', '0', '2012-01-15 10:53:33', '2012-01-15 10:53:33', 'local', '');
