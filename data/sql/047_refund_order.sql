CREATE TABLE `cake_refund_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11)  NOT NULL,
  `refund_fee` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `trade_type` varchar(20) NOT NULL DEFAULT '',
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8;

ALTER TABLE cake_refund_logs
     ADD CONSTRAINT fk_cake_refund_logs_order_id
     FOREIGN KEY (order_id)
     REFERENCES cake_orders(id);