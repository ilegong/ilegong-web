ALTER TABLE `cake_offline_stores`
ADD COLUMN `child_area_id` INT(11) DEFAULT NULL;
update cake_offline_stores set child_area_id = 900001 where name like '%昌平县城%';
update cake_offline_stores set child_area_id = 900002 where name like '%天通苑%';
update cake_offline_stores set child_area_id = 900003 where name like '%回龙观%';
update cake_offline_stores set child_area_id = 900004 where name like '%北七家镇%';
update cake_offline_stores set child_area_id = 900005 where name like '%沙河镇%';
update cake_offline_stores set child_area_id = 900006 where name like '%立水桥%';
update cake_offline_stores set child_area_id = 900007 where name like '%霍营%';
update cake_offline_stores set child_area_id = 900001 where id in (54,172);
