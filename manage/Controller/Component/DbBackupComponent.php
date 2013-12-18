<?php

/**
 * 数据库备份组件，执行数据库的备份与恢复操作
 */
class DbBackupComponent extends Component {
	
	private $subfix = '';
	
	function startup($controller){
		$this->subfix = random_str(6);
	}

	function export($volume,$table='',$id=''){
		App::uses( 'CakeSchema','Model');
		
		$dataSourceName = 'default';
		
		$path = APP_PATH . 'webroot/backups/';
		
		$Folder = new Folder($path, true);
		
		$fileSufix = date('Ymd').'_'.random_str(6) . '.sql';
		$file = $path . $fileSufix;
		if (!is_writable($path)) {
			trigger_error('The path "' . $path . '" isn\'t writable!', E_USER_ERROR);
		}
		
		$File = new File($file);
		
		$config = ConnectionManager::getInstance()->getDataSource($dataSourceName)->config;
		
		foreach (ConnectionManager::getInstance()->getDataSource($dataSourceName)->listSources() as $table) {
		
			$table = str_replace($config['prefix'], '', $table);
			$ModelName = Inflector::classify($table);
			$Model = ClassRegistry::init($ModelName);
			$DataSource = $Model->getDataSource();
		
			$CakeSchema = new CakeSchema();
			$CakeSchema->tables = array($table => $Model->_schema);
		
			$File->write("\n/* Backuping table schema {$table} */\n");
			$File->write($DataSource->createSchema($CakeSchema, $table) . "\n");
			$File->write("\n/* Backuping table data {$table} */\n");
		
			unset($valueInsert, $fieldInsert);
		
			$rows = $Model->find('all', array('recursive' => -1));
			$quantity = 0;
			if (sizeOf($rows) > 0) {
				$fields = array_keys($rows[0][$ModelName]);
				$values = array_values($rows);
				$count = count($fields);
		
				for ($i = 0; $i < $count; $i++) {
					$fieldInsert[] = $DataSource->name($fields[$i]);
				}
				$fieldsInsertComma = implode(', ', $fieldInsert);
		
				foreach ($rows as $k => $row) {
					unset($valueInsert);
					for ($i = 0; $i < $count; $i++) {
						$valueInsert[] = $DataSource->value($row[$ModelName][$fields[$i]], $Model->getColumnType($fields[$i]), false);
					}
					$query = array(
							'table' => $DataSource->fullTableName($table),
							'fields' => $fieldsInsertComma,
							'values' => implode(', ', $valueInsert)
					);
					$File->write($DataSource->renderStatement('create', $query) . ";\n");
					$quantity++;
				}
			}
		
			$this->out('Model "' . $ModelName . '" (' . $quantity . ')');
		}
		$File->close();
		$this->out("\nFile \"" . $file . "\" saved (" . filesize($file) . " bytes)\n");
		
		if (class_exists('ZipArchive') && filesize($file) > 100) {
			$this->out('Zipping...');
			$zip = new ZipArchive();
			$zip->open($file . '.zip', ZIPARCHIVE::CREATE);
			$zip->addFile($file, $fileSufix);
			$zip->close();
			$this->out("Zip \"" . $file . ".zip\" Saved (" . filesize($file . '.zip') . " bytes)\n");
			$this->out("Removing original file...");
			if (file_exists($file . '.zip') && filesize($file) > 10) {
				unlink($file);
			}
			$this->out("Original file removed.\n");
		}
	}
	
	function import(){
		
	}
	
	function optimize(){
		
	}
}
?>