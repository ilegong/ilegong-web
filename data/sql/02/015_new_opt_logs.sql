CREATE TABLE `cake_new_opt_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `share_id` int(11) NOT NULL,
    `proxy_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `data_type_tag` smallint(6) NOT NULL,
    `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted` tinyint(4) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_new_opt_logs_share_id` (`share_id`),
    KEY `idx_new_opt_logs_proxy_id` (`proxy_id`),
    KEY `idx_new_opt_logs_customer_id` (`customer_id`),
    KEY `idx_new_opt_logs_time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=11064 DEFAULT CHARSET=utf8mb4;
