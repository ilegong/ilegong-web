SET SQL_SAFE_UPDATES=0;

UPDATE cake_weshares SET type=6 where refer_share_id in (SELECT weshare_id FROM cake_pool_products);

UPDATE cake_weshares set type=3 where id in (SELECT weshare_id FROM cake_pool_products);

UPDATE cake_weshares set type=3 and refer_share_id=0 where id in (SELECT weshare_id FROM cake_pool_products);

UPDATE cake_weshares set refer_share_id=0 where type=4;

