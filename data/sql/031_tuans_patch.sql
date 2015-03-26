
-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- Host: w.rdc.sae.sina.com.cn:3307
-- Generation Time: Mar 12, 2015 at 10:30 AM
-- Server version: 5.5.23
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `app_51daifan`
--

-- --------------------------------------------------------

--
-- Table structure for table `cake_tuans`
--

CREATE TABLE IF NOT EXISTS `cake_tuans` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(80) NOT NULL DEFAULT '',
  `leader_name` varchar(18) DEFAULT NULL,
  `tuan_name` varchar(40) NOT NULL DEFAULT '',
  `leader_id` int(11) DEFAULT '0',
  `leader_weixin` varchar(20) NOT NULL DEFAULT '0',
  `status` tinyint(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `location_long` double DEFAULT '0',
  `location_lat` double DEFAULT '0',
  `tuan_addr` varchar(255) DEFAULT '',
  `tuan_desc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `cake_tuans`
--

INSERT INTO `cake_tuans` (`id`, `address`, `leader_name`, `tuan_name`, `leader_id`, `leader_weixin`, `status`, `created`, `location_long`, `location_lat`, `tuan_addr`, `tuan_desc`) VALUES
(1, '搜狐媒体好邻居店', '潘婷', '搜狐媒体美食团    ', 0, 'pt8220707', 0, NULL, 116.33112, 39.989367, '中关村新科祥园甲2号楼1层03室', NULL),
(3, '金宝街好邻居2分店', '燕子', '金宝街美食团', 0, 'pyshuo2015', 0, NULL, 116.429071, 39.921718, '北京市东城区金宝街临时3号', NULL),
(4, '西三旗上奥世纪B座430', '浩子', '西三旗美食团', 0, 'pyshuo2015', 0, NULL, 116.336402, 40.06276, '北京市昌平区建材城西路 ', NULL),
(5, '五道口搜狐网络大厦', '王谷丹', '五道口美食团', 0, 'pyshuo2015', 0, NULL, 116.339079, 39.999683, '北京市海淀区中关村东路1号	', NULL),
(6, '苏州街好邻居6分店', '李慧敏', '中关村美食团', 0, 'pyshuo2015', 0, NULL, 116.312186, 39.985067, '中关村银科大厦—苏州街6店(海淀区苏州街28号)', NULL),
(7, '好邻居木樨地店', '侯志嘉', '木樨地美食团', 0, 'pyshuo2015', 0, NULL, 116.345032, 39.913418, '西城区木樨地25号', NULL),
(8, '好邻居便利店（建华南路分店）', 'Amy', '建华南路好邻居新鲜水果自体部落', 0, 'amyshen', 0, NULL, 116.316178, 40.047731, '建外大街建华南路11号商通大厦底商', NULL),
(9, '联想新大厦好邻居', 'Haodm 喜乐', '联想新大厦好邻居美食团', 0, 'wxid_lelerita', 0, NULL, 116.34625, 39.91258, '海淀区上地创业路8号', NULL);

UPDATE `cake_tuans` SET `address`='好邻居(建华南路分店)', `tuan_name`='建华南路好邻居' WHERE `id`='8';

UPDATE `cake_tuans` SET `leader_name`='喜乐', `tuan_name`='联想新大厦美食团' WHERE `id`='9';


INSERT INTO `cake_tuans` (`id`, `address`, `leader_name`, `tuan_name`, `leader_id`, `leader_weixin`, `status`, `location_long`, `location_lat`, `tuan_addr`) VALUES ('10', '工体东路好邻居', 'Eva', '工体东路美食团', '0', 'xiyingying7479', '0', '116.458039', '39.932453', '朝阳区工人体育场东路16号北门附近');
INSERT INTO `cake_tuans` (`id`, `address`, `tuan_name`, `leader_id`, `leader_weixin`, `status`, `location_long`, `location_lat`, `tuan_addr`) VALUES ('11', '好邻居便利店(望京西园店)', '望京西园美食团', '0', ' Nancy Niu', '0', '116.474849', '40.008671', '广顺北大街望京西园二区222号楼星源国际公寓E座1楼2号');




INSERT INTO `cake_tuans` (`id`, `address`, `leader_name`, `tuan_name`, `leader_id`, `leader_weixin`, `status`, `location_long`, `location_lat`, `tuan_addr`) VALUES ('12', '石景山', '李文艳', '石景山美食团', '0', 'pyshuo2015', '0', '116.210203', '39.924284', '石景山区八角东街65号融科创意中心');

INSERT INTO `cake_tuans` (`id`, `address`, `leader_name`, `tuan_name`, `leader_id`, `leader_weixin`, `status`, `location_long`, `location_lat`, `tuan_addr`) VALUES ('13', '海淀桥', '羊羊', '海淀桥美食团', '0', 'pyshuo2015', '0', '116.311607', '39.987321', '海淀区海淀大街44号蓝格赛底商103门 ');

UPDATE `cake_tuans` SET `leader_name`='Nancy Niu' WHERE `id`='11';

UPDATE `cake_tuans` SET `leader_weixin`='nancygirl2011' WHERE `id`='11';

UPDATE `cake_tuans` SET `location_long`='116.212569', `tuan_addr`='石景山八角东路(西五环与长安街交汇处西北侧)' WHERE `id`='12';

alter table `cake_tuans` add `priority` BIGINT(11) default 0;




-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- Host: w.rdc.sae.sina.com.cn:3307
-- Generation Time: Mar 12, 2015 at 10:31 AM
-- Server version: 5.5.23
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `app_51daifan`
--

-- --------------------------------------------------------

--
-- Table structure for table `cake_tuan_buyings`
--

CREATE TABLE IF NOT EXISTS `cake_tuan_buyings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tuan_id` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `join_num` int(11) DEFAULT '0',
  `sold_num` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `end_time` datetime DEFAULT NULL,
  `consign_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `cake_tuan_buyings`
--

INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES
(1, 1, 838, 3, 5, 0, '2015-03-12 22:00:00', '2015-03-13 10:00:00'),
(2, 3, 838, 1, 1, 0, '2015-03-12 22:00:00', '2015-03-13 10:00:00'),
(3, 4, 838, 4, 21, 0, '2015-03-12 22:00:00', '2015-03-13 10:00:00'),
(4, 5, 838, 16, 35, 0, '2015-03-12 22:00:00', '2015-03-13 10:00:00'),
(5, 6, 838, 3, 11, 0, '2015-03-16 17:00:00', '2015-03-17 10:00:00'),
(6, 7, 838, 0, 0, 0, '2015-03-16 17:00:00', '2015-03-17 10:00:00'),
(7, 8, 838, 1, 2, 0, '2015-03-16 17:00:00', '2015-03-17 10:00:00'),
(8, 9, 838, 0, 0, 0, '2015-03-16 17:00:00', '2015-03-17 10:00:00');


INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('9', '4', '838', '0', '0', '0', '2015-03-19 17:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('10', '1', '838', '0', '0', '0', '2015-03-18 17:00:00', '2015-03-19 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('11', '7', '838', '0', '0', '0', '2015-03-18 17:00:00', '2015-03-19 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('12', '9', '838', '0', '0', '0', '2015-03-18 17:00:00', '2015-03-19 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('13', '13', '838', '0', '0', '0', '2015-03-16 17:00:00', '2015-03-17 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('14', '3', '838', '0', '0', '0', '2015-03-19 17:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('15', '5', '838', '0', '0', '0', '2015-03-19 17:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('16', '12', '838', '0', '0', '0', '2015-03-19 17:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('17', '10', '838', '0', '0', '0', '2015-03-19 17:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`id`, `tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('18', '11', '838', '0', '0', '0', '2015-03-19 17:00:00', '2015-03-20 10:00:00');




INSERT INTO `cake_tuans` (`address`, `tuan_name`, `leader_name`, `leader_weixin`, `status`, `location_long`, `location_lat`, `tuan_addr`) VALUES ('北辰世纪中心A座', '京东北辰美食团', '叶小胖', 'yexiaopang002', '0', '116.394458', '40.006543', '北辰世纪中心A座');
INSERT INTO `cake_tuans` (`address`, `tuan_name`, `leader_name`, `leader_weixin`, `status`, `location_long`, `location_lat`, `tuan_addr`) VALUES ('大兴区荣华中路朝林广场A座', '京东亦庄美食团', '叶小胖', 'yexiaopang002', '0', '116.512409', '39.799484', '大兴区荣华中路朝林广场A座');
INSERT INTO `cake_tuans` (`address`, `tuan_name`, `leader_name`, `leader_weixin`, `status`, `location_long`, `location_lat`, `tuan_addr`) VALUES ('海淀区杏石口路65号益园文创基地C区11号楼', '京东杏石口美食团', '叶小胖', 'yexiaopang002', '0', '116.241019', '39.957172', '海淀区杏石口路65号益园文创基地C区11号楼');


INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('19', '838', '0', '0', '0', '2015-03-19 18:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('20', '838', '0', '0', '0', '2015-03-19 18:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('21', '838', '0', '0', '0', '2015-03-19 18:00:00', '2015-03-20 10:00:00');
INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('1', '838', '0', '0', '0', '2015-03-23 18:00:00', '2015-03-24 10:00:00');
INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('6', '838', '0', '0', '0', '2015-03-23 18:00:00', '2015-03-24 10:00:00');
INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('15', '230', '0', '0', '0', '2015-03-29 18:00:00', '2015-03-30 10:00:00');
INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('25', '230', '0', '0', '0', '2015-03-29 18:00:00', '2015-03-31 10:00:00');
INSERT INTO `cake_tuan_buyings` (`tuan_id`, `pid`, `join_num`, `sold_num`, `status`, `end_time`, `consign_time`) VALUES ('28', '230', '0', '0', '0', '2015-03-29 18:00:00', '2015-03-31 10:00:00');





