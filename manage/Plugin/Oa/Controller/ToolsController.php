<?php

class ToolsController extends AppController {

    var $name = 'Tools';

    function admin_index() {
        $this->pageTitle = __('Tools', true);
    }
    /**
     * SAE环境初始化。
     */
    function admin_saeinit(){
        
    }

    /**
     * 比对数据库差异，生成升级语句
     */
    function admin_dbsync() {
    	set_time_limit(0);
        // 使用两个Model，一个连接新库，一个连接旧库。比对差异
        $this->autoRender = false;
        $this->loadModel('I18nfield');  // 连新库
        $this->loadModel('Modelextend'); // 连旧库
        $this->Modelextend->setDataSource('olddb');


        $useDbConfig = $this->I18nfield->useDbConfig;
        $dbconfig = & new DATABASE_CONFIG();
        if ($dbconfig->{$useDbConfig}['prefix']) {
            $tables = $this->I18nfield->query("SHOW TABLES like '" . $dbconfig->{$useDbConfig}['prefix'] . "%'");
            $old_tables = $this->Modelextend->query("SHOW TABLES like '" . $dbconfig->{$useDbConfig}['prefix'] . "%'");
        } else {
            $tables = $this->I18nfield->query("SHOW TABLES");
            $old_tables = $this->Modelextend->query("SHOW TABLES");
        }
        $old_tables_name = array();
        foreach ($old_tables as $key => $val) {
            $old_tables_name[] = array_pop($val['TABLE_NAMES']);
        }

        App::uses('DbStructUpdater', 'Lib');
        App::uses('DbDataSync', 'Lib');

        $updater = new DbStructUpdater();


        $struct_sqls = array();
        $data_sqls = array();
        try {
            foreach ($tables as $key => $val) {
                //$tables[$key]['TABLE_NAMES'] = str_replace($dbconfig->{$useDbConfig}['prefix'],'',$val['TABLE_NAMES']);
                $table_name = array_pop($val['TABLE_NAMES']);
                
                /** 比较表结构开始 **/
                if (in_array($table_name, $old_tables_name)) {
                    $create_str_old = $this->Modelextend->query("SHOW CREATE TABLE " . $table_name);
                } else {
                    $create_str_old = '';
                }
                $create_str_new = $this->I18nfield->query("SHOW CREATE TABLE " . $table_name);
                if ($create_str_old) {
                    $res = $updater->getUpdates($create_str_old[0][0]['Create Table'], $create_str_new[0][0]['Create Table']);
                    if (!empty($res)) {
                        $struct_sqls = array_merge($struct_sqls, $res);
                    }
                } else {
                    $struct_sqls[] = $create_str_new[0][0]['Create Table'];
                }
                /** 比较表结构结束 **/

                
                /** 比较表数据开始 **/
                if (in_array($table_name, array(
                	$dbconfig->default['prefix'] . 'categories',
                    $dbconfig->default['prefix'] . 'crawls',
                    $dbconfig->default['prefix'] . 'i18nfields',
                    $dbconfig->default['prefix'] . 'menus',
                    $dbconfig->default['prefix'] . 'misccates',
                    $dbconfig->default['prefix'] . 'modelcates',
                    $dbconfig->default['prefix'] . 'modelextends',))) {
                    	// 需要覆盖全部内容的数据表
                    	
                    $data_sync = new DbDataSync();
                    $data_sync->masterSet($dbconfig->default['host'], $dbconfig->default['login'], $dbconfig->default['password'], $dbconfig->default['database'], $table_name, "id");
                    $datasql = $data_sync->slaveSyncronization();
                    if (!empty($datasql)) {
                        $data_sqls = array_merge($data_sqls, $datasql);
                    }                    	
                }
                elseif (!in_array($table_name, array(
                
                    $dbconfig->default['prefix'] . 'taobaokes',
                    $dbconfig->default['prefix'] . 'articles',
                    $dbconfig->default['prefix'] . 'crawl_title_lists',
                    $dbconfig->default['prefix'] . 'estate_articles',
                    $dbconfig->default['prefix'] . 'estate_invite_tenders',
                    $dbconfig->default['prefix'] . 'taobao_trade_rates',
                    $dbconfig->default['prefix'] . 'taobao_promotions',
                    $dbconfig->default['prefix'] . 'category_articles',
                    $dbconfig->default['prefix'] . 'stats_days',
                    $dbconfig->default['prefix'] . 'sessions',
                    $dbconfig->default['prefix'] . 'template_histories'))) {
                    	// 跳过不需要覆盖内容的数据表

                    $data_sync = new DbDataSync();
                    $data_sync->masterSet($dbconfig->default['host'], $dbconfig->default['login'], $dbconfig->default['password'], $dbconfig->default['database'], $table_name, "id");
                    if (in_array($table_name, $old_tables_name)) {
                        $data_sync->slaveSet($dbconfig->olddb['host'], $dbconfig->olddb['login'], $dbconfig->olddb['password'], $dbconfig->olddb['database'], $table_name, "id");
                    } else {
                        $data_sync->slaveSet($dbconfig->olddb['host'], $dbconfig->olddb['login'], $dbconfig->olddb['password'], $dbconfig->olddb['database'], '', "id");
                    }
                    $datasql = $data_sync->slaveSyncronization();
                    if (!empty($datasql)) {
                        $data_sqls = array_merge($data_sqls, $datasql);
                    }
                }
                /** 比较表数据结束 **/
            }
            echo implode(";\r\n", $struct_sqls) . ";\r\n";
            echo implode(";\r\n", $data_sqls) . ";\r\n";
            //print_r($struct_sqls);
            //print_r($data_sqls);
        } catch (CakeException $e) {
            echo $e->getMessage();
        } catch (RuntimeException $e) {
            echo $e->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 清空缓存
     */
    function admin_clearcache() {
        Cache::clear(false, '_cake_model_');
        Cache::clear(false, '_cake_core_');
        Cache::clear(false, 'default');

        Cache::config('front', array('engine' => 'File', 'prefix' => 'saecms_app_',));
        Cache::clear(false, 'front');

        $this->__message(__('Done', true), array('action' => 'index'), 99999);
    }

    /**
     * 生成链接的slug别名
     */
    function admin_genSlug() {
        $slug = Inflector::slug($_REQUEST['word']);
        $this->autoRender = false;
        echo json_encode(array('slug' => $slug));
    }

    /**
     * 更新语言包的缓存
     */
    function admin_updateLanCache() {

//    	$locals = App::path('locales'); print_r($locals);exit;

        if (empty($_REQUEST['uplang'])) {
            $this->loadModel('Language');
            $lans = $this->Language->find('all');
            $selectlans = array();
            foreach ($lans as $lang) {
                $selectlans[$lang['Language']['alias']] = $lang['Language']['native'];
            }
            $this->set('selectlans', $selectlans);
        } else {
            Configure::write('Config.language', $_REQUEST['uplang']);
            // 获取对应的地域名称
            $I18n = I18n::getInstance();
            $I18n->l10n->get(Configure::read('Config.language'));
            $locale_alias = $I18n->l10n->locale;

            // 获取第一个locale文件夹，
            if (!class_exists('I18n')) {
                App::uses('I18n', 'I18n');
            }
            $locals = App::path('locales');
            $local_path = array_shift($locals);

            App::uses('File', 'Utility');


            $this->loadModel('I18nfield');
            $fields = $this->I18nfield->find('all');
            $file_contnets = '';
            foreach ($fields as $key => $value) {
                $file_contnets .= 'msgid "Field_' . $value['I18nfield']['model'] . '_' . $value['I18nfield']['name'] . "\"\r\n";
                $file_contnets .= 'msgstr "' . str_replace('"', '\"', $value['I18nfield']['translate']) . "\"\r\n";
            }
            $filename = $local_path . $locale_alias . DS . 'LC_MESSAGES' . DS . 'i18nfield.po';
            $file = new File($filename, true);
            $file->write($file_contnets);

//    		print_r($file_contnets);
            //$this->loadModel('Modelextends');

            $this->loadModel('Modelextend');
            $fields = $this->Modelextend->find('all');
            $file_contnets = '';
            foreach ($fields as $key => $value) {
                $file_contnets .= 'msgid "Model_' . $value['Modelextend']['name'] . "\"\r\n";
                $file_contnets .= 'msgstr "' . str_replace('"', '\"', $value['Modelextend']['cname']) . "\"\r\n";
            }
            $filename = $local_path . $locale_alias . DS . 'LC_MESSAGES' . DS . 'modelextend.po';
            $file = new File($filename, true);
            $file->write($file_contnets);

            $this->__message('Success', array('action' => 'updateLanCache'), 150);
        }
    }

    function admin_startseo() {
    	
    }

    /**
     * 生成SEO数据
     * @param $modelname
     * @param $page
     * @param $pagesize
     */
    function admin_autoseo($modelname='', $page=1, $pagesize=10, $autonext = 0) {

        if ($this->data['Tool']['modelname']) {
            $modelname = $this->data['Tool']['modelname'];
        }
        //print_r($this->data);exit;
        $this->loadmodel($modelname);

        $this->{$modelname}->recursive = 1;
        $options = array(
            'limit' => $pagesize,
            'page' => $page,
            'order' => 'id desc'
        );
        $controlname = Inflector::pluralize($modelname);
        $datas = $this->{$modelname}->find('all', $options);

        $this->loadmodel('KeywordRelated');

        foreach ($datas as $data) {
            if ($data[$modelname]['content']) { //分词，并保存入库
                $keywords = $this->WordSegment->segment($data[$modelname]['content']);
                if (empty($keywords))
                    continue;
                $seokeywords = array();
                $mainkeywords = array();
                $i = 0;
                foreach ($keywords as $k => $v) {
                    if ($i < 5) {
                        $mainkeywords[$k] = $v;
                    }
                    if ($i < 20) {
                        $seokeywords[$k] = $v;
                    } else {
                        break;
                    }
                    $i++;
                }
                $seodata = array();
                $seodata['seokeywords'] = $sv_seokeywords = implode(',', $seokeywords); // 20个词作为seokeywords
                $seodata['keywords'] = $sv_keywords = implode(',', $mainkeywords); // 5个词作为keywords
                $seodata['id'] = $data[$modelname]['id'];
                $this->{$modelname}->save($seodata);
                // 修改表中关键字
//				$this->{$modelname}->updateAll(
//					array('seokeywords'=> $sv_seokeywords,'keywords'=>$sv_keywords),
//					array('id'=> $data[$modelname]['id'])
//				);
                // 更新key_related中相关的记录
                $this->KeywordRelated->deleteAll(array('relatedid' => $data[$modelname]['id'], 'relatedmodel' => $modelname), true, true);
                foreach ($mainkeywords as $key => $value) {
                    $this->KeywordRelated->create();
                    $keyword_related['relatedid'] = $data[$modelname]['id'];
                    $keyword_related['relatedmodel'] = $modelname;
                    $keyword_related['keyword_id'] = $key;
                    $this->KeywordRelated->save($keyword_related);
                }
            }
        }
        if (empty($datas) || count($datas) < $pagesize) {
            $this->__message(__("seo do over", true), array('action' => 'startseo'), 99999);
        }
        $nextpage = $page + 1;
        $this->__message(__("page %s Done", $page), array('action' => 'autoseo', $modelname, $nextpage, $pagesize), 3);
    }

    /**
     * 以下几个方法用于自动生成aco表的记录
     */
    function admin_build_acl() {
        if (!Configure::read('debug')) {
            return $this->_stop(); // 仅在调试模式才能运行
        }
        set_time_limit(0);
        $log = array();

        $aco = & $this->Acl->Aco;
        $root = $aco->node('controllers');
        if (!$root) {
            $aco->create(array('parent_id' => null, 'model' => null, 'alias' => 'controllers'));
            $root = $aco->save();
            $root['Aco']['id'] = $aco->id;
            $log[] = 'Created Aco node for controllers';
        } else {
            $root = $root[0];
        }

        $Controllers = array('Systems' => array('admin_index'), 'Menus' => array('admin_menu'), 'Flowsteps' => array('admin_dataadd', 'admin_dataedit', 'admin_datalist', '', ''));
        // look at each controller in app/controllers
        foreach ($Controllers as $ctrlName => $methods) {

            if (!$methods) {
                $modelClass = Inflector::tableize($ctrlName);
                $methods = get_class_methods($modelClass . 'Model');
            }

            // find / make controller node
            $controllerNode = $aco->node('controllers/' . $ctrlName);
            if (!$controllerNode) {
                $aco->create(array('parent_id' => $root['Aco']['id'], 'model' => null, 'alias' => $ctrlName));
                $controllerNode = $aco->save();
                $controllerNode['Aco']['id'] = $aco->id;
                $log[] = 'Created Aco node for ' . $ctrlName;
            } else {
                $controllerNode = $controllerNode[0];
            }

            //clean the methods. to remove those in Controller and private actions.
            foreach ($methods as $k => $method) {
                if (strpos($method, '_', 0) === 0) {
                    unset($methods[$k]);
                    continue;
                }
                if (empty($method)) {
                    continue;
                }
                $methodNode = $aco->node('controllers/' . $ctrlName . '/' . $method);
                if (!$methodNode) {
                    $aco->create(array('parent_id' => $controllerNode['Aco']['id'], 'model' => null, 'alias' => $method));
                    $methodNode = $aco->save();
                    $log[] = 'Created Aco node for ' . $method;
                }
            }
        }
        if (count($log) > 0) {
            debug($log);
        }
    }

}

?>