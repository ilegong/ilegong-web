alter table cake_product_tries add column product_name varchar(255) default '' not null;

INSERT INTO `cake_i18nfields` (`name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`)
VALUES ('product_name',1,'产品名称','string','ProductTry','zh_cn','250','17',1,1,NUll,NULL,NULl,NULL,1,'','0',NULL,NULL,'equal','input','',1,NULL,'',NULL,NULL,'用于管理员标记产品名称',0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL)

UPDATE  `cake_settings` SET  `value` =  'id,product_name,product_id,start_time,spec,price,limit_num,status,sold_num' WHERE  `cake_settings`.`id` =123;