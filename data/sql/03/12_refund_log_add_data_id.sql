ALTER TABLE `cake_refund_logs`
ADD COLUMN `data_id` INT NOT NULL DEFAULT 0 AFTER `type`;


update cake_refund_logs a
left join cake_orders b on
    a.order_id = b.id
set
    a.data_id = b.member_id;


