ALTER  table cake_products add column product_alias varchar(20) default null;

INSERT INTO `cake_i18nfields` (`name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`)
VALUES
('product_alias', 1, '别名', 'string', 'Product', 'zh_cn', '20', NULL, 1, 1, '', '', '', NULL, 1, '', 0, '', '', '', 'input', '', 1, '', NULL, '', '', '', 0, '2014-01-11 22:07:16', '2014-01-11 22:07:16', '');