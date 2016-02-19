CREATE TABLE `cake_statistics_refer_datas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL DEFAULT 0,
  `recommend_user_count` INT NOT NULL DEFAULT 0,
  `sum_money` INT NOT NULL DEFAULT 0,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  PRIMARY KEY (`id`));
