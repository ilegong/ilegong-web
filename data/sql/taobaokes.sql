DROP TABLE IF EXISTS `cake_taobaokes`;
CREATE TABLE IF NOT EXISTS `cake_taobaokes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `cate_id` int(11) DEFAULT '0',
  `creator` bigint(13) DEFAULT '0',
  `lastupdator` bigint(13) DEFAULT '0',
  `content` text,
  `volume` bigint(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `nick` varchar(60) DEFAULT NULL,
  `num_iid` varchar(60) DEFAULT NULL,
  `item_location` varchar(60) DEFAULT NULL,
  `pic_url` varchar(200) DEFAULT NULL,
  `price` float DEFAULT '0',
  `taobao_product_id` varchar(60) DEFAULT NULL,
  `commission` float(10,2) DEFAULT '0.00',
  `commission_rate` float(10,3) DEFAULT '0.000',
  `commission_volume` float(10,2) DEFAULT '0.00',
  `commission_num` bigint(10) DEFAULT '0',
  `click_url` varchar(500) DEFAULT NULL,
  `post_fee` float(10,2) DEFAULT '0.00',
  `express_fee` float(10,2) DEFAULT '0.00',
  `ems_fee` float(10,2) DEFAULT '0.00',
  `freight_payer` varchar(10) DEFAULT NULL,
  `item_imgs` text,
  `seotitle` varchar(255) DEFAULT NULL,
  `seokeywords` varchar(255) DEFAULT NULL,
  `seodescription` varchar(255) DEFAULT NULL,
  `view_nums` bigint(10) DEFAULT '0',
  `shop_type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_iid` (`num_iid`),
  KEY `cate_id` (`cate_id`),
  KEY `cate_id_2` (`cate_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Taobaoke', 'zh_cn', '11', 11, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'name', '1', '产品名称', 'string', 'Taobaoke', 'zh_cn', '200', 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', ''),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Taobaoke', 'zh_cn', '11', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'Taobaoke', 'zh_cn', '11', 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Taobaoke', 'zh_cn', '11', 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'content', '1', '内容', 'content', 'Taobaoke', 'zh_cn', '', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'ckeditor', '', '1', '', NULL, '', '', '', 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', ''),
(NULL, 'volume', '1', '最近成交量', 'integer', 'Taobaoke', 'zh_cn', '11', 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '0', '1', '', NULL, '', '', '', 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', ''),
(NULL, 'published', '1', '是否发布', 'integer', 'Taobaoke', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Taobaoke', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Taobaoke', 'zh_cn', NULL, 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Taobaoke', 'zh_cn', NULL, 3, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-20 20:48:26', '2011-08-20 20:48:26', NULL),
(NULL, 'nick', '1', '店铺名称', 'string', 'Taobaoke', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-20 23:55:20', '2011-08-20 23:55:20', ''),
(NULL, 'num_iid', '1', '淘宝客商品id', 'string', 'Taobaoke', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 00:02:00', '2011-08-21 00:02:00', ''),
(NULL, 'item_location', '1', '商品地区', 'string', 'Taobaoke', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 00:04:11', '2011-08-21 00:04:11', ''),
(NULL, 'pic_url', '1', '图片路径', 'string', 'Taobaoke', 'zh_cn', '200', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 00:05:15', '2011-08-21 00:05:15', ''),
(NULL, 'price', '1', '商品价格', 'float', 'Taobaoke', 'zh_cn', '10', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 00:06:45', '2011-08-21 00:06:45', ''),
(NULL, 'taobao_product_id', '1', '淘宝商品iid', 'string', 'Taobaoke', 'zh_cn', '60', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 00:27:18', '2011-08-21 00:27:18', ''),
(NULL, 'commission', '1', '广告成交佣金', 'float', 'Taobaoke', 'zh_cn', '10,2', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 08:12:49', '2011-08-21 08:12:49', ''),
(NULL, 'commission_rate', '1', '佣金占价格比例', 'float', 'Taobaoke', 'zh_cn', '10,3', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 08:31:58', '2011-08-21 08:31:58', ''),
(NULL, 'commission_volume', '1', '30天佣金支出量', 'float', 'Taobaoke', 'zh_cn', '10,2', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 08:33:30', '2011-08-21 08:33:30', ''),
(NULL, 'commission_num', '1', '30天推广量', 'integer', 'Taobaoke', 'zh_cn', '10', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-21 08:35:22', '2011-08-21 08:35:22', ''),
(NULL, 'click_url', '1', '点击链接', 'string', 'Taobaoke', 'zh_cn', '500', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-25 23:42:10', '2011-08-25 23:42:10', ''),
(NULL, 'post_fee', '1', '平邮费用', 'float', 'Taobaoke', 'zh_cn', '10,2', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'input', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2011-09-01 17:54:40', '2011-09-01 17:54:40', NULL),
(NULL, 'express_fee', '1', '快递费用', 'float', 'Taobaoke', 'zh_cn', '10,2', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'input', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2011-09-01 17:54:41', '2011-09-01 17:54:41', NULL),
(NULL, 'ems_fee', '1', 'ems费用', 'float', 'Taobaoke', 'zh_cn', '10,2', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'input', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2011-09-01 17:54:41', '2011-09-01 17:54:41', NULL),
(NULL, 'freight_payer', '1', '运费承担方式', 'string', 'Taobaoke', 'zh_cn', '10', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'input', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2011-09-01 17:54:42', '2011-09-01 17:54:42', NULL),
(NULL, 'item_imgs', '1', '商品图片列表', 'content', 'Taobaoke', 'zh_cn', '10,2', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'textarea', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2011-09-01 17:54:42', '2011-09-01 17:54:42', NULL),
(NULL, 'seotitle', '1', 'SEO页面标题', 'string', 'Taobaoke', 'zh_cn', '255', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'textarea', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-09-15 16:42:20', '2011-09-15 16:42:20', NULL),
(NULL, 'seokeywords', '1', 'SEO页面关键字', 'string', 'Taobaoke', 'zh_cn', '255', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'textarea', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-09-15 16:42:21', '2011-09-15 16:42:21', NULL),
(NULL, 'seodescription', '1', 'SEO页面描述', 'string', 'Taobaoke', 'zh_cn', '255', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'textarea', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-09-15 16:42:21', '2011-09-15 16:42:21', NULL),
(NULL, 'view_nums', '1', '查看次数', 'integer', 'Taobaoke', 'zh_cn', '', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '1', '1', '', NULL, '', '', '', 0, '2011-09-18 08:29:11', '2011-09-18 08:29:11', ''),
(NULL, 'shop_type', '1', '店铺类型', 'string', 'Taobaoke', 'zh_cn', '10', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', 'C', '1', '', NULL, '', '', '', 0, '2011-10-12 23:45:03', '2011-10-12 23:45:03', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Taobaoke', '淘宝客', '', 'default', '', 27, '2011-08-20 20:48:26', '2011-08-20 20:48:26', 'cake_taobaokes', '', '', '', '0', 0, 0);
