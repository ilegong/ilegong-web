ALTER TABLE `cake_oauthbinds`
CHANGE COLUMN `unionId` `unionId` VARCHAR(64) NULL DEFAULT NULL;

create index idx_oauthbinds_unionId on cake_oauthbinds (unionId);
