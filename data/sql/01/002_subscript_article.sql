

INSERT INTO `cake_modelextends`(`name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`)
VALUES ('Subscript', '订阅号文章', 'onetomany', 'default', '', 26, NULL, NULL, 'cake_subscripts', '', '', '', 0, 1, 2);





INSERT INTO `cake_settings` (`key`, `value`, `title`, `description`, `input_type`, `editable`, `weight`, `params`, `scope`, `locale`)
VALUES ('Subscript.list_fields', 'id,title,published,created,slug,summary,link', NULL, NULL, 'text', 1, 58, NULL, 'manage', ''), ('Subscript.pagesize', '100', '列表每页数目', '', 'text', 1, 79, '', 'global', ''), ('Subscript.search_fields', 'id', NULL, NULL, NULL, 1, 86, NULL, 'manage', '');



CREATE TABLE `cake_subscripts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `pictures` varchar(255) DEFAULT NULL,
  `content` text,
  `published` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  `views_count` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;


INSERT INTO `cake_i18nfields` (`name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`)
VALUES ('id', 1, '编号', 'integer', 'Subscript', 'zh_cn', '11', 28, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, 0, NULL, NULL, 'equal', '', NULL, 1, NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('title', 1, '标题', 'string', 'Subscript', 'zh_cn', '250', 26, 1, 1, '', NULL, NULL, NULL, 1, '', 0, '', '', 'equal', 'input', '', 0, '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('slug', 1, '文章类型', 'string', 'Subscript', 'zh_cn', '250', 23, 1, 1, NULL, NULL, NULL, NULL, 1, 'chu_niang_bang_shou =>厨娘帮手\npeng_you_gu_shi=> 朋友故事\nshi_chi_inforamtion => 试吃早知道\nchi_huo_ju_hui => 吃货聚会\nabout_us=> 关于我们\npeng_you_recommend=>朋友推荐\nxian_shang_huo_dong=>线上活动\nha_ha_ha_ha=>哈哈哈哈', 0, NULL, NULL, 'equal', '', NULL, 1, NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('created', 1, '创建时间', 'datetime', 'Subscript', 'zh_cn', NULL, 22, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, 0, NULL, NULL, 'equal', 'datetime', NULL, 1, NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('published', 1, '是否发布', 'boolean', 'Subscript', 'zh_cn', '1', 21, 1, 1, NULL, NULL, NULL, NULL, 1, '0=>否\n1=>是', 0, NULL, NULL, 'equal', 'select', '1', 1, NULL, '', NULL, NULL, '请选择是，否则不会显示；', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('pictures', 1, '文章图片', 'string', 'Subscript', 'zh_cn', '250', 20, 1, 1, NULL, NULL, NULL, NULL, 1, '', 0, NULL, NULL, 'equal', 'file', '0', 1, NULL, '', NULL, NULL, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('summary', 1, '摘要', 'string', 'Subscript', 'zh_cn', '500', 19, 1, 1, NULL, NULL, NULL, NULL, 1, '', 0, NULL, NULL, 'equal', 'textarea', '', 1, NULL, '', NULL, NULL, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('content', 1, '内容', 'string', 'Subscript', 'zh_cn', '', 18, 0,0, NULL, NULL, NULL, NULL, 1, '', 0, NULL, NULL, 'equal', 'ckeditor', '', 1, NULL, NULL, NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('view_count', 1, '阅读次数', 'integer', 'Subscript', 'zh_cn', '11', 24, 0, 0, NULL, NULL, NULL, NULL, 1, '', 0, NULL, NULL, 'equal', '', '', 1, NULL, ' ', NULL, NULL, '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
('priority',1,'排序优先级','integer','Subscript','zh_cn','11',25,1,1,NULL,NULL,NULL,NULL,1,'',0,NULL,NULL,'equal','',1,1,NULL,NULL,NULL,NULL,'排序优先级，值越大的排前面',0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL),
('link',1,'原文链接','string','Subscript','zh_cn','250','17',1,1,NUll,NULL,NULl,NULL,1,'','0',NULL,NULL,'equal','textarea','',1,NULL,'',NULL,NULL,'有原文链接时，会直接跳转到原文，否则显示文章',0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL)