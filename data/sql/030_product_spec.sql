CREATE TABLE cake_product_attributes (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `deleted` INT NULL DEFAULT 0,
  PRIMARY KEY (`id`));

  CREATE TABLE cake_product_specs (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL,
  `attr_id` INT NULL,
  `deleted` INT NULL DEFAULT 0,
  PRIMARY KEY (`id`));

CREATE TABLE cake_product_spec_groups (
  `id` INT NOT NULL AUTO_INCREMENT,
  `spec_ids` VARCHAR(100) NULL,
  `price` FLOAT NULL,
  `stock` INT NULL,
  `deleted` INT NULL DEFAULT 0,
  PRIMARY KEY (`id`));

ALTER TABLE `cake_product_spec_groups`
ADD COLUMN `spec_names` VARCHAR(500) NULL AFTER `deleted`;

ALTER TABLE `cake_product_spec_groups`
ADD COLUMN `product_id` INT NULL AFTER `spec_names`;


