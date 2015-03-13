CREATE TABLE `cake_download_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `tag` VARCHAR(45) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`));

ALTER TABLE `cake_download_logs`
ADD UNIQUE INDEX `name_UNIQUE` (`name` ASC);

INSERT INTO `cake_download_logs` (`id`, `name`, `tag`) VALUES ('1', 'download_photo_from_wx', '0');

CREATE TABLE `cake_cron_faild_infos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `info_id` INT NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`));
