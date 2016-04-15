SET SQL_SAFE_UPDATES=0;

--删除无效地址

DELETE FROM 51daifan.cake_order_consignees where address is null or address='';

DELETE FROM 51daifan.cake_order_consignees where province_id is null or county_id is null;

DELETE FROM 51daifan.cake_order_consignees where province_id=0 or county_id=0;

--合并地址

UPDATE 51daifan.cake_order_consignees set type=0 where type=4;

--删除以前自提地址

DELETE FROM 51daifan.cake_order_consignees where type!=0;

UPDATE 51daifan.cake_order_consignees set status=1;


ALTER TABLE `51daifan`.`cake_order_consignees`
ADD INDEX `idx_order_consignee_creator_type_status` (`creator` ASC, `status` ASC, `type` ASC);
