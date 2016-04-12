SET SQL_SAFE_UPDATES=0;

--删除空地址

DELETE FROM 51daifan.cake_order_consignees where address is null or address='';

DELETE FROM 51daifan.cake_order_consignees where province_id is null or county_id is null;

DELETE FROM 51daifan.cake_order_consignees where province_id=0 or county_id=0;

UPDATE 51daifan.cake_order_consignees set status=1 where type!=0;

UPDATE 51daifan.cake_order_consignees as c1 set c1.status=1 WHERE c1.id in (SELECT max(c2.id) FROM 51daifan.cake_order_consignees as c2 where c2.type=0 group by c2.creator);
