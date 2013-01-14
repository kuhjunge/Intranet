<?php
Class Datenbank
{
	private $db_server = 		MYSQLSERVER;
	private $db_user = 			MYSQLUSER;
	private $db_password = 		MYSQLPW;
	private $db_database = 		MYSQLDB;
	private $db_state = 		'online';
	
	private $debug = FEHLER;
	// ----- Constructor = Wird bei jedem aufruf als erstes ausgeführt -----
	function __construct($dbname=MYSQLDB) 
	{
	  $this->db_database= $dbname; // Datenbanknamen Setzten
	  if (@mysql_connect($this->db_server, $this->db_user, $this->db_password) != true) // Fehler ausgeblendet
	  {
		$sql = "use $this->db_database"; // Datenbank auswählen
		if ($this->debug) $result = mysql_query($sql);
		else @$result = mysql_query($sql); // Fehler ausgeblendet
		if (!$result)
		{
			//  die ('Fehler Datenbankauswahl: ' . mysql_error());
			$this->db_state = 'DB Offline';
			if ($this->debug) echo 'Fehler Datenbankauswahl: ' . mysql_error().'<br />';
		}
	  }
	}
	
	function getstate()
	{
		return $this->db_state;
	}
	
	// ----- Datenbank, get und set -----
	
	function getrow ($select, $spalte, $db)
	{
	/*	$select = mysql_real_escape_string($select);
		$spalte = mysql_real_escape_string($spalte);
		$db = mysql_real_escape_string($db); */

		$q = $this->q("SELECT * FROM $this->db_database.$db WHERE $spalte = '".$select."';");
		if (!empty($q))
		{	
			$row = mysql_fetch_row($q);
			return $row;
		}
	}
	
	function get_array($qx)
	{
	//	$qs = $this->q("use $this->db_database");
		$row = $this->q($qx);
		return $row;
	}
	
	function get($select, $id, $idcontent , $db)
	{
		//$select = mysql_real_escape_string($select);
		$q = $this->q("SELECT ".$select." FROM `$this->db_database`.`$db` WHERE $id='$idcontent';");
		if (!empty($q))
		{	
			$row = mysql_fetch_row($q);
			return $row[0];
		}
	}
	
	function getall($select, $db)
	{
		//$select = mysql_real_escape_string($select);
		$q = $this->q("SELECT ".$select." FROM `$this->db_database`.`$db`");
		return $q;
	}
	
	function set($data, $row, $db)
	{	
		$data = mysql_real_escape_string($data);
		$row = mysql_real_escape_string($row);
		$db = mysql_real_escape_string($db);
		$this->q("INSERT INTO $this->db_database.$db ($row) VALUES ('$data');");
	}
	
	function update($data,$column,$id,$idcontent,$db)
	{
		$this->q("UPDATE `$this->db_database`.`$db` SET `$column`=$data WHERE `$id`='$idcontent';" );
	}	
	
	function delete($id,$idcontent,$db)
	{
		$this->q("DELETE FROM `$this->db_database`.`$db` WHERE $id='$idcontent';");
	}
	
	function q($q)
	{
		if ($this->db_state == 'DB Offline') $qerg = ""; // Wenn DB offline
		else if ($this->debug)
		{ 
			$qerg = mysql_query($q);
			if (!$qerg) 
			{
				echo "MySQL Error: ".mysql_error()."<br>";
			}
		if (DEBUG)	 echo '<br /> QUERY: '.$q; // Query ausgeben 
		}
		else
		{
			@$qerg = mysql_query($q);
			if (!$qerg)
			{
				$this->db_state = 'Fehlerhafte DB-Abfrage';
				$qerg = "";
			}
		}
		return $qerg;
	}
	
	// ----- Destructor = Wird bei jedem aufruf als letztes ausgeführt -----
	function __destruct()
	{
		if (@mysql_close() != true)
	  {
	//	die('Die Verbindung zum Datenbankserver konnte nihct beendet werden!');
	  }
	}
	
	function count($db) {
		$result = $this->q("SELECT * FROM  $this->db_database.$db");
		$num_rows = mysql_num_rows($result); 
		return $num_rows;
	}
	
	function install()
	{
	$this->q("CREATE DATABASE `$this->db_database`"); // Datenbank erstellen
	$this->q("use $this->db_database"); // Datenbank auswählen
	// Content DB erstellen
	$this->q("CREATE TABLE `$this->db_database`.`content` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`content` TEXT NULL 
			) ENGINE = InnoDB;");
	// Content DB füllen
	$this->q("INSERT INTO `$this->db_database`.`content` (
			`id` ,`content`)
			VALUES ('1', 'Hallo Welt!');");
	// Support DB erstellen		
	$this->q("CREATE TABLE `$this->db_database`.`incident` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`name` VARCHAR( 100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
		`ort` VARCHAR( 100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
		`kontakt` VARCHAR( 100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
		`hardware` VARCHAR( 100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
		`prio` MEDIUMINT NULL,
		`titel` VARCHAR( 100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
		`beschreibung` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
		`loesung` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
		`createdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
		`lastedit` TIMESTAMP NULL DEFAULT NULL ,
		`ersteller` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
		`bearbeiter` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
		`status` INT( 1 ) NULL
		) ENGINE = InnoDB;");
	// User datenbank
/*	$this->q("CREATE TABLE `$this->db_database`.`user` (
			`username` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL PRIMARY KEY,
			`name` VARCHAR( 20 ) CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
			`email` VARCHAR( 30 ) CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
			`bio` TINYTEXT CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
			`password` VARCHAR( 160 ) CHARACTER SET ucs2 COLLATE ucs2_general_ci NOT NULL ,
			`createdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
			`lastlogin` TIMESTAMP NULL DEFAULT NULL ,
			`loginactive` INT( 1 ) NOT NULL ,
			`right` INT( 2 ) NOT NULL ,
			`note` TINYTEXT CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
			`iwas` INT( 1 ) NULL 
			) ENGINE = InnoDB;"); // Überflüssig da Userclasse selbständig DB Installiert */
	}
}
?>
