insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '315', 15);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '577', 10);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '652', 10);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '336', 10);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '560', 15);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '300', 20);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '606', 15);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '610', 20);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '262', 20);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '666', 20);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '302', 5 );
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '365', 10);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '275', 10);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '274', 20);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '266', 5 );
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '699', 20);
insert into cake_coupons(name, valid_begin, valid_end, last_updator, type, product_list, reduced_price) values('年货专场', '2015-01-21 00:00:00', '2015-02-02 00:00:00', 632, 2, '561', 20);

update cake_coupons c inner join cake_products p on c.product_list = p.id  set c.status = 1,  c.brand_id = p.brand_id, c.published = 1  where valid_end='2015-02-02 00:00:00';