alter table cake_pool_products add column category smallint(2) after created;
update cake_pool_products set category = 1;

CREATE TABLE `cake_pool_product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(60) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO cake_pool_product_categories VALUES ('', '测试分类1', 0);
INSERT INTO cake_pool_product_categories VALUES ('', '测试分类2', 0);
INSERT INTO cake_pool_product_categories VALUES ('', '测试分类3', 0);