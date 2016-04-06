SET SQL_SAFE_UPDATES=0;
UPDATE cake_weshares SET type=6 where refer_share_id in (SELECT weshare_id FROM cake_pool_products);
