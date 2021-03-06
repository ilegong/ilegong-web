ALTER TABLE `cake_opt_logs`
ADD COLUMN `event_id` INT NULL AFTER `obj_id`;

ALTER TABLE `cake_opt_logs`
CHANGE COLUMN `event_id` `event_id` INT(11) NULL DEFAULT 0 ;


ALTER TABLE `51daifan`.`cake_opt_logs`
CHANGE COLUMN `id` `id` BIGINT(32) NOT NULL AUTO_INCREMENT ;


--创建分享的event_id 设置为 分享 id
update cake_opt_logs set event_id=obj_id where obj_type=1;
--推荐分享的event_id 设置为 分享 id
update cake_opt_logs set event_id=obj_id where obj_type=2;
--以前小区拼团数据
update cake_opt_logs set event_id=obj_id where obj_type=5;
--类似拼货拼团数据
update cake_opt_logs set event_id=obj_id where obj_type=6;

update cake_opt_logs

   left join cake_orders

   on cake_opt_logs.obj_id=cake_orders.member_id

   and cake_opt_logs.user_id=cake_orders.creator

   and cake_orders.type=9

   and date(cake_opt_logs.created)=date(cake_orders.created)

   set cake_opt_logs.event_id=cake_orders.id

where cake_opt_logs.obj_type=3;


update cake_opt_logs

   left join cake_comments

   on cake_opt_logs.obj_id=cake_comments.data_id

   and cake_opt_logs.user_id=cake_comments.user_id

   and cake_comments.type='Share'

   and cake_opt_logs.reply_content = cake_comments.body

   and date(cake_opt_logs.created)=date(cake_comments.created)

   set cake_opt_logs.event_id=cake_comments.id

where cake_opt_logs.obj_type=4;
