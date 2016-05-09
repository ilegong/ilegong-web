<?php
class DB
{
	//Member Zone
	var $dataserver;
	var $database;
	var $result = array();
	var $row_result;
	var $error_name;
	var $error_number;

	//Methode Zone
	function DB()
	{
	}

	function DBDown()
	//database down
	{
		//$this->error_number = mysql_errno();
		//$this->error_name = mysql_error();
		$this->error_number = mysqli_errno($this->dataserver);
		$this->error_number = mysqli_error($this->dataserver);
		print "Database down";
		if ($this->error_name != "")
		{
			print "<br>" . $this->error_number . ": " . $this->error_name . "<br>";
		}
		exit();
	}

	function Open($plocation,$puser,$ppassword,$pdb)
	//Open a connection to a MySQL Server
	{
		$this->database = $pdb;
		//if(!$this->dataserver = mysql_connect($plocation, $puser, $ppassword))
		if(!$this->dataserver = mysqli_connect($plocation, $puser, $ppassword))
		{
			DB::DBDown();
		}
			
		//mysql_select_db($pdb,$this->dataserver);
		mysqli_select_db($this->dataserver,$pdb);

		if(function_exists('mysql_set_charset')){
			//mysql_set_charset('utf8',$this->dataserver);
			mysqli_set_charset($this->dataserver,'utf8');
		}
		else{
			//mysql_query("SET NAMES 'utf8'",$this->dataserver);
			mysqli_query($this->dataserver , "SET NAMES 'utf8'");
		}
	}

	function executeSQL($sql)
	//Query database
	{
		//if (!$res=mysql_query($sql,$this->dataserver))
		if (!$res=mysqli_query($this->dataserver , $sql))
		{
			DB::DBDown();
		}
		$this->row_result=0;
		$this->result=array();
		//print "sql=".$sql."<br>";
		if ((strpos("_".$sql,'SELECT'))||(strpos("_".$sql,'SHOW'))||(strpos("_".$sql,'DESCRIBE'))||(strpos("_".$sql,EXPLAIN)))
		{
			//while ($this->result[]=mysql_fetch_array($res, MYSQL_NUM))
			while ($this->result[]=mysqli_fetch_array($res, MYSQLI_ASYNC))
			{
				$this->row_result++;
			}
		}
	}

	function cleanSQL($param)
	//Clean sql parameters
	{
		return mysqli_real_escape_string($this->dataserver,$param);
	}

}

class DbDataSync
{
	//Member Zone
	var $db_master;
	var $table_master;
	var $idx_master;
	var $col_master;
	var $db_slave;
	var $table_slave;
	var $idx_slave;
	var $col_slave;

	//Methode Zone
	function DbDataSync(){
	}


	function masterColumns(){
		//return columns name
		$this->db_master->executeSQL("SHOW COLUMNS from ".$this->db_master->database.".".$this->table_master);
		$i = 0;
		$col = "";
		foreach ($this->db_master->result as $row)
		{
			if ($row != "")
			{
				if ($i == 1) $col .= ",";
				$col .= '`'.$row[0].'`';
				if ($i == 0) $i = 1;
			}
		}
		return $col;
	}

	function masterAllIndexes(){
		//return all index value
		$this->db_master->executeSQL("SELECT ".$this->idx_master." from ".$this->db_master->database.".".$this->table_master);
	}

	function masterDataRows($sin){
		// return data from master database
		$this->db_master->executeSQL("SELECT * from ".$this->db_master->database.".".$this->table_master." where ".$this->idx_master." in (".$sin.")");
	}

	function masterSet($mplocation, $mpuser, $mppassword, $mpdb, $mptable, $mpidx)
	//set master database
	{
		$this->table_master = $mptable;
		$this->idx_master = $mpidx;
		$this->db_master = new DB();
		$this->db_master->Open($mplocation,$mpuser,$mppassword,$mpdb);
		$this->col_master = $this->masterColumns();
	}

	function slaveColumns()
	{
		if($this->db_slave->database && $this->table_slave){
			$this->db_slave->executeSQL("SHOW COLUMNS from ".$this->db_slave->database.".".$this->table_slave);
			$i = 0;
			$col = "";
			foreach ($this->db_slave->result as $row)
			{
				if ($row != "")
				{
					if ($i == 1) $col .= ",";
					$col .= '`'.$row[0].'`';
					if ($i == 0) $i = 1;
				}
			}
			return $col;
		}
		return array();
	}

	function slaveAllIndexes()
	{
		if($this->db_slave->database && $this->table_slave){
			//return all index value
			$this->db_slave->executeSQL("SELECT ".$this->idx_slave." from ".$this->db_slave->database.".".$this->table_slave);
		}
	}

	function slaveSet($mplocation, $mpuser, $mppassword, $mpdb, $mptable, $mpidx)
	//set slave database
	{
		$this->table_slave = $mptable;
		$this->idx_slave = $mpidx;
		$this->db_slave = new DB();
		$this->db_slave->Open($mplocation,$mpuser,$mppassword,$mpdb);
		$this->col_slave = $this->slaveColumns();
	}

	function slaveSyncronization()
	{
		$sync_sql = array();
		$this->masterAllIndexes();
		$masterid=array();
		foreach ($this->db_master->result as $id){
			$masterid[] = $id[0];
		}
			
		$this->slaveAllIndexes();
		$slaveid=array();
		if(is_array($this->db_slave->result) && !empty($this->db_slave->result)){
			foreach ($this->db_slave->result as $id){
				$slaveid[] = $id[0];
			}
		}
			
		$in = array_diff($masterid, $slaveid);
		$in = array_delete_value($in,'');
		$sin = implode(",", $in);
		if ($sin != ""){
			$this->masterDataRows($sin);
			$sql = "REPLACE INTO ".$this->table_master." (".$this->col_master.") values ";
			$i = 0;
			foreach ($this->db_master->result as $row){
				if (count($row) > 1){
					if ($i == 1) $sql .= ",";
					$sql .= "\r\n(";
					$j=0;
					//print_r($row);
					foreach ($row as $col){
						if ($j == 1) $sql .= ",";
						$col = $this->db_master->cleanSQL($col);
						$sql .= "'".$col."'";
						if ($j == 0) $j = 1;
					}
					$sql .= ")";
					if ($i == 0) $i = 1;
				}
			}
			$sync_sql[] = $sql;
			//$this->db_slave->executeSQL($sql);
		}
			
		$out = array_diff($slaveid, $masterid);
		$sout = implode(",", $out);
		if ($sout != ""){
			$sql = "DELETE FROM ".$this->table_master." where ".$this->idx_slave." in (".$sout.")";
			$sync_sql[] = $sql;
			//$this->db_slave->executeSQL($sql);
		}
		return $sync_sql;
	}
}

?>