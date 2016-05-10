DROP TABLE IF EXISTS `cake_taobao_trade_rates`;
CREATE TABLE IF NOT EXISTS `cake_taobao_trade_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1000) DEFAULT NULL,
  `taobaoke_id` bigint(11) DEFAULT '0',
  `tid` varchar(40) DEFAULT NULL,
  `oid` varchar(40) DEFAULT NULL,
  `reply` varchar(1000) DEFAULT NULL,
  `result` varchar(10) DEFAULT NULL,
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `role` varchar(30) DEFAULT NULL,
  `nick` varchar(30) DEFAULT NULL,
  `origin` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taobaoke_id` (`taobaoke_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'TaobaoTradeRate', 'zh_cn', '11', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', NULL),
(NULL, 'name', '1', '评价内容', 'string', 'TaobaoTradeRate', 'zh_cn', '1000', 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', ''),
(NULL, 'taobaoke_id', '1', '商品编号', 'integer', 'TaobaoTradeRate', 'zh_cn', '11', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', ''),
(NULL, 'tid', '1', '交易id', 'string', 'TaobaoTradeRate', 'zh_cn', '40', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', ''),
(NULL, 'oid', '1', '子订单id', 'string', 'TaobaoTradeRate', 'zh_cn', '40', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', ''),
(NULL, 'reply', '1', '评价解释/回复', 'string', 'TaobaoTradeRate', 'zh_cn', '1000', 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', ''),
(NULL, 'result', '1', '评价结果', 'string', 'TaobaoTradeRate', 'zh_cn', '10', 3, '1', '1', '', NULL, NULL, NULL, '1', '0=>否\r\n1=>是', '0', '', '', 'equal', 'select', '0', '1', '', NULL, '', '', '', 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', ''),
(NULL, 'published', '1', '是否发布', 'integer', 'TaobaoTradeRate', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'TaobaoTradeRate', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'TaobaoTradeRate', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'TaobaoTradeRate', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-10-02 22:00:05', '2011-10-02 22:00:05', NULL),
(NULL, 'role', '1', '评价者角色', 'string', 'TaobaoTradeRate', 'zh_cn', '30', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-10-02 22:24:07', '2011-10-02 22:24:07', NULL),
(NULL, 'nick', '1', '评价者昵称', 'string', 'TaobaoTradeRate', 'zh_cn', '30', 0, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-10-02 22:24:07', '2011-10-02 22:24:07', NULL),
(NULL, 'origin', '1', '评价来源', 'string', 'TaobaoTradeRate', 'zh_cn', '20', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-10-02 22:39:35', '2011-10-02 22:39:35', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'TaobaoTradeRate', '交易评价', '', 'default', '', 27, '2011-10-02 22:00:05', '2011-10-02 22:00:05', 'cake_taobao_trade_rates', '', '', '', '0', 0, 0);
