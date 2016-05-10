DROP TABLE IF EXISTS `cake_order_consignees`;
CREATE TABLE IF NOT EXISTS `cake_order_consignees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `address` varchar(255) DEFAULT NULL,
  `creator` bigint(13) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `mobilephone` varchar(40) DEFAULT NULL,
  `telephone` varchar(40) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `postcode` varchar(40) DEFAULT NULL,
  `area` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'OrderConsignee', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-04-23 13:13:24', '2011-04-23 13:13:24', NULL),
(NULL, 'name', '1', '名称', 'string', 'OrderConsignee', 'zh_cn', '200', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-04-23 13:13:24', '2011-04-23 13:13:24', NULL),
(NULL, 'address', '1', '地址', 'string', 'OrderConsignee', 'zh_cn', '255', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', NULL, '', '', '', 0, '2011-04-23 13:13:24', '2011-04-23 13:13:24', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'OrderConsignee', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-04-23 13:13:24', '2011-04-23 13:13:24', NULL),
(NULL, 'status', '1', '状态', 'integer', 'OrderConsignee', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-04-23 13:13:24', '2011-04-23 13:13:24', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'OrderConsignee', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-04-23 13:13:24', '2011-04-23 13:13:24', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'OrderConsignee', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-04-23 13:13:24', '2011-04-23 13:13:24', NULL),
(NULL, 'mobilephone', '1', '手机号', 'string', 'OrderConsignee', 'zh_cn', '40', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-04-23 15:49:02', '2011-04-23 15:49:02', NULL),
(NULL, 'telephone', '1', '电话', 'string', 'OrderConsignee', 'zh_cn', '40', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-04-23 15:49:29', '2011-04-23 15:49:29', NULL),
(NULL, 'email', '1', '邮箱', 'string', 'OrderConsignee', 'zh_cn', '40', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-04-23 15:50:19', '2011-04-23 15:50:19', NULL),
(NULL, 'postcode', '1', '邮编', 'string', 'OrderConsignee', 'zh_cn', '40', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-04-23 15:50:54', '2011-04-23 15:50:54', NULL),
(NULL, 'area', '1', '地区', 'string', 'OrderConsignee', 'zh_cn', '200', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', '', '', '1', '', NULL, '', '', '', 0, '2011-04-23 16:50:57', '2011-04-23 16:50:57', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'OrderConsignee', '订单收件人', '', 'default', '', 27, '2011-04-23 13:13:24', '2011-04-23 13:13:24', 'cake_order_consignees', '', 'self', '', '0', 0, 0);
