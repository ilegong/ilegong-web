DROP TABLE IF EXISTS `cake_flowsteps`;
CREATE TABLE IF NOT EXISTS `cake_flowsteps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '0',
  `deleted` int(11) DEFAULT '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `flowmodel` varchar(60) DEFAULT NULL,
  `allowactions` varchar(60) DEFAULT NULL,
  `edit_fields` text,
  `list_fields` text,
  `opratetype` varchar(60) DEFAULT NULL,
  `conditions` text,
  `allowoptions` text,
  `view_fields` text,
  `flow_id` int(11) DEFAULT '0',
  `slug` varchar(80) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Flowstep', 'zh_cn', '11', 16, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'integer', 'Flowstep', 'zh_cn', '11', 4, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Flowstep', 'zh_cn', '11', 3, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Flowstep', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Flowstep', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '步骤名称', 'string', 'Flowstep', 'zh_cn', '60', 15, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'flowmodel', '1', '操作模块', 'string', 'Flowstep', 'zh_cn', '60', 12, '1', '1', 'Modelextend', 'name', 'cname', NULL, '1', '', '0', '', '', 'equal', 'select', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'allowactions', '1', '允许的操作', 'string', 'Flowstep', 'zh_cn', '60', 11, '1', '1', '', '', '', NULL, '1', 'add=>添加\r\nedit=>修改\r\nview=>查看\r\nsearch=>搜索\r\ntrash=>回收站\r\nrestore=>恢复\r\ndelete=>删除', '0', '', '', '', 'checkbox', '', '1', '', NULL, '', 'none', '当什么操作都不选择时，只能进入列表页，只能看到列表中展示的几列。其他无任何权限', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'edit_fields', '1', '允许编辑字段', 'content', 'Flowstep', 'zh_cn', NULL, 10, '1', '1', 'I18nfield', 'name', 'translate', NULL, '0', '', '1', 'flowmodel', 'model', 'equal', 'checkbox', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'list_fields', '1', '列表显示字段', 'content', 'Flowstep', 'zh_cn', NULL, 9, '1', '1', 'I18nfield', 'name', 'translate', NULL, '0', '', '1', 'flowmodel', 'model', 'equal', 'checkbox', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'opratetype', '1', '操作形式', 'string', 'Flowstep', 'zh_cn', '60', 7, '1', '1', '', NULL, NULL, NULL, '1', 'dialog=>弹出框内操作\ninline=>行内操作\nnewtab=>新页面操作', '0', '', '', 'equal', 'select', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'conditions', '1', '数据满足条件', 'content', 'Flowstep', 'zh_cn', NULL, 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'allowoptions', '1', '选项可选范围', 'content', 'Flowstep', 'zh_cn', NULL, 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', NULL, '', 'none', '适用于单选，多选，下拉现在框', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'view_fields', '1', '值可见字段', 'content', 'Flowstep', 'zh_cn', NULL, 8, '1', '1', 'I18nfield', 'name', 'translate', NULL, '1', '', '1', 'flowmodel', 'model', 'equal', 'checkbox', '', '1', '', NULL, '', 'none', '值可见字段，标示用户的权限是否可以看到这个字段的值。搜索表单中，这些字段也供填入值搜索。其余没有勾选的字段不可见', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'flow_id', '1', '所属流程', 'integer', 'Flowstep', 'zh_cn', NULL, 13, '1', '1', 'Flow', 'id', 'name', NULL, '1', '', '0', '', '', 'equal', 'select', '', '1', '', NULL, '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'slug', '1', '链接文字', 'string', 'Flowstep', 'zh_cn', '80', 14, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2013-02-02 10:23:36', '2013-02-02 10:23:36', ''),
(NULL, 'content', '1', '流程说明', 'content', 'Flowstep', 'zh_cn', '65535', NULL, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'ckeditor', '', '1', '', NULL, '', '', '', 0, '2013-02-02 11:17:46', '2013-02-02 11:17:46', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Flowstep', '流程步骤', 'onetomany', 'default', '<id>', 27, '2010-08-16 17:36:21', '2010-08-16 17:36:21', 'cake_flowsteps', NULL, NULL, NULL, '0', 0, 0);
