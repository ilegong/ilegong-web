DROP TABLE IF EXISTS `cake_staff_roles`;
CREATE TABLE IF NOT EXISTS `cake_staff_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_id` (`staff_id`,`role_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '0', 'id', 'integer', 'StaffRole', NULL, '11', 0, '0', '0', NULL, NULL, NULL, 0, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-06-22 23:49:41', '2013-06-22 23:49:41', NULL),
(NULL, 'staff_id', '0', 'staff_id', 'integer', 'StaffRole', NULL, '11', 0, '0', '0', NULL, NULL, NULL, 0, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-06-22 23:49:41', '2013-06-22 23:49:41', NULL),
(NULL, 'role_id', '0', 'role_id', 'integer', 'StaffRole', NULL, '11', 0, '0', '0', NULL, NULL, NULL, 0, '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2013-06-22 23:49:41', '2013-06-22 23:49:41', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'StaffRole', 'StaffRole', 'onetomany', 'default', NULL, 1, '2013-10-16 22:09:16', '2013-10-16 22:09:16', 'cake_staff_roles', NULL, NULL, NULL, '0', 0, 0);
