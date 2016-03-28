--新的动态表
CREATE TABLE cake_new_opt_logs (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `share_id` int(11) NOT NULL,
      `proxy_id` int(11) NOT NULL,
      `customer_id` int(11) NOT NULL,
      `data_type_tag` smallint NOT NULL,
      `time` datetime NOT NULL,
      `deleted` tinyint NOT NULL,
      primary key(`id`),
      unique index `idx_share_id` (`share_id` ASC),
      index `idx_proxy_id` (`proxy_id` ASC),
      index `idx_customer_id` (`customer_id` ASC),
      index `idx_time` (`time` ASC)
)engine=InnoDB default charset utf8;

