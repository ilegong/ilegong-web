ALTER TABLE `cake_comments`
ADD COLUMN `data_creator` BIGINT NULL AFTER `publish_time`;
update cake_comments, cake_weshares set cake_comments.data_creator=cake_weshares.creator where cake_comments.data_id = cake_weshares.id and cake_comments.type='Share';
