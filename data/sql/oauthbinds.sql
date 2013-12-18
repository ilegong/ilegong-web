DROP TABLE IF EXISTS `cake_oauthbinds`;
CREATE TABLE IF NOT EXISTS `cake_oauthbinds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(13) NOT NULL,
  `source` varchar(10) NOT NULL DEFAULT 'sina',
  `oauth_uid` bigint(13) DEFAULT '0',
  `oauth_token` varchar(44) NOT NULL,
  `oauth_token_secret` varchar(44) NOT NULL,
  `domain` varchar(30) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `oauth_name` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `source` (`source`,`oauth_uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Oauthbind', 'zh_cn', '11', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'user_id', '1', 'user_id', 'integer', 'Oauthbind', 'zh_cn', '13', 9, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'source', '1', 'source', 'string', 'Oauthbind', 'zh_cn', '10', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, 'sina', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'oauth_uid', '1', '认证用户名编号', 'integer', 'Oauthbind', 'zh_cn', '13', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'oauth_token', '1', 'oauth_token', 'string', 'Oauthbind', 'zh_cn', '44', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'oauth_token_secret', '1', 'oauth_token_secret', 'string', 'Oauthbind', 'zh_cn', '44', 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'domain', '1', 'domain', 'string', 'Oauthbind', 'zh_cn', '30', 3, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Oauthbind', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Oauthbind', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'oauth_name', '1', '认证用户名', 'string', 'Oauthbind', 'zh_cn', '60', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2011-07-10 15:27:51', '2011-07-10 15:27:51', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Oauthbind', '第三方账号绑定', 'onetomany', 'default', '', 27, '2011-03-05 20:26:57', '2011-03-05 20:26:57', 'cake_oauthbinds', '', '', '', '0', NULL, 0);
