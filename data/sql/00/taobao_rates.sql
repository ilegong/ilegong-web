DROP TABLE IF EXISTS `cake_taobao_rates`;
CREATE TABLE IF NOT EXISTS `cake_taobao_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `nick` varchar(60) DEFAULT NULL,
  `content` varchar(1000) DEFAULT NULL,
  `item_price` float(10,2) DEFAULT '0.00',
  `reply` varchar(1000) DEFAULT NULL,
  `result` varchar(10) DEFAULT NULL,
  `rated_nick` varchar(60) DEFAULT NULL,
  `role` char(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'TaobaoRate', 'zh_cn', '11', 11, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', NULL),
(NULL, 'name', '1', '商品名称', 'string', 'TaobaoRate', 'zh_cn', '200', 10, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'nick', '1', '评价者昵称', 'string', 'TaobaoRate', 'zh_cn', '60', 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'content', '1', '内容', 'string', 'TaobaoRate', 'zh_cn', '1000', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', NULL, '', '', '', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'item_price', '1', '购买价格', 'float', 'TaobaoRate', 'zh_cn', '10,2', 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', NULL, '', '', '', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'reply', '1', '回复', 'string', 'TaobaoRate', 'zh_cn', '1000', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', NULL, '', '', '', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'result', '1', '评价结果', 'string', 'TaobaoRate', 'zh_cn', '10', 5, '1', '1', '', NULL, NULL, NULL, '1', 'good=>(好评)\r\nneutral=>(中评)\r\nbad=>(差评)', '0', '', '', 'equal', 'select', '0', '1', '', NULL, '', '', '评价结果,可选值:good(好评),neutral(中评),bad(差评)', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'rated_nick', '1', '被评价者昵称', 'string', 'TaobaoRate', 'zh_cn', '60', 4, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\r\n1=>是', '0', '', '', 'equal', 'input', '0', '1', '', NULL, '', '', '', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'role', '1', '评价者角色', 'char', 'TaobaoRate', 'zh_cn', '10', 3, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\r\n1=>是', '0', '', '', 'equal', 'input', '0', '1', '', NULL, '', '', '', 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', ''),
(NULL, 'created', '1', '创建时间', 'datetime', 'TaobaoRate', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'TaobaoRate', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-08-25 23:44:39', '2011-08-25 23:44:39', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'TaobaoRate', '商品评价', '', 'default', '', 27, '2011-08-25 23:44:39', '2011-08-25 23:44:39', 'cake_taobao_rates', '', '', '', '0', 0, 0);
