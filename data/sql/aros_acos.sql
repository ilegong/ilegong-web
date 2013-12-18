DROP TABLE IF EXISTS `cake_aros_acos`;
CREATE TABLE IF NOT EXISTS `cake_aros_acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `_create` varchar(2) NOT NULL DEFAULT '0',
  `_read` varchar(2) NOT NULL DEFAULT '0',
  `_update` varchar(2) NOT NULL DEFAULT '0',
  `_delete` varchar(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'ArosAco', 'zh_cn', '10', 7, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'aro_id', '1', 'aro_id', 'integer', 'ArosAco', 'zh_cn', '10', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'aco_id', '1', 'aco_id', 'integer', 'ArosAco', 'zh_cn', '10', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, '_create', '1', '_create', 'string', 'ArosAco', 'zh_cn', '2', 4, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, '_read', '1', '_read', 'string', 'ArosAco', 'zh_cn', '2', 3, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, '_update', '1', '_update', 'string', 'ArosAco', 'zh_cn', '2', 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, '_delete', '1', '_delete', 'string', 'ArosAco', 'zh_cn', '2', 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', '0', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'ArosAco', '权限关系', 'onetomany', 'default', '', 27, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_aros_acos', '', '', '', '0', NULL, 0);



REPLACE INTO `cake_aros_acos` (`id`, `aro_id`, `aco_id`, `_create`, `_read`, `_update`, `_delete`) VALUES (13, 3, 11, '1', '1', '1', '1'),
(14, 3, 13, '1', '1', '1', '1'),
(18, 71, 18, '1', '1', '1', '1'),
(20, 71, 19, '0', '0', '0', '0'),
(21, 72, 12, '1', '1', '1', '1'),
(22, 72, 13, '1', '1', '1', '1'),
(23, 4, 73, '1', '1', '1', '1'),
(24, 4, 355, '1', '1', '1', '1'),
(25, 4, 363, '1', '1', '1', '1'),
(26, 4, 362, '1', '1', '1', '1'),
(27, 4, 361, '1', '1', '1', '1'),
(28, 4, 360, '1', '1', '1', '1'),
(29, 4, 359, '1', '1', '1', '1'),
(30, 4, 358, '1', '1', '1', '1'),
(31, 4, 357, '1', '1', '1', '1'),
(32, 4, 356, '1', '1', '1', '1'),
(33, 4, 354, '1', '1', '1', '1'),
(34, 4, 174, '1', '1', '1', '1'),
(35, 4, 74, '1', '1', '1', '1'),
(36, 4, 81, '1', '1', '1', '1');
