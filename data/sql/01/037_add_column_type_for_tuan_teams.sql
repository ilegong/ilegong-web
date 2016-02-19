alter table cake_tuan_teams add column `county_id` INT(11) default '0' not null;

update cake_tuan_teams set county_id = 110101 where tuan_addr like '%东城%';
update cake_tuan_teams set county_id = 110101 where tuan_addr like '%崇文%';
update cake_tuan_teams set county_id = 110102 where tuan_addr like '%西城%';
update cake_tuan_teams set county_id = 110102 where tuan_addr like '%宣武%';
update cake_tuan_teams set county_id = 110105 where tuan_addr like '%朝阳%';
update cake_tuan_teams set county_id = 110106 where tuan_addr like '%丰台%';
update cake_tuan_teams set county_id = 110107 where tuan_addr like '%石景山%';
update cake_tuan_teams set county_id = 110108 where tuan_addr like '%海淀%';
update cake_tuan_teams set county_id = 110109 where tuan_addr like '%门头沟%';
update cake_tuan_teams set county_id = 110111 where tuan_addr like '%房山%';
update cake_tuan_teams set county_id = 110112 where tuan_addr like '%通州%';
update cake_tuan_teams set county_id = 110113 where tuan_addr like '%顺义%';
update cake_tuan_teams set county_id = 110114 where tuan_addr like '%昌平%';
update cake_tuan_teams set county_id = 110115 where tuan_addr like '%大兴%';
update cake_tuan_teams set county_id = 110116 where tuan_addr like '%怀柔%';
update cake_tuan_teams set county_id = 110117 where tuan_addr like '%平谷%';
update cake_tuan_teams set county_id = 110128 where tuan_addr like '%密云%';
update cake_tuan_teams set county_id = 110129 where tuan_addr like '%延庆%';

