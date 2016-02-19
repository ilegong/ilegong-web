ALTER TABLE `cake_products`
ADD COLUMN `limit_area` INT NOT NULL DEFAULT 0 AFTER `sort_in_store`;

INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`) VALUES ('limit_area',1,'限制北京','integer','Product','zh_cn','1',0,1,1,NULL,NULL,NULL,NULL,1,'',0,NULL,NULL,'','input','0',1,NULL,NULL,NULL,NULL,NULL,0,'2014-10-21 19:22:30','2014-10-21 19:22:30',NULL);

ALTER TABLE `cake_products`
ADD COLUMN `listimg` VARCHAR(255) NOT NULL AFTER `limit_area`;


INSERT INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES ('0', 'listimg', '1', '列表图片', 'string', 'Product', 'zh_cn', '255', '27', '1', '1', '', '1', '', '0', '', '', 'equal', 'coverimg', '', '1', '', '', '', '', '', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '');

