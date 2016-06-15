ALTER TABLE `51daifan`.`cake_sharer_statics_datas`
ADD COLUMN `view_count` BIGINT NOT NULL DEFAULT 0 AFTER `fans_count`;
