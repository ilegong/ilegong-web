---标示分享者是否已经激活红包了
ALTER TABLE `cake_share_offers`
ADD COLUMN `sharer_active` TINYINT(4) NOT NULL DEFAULT 1 AFTER `sharer_id`;
