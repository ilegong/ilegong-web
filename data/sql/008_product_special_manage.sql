INSERT INTO `cake_modelextends`(`name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`)
VALUES ('ProductSpecial', '特惠', 'manytoone', 'default', '', 26, NULL, NULL, 'cake_product_specials', '', '', '', 0, 1, 2);


INSERT INTO `cake_settings` (`key`, `value`, `title`, `description`, `input_type`, `editable`, `weight`, `params`, `scope`, `locale`)
VALUES ('ProductSpecial.list_fields', 'product_id,special_id,published,limit_total,limit_per_user,special_price,show_day,recommend', NULL, NULL, 'text', 1, 58, NULL, 'manage', ''),
('ProductSpecial.pagesize', '100', '列表每页数目', '', 'text', 1, 79, '', 'global', ''),
 ('ProductSpecial.search_fields', 'id', NULL, NULL, NULL, 1, 86, NULL, 'manage', '');


INSERT INTO `cake_i18nfields` (`name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `conditions`)
VALUES ('id', 1, '编号', 'integer', 'ProductSpecial', 'zh_cn', '11', 28, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, 0, NULL, NULL, 'equal', '', NULL, 1, NULL, '', NULL, NULL, NULL, NULL),
('product_id', 1, '产品id', 'string', 'ProductSpecial', 'zh_cn', '4', 26, 1, 1, '', NULL, NULL, NULL, 1, '', 0, '', '', 'equal', 'input', '', 0, '', '', '', '', '', NULL),
('special_id', 1, '所属专场', 'string', 'ProductSpecial', 'zh_cn', '10', 23, 1, 1, NULL, NULL, NULL, NULL, 1, '3=>双十二专场\n4=>今日特价\n1=>活动专场\n2=>百度特惠专场', 0, NULL, NULL, 'equal', '', NULL, 1, NULL, '', NULL, NULL, '没找到想要的专场请联系开发', NULL),
('show_day', 1, '优惠日', ' date', 'ProductSpecial', 'zh_cn', NULL, 23, 1, 1, NULL, NULL, NULL, NULL, 1, NULL, 0, NULL, NULL, 'equal', 'input', '0000-00-00', 1, NULL, '', NULL, NULL, '今日特价适用（0000-00-00是则不限制优惠日）', NULL),
('created', 1, '创建时间', 'datetime', 'ProductSpecial', 'zh_cn', NULL, 22, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, 0, NULL, NULL, 'equal', 'datetime', NULL, 1, NULL, '', NULL, NULL, NULL, NULL),
('published', 1, '是否发布', 'boolean', 'ProductSpecial', 'zh_cn', '1', 21, 1, 1, NULL, NULL, NULL, NULL, 1, '0=>否\n1=>是', 0, NULL, NULL, 'equal', 'select', '1', 1, NULL, '', NULL, NULL, '', NULL),
('limit_total', 1, '总份数限制', 'integer', 'ProductSpecial', 'zh_cn', '4', 19, 1, 1, NULL, NULL, NULL, NULL, 1, '', 0, NULL, NULL, 'equal', 'input', '0', 1, NULL, '', NULL, NULL, '可选，0则不限制', NULL),
('limit_per_user', 1, '每人限制', 'integer', 'ProductSpecial', 'zh_cn', '4', 18, 1,1, NULL, NULL, NULL, NULL, 1, '', 0, NULL, NULL, 'equal', 'input', '0', 1, NULL, NULL, NULL, NULL, '可选， 0则不限制', NULL),
('special_price', 1, '价格', 'integer', 'ProductSpecial', 'zh_cn', '11', 24, 1, 1, NULL, NULL, NULL, NULL, 1, '', 0, NULL, NULL, 'equal', 'input', '-1', 1, NULL, ' ', NULL, NULL, '单位为分！为-1则使用默认价格, 不会在产品详情页显示优惠', NULL),
('recommend',1,'排序优先级','integer','ProductSpecial','zh_cn','11',25,1,1,NULL,NULL,NULL,NULL,1,'',0,NULL,NULL,'equal','',1,1,NULL,NULL,NULL,NULL,'排序优先级，值越大的排前面',NULL);
