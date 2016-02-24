ALTER TABLE `cake_order_consignees`
ADD COLUMN `type` INT NOT NULL DEFAULT 0 AFTER `deleted`;
update cake_order_consignees set type=2 where status=2;
update cake_order_consignees set type=3 where status=3;
update cake_order_consignees set type=4 where status=4;
update cake_order_consignees set type=5 where status=5;
