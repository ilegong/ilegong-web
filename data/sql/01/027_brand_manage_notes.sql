alter table cake_brands add column manage_notes varchar(1000) default '' not null;

INSERT INTO `cake_i18nfields` (`name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`)
VALUES ('manage_notes',1,'管理备注','string','Brand','zh_cn','400','17',1,1,NUll,NULL,NULl,NULL,1,'','0',NULL,NULL,'equal','textarea','',1,NULL,'',NULL,NULL,'用于管理员记录商家备忘信息',0,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL)

update cake_settings set value='id,cate_id,name,coverimg,manage_notes,creator,published,deleted' where `key`='Brand.list_fields';