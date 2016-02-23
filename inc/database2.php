<?php
	class pdodb {

		private $db;

		private function dbExists($dbhost, $dbname) {
			$showquery = "show databases like " . $dbname;
			$showresult = $dbhost->query($showquery);
			return (boolean) ($showresult->fetch());
		}

		public function __construct() {
			global $db;
			$option = array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION);
			try {
				$db = new PDO(DSN, USERNAME, PASSWORD, $option);
			}
			catch (PDOException $failure) {
				echo 'Connection failed: ' . $failure->getMessage();
			}
			if (!pdodb::dbExists($db, DATABASE)) {
				$this->$db->query("CREATE DATABASE" . DATABASE);
			}
	       	$db->exec("USE " . DATABASE);
			try { 
	      		$this->$db->exec(CREATEQUERY);
		    }
		    catch (PDOException $failure ) { 
				echo 'Server failed: ' . $failure->getMessage();
			}
		}

		public function Query($sql) {
			global $db;
			try {
				$result = $this->$db->query($sql);
				return $result;
			}
			catch ( PDOException $failure ) { 
			       	
			}
		}		
	}
?>