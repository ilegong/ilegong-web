INSERT INTO `cake_weshare_delivery_templates`
(`id`,
`user_id`,
`weshare_id`,
`unit_type`,
`start_units`,
`start_fee`,
`add_units`,
`add_fee`,
`is_default`,
`created`)
SELECT 0,b.creator,a.weshare_id,0,1,a.ship_fee,1,0,1,'2016-02-24 19:00:00' FROM cake_weshare_ship_settings as a join cake_weshares as b on a.weshare_id = b.id where tag='kuai_di';
