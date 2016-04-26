alter table cake_index_products add column measurement varchar(255) after description;
alter table cake_users add column label varchar(15) after is_proxy;

update cake_users set label = '品质尖货买手' where id = 811917;
update cake_users set label = '爱心宝妈' where id = 810684;
update cake_users set label = '美女团长' where id = 897075;
update cake_users set label = '橘子姐姐' where id = 815328;
update cake_users set label = '资深吃货' where id = 173967;
update cake_users set label = '品质吃货' where id = 849084;
update cake_users set label = '辣妈团长' where id = 806889;
update cake_users set label = '超级奶爸' where id = 859965;
update cake_users set label = '超级辣妈' where id = 878825;
update cake_users set label = '山药姐姐' where id = 12376;

alter table cake_index_products change measurement specification varchar(255);
