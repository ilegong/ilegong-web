CREATE TABLE `cake_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `pc_image` varchar(100) DEFAULT NULL,
  `mobile_image` varchar(100) DEFAULT NULL,
  `detail_url` varchar(100) DEFAULT NULL,
  `recommend` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


/*
-- Query: SELECT * FROM 51daifan.cake_modelextends where name like '%banner%'
LIMIT 0, 1000

-- Date: 2015-01-26 16:59
*/
INSERT INTO `cake_modelextends` (`name`,`cname`,`belongtype`,`modeltype`,`idtype`,`status`,`created`,`updated`,`tablename`,`related_model`,`security`,`operatorfields`,`deleted`,`cate_id`,`localetype`) VALUES ('Banner','Banner推广','onetomany','default','',26,'2014-06-30 23:06:27',NULL,'cake_banners','','','',0,1,0);


/*
-- Query: SELECT * FROM 51daifan.cake_settings cs where `key` like '%Banner%'
LIMIT 0, 100

-- Date: 2015-01-26 17:07
*/
INSERT INTO `cake_settings` (`key`,`value`,`title`,`description`,`input_type`,`editable`,`weight`,`params`,`scope`,`locale`) VALUES ('Banner.list_fields','id,pc_image,mobile_image,detail_url,product_name,recommend',NULL,NULL,'text',1,58,NULL,'manage','');
INSERT INTO `cake_settings` (`key`,`value`,`title`,`description`,`input_type`,`editable`,`weight`,`params`,`scope`,`locale`) VALUES ('Banner.pagesize','10','列表每页数目','','text',1,79,'','global','');
INSERT INTO `cake_settings` (`key`,`value`,`title`,`description`,`input_type`,`editable`,`weight`,`params`,`scope`,`locale`) VALUES ('Banner.search_fields','id,product_name',NULL,NULL,NULL,1,86,NULL,'manage','');


/*
-- Query: SELECT * FROM 51daifan.cake_i18nfields where model like '%Banner%'
LIMIT 0, 100

-- Date: 2015-01-26 17:08
*/
INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`) VALUES ('id',1,'编号','integer','Banner','zh_cn','11',28,0,1,NULL,NULL,NULL,NULL,1,NULL,0,NULL,NULL,'equal','',NULL,1,NULL,'',NULL,NULL,NULL,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL);
INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`) VALUES ('name',1,'产品名称','string','Banner','zh_cn','250',24,1,1,'',NULL,NULL,NULL,1,'',0,'','','equal','','',0,'','','','','',0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL);
INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`) VALUES ('pc_image',1,'pc展示图片','string','Banner','zh_cn','100',4,1,1,NULL,NULL,NULL,NULL,1,NULL,0,NULL,NULL,'equal','',NULL,1,NULL,'',NULL,NULL,NULL,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL);
INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`) VALUES ('mobile_image',1,'mobile手机图片','string','Banner','zh_cn','100',4,1,1,NULL,NULL,NULL,NULL,1,NULL,0,NULL,NULL,'equal','',NULL,1,NULL,'',NULL,NULL,NULL,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL);
INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`) VALUES ('detail_url',1,'产品详情url','string','Banner','zh_cn','100',4,1,1,NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,'equal',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL);
INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`)VALUES('recommend',1,'推荐度','integer','Banner','zh_cn','100',4,1,1,NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,'equal',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL);



ALTER TABLE `cake_banners` 
ADD COLUMN `type` INT NULL DEFAULT 0 AFTER `recommend`;

INSERT INTO `cake_i18nfields` (`name`,`savetodb`,`translate`,`type`,`model`,`locale`,`length`,`sort`,`allowadd`,`allowedit`,`selectmodel`,`selectvaluefield`,`selecttxtfield`,`selectparentid`,`selectautoload`,`selectvalues`,`associateflag`,`associateelement`,`associatefield`,`associatetype`,`formtype`,`default`,`allownull`,`validationregular`,`description`,`onchange`,`explodeimplode`,`explain`,`deleted`,`created`,`updated`,`conditions`)VALUES('type',1,'展示类型','integer','Banner','zh_cn','100',4,1,1,NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,'equal',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL);

UPDATE `cake_settings` SET `value`='id,pc_image,mobile_image,detail_url,product_name,recommend,type' WHERE `key`='Banner.list_fields';



