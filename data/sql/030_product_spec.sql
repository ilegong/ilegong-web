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
