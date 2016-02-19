insert into cake_locations (`id`,`name`, `parent_id`) values
(1, '华东', -2),
(2, '华北', -2),
(3, '华中', -2),
(4, '华南', -2),
(5, '东北', -2),
(6, '西北', -2),
(7, '西南', -2),
(8, '港澳台', -2);

update cake_locations set parent_id = 1 where parent_id = 1 and name in ('上海', '江苏', '浙江', '安徽', '江西');
update cake_locations set parent_id = 2 where parent_id = 1 and name in ('北京', '天津', '山西', '山东', '河北', '内蒙古');
update cake_locations set parent_id = 3 where parent_id = 1 and name in ('湖南', '湖北', '河南');
update cake_locations set parent_id = 4 where parent_id = 1 and name in ('广东', '广西', '福建', '海南');
update cake_locations set parent_id = 5 where parent_id = 1 and name in ('辽宁', '吉林', '黑龙江');
update cake_locations set parent_id = 6 where parent_id = 1 and name in ('陕西', '新疆', '甘肃', '宁夏', '青海');
update cake_locations set parent_id = 7 where parent_id = 1 and name in ('重庆', '云南', '贵州', '西藏', '四川');
update cake_locations set parent_id = 7 where parent_id = 1 and name in ('香港', '澳门', '台湾');
