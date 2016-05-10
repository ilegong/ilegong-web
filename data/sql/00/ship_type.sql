drop table cake_ship_types;
CREATE TABLE `cake_ship_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) DEFAULT '0',
  `priority` int(11) DEFAULT '0',
  `deleted` tinyint(1) default 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);



insert into cake_ship_types(id, name) values
  (101, '申通'),
  (102, '圆通'),
  (103, '韵达'),
  (104, '顺丰'),
  (105, 'EMS'),
  (106, '邮政包裹'),
  (107, '天天'),
  (108, '汇通'),
  (109, '中通'),
  (110, '全一'),
  (111, '宅急送'),
  (112, '全峰'),
  (113, '快捷')
;


INSERT INTO `cake_modelextends` (`name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`)
VALUES
  ('ShipType', '快递方式', 'onetomany', 'default', '', 26, '2014-06-30 23:06:27', '2014-06-30 23:06:27', 'cake_ship_types', '', '', '', 0, 1, 0);

INSERT INTO `cake_settings` (`key`, `value`, `title`, `description`, `input_type`, `editable`, `weight`, `params`, `scope`, `locale`)
VALUES
  ('ShipType.list_fields', 'id,name,priority', NULL, NULL, 'text', 1, 58, NULL, 'manage', ''),
  ('ShipType.pagesize', '100', '列表每页数目', '', 'text', 1, 79, '', 'global', ''),
  ('ShipType.search_fields', 'id,name', NULL, NULL, NULL, 1, 86, NULL, 'manage', '')
;
