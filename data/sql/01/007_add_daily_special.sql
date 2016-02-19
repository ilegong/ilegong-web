INSERT INTO `cake_special_lists` (`id`, `name`, `slug`, `recommend`, `published`, `created`, `showed_count`, `start`, `end`, `show_timer`, `headimg`, `type`, `share_img`) VALUES
(4, '今日特价', 'daily_special_fjdldlafjkas', 1, 1, NULL, 100, '2014-12-12 00:00:00', '2020-01-01 00:00:00', 1, '', 1, '');


alter table `cake_special_lists` add column visible tinyint not null default 0;
alter table `cake_product_specials` add column show_day date not null default '0000-00-00';

update cake_special_lists set visible = 0 where id = 4;

INSERT INTO `cake_product_specials` (`product_id`, `special_id`, `published`, `show_count`, `limit_total`, `limit_per_user`, `special_price`, `show_day`)
VALUES
	(81, 4, 1, 100, 20, 1, 3800, '2015-01-21');






