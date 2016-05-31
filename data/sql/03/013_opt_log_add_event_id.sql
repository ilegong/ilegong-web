ALTER TABLE `cake_opt_logs`
ADD COLUMN `event_id` INT NULL AFTER `obj_id`;

ALTER TABLE `cake_opt_logs`
CHANGE COLUMN `event_id` `event_id` INT(11) NULL DEFAULT 0 ;

--创建分享的event_id 设置为 分享 id
update cake_opt_logs set event_id=obj_id where obj_type=1;

update cake_opt_logs set event_id=obj_id where obj_type=2;


update cake_opt_logs

   left join cake_orders on cake_opt_logs.obj_id=cake_orders.member_id

   and cake_opt_logs.obj_creator=cake_orders.creator

   and date(cake_opt_logs.created)=date(cake_orders.created)

   set cake_opt_logs.event_id=cake_orders.id

where cake_opt_logs.obj_type=3;


update cake_opt_logs

   left join cake_orders on cake_opt_logs.obj_id=cake_orders.member_id

   and cake_opt_logs.obj_creator=cake_orders.creator

   and date(cake_opt_logs.created)=date(cake_orders.created)

   set cake_opt_logs.event_id=cake_orders.id

where cake_opt_logs.obj_type=4;

