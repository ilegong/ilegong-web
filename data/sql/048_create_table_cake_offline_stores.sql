DROP TABLE IF EXISTS `cake_offline_stores`;

CREATE TABLE IF NOT EXISTS `cake_offline_stores` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_no` int(11) NOT NULL DEFAULT '0',
  `area_id` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(64) DEFAULT NULL,
  `name` varchar(256) NOT NULL DEFAULT '',
  `type` smallint(3) NOT NULL DEFAULT '0',
  `owner_name` varchar(32) DEFAULT NULL,
  `owner_phone` varchar(20) DEFAULT NULL,
  `delete` tinyint(1) NOT NULL DEFAULT '0',
  `location_long` double NOT NULL DEFAULT '0',
  `location_lat` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `cake_offline_stores` (`id`, `shop_no`, `area_id`, `alias`, `name`, `type`, `owner_name`, `owner_phone`, `delete`, `location_long`, `location_lat`) VALUES
(1, 0, 110108, '百度二店', '北京海淀区上地东路一号院鹏寰大厦（好邻居-百度二店）', 0, NULL, NULL, 0, 116.308402, 40.061414),
(2, 830, 110108, '苏州街6店', '北京海淀区苏州街28号银科大厦好邻居(苏州街6店)', 0, NULL, NULL, 0, 116.312186, 39.985067),
(3, 275, 110105, '丽都店', '北京市朝阳区高家园小区311号好邻居便利店', 0, NULL, NULL, 0, 116.486509, 39.984958),
(4, 322, 110105, '七圣路分店', '北京市朝阳区光熙门北里甲31号好邻居便利店', 0, NULL, NULL, 0, 116.442097, 39.973949),
(5, 298, 110105, '慧忠北路店', '北京市朝阳区慧忠北路慧忠里231楼鼓浪屿会所一层底商好邻居便利店', 0, NULL, NULL, 0, 116.407968, 40.004823),
(6, 291, 110105, '酒仙桥路店', '北京市朝阳区酒仙桥路26号院1号楼A05号好邻居便利店', 0, NULL, NULL, 0, 116.500083, 39.970223),
(7, 300, 110101, '东直南小店', '北京市东直门南小街20-1 好邻居便利店', 0, NULL, NULL, 0, 116.43254, 39.940958),
(8, 263, 110108, '畅春园店', '北京市海淀区西苑草场5号好邻居便利店', 0, NULL, NULL, 0, 116.3113, 39.995205),
(9, 221, 110102, '友谊医院店', '北京市宣武区永安路32号好邻居便利店', 0, NULL, NULL, 0, 116.401018, 39.892384),
(10, 343, 110105, '管庄分店', '朝阳区朝阳路管庄西里65号好邻居便利店', 0, NULL, NULL, 0, 116.595474, 39.918661),
(11, 148, 110105, '北沙滩店', '朝阳区大屯路风林绿洲小区6号楼底商D单元S-F06-01D好邻居便利店', 0, NULL, NULL, 0, 116.390494, 40.00807),
(12, 249, 110105, '好邻居广顺桥南店', '朝阳区利泽中一路1号望京科技大厦商铺好邻居便利店', 0, NULL, NULL, 0, 116.47653, 40.019315),
(13, 229, 110101, '都市馨园店', '崇文区兴隆都市馨园地上一层A101好邻居便利店', 0, NULL, NULL, 0, 116.42161, 39.901753),
(14, 251, 110101, '金宝街金宝汇店', '东城区金宝街道路北侧一线临时建筑物好邻居便利店', 0, NULL, NULL, 0, 116.429121, 39.921926),
(15, 146, 110101, '兴化路店', '东城区兴华西里2号楼南侧好邻居便利店', 0, NULL, NULL, 0, 116.419989, 39.96611),
(16, 125, 110108, '北三环店', '海淀区北三环西路60号好邻居便利店', 0, NULL, NULL, 0, 116.326494, 39.971958),
(17, 183, 110108, '厂洼路店', '海淀区厂洼小区24号楼北京电视台西门好邻居便利店', 0, NULL, NULL, 0, 116.313109, 39.964403),
(18, 351, 110108, '板井店', '海淀区车道沟桥进入板井路单行路直行300米路北好邻居便利店', 0, NULL, NULL, 0, 116.295582, 39.955243),
(19, 350, 110108, '大钟寺东路', '海淀区大钟寺东路京仪大厦底商海淀区大钟寺东路9号1幢1层101-1好邻居便利店', 0, NULL, NULL, 0, 116.344811, 39.977391),
(20, 169, 110108, '科学院南路店', '海淀区科学院南路55号 中关村中学正对面好邻居便利店', 0, NULL, NULL, 0, 116.331498, 39.984315),
(21, 561, 110108, '万泉河店', '海淀区万泉河路68号紫金大厦1层好邻居便利店', 0, NULL, NULL, 0, 116.314524, 39.972615),
(22, 147, 110108, '羊坊路店', '海淀区羊坊店路3号 好邻居便利店', 0, NULL, NULL, 0, 116.327509, 39.911252),
(23, 199, 110108, '永定路店', '海淀区永定路63号(武警总医院北200米) 好邻居便利店', 0, NULL, NULL, 0, 116.270987, 39.918917),
(24, 379, 110108, '科南二分店', '海淀区中关村新科祥园甲2号楼1层03室好邻居底商', 0, NULL, NULL, 0, 116.33112, 39.989367),
(25, 116, 110102, '佟麟阁路店', '西城区佟麟阁路91号好邻居便利店', 0, NULL, NULL, 0, 116.374796, 39.9067),
(26, 110, 110102, '月坛北街店', '西城区月坛北街11号楼7号好邻居便利店', 0, NULL, NULL, 0, 116.352528, 39.924319),
(27, 0, 110108, '电科院超市发店', '清河小营电科院旁边超市发', 1, NULL, NULL, 0, 116.356246, 40.045908),
(28, 0, 110108, '上奥世纪B座', '西三旗上奥世纪B座430', 1, NULL, NULL, 0, 116.336402, 40.06276),
(29, 0, 110108, '育新小区北门超市', '西三旗育新小区北门超市', 1, NULL, NULL, 0, 116.351228, 40.06509),
(30, 0, 110108, '知本时代御享果蔬店', '西三旗知本时代御享果蔬店', 1, NULL, NULL, 0, 116.365372, 40.068385),
(31, 0, 110108, '富力桃园世纪华联超市', '西三旗富力桃园世纪华联超市', 1, NULL, NULL, 0, 116.384685, 40.068675),
(32, 0, 110108, '当代城市花园东门对面焙好味面包坊', '安宁庄西路安宁华庭3区上林溪底商焙好味面包坊', 1, NULL, NULL, 0, 116.329054, 40.050175),
(33, 0, 110114, '万科小区', '昌平县城万科城东门世纪联华超市蔬菜水果铺', 1, NULL, NULL, 0, 116.242337, 40.213565),
(34, 0, 110114, '佳莲小区', '昌平区中山口路21号妇幼保健医院东面佳莲小区底商万姐商店', 1, NULL, NULL, 0, 116.2473051541, 40.235703868744),
(35, 0, 110114, '国泰商场附近', '昌平区东二条胡同阳光麦当劳西侧金时商店（联系人：谷振勇）', 1, NULL, '13716575292', 0, 116.25076, 40.226406),
(36, 0, 110114, '宁馨苑畅春阁', '昌平区宁馨苑小区北门往里50m路东', 1, NULL, NULL, 0, 116.265442, 40.223667),
(37, 0, 110114, '西关三角地', '昌平区西关三角地政府街西路南侧', 1, NULL, NULL, 0, 116.23073330388, 40.22583207036),
(38, 0, 110114, '北七家望都新地', '昌平区北七家镇物美停车场内红花郎烟酒专卖', 1, NULL, NULL, 0, 116.439218, 40.072064),
(39, 0, 110114, '领秀慧谷', '昌平区朱辛庄领秀慧谷B区101号领秀超市', 1, NULL, NULL, 0, 116.30971295313, 40.103086049872),
(40, 0, 110114, '回龙观天露园/风雅园', '昌平区回龙观天露园二区3号楼182-1红星二锅头专卖店', 1, NULL, NULL, 0, 116.32402912318, 40.089137971483),
(41, 0, 110114, '拓然佳苑小区', '昌平区科技园区白浮泉路12-2圆通快递', 1, NULL, NULL, 0, 116.243894, 40.205174),
(42, 508, 110102, '木樨地店', '西城区复兴门外大街甲22号木樨地好邻居店', 0, NULL, NULL, 0, 116.34625, 39.91258),
(43, 296, 110108, '西直门北店', '北京市海淀区西直门北大街47号院2号楼北侧一层好邻居店', 0, NULL, NULL, 0, 116.362382, 39.953238);
