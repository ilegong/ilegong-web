DROP TABLE IF EXISTS `cake_product_consignments`;

CREATE TABLE IF NOT EXISTS `cake_product_consignments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned DEFAULT NULL,
  `consignment_id` date NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

ALTER TABLE cake_product_consignments
  ADD CONSTRAINT uc_cake_product_consignments_product_id_consignment_id UNIQUE (product_id, consignment_id);