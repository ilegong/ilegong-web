DROP TABLE IF EXISTS `cake_settings`;
CREATE TABLE IF NOT EXISTS `cake_settings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) DEFAULT NULL,
  `value` text,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `input_type` varchar(255) DEFAULT NULL,
  `editable` tinyint(1) DEFAULT '1',
  `weight` int(11) DEFAULT NULL,
  `params` text,
  `scope` varchar(20) DEFAULT NULL,
  `locale` char(5) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Setting', 'zh_cn', '20', 9, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'key', '1', '索引', 'string', 'Setting', 'zh_cn', '64', 8, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'value', '1', '值', 'string', 'Setting', 'zh_cn', NULL, 7, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'title', '1', '标题', 'string', 'Setting', 'zh_cn', '255', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'description', '1', '描述', 'string', 'Setting', 'zh_cn', '255', 5, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'input_type', '1', '表单项类型', 'string', 'Setting', 'zh_cn', '255', 4, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', 'text', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'editable', '1', '允许设置', 'string', 'Setting', 'zh_cn', '1', 3, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '1', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'weight', '1', '权重', 'integer', 'Setting', 'zh_cn', '11', 2, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'params', '1', '其他参数', 'string', 'Setting', 'zh_cn', NULL, 1, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'scope', '1', '生效范围', 'string', 'Setting', 'zh_cn', '20', NULL, '1', '1', '', NULL, NULL, NULL, '1', 'global=>全局\nmanage=>后台', '0', '', '', '', 'select', 'global', '0', '', NULL, '', '', '', 0, '2011-03-23 22:03:52', '2011-03-23 22:03:52', NULL),
(NULL, 'locale', '1', '语言类型', 'char', 'Setting', 'zh_cn', '5', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'select', '', '1', '', NULL, '', '', '该配置仅对所选的语言生效，对所有语言生效时，请将此项置空', 0, '2012-09-14 17:27:09', '2012-09-14 17:27:09', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Setting', '设置', 'onetomany', 'default', '', 26, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_settings', NULL, NULL, NULL, '0', 0, 0);



REPLACE INTO `cake_settings` (`id`, `key`, `value`, `title`, `description`, `input_type`, `editable`, `weight`, `params`, `scope`, `locale`) VALUES (NULL, 'Modelextend.seofield', 'seotitle,seodescription,seokeywords,summary,', '', '', 'text', '0', NULL, '', 'manage', ''),
(NULL, 'Site.style', '2', '网站风格', '', 'text', '0', NULL, '', 'global', 'zh_cn'),
(NULL, 'Site.index_page', '90', '首页栏目', '', 'text', '0', NULL, '', 'global', 'zh_cn'),
(NULL, 'Article.view_nums', '1', '访问时马上记录访问次数', '访问时马上记录访问次数', 'select', '1', NULL, '1=>是\r\n0=>否', 'global', 'zh_cn'),
(NULL, 'Article.pagesize', '65', '列表每页数目', '', 'text', '1', NULL, '', 'global', 'zh_cn'),
(NULL, 'Advertise.couplet_open', '0', '开启对联广告', '', 'select', '1', NULL, '0=>否\r\n1=>是', 'global', 'zh_cn'),
(NULL, 'Advertise.couplet_width', '100', '对联广告宽度', '', 'text', '1', NULL, '', 'global', 'zh_cn'),
(NULL, 'Advertise.couplet_height', '280', '对联广告高度', '', 'text', '1', NULL, '', 'global', 'zh_cn'),
(NULL, 'Advertise.couplet_left', '/files/201301/fcd10830450_0107.jpg', '左侧对联广告内容', '图片地址或者swf地址', 'file', '1', NULL, '', 'global', 'zh_cn'),
(NULL, 'Advertise.couplet_right', '/files/201301/57ad3381c48_0106.jpg', '右侧对联广告内容', '图片地址或者swf地址', 'file', '1', NULL, '', 'global', 'zh_cn'),
(NULL, 'Advertise.couplet_left_link', '', '左侧对联广告链接', '输入链接地址', 'text', '1', NULL, '', 'global', 'zh_cn'),
(NULL, 'Advertise.couplet_right_link', '', '右侧对联广告链接', '输入链接地址', 'text', '1', NULL, '', 'global', 'zh_cn'),
(NULL, 'User.allow_register', '', '是否开放注册', '', 'select', '1', NULL, '1=>是\r\n0=>否', 'global', 'zh_cn'),
(NULL, 'Admin.settings', 'Site,Meta,Reading,Writing,Comment,Service', '', '', '', '1', 1, '', 'global', ''),
(NULL, 'Site.default_site_cate_id', '110', '默认站点id', '用于多站点模式，单站点时请保持此值为0', 'text', '1', 1, '', 'global', ''),
(NULL, 'Site.icp', '', '站点备案号', '', 'text', '1', 2, '', 'global', 'zh_cn'),
(NULL, 'Site.stat_code', '', '站点统计码', '统计js代码，如CNZZ统计码', 'textarea', '1', 3, '', 'global', 'zh_cn'),
(NULL, 'Site.timezone', '', '所属时区', 'zero (0) for GMT', 'text', '1', 4, '', 'global', ''),
(NULL, 'Site.seokeywords', '汇你所思，创你所想,CMS,网络信息平台,云计算,sina app engine\r\n', '站点关键字', '', 'textarea', '1', 5, '', 'global', ''),
(NULL, 'Site.seodescription', '点名和参与中增进彼此感情。让世界充满交流。汇你所思，创你所想。', '站点描述', '', 'textarea', '1', 6, '', 'global', ''),
(NULL, 'Site.email', 'support@ideamuster.com', '邮箱联系方式', '', 'text', '1', 7, '', 'global', ''),
(NULL, 'Site.logo_url', '/', '站点logo链接地址', '', 'text', '1', 8, '', 'global', 'zh_cn'),
(NULL, 'Site.status', '1', '开启站点', '勾选时开启，不勾选时站点关闭', 'select', '1', 9, '1=>是\r\n0=>否', 'global', ''),
(NULL, 'Meta.generator', 'SaeCMS - Content Management System', '', '', '', '0', 10, '', 'global', ''),
(NULL, 'Site.openStatic', '0', '开启真静态', '访问详情页时生成真静态文件', 'select', '1', 10, '1=>开启\r\n0=>关闭', 'global', ''),
(NULL, 'Service.akismet_url', 'http://your-blog.com', '', '', '', '1', 11, '', 'global', ''),
(NULL, 'Site.logo', '/img/logo.png', '站点logo', '', 'file', '1', 11, '', 'global', 'zh_cn'),
(NULL, 'Site.title', 'MiaoCMS网站系统', '站点名称', '', 'text', '1', 12, '', 'global', ''),
(NULL, 'Service.akismet_key', 'your-key', '', '', '', '1', 12, '', 'global', ''),
(NULL, 'Service.recaptcha_public_key', 'your-public-key', '', '', '', '1', 13, '', 'global', ''),
(NULL, 'Service.recaptcha_private_key', 'your-private-key', '', '', '', '1', 14, '', 'global', ''),
(NULL, 'Site.theme', 'default', '网站主题', '', 'text', '0', 15, '', 'global', ''),
(NULL, 'Reading.nodes_per_page', '5', '', '', '', '1', 17, '', 'global', ''),
(NULL, 'Writing.wysiwyg', '1', '启用可见及可得编辑器', '', 'select', '1', 18, '1=>是\r\n0=>否', 'global', ''),
(NULL, 'Comment.level', '1', '', 'levels deep (threaded comments)', '', '1', 19, '', 'global', ''),
(NULL, 'Comment.feed_limit', '10', '', 'number of comments to show in feed', '', '1', 20, '', 'global', ''),
(NULL, 'Site.locale', 'chi', '', '', 'text', '0', 21, '', 'global', ''),
(NULL, 'Reading.date_time_format', 'D, M d Y H:i:s', '时间格式', '', 'text', '1', 22, '', 'global', ''),
(NULL, 'Comment.date_time_format', 'M d, Y', '日期格式', '', 'text', '1', 23, '', 'global', ''),
(NULL, 'Article.list_fields', 'id,name,slug,remoteurl,created,published,cate_id', '', '', 'text', '1', 24, '', 'manage', ''),
(NULL, 'Crawl.list_fields', 'id,title,targeturl,datatype,pages,category_id,saveimg,cate_id,published', '', '', 'text', '1', 25, '', 'manage', ''),
(NULL, 'Menu.list_fields', 'id,name,slug,link,sort,created', '', '', 'text', '1', 26, '', 'manage', ''),
(NULL, 'Category.list_fields', 'id,name,slug,visible,domain,model', '', '', 'text', '1', 27, '', 'manage', ''),
(NULL, 'I18nfield.list_fields', 'id,name,translate,type,model,length,formtype,default,allownull,description', '', '', 'text', '1', 28, '', 'manage', ''),
(NULL, 'Misccate.list_fields', 'id,parent_id,name,slug,model', '', '', 'text', '1', 29, '', 'manage', ''),
(NULL, 'Organization.list_fields', 'name,parent_id,created,updated', '', '', 'text', '1', 30, '', 'manage', ''),
(NULL, 'Position.list_fields', 'name,organize_id,parent_id,created', '', '', 'text', '1', 31, '', 'manage', ''),
(NULL, 'User.activate', 'activate', '注册用户的激活状态', '', 'select', '1', 32, 'activate=>注册即激活\r\nemail=>邮件激活\r\nhand=>管理员审核激活\r\n', 'global', ''),
(NULL, 'User.defaultroler', '2', '注册用户的默认角色', '', '', '1', 33, '', 'global', ''),
(NULL, 'User.list_fields', 'id,role_id,username,nickname,email,website', '', '', 'text', '1', 34, '', 'manage', ''),
(NULL, 'Modelextend.advancedfield', 'status,deleted,created,updated,creator,lastupdator,remoteurl,comment_status,point_nums,favor_nums,authorname,origin,subtitle', '', '', 'text', '1', 35, '', 'manage', ''),
(NULL, 'Staff.list_fields', 'id,name,nickname,sex,email,created', '', '', 'text', '1', 36, '', 'manage', ''),
(NULL, 'Tenure.list_fields', 'id,staff_id,organize_id,position_id,start_time', '', '', 'text', '1', 37, '', 'manage', ''),
(NULL, 'Customer.list_fields', 'id,name,grade,country_id,province_id,city_id,email,customerfile', '', '', 'text', '1', 38, '', 'manage', ''),
(NULL, 'Task.list_fields', 'id,name,starttime,endtime,quantity,finishcondition', '', '', 'text', '1', 39, '', 'manage', ''),
(NULL, 'Taskexecute.list_fields', 'id,executer_id,task_id,achieve_num,customer_id,content,created', '', '', 'text', '1', 40, '', 'manage', ''),
(NULL, 'Tasking.list_fields', 'id,task_id,staff_id', NULL, NULL, 'text', '1', 41, NULL, 'manage', ''),
(NULL, 'Flowstep.list_fields', 'id,name,flow_id,flowmodel,allowactions,edit_fields,list_fields,allowoptions', NULL, NULL, 'text', '1', 42, NULL, 'manage', ''),
(NULL, 'Modelextend.list_fields', 'id,name,cname,tablename,related_model,belongtype,modeltype,cate_id,localetype', NULL, NULL, 'text', '1', 44, NULL, 'manage', ''),
(NULL, 'Modelextend.search_fields', 'id,name,cname,tablename', NULL, NULL, 'text', '1', 45, NULL, 'manage', ''),
(NULL, 'Customer.search_fields', 'id,name,ownerid,email', NULL, NULL, 'text', '1', 46, NULL, 'manage', ''),
(NULL, 'Site.csstheme', 'united', 'css样式', '', 'select', '0', 47, 'smoothness=>沉稳灰色\r\nredmond=>经典浅蓝1\r\nflick=>温馨蓝灰\r\ncupertino=>经典浅蓝2\r\nui-lightness=>橙色海洋\r\nui-darkness=>暗色橙蓝\r\nstart=>经典深蓝\r\nsunny=>晴朗干地\r\novercast=>阴天了\r\npepper-grinder=>咖啡磨砂\r\neggplant=>茄子满园\r\ndark-hive=>黑色蜂箱\r\nsouth-street=>南方街道\r\nblitzer=>大红\r\nhumanity=>泥土芬芳\r\nexcite-bike=>兴奋的海滩\r\nvarder=>黑色苹果\r\nmint-choc=>褐色天空\r\nblack-tie=>黑领结\r\ntrontastic=>绿色金属\r\nswanky-purse=>时髦钱包', 'manage', ''),
(NULL, 'Setting.list_fields', 'id,key,value,title,description,input_type,editable,scope,locale', NULL, NULL, 'text', '1', 48, NULL, 'manage', ''),
(NULL, 'Role.list_fields', 'id,name,alias,created', NULL, NULL, 'text', '1', 49, NULL, 'manage', ''),
(NULL, 'Contact.list_fields', 'id,name,created', NULL, NULL, 'text', '1', 50, NULL, 'manage', ''),
(NULL, 'Task.search_fields', 'id,name,starttime,endtime,quantity', NULL, NULL, 'text', '1', 51, NULL, 'manage', ''),
(NULL, 'Article.search_fields', 'id,name,titleimg,status,created,published', NULL, NULL, 'text', '1', 52, NULL, 'manage', ''),
(NULL, 'Contact.search_fields', 'id,name,customer_id,sex,favorite,address,zipcode,workphone,homephone,mobilephone,created', NULL, NULL, 'text', '1', 53, NULL, 'manage', ''),
(NULL, 'Region.list_fields', 'id,name,model,preimg,rows,template,showpages', NULL, NULL, 'text', '1', 54, NULL, 'manage', ''),
(NULL, 'Modelcate.list_fields', 'id,name,slug,visible,has_split', NULL, NULL, 'text', '1', 55, NULL, 'manage', ''),
(NULL, 'Idiom.list_fields', 'id,name,published,deleted,created', NULL, NULL, 'text', '1', 56, NULL, 'manage', ''),
(NULL, 'Flow.list_fields', 'id,name,cateid,content', NULL, NULL, 'text', '1', 57, NULL, 'manage', ''),
(NULL, 'Product.list_fields', 'id,name,titleimg,price', NULL, NULL, 'text', '1', 58, NULL, 'manage', ''),
(NULL, 'Hook.bootstraps', 'Ace', NULL, NULL, 'text', '0', 59, NULL, 'global', ''),
(NULL, 'Admin.max_image_size', '1200', '图片最大尺寸', '', 'text', '1', 61, '', 'manage', ''),
(NULL, 'Admin.min_image_size', '150', '缩略图尺寸', '', 'text', '1', 62, '', 'manage', ''),
(NULL, 'Advertise.list_fields', 'id,cate_id,name,published,deleted,advertise_url', NULL, NULL, 'text', '1', 64, NULL, 'manage', ''),
(NULL, 'Museum.list_fields', 'id,name,titleimg,address', NULL, NULL, 'text', '1', 66, NULL, 'manage', ''),
(NULL, 'Taobaoke.list_fields', 'id,name,creator,nick,item_location,price,commission,commission_rate,commission_num', NULL, NULL, 'text', '1', 67, NULL, 'manage', ''),
(NULL, 'CrawlTitleList.list_fields', 'id,name,crawl_id,published,allow_crawl', NULL, NULL, 'text', '1', 68, NULL, 'manage', ''),
(NULL, 'TaobaoCate.list_fields', 'id,parent_id,name,link,visible', NULL, NULL, 'text', '1', 69, NULL, 'manage', ''),
(NULL, 'TaobaoPromotion.list_fields', 'id,name,groupName,discountType,discountValue,promPrice,promName', NULL, NULL, 'text', '1', 70, NULL, 'manage', ''),
(NULL, 'EstateInviteTender.list_fields', 'id,name,origin', '', '', 'text', '0', 71, '', 'manage', ''),
(NULL, 'CrawlRelease.list_fields', 'id,name,crawl_id,siteid,model_to,cid', '', '', 'text', '0', 72, '', 'manage', ''),
(NULL, 'EstateArticle.list_fields', 'id,name,cate_id', '', '', 'text', '0', 73, '', 'manage', ''),
(NULL, 'Comment.list_fields', 'id,data_id,user_id,ip,body,status,notify,created', '', '', 'text', '0', 74, '', 'manage', ''),
(NULL, 'Download.list_fields', 'id,cate_id,name,coverimg', '', '', 'text', '0', 75, '', 'manage', ''),
(NULL, 'Photo.list_fields', 'id,coverimg,cate_id,creator,name,slug,photo,remoteurl,views_count,favor_nums', '', '', 'text', '0', 76, '', 'manage', ''),
(NULL, 'Admin.theme', 'desktop', '管理后台主题', '', 'text', '0', 77, '', 'manage', ''),
(NULL, 'Video.list_fields', 'cate_id,id,name,coverimg,published', '视频列表字段', '', 'text', '1', 78, '', 'manage', ''),
(NULL, 'Product.pagesize', '65', '列表每页数目', '', 'text', '1', 79, '', 'global', ''),
(NULL, 'Product.view_nums', '1', '访问时马上记录访问次数', '访问时马上记录访问次数', 'select', '1', 80, '1=>是\r\n0=>否', 'global', ''),
(NULL, 'Link.list_fields', 'id,cate_id,name,link_img,link_url,status', NULL, NULL, NULL, '1', 81, NULL, 'manage', ''),
(NULL, 'Aco.list_fields', 'id,name,model,alias', NULL, NULL, NULL, '1', 82, NULL, 'manage', '');
