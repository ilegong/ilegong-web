update cake_settings set value='id,data_id,user_id,order_id,rating,body,created' where `key`='Comment.list_fields';
INSERT INTO `cake_i18nfields` (`name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`)
VALUES
	('order_id', 1, '订单号', 'integer', 'Comment', 'zh_cn', '11', 19, 0, 0, '', '', '', NULL, 1, '', 0, '', '', '', '', '0', 1, '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '');
