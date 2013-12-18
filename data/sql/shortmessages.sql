DROP TABLE IF EXISTS `cake_shortmessages`;
CREATE TABLE IF NOT EXISTS `cake_shortmessages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `msgfrom` varchar(60) DEFAULT NULL,
  `msgfromid` int(11) DEFAULT NULL,
  `receiver` varchar(60) DEFAULT NULL,
  `receiverid` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text,
  `haveread` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Shortmessage', 'zh_cn', '11', 13, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-01 09:14:13', '2010-09-01 09:14:13', NULL),
(NULL, 'name', '1', '类型', 'string', 'Shortmessage', 'zh_cn', '200', 5, '0', '0', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', 'Staff', '0', '', NULL, '', '', '为Staff或者User，标识是用户的短信，还是内部职员的短信', 0, '2010-09-01 09:14:13', '2010-09-01 09:14:13', NULL),
(NULL, 'status', '1', '数据状态', 'integer', 'Shortmessage', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-01 09:14:13', '2010-09-01 09:14:13', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Shortmessage', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-01 09:14:13', '2010-09-01 09:14:13', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Shortmessage', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-01 09:14:13', '2010-09-01 09:14:13', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Shortmessage', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-09-01 09:14:13', '2010-09-01 09:14:13', NULL),
(NULL, 'msgfrom', '1', '发送人', 'string', 'Shortmessage', 'zh_cn', '60', 10, '0', '0', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-09-01 09:36:45', '2010-09-01 09:36:45', NULL),
(NULL, 'msgfromid', '1', '发起人编号', 'integer', 'Shortmessage', 'zh_cn', '11', 9, '0', '0', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'hidden', '', '1', '', NULL, '', '', '', 0, '2010-09-01 09:37:24', '2010-09-01 09:37:24', NULL),
(NULL, 'receiver', '1', '接收人', 'string', 'Shortmessage', 'zh_cn', '60', 12, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '0', '', NULL, '', '', '', 0, '2010-09-01 09:40:07', '2010-09-01 09:40:07', NULL),
(NULL, 'receiverid', '1', '接收人编号', 'integer', 'Shortmessage', 'zh_cn', '11', 11, '0', '0', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'hidden', '', '1', '', NULL, '', '', '', 0, '2010-09-01 09:40:40', '2010-09-01 09:40:40', NULL),
(NULL, 'title', '1', '短信标题', 'string', 'Shortmessage', 'zh_cn', '200', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '0', '', NULL, '', '', '', 0, '2010-09-01 09:53:21', '2010-09-01 09:53:21', NULL),
(NULL, 'message', '1', '短消息内容', 'content', 'Shortmessage', 'zh_cn', NULL, 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'textarea', '', '1', '', NULL, '', '', '', 0, '2010-09-01 09:53:45', '2010-09-01 09:53:45', NULL),
(NULL, 'haveread', '1', '是否已读', 'integer', 'Shortmessage', 'zh_cn', '1', 6, '0', '0', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'hidden', '', '1', '', NULL, '', '', '', 0, '2010-09-02 09:07:49', '2010-09-02 09:07:49', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Shortmessage', '短消息', 'onetomany', 'default', '<id>', 27, '2010-09-01 09:14:13', '2010-09-01 09:14:13', 'cake_shortmessages', '', 'self', 'msgfromid,receiverid', '0', 0, 0);
