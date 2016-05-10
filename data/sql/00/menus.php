<?php
$datas = array (
  0 => 
  array (
    'Menu' => 
    array (
      'id' => '1',
      'parent_id' => NULL,
      'name' => '站点管理',
      'slug' => 'site',
      'visible' => '1',
      'rel' => '',
      'target' => '',
      'link' => '#',
      'left' => '1',
      'right' => '114',
      'created' => '2010-05-14 22:52:07',
      'updated' => '2013-01-13 21:04:10',
      'deleted' => '0',
      'locale' => 'zh_cn',
    ),
    'children' => 
    array (
      0 => 
      array (
        'Menu' => 
        array (
          'id' => '10',
          'parent_id' => '1',
          'name' => '用户',
          'slug' => 'user',
          'visible' => '1',
          'rel' => '',
          'target' => 'leftmenu',
          'link' => '/admin/menus/menu/10',
          'left' => '28',
          'right' => '39',
          'created' => '2010-05-15 17:14:30',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '27',
              'parent_id' => '10',
              'name' => '用户管理',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '',
              'left' => '29',
              'right' => '38',
              'created' => '2010-05-17 22:38:10',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '11',
                  'parent_id' => '27',
                  'name' => '前台用户',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/users/list',
                  'left' => '30',
                  'right' => '31',
                  'created' => '2010-05-15 17:15:22',
                  'updated' => '2013-05-17 05:35:59',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '12',
                  'parent_id' => '27',
                  'name' => '角色管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/roles/list',
                  'left' => '34',
                  'right' => '35',
                  'created' => '2010-05-15 17:15:55',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '13',
                  'parent_id' => '27',
                  'name' => '权限管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/acl/acl_permissions',
                  'left' => '32',
                  'right' => '33',
                  'created' => '2010-05-15 17:16:43',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              3 => 
              array (
                'Menu' => 
                array (
                  'id' => '65',
                  'parent_id' => '27',
                  'name' => '后台用户',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/staffs/list',
                  'left' => '36',
                  'right' => '37',
                  'created' => '2010-06-26 18:43:33',
                  'updated' => '2013-05-17 05:36:24',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      1 => 
      array (
        'Menu' => 
        array (
          'id' => '26',
          'parent_id' => '1',
          'name' => '设置',
          'slug' => 'setting',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '#',
          'left' => '16',
          'right' => '27',
          'created' => '2010-05-15 18:38:44',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '17',
              'parent_id' => '26',
              'name' => '站点设置',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => 'main',
              'link' => '/admin/settings/prefix/Site.html',
              'left' => '17',
              'right' => '18',
              'created' => '2010-05-15 18:06:54',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
          1 => 
          array (
            'Menu' => 
            array (
              'id' => '18',
              'parent_id' => '26',
              'name' => '评论设置',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => 'main',
              'link' => '/admin/settings/prefix/Comment.html',
              'left' => '23',
              'right' => '24',
              'created' => '2010-05-15 18:07:41',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
          2 => 
          array (
            'Menu' => 
            array (
              'id' => '19',
              'parent_id' => '26',
              'name' => '语言设置',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => 'main',
              'link' => '/admin/languages/list.html',
              'left' => '19',
              'right' => '20',
              'created' => '2010-05-15 18:08:16',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
          3 => 
          array (
            'Menu' => 
            array (
              'id' => '70',
              'parent_id' => '26',
              'name' => '用户设置',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '/admin/settings/prefix/User.html',
              'left' => '21',
              'right' => '22',
              'created' => '2010-07-18 00:45:38',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
          4 => 
          array (
            'Menu' => 
            array (
              'id' => '118',
              'parent_id' => '26',
              'name' => '页面变量设置',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '/admin/settings/prefix/Page',
              'left' => '25',
              'right' => '26',
              'created' => '2013-01-08 23:24:42',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
        ),
      ),
      2 => 
      array (
        'Menu' => 
        array (
          'id' => '36',
          'parent_id' => '1',
          'name' => '运营',
          'slug' => '',
          'visible' => '1',
          'rel' => '',
          'target' => 'main',
          'link' => '/admin/menus/menu/36',
          'left' => '48',
          'right' => '69',
          'created' => '2010-05-23 00:15:58',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '45',
              'parent_id' => '36',
              'name' => '搜索引擎营销',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '49',
              'right' => '60',
              'created' => '2010-05-23 11:39:00',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '37',
                  'parent_id' => '45',
                  'name' => 'SEO关键字优化',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/tools/startseo',
                  'left' => '52',
                  'right' => '53',
                  'created' => '2010-05-23 00:19:16',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '38',
                  'parent_id' => '45',
                  'name' => 'SEO批量处理：标题，摘要，关键字，图片优化',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '#',
                  'left' => '58',
                  'right' => '59',
                  'created' => '2010-05-23 00:19:36',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '1',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '39',
                  'parent_id' => '45',
                  'name' => '网站收录页面数',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '#',
                  'left' => '56',
                  'right' => '57',
                  'created' => '2010-05-23 00:27:47',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              3 => 
              array (
                'Menu' => 
                array (
                  'id' => '40',
                  'parent_id' => '45',
                  'name' => '关键字排行',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '#',
                  'left' => '54',
                  'right' => '55',
                  'created' => '2010-05-23 00:29:01',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              4 => 
              array (
                'Menu' => 
                array (
                  'id' => '72',
                  'parent_id' => '45',
                  'name' => '关键字管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/keywords/list',
                  'left' => '50',
                  'right' => '51',
                  'created' => '2010-07-24 21:25:47',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
          1 => 
          array (
            'Menu' => 
            array (
              'id' => '126',
              'parent_id' => '36',
              'name' => '广告',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '61',
              'right' => '68',
              'created' => '2013-01-13 16:51:04',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '130',
                  'parent_id' => '126',
                  'name' => '广告管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/advertises/list',
                  'left' => '66',
                  'right' => '67',
                  'created' => '2013-01-13 23:04:28',
                  'updated' => '2013-01-13 23:04:28',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '119',
                  'parent_id' => '126',
                  'name' => '广告设置',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/settings/prefix/Advertise',
                  'left' => '62',
                  'right' => '63',
                  'created' => '2013-01-08 23:25:18',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '129',
                  'parent_id' => '126',
                  'name' => '链接管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/links/list',
                  'left' => '64',
                  'right' => '65',
                  'created' => '2013-01-13 20:33:17',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      3 => 
      array (
        'Menu' => 
        array (
          'id' => '57',
          'parent_id' => '1',
          'name' => '系统',
          'slug' => 'system',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/menus/menu/57',
          'left' => '70',
          'right' => '85',
          'created' => '2010-06-26 12:14:21',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '58',
              'parent_id' => '57',
              'name' => '菜单及选项',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '81',
              'right' => '84',
              'created' => '2010-06-26 12:14:47',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '25',
                  'parent_id' => '58',
                  'name' => '选项分类管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/misccates/list',
                  'left' => '82',
                  'right' => '83',
                  'created' => '2010-05-15 18:31:43',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
          1 => 
          array (
            'Menu' => 
            array (
              'id' => '59',
              'parent_id' => '57',
              'name' => '系统工具',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '71',
              'right' => '80',
              'created' => '2010-06-26 12:38:23',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '60',
                  'parent_id' => '59',
                  'name' => '清除缓存',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => 'ajaxAction',
                  'target' => 'main',
                  'link' => '/admin/tools/clearcache',
                  'left' => '78',
                  'right' => '79',
                  'created' => '2010-06-26 12:39:26',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '89',
                  'parent_id' => '59',
                  'name' => '系统备份',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/tools/dbexport',
                  'left' => '72',
                  'right' => '73',
                  'created' => '2010-09-11 07:52:32',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '90',
                  'parent_id' => '59',
                  'name' => '备份恢复',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/tools/dbimport',
                  'left' => '74',
                  'right' => '75',
                  'created' => '2010-09-11 07:52:45',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              3 => 
              array (
                'Menu' => 
                array (
                  'id' => '99',
                  'parent_id' => '59',
                  'name' => '自定义语言包',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/defined_language/defined_languages/index',
                  'left' => '76',
                  'right' => '77',
                  'created' => '2011-08-09 22:26:27',
                  'updated' => '2013-01-14 23:22:07',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      4 => 
      array (
        'Menu' => 
        array (
          'id' => '120',
          'parent_id' => '1',
          'name' => '扩展',
          'slug' => 'extend',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '',
          'left' => '86',
          'right' => '113',
          'created' => '2013-01-12 23:30:56',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '32',
              'parent_id' => '120',
              'name' => '模块管理',
              'slug' => 'models',
              'visible' => '1',
              'rel' => '',
              'target' => 'main',
              'link' => '/admin/menus/menu/32',
              'left' => '87',
              'right' => '96',
              'created' => '2010-05-22 11:49:38',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '33',
                  'parent_id' => '32',
                  'name' => '字段管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/i18nfields/index',
                  'left' => '88',
                  'right' => '89',
                  'created' => '2010-05-22 11:51:02',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '34',
                  'parent_id' => '32',
                  'name' => '模块扩展',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/modelextends/list',
                  'left' => '90',
                  'right' => '91',
                  'created' => '2010-05-22 18:03:54',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '66',
                  'parent_id' => '32',
                  'name' => '字段更新',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/i18nfields/generate',
                  'left' => '92',
                  'right' => '93',
                  'created' => '2010-06-26 18:59:51',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              3 => 
              array (
                'Menu' => 
                array (
                  'id' => '67',
                  'parent_id' => '32',
                  'name' => '模块更新',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/modelextends/generate',
                  'left' => '94',
                  'right' => '95',
                  'created' => '2010-06-30 23:01:26',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
          1 => 
          array (
            'Menu' => 
            array (
              'id' => '121',
              'parent_id' => '120',
              'name' => '开发工具',
              'slug' => 'develop_tools',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '',
              'left' => '97',
              'right' => '112',
              'created' => '2013-01-12 23:32:14',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '28',
                  'parent_id' => '121',
                  'name' => '后台菜单管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/menus/list',
                  'left' => '98',
                  'right' => '99',
                  'created' => '2010-05-18 23:11:56',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '96',
                  'parent_id' => '121',
                  'name' => '插件管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/extensions/extensions/index',
                  'left' => '102',
                  'right' => '103',
                  'created' => '2011-03-06 01:24:00',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '98',
                  'parent_id' => '121',
                  'name' => '更新语言包缓存',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/tools/updateLanCache',
                  'left' => '110',
                  'right' => '111',
                  'created' => '2011-08-04 21:16:50',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              3 => 
              array (
                'Menu' => 
                array (
                  'id' => '122',
                  'parent_id' => '121',
                  'name' => '设置管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/settings/list',
                  'left' => '100',
                  'right' => '101',
                  'created' => '2013-01-12 23:38:44',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              4 => 
              array (
                'Menu' => 
                array (
                  'id' => '106',
                  'parent_id' => '121',
                  'name' => '数据库升级sql生成',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/tools/dbsync',
                  'left' => '104',
                  'right' => '105',
                  'created' => '2011-09-17 21:44:26',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              5 => 
              array (
                'Menu' => 
                array (
                  'id' => '127',
                  'parent_id' => '121',
                  'name' => '导出安装sql',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/devtools/exportModelSql.html',
                  'left' => '106',
                  'right' => '107',
                  'created' => '2013-01-13 18:00:06',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              6 => 
              array (
                'Menu' => 
                array (
                  'id' => '128',
                  'parent_id' => '121',
                  'name' => '初始化ACL',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/devtools/build_acl.html',
                  'left' => '108',
                  'right' => '109',
                  'created' => '2013-01-13 18:01:04',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      5 => 
      array (
        'Menu' => 
        array (
          'id' => '117',
          'parent_id' => '1',
          'name' => '内容',
          'slug' => 'content',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/menus/contentmenu',
          'left' => '2',
          'right' => '15',
          'created' => '2012-08-17 22:29:05',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '108',
              'parent_id' => '117',
              'name' => '采集管理',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '3',
              'right' => '14',
              'created' => '2011-12-24 13:54:54',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '16',
                  'parent_id' => '108',
                  'name' => '采集规则设置',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/crawls/list',
                  'left' => '6',
                  'right' => '7',
                  'created' => '2010-05-15 17:41:16',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '101',
                  'parent_id' => '108',
                  'name' => '采集数据发布',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/crawl_title_lists/publishlist',
                  'left' => '12',
                  'right' => '13',
                  'created' => '2011-08-27 09:46:09',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '109',
                  'parent_id' => '108',
                  'name' => '采集分类管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/modelcates/list/model:Crawl',
                  'left' => '4',
                  'right' => '5',
                  'created' => '2011-12-24 21:02:22',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              3 => 
              array (
                'Menu' => 
                array (
                  'id' => '110',
                  'parent_id' => '108',
                  'name' => '采集发布规则',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/crawl_releases/list/',
                  'left' => '10',
                  'right' => '11',
                  'created' => '2011-12-25 16:15:10',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              4 => 
              array (
                'Menu' => 
                array (
                  'id' => '111',
                  'parent_id' => '108',
                  'name' => '采集发布站点',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/crawl_release_sites/list/',
                  'left' => '8',
                  'right' => '9',
                  'created' => '2011-12-26 22:01:08',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      6 => 
      array (
        'Menu' => 
        array (
          'id' => '123',
          'parent_id' => '1',
          'name' => '界面',
          'slug' => 'ui',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '#',
          'left' => '40',
          'right' => '47',
          'created' => '2013-01-13 13:50:40',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '124',
              'parent_id' => '123',
              'name' => '风格管理',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '/admin/styles/list',
              'left' => '41',
              'right' => '42',
              'created' => '2013-01-13 13:51:37',
              'updated' => '2013-10-11 20:51:19',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
          1 => 
          array (
            'Menu' => 
            array (
              'id' => '125',
              'parent_id' => '123',
              'name' => '模板套系',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '/admin/tools/themes',
              'left' => '43',
              'right' => '44',
              'created' => '2013-01-13 13:52:08',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
          2 => 
          array (
            'Menu' => 
            array (
              'id' => '131',
              'parent_id' => '123',
              'name' => '新增风格',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '/admin/styles/addstyle',
              'left' => '45',
              'right' => '46',
              'created' => '2013-10-11 20:46:44',
              'updated' => '2013-10-11 20:46:44',
              'deleted' => '1',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
        ),
      ),
    ),
  ),
  1 => 
  array (
    'Menu' => 
    array (
      'id' => '30',
      'parent_id' => NULL,
      'name' => '办公系统',
      'slug' => 'oa',
      'visible' => '1',
      'rel' => '',
      'target' => '',
      'link' => '#',
      'left' => '115',
      'right' => '156',
      'created' => '2010-05-21 23:55:18',
      'updated' => '2013-01-13 21:04:10',
      'deleted' => '0',
      'locale' => 'zh_cn',
    ),
    'children' => 
    array (
      0 => 
      array (
        'Menu' => 
        array (
          'id' => '50',
          'parent_id' => '30',
          'name' => '调查与评价',
          'slug' => '',
          'visible' => '1',
          'rel' => '',
          'target' => '0',
          'link' => '/admin/menus/menu/50',
          'left' => '140',
          'right' => '147',
          'created' => '2010-06-26 09:05:48',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '91',
              'parent_id' => '50',
              'name' => '调查管理',
              'slug' => NULL,
              'visible' => '1',
              'rel' => NULL,
              'target' => '',
              'link' => '',
              'left' => '141',
              'right' => '146',
              'created' => '2010-09-11 08:06:04',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '92',
                  'parent_id' => '91',
                  'name' => '调查管理',
                  'slug' => NULL,
                  'visible' => '1',
                  'rel' => NULL,
                  'target' => '',
                  'link' => '',
                  'left' => '142',
                  'right' => '143',
                  'created' => '2010-09-11 08:08:17',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '93',
                  'parent_id' => '91',
                  'name' => '评价管理',
                  'slug' => NULL,
                  'visible' => '1',
                  'rel' => NULL,
                  'target' => '',
                  'link' => '/admin/appraises/list',
                  'left' => '144',
                  'right' => '145',
                  'created' => '2010-09-11 08:08:31',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      1 => 
      array (
        'Menu' => 
        array (
          'id' => '51',
          'parent_id' => '30',
          'name' => '人力资源管理',
          'slug' => '',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/menus/menu/51',
          'left' => '128',
          'right' => '139',
          'created' => '2010-06-26 09:07:03',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '61',
              'parent_id' => '51',
              'name' => '部门与人员',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '129',
              'right' => '136',
              'created' => '2010-06-26 15:27:36',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '62',
                  'parent_id' => '61',
                  'name' => '部门管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/organizations/list',
                  'left' => '130',
                  'right' => '131',
                  'created' => '2010-06-26 15:30:37',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '63',
                  'parent_id' => '61',
                  'name' => '职位管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/positions/list',
                  'left' => '132',
                  'right' => '133',
                  'created' => '2010-06-26 18:39:47',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '64',
                  'parent_id' => '61',
                  'name' => '任职管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/tenures/list',
                  'left' => '134',
                  'right' => '135',
                  'created' => '2010-06-26 18:43:33',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
          1 => 
          array (
            'Menu' => 
            array (
              'id' => '82',
              'parent_id' => '51',
              'name' => '人员角色',
              'slug' => NULL,
              'visible' => '1',
              'rel' => NULL,
              'target' => '',
              'link' => '#',
              'left' => '137',
              'right' => '138',
              'created' => '2010-08-21 09:49:26',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
            ),
          ),
        ),
      ),
      2 => 
      array (
        'Menu' => 
        array (
          'id' => '52',
          'parent_id' => '30',
          'name' => '任务管理',
          'slug' => '',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/menus/menu/52',
          'left' => '116',
          'right' => '127',
          'created' => '2010-06-26 09:07:36',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '73',
              'parent_id' => '52',
              'name' => '任务管理',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '117',
              'right' => '126',
              'created' => '2010-08-05 21:16:59',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '74',
                  'parent_id' => '73',
                  'name' => '任务管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/tasks/list.html',
                  'left' => '118',
                  'right' => '119',
                  'created' => '2010-08-05 21:17:52',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '78',
                  'parent_id' => '73',
                  'name' => '任务分配与参与',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/taskings/list',
                  'left' => '120',
                  'right' => '121',
                  'created' => '2010-08-05 21:47:43',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              2 => 
              array (
                'Menu' => 
                array (
                  'id' => '79',
                  'parent_id' => '73',
                  'name' => '任务实施记录',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => 'main',
                  'link' => '/admin/taskexecutes/list.html',
                  'left' => '122',
                  'right' => '123',
                  'created' => '2010-08-05 21:48:55',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              3 => 
              array (
                'Menu' => 
                array (
                  'id' => '80',
                  'parent_id' => '73',
                  'name' => '个人任务查询',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '',
                  'left' => '124',
                  'right' => '125',
                  'created' => '2010-08-05 21:49:29',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      3 => 
      array (
        'Menu' => 
        array (
          'id' => '75',
          'parent_id' => '30',
          'name' => '客户关系管理',
          'slug' => '',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/menus/menu/75',
          'left' => '148',
          'right' => '155',
          'created' => '2010-08-05 21:21:17',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '76',
              'parent_id' => '75',
              'name' => '客户关系',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '#',
              'left' => '149',
              'right' => '154',
              'created' => '2010-08-05 21:22:19',
              'updated' => '2013-01-13 21:04:10',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '77',
                  'parent_id' => '76',
                  'name' => '客户管理',
                  'slug' => '',
                  'visible' => '1',
                  'rel' => '',
                  'target' => '',
                  'link' => '/admin/customers/list',
                  'left' => '150',
                  'right' => '151',
                  'created' => '2010-08-05 21:22:56',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '83',
                  'parent_id' => '76',
                  'name' => '联系人管理',
                  'slug' => NULL,
                  'visible' => '1',
                  'rel' => NULL,
                  'target' => 'main',
                  'link' => '/admin/contacts/list',
                  'left' => '152',
                  'right' => '153',
                  'created' => '2010-08-30 21:10:40',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  2 => 
  array (
    'Menu' => 
    array (
      'id' => '53',
      'parent_id' => NULL,
      'name' => '个人中心',
      'slug' => '',
      'visible' => '1',
      'rel' => '',
      'target' => '',
      'link' => '',
      'left' => '157',
      'right' => '174',
      'created' => '2010-06-26 10:11:37',
      'updated' => '2013-01-13 21:04:10',
      'deleted' => '0',
      'locale' => 'zh_cn',
    ),
    'children' => 
    array (
      0 => 
      array (
        'Menu' => 
        array (
          'id' => '54',
          'parent_id' => '53',
          'name' => '工作流程',
          'slug' => '',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/flowsteps/menu',
          'left' => '160',
          'right' => '161',
          'created' => '2010-06-26 10:14:20',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
        ),
      ),
      1 => 
      array (
        'Menu' => 
        array (
          'id' => '55',
          'parent_id' => '53',
          'name' => '个人资料',
          'slug' => '',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/menus/menu/55',
          'left' => '162',
          'right' => '173',
          'created' => '2010-06-26 10:14:37',
          'updated' => '2013-01-13 21:04:10',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
          0 => 
          array (
            'Menu' => 
            array (
              'id' => '84',
              'parent_id' => '55',
              'name' => '修改密码',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '/admin/staffs/editpassword',
              'left' => '163',
              'right' => '166',
              'created' => '2010-08-31 21:33:38',
              'updated' => '2013-05-25 23:38:18',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '88',
                  'parent_id' => '84',
                  'name' => '修改密码',
                  'slug' => NULL,
                  'visible' => '1',
                  'rel' => NULL,
                  'target' => '',
                  'link' => '/admin/staffs/editpassword',
                  'left' => '164',
                  'right' => '165',
                  'created' => '2010-08-31 21:58:23',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
          1 => 
          array (
            'Menu' => 
            array (
              'id' => '85',
              'parent_id' => '55',
              'name' => '站内短信',
              'slug' => '',
              'visible' => '1',
              'rel' => '',
              'target' => '',
              'link' => '/admin/shortmessages/list',
              'left' => '167',
              'right' => '172',
              'created' => '2010-08-31 21:36:37',
              'updated' => '2013-05-25 23:38:39',
              'deleted' => '0',
              'locale' => 'zh_cn',
            ),
            'children' => 
            array (
              0 => 
              array (
                'Menu' => 
                array (
                  'id' => '86',
                  'parent_id' => '85',
                  'name' => '收件箱',
                  'slug' => NULL,
                  'visible' => '1',
                  'rel' => NULL,
                  'target' => '',
                  'link' => '/admin/shortmessages/list/folder:inbox/',
                  'left' => '168',
                  'right' => '169',
                  'created' => '2010-08-31 21:39:55',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
              1 => 
              array (
                'Menu' => 
                array (
                  'id' => '87',
                  'parent_id' => '85',
                  'name' => '发件箱',
                  'slug' => NULL,
                  'visible' => '1',
                  'rel' => NULL,
                  'target' => 'main',
                  'link' => '/admin/shortmessages/list/folder:outbox/',
                  'left' => '170',
                  'right' => '171',
                  'created' => '2010-08-31 21:40:32',
                  'updated' => '2013-01-13 21:04:10',
                  'deleted' => '0',
                  'locale' => 'zh_cn',
                ),
                'children' => 
                array (
                ),
              ),
            ),
          ),
        ),
      ),
      2 => 
      array (
        'Menu' => 
        array (
          'id' => '56',
          'parent_id' => '53',
          'name' => '我的权限',
          'slug' => 'privilege',
          'visible' => '1',
          'rel' => '',
          'target' => '',
          'link' => '/admin/menus/menu/56',
          'left' => '158',
          'right' => '159',
          'created' => '2010-06-26 10:16:42',
          'updated' => '2013-05-26 22:39:29',
          'deleted' => '0',
          'locale' => 'zh_cn',
        ),
        'children' => 
        array (
        ),
      ),
    ),
  ),
);


	saveTreeItems($datas,'Menu');
