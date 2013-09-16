<?php
/* User Management Klasse 
V. 1.4			by Chris Deter
Anleitung im beiligenden PDF */

//$user = new User;
Class User
{
/* ---------------------
	Hier sind die Einstellungsmöglichkeiten für den Anwender.
	Diese Einstellungen müssen einmalig getätigt werden.
   --------------------- */ 
   	private $dbu_local = array( // Lokale Datenbank
		'chris' => array('password' => '1d77db876c20c456dad144b55b608137757d9c95', 'grp' => 9),
		'admin' => array('password' => '1234', 'grp' => 9),
		'philip'  => array('password' => 'test', 'grp' => 8));
	private $mysqldb = USEMYSQL;
	private $mysqldbname = MYSQLDB;		
	private $hashsys = HASHSYS;
	private $salt = SALT; 
	private $casesensitive = CASESENSITIVE;
	private $debug = DEBUG; // Debug Funktion
   	private $loginurl = 'index.php?id=login';
	private $db = 'user';
	private $dbu = ''; // Objektvariable für DB Verbindung
	private $auth = false;  // User momentan authorisiert?
	private $pwhash = ''; //passwort Hash zum wiedererkennen
	private $lasturl = '#';
	private $edit = '0';
	private $dbchange = false;
	private $adminmode = false;
	private $user = array(
		'username' => 'gast', 
		'fullname' => '',
		'email' => '', 		
		'bio' => '',
		'pwhash' => '',
		'createdate' => 0,
		'lastlogin' => 0,
		'loginactive' => 1,
		'grp' => 1,
		'note' => ''
		);
	private $group = array(0); // Die Gruppe des Nutzers
	private $recht = array(0); // Die Rechte des Nutzers
	
	function __construct($neu = false) 
	{
		$this->lasturl = (isset($_SERVER['HTTPS'])?'https':'http').'://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; //Aktuelle url greifen
//		session_start(); // die Session laden
		if($this->mysqldb)
		{
			$this->dbu = new Datenbank($this->mysqldbname);
			$dbu = $this->dbu;
			if ($dbu->getstate() != 'online') $this->mysqldb = false;
		}
		if ($this->debug) echo 'Debug: Constructor';
		if(isset($_SESSION['login']) && isset($_SESSION['username']) && !$neu)
		{ // Wenn Session mit Login vorhanden, dann reauthentifizieren
			$erg = $this->reauth($_SESSION['login'], $_SESSION['username']);
			if (!$erg) 
			{
				session_unset();
				session_destroy();
				Header("Location: ".$this->getLasturl());				
			}
			else 
			{
				if ($this->debug) echo "<br />Debug: Login: ".$_SESSION['login'];
				if ($this->debug) echo "<br />Debug: User: ".$_SESSION['username'];
				if ($this->debug) echo "<br />Debug: SID: ".session_id();
			}
		}
		else if ($this->debug && !isset($_SESSION['login']) ) echo "<br />Debug: Session nicht gestartet";
		if (!isset($_SESSION['redirect'])) $_SESSION['redirect'] = $this->lasturl;
	}
	
	function __destruct() {
		if ($this->dbchange) $this->wrtdb();
   }
	
	// -- Basic Funktionen --
	/*
	~ login(name,pw) : Loggt einen Benutzer ein
	- reauth() : Reauthentifiziert einen Nutzer mithilfe der Session
	- getlogin([back j/n]) : erfasst einen Login über POST
	- logout() : Loggt aktuellen Benutzer aus
	- loadbyname($sender)
	- gesperrt() : Sperrt Seiten vor Fremdzugriff
	*/
	
	private function login($name, $pw)
	{
		if (empty ($pw)) $pw = '?';
		if(!$this->casesensitive) $name = strtolower($name);
		$state = false;
		if ($this->debug) echo '<br />Debug: Login start</br>';
		$password = $this->pwhash($pw);
		$this->user['pwhash'] = $password;
		if ($this->askdb("pw",$name) == $password && $this->askdb("aktiv",$name) == 1) 
		{
			// Basics
			$this->auth = true;
			$this->askdb("all",$name);
			// Timestamp
			$date = new DateTime();
			$this->user['lastlogin'] = $date->getTimestamp();
			// Session 
			$_SESSION['login'] = $this->user['pwhash'];
			$_SESSION['username'] = $this->user['username'];
			$this->loadgroup();
			if ($this->debug) echo '<br />Debug: Login:'.$this->auth;
			$this->dbchange = true;
			$state = true;
		}
		else if ($this->debug)  echo '<br />Debug: Login: PW Fehler';
		return $state;
	}
	
	function reauth()
	{
		$username = 'gast';
		$login = '';
		if (isset($_SESSION['username'])) $name = $_SESSION['username'];
		if (isset($_SESSION['login'])) $login = $_SESSION['login'];
		$pw = $this->askdb("pw",$name);
		if ($this->debug) echo '<br />Debug: Reauth: '.$pw.' - '.$login;
		if ($pw == $login)
		{
			// Basics
			$this->auth = true;
			$this->askdb("all",$name);
			// Timestamp entfällt
			// Session entfällt
			$this->loadgroup();
			if ($this->debug) echo '<br /> Debug: Reauth erfolgreich';
			return true;
		}
		else return false;
	}
	
	function getlogin($back = false) {
		$logstate = true;
		$back = '';
		if (!$this->auth)
		{
			$logstate = false;
			// if (isset($_POST["loginname"])) $_POST['username'] = $_POST["loginname"];
			if (isset($_POST["loginname"]) && isset($_POST["loginpasswort"]))
			{ // Wenn Passwort übergeben, dann einloggen
				$logstate = $this->login($this->val($_POST["loginname"]),$this->val($_POST["loginpasswort"]));
				if($back && $logstate) Header("Location: ".$this->getLasturl());
				$back = $_POST["loginname"];
			}	
		}
		if ($this->debug) echo '<br />Debug: Getlogin: '.$logstate;
		if ($logstate)
		{
			Header("Location: ".$this->getLasturl());
			return $logstate;
		}
		else return $back;
	}
	
	function logout()
	{
		$this->auth = false;
		session_unset();
		session_destroy();
		Header("Location: ".$this->getLasturl());
	}
	
	function loadbyname($name, $reqname)
	{
		if ($this->debug) echo "<br />Debug: Lade fremde Nutzerdaten: $name";
		$this->askdb("all",$name); //Daten ins objekt laden.
		if ($name == $reqname->getName()) // Wenn User = Aktiver Nutzer ist
			$this->edit = "1"; // Bearbeitungsrechte Stufe 1
		else if ($reqname->checkPerm(4)) // Wennn totaler Admin
			$this->edit = "2"; // Bearbeitungsrechte Stufe 2
		else if ($reqname->checkPerm(3)) //Wenn über Stufe 7
			$this->edit = "1"; // Bearbeitungsrechte Stufe 1
		else $this->edit = "0"; // Sonst keine Bearbeitungsrechte

		if ($this->getName() == 'gast') return false;
		else return true;
	}
	
	function gesperrt()
	{
		$this->lasturl = (isset($_SERVER['HTTPS'])?'https':'http').'://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if(!$this->auth) Header("Location: ".$this->loginurl);
		if ($this->debug) echo '<br />Debug: Gesperrt: '.$this->user['username'].' : '.$this->auth.'</br>';
		// exit;
	}

	// -- Get / Set Funktionen --
	/*
	- getLasturl()
	- getName() : Name auslesen
	- getEdit() :
	- getMail() :
	- getBio() :
	- getgrp() :
	- getActive()
	- setName($name)
	- setMail($mail)
	- setBio($bio)
	- setgrp($grp)
	- setActive($a)
	- setpw($p,$np,$np2)
	*/

	function getLasturl() {
		if ((($_SERVER['HTTPS'])?'https':'http').'://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] != $this->lasturl) return $this->lasturl;
		else return 'index.php';
	}
	
	function getName() {
		return $this->user['username'];
	}

	function getEdit() {
		return  $this->edit;
	}
	
	function getMail() {
		return  $this->user['email'];
	}
	
	function getBio() {
		return  $this->user['bio'];
	}
	
	function getActive() {
		return  $this->user['loginactive'];
	}
	
	function getGrp() {
		return $this->user['grp'];
	}
	
	function getAdminmode() {
		return  $this->adminmode;
	}
	
	function setName($name) {
		$this->dbchange = true;
		$this->user['username'] = $this->val($name);
	}
	
	function setMail($mail) {
		$this->dbchange = true;
		$this->user['email'] = $this->val($mail);
	}
	
	function setBio($bio) {
		$this->dbchange = true;
		$this->user['bio'] = $this->val($bio);
	}
	
	function setGrp($grp) {
		$this->dbchange = true;
		$this->user['grp'] = $this->val($grp);
	}
	
	function setActive($a) {
		$this->dbchange = true;
		$this->user['loginactive'] = $a;
	}
	
	function setAdminmode() {
		if($this->checkPerm(4))
		$this->adminmode = !$this->adminmode;
	}
	
	function setpw($p,$np,$np2,$req = "")
	{
		if ($this->debug) echo "<br />Debug: SetPW: ";
		$npass = $this->pwhash($np);
		$npass2 = $this->pwhash($np2);
		$pass = $this->pwhash($p);
		if (empty($req)) $req = $this->getName(); // Setze Standardname
		if (($pass == $this->user['pwhash'] || $req != $this->getName()) && $npass == $npass2)
			{
				if ($this->debug) echo "Change PW";
				$this->dbchange = true;
				$this->user['pwhash'] = $npass;
				if ($req == $this->getName()) $_SESSION['login'] = $npass;
				return true;
			}
		else return false;
	}
	
	// -- Datenbank Kommunikation --
	/*
	- userlist() : Gesammtliste aller Nutzer
	- delete($regname) : User lÃ¶schen
	- createUser($name, $npw, $npw2) : User erstellen
	- grouplist() : Listet alle Gruppen auf
	- rightlist() : Listet alle Rechte auf
	- showright($gruppe) : Listet alle Rechte einer Gruppe auf
	- loadgroup() : LÃ¤d die Gruppen des Nutzers
	- getRightbyID($id) : LÃ¤d die Rechte eines Nutzers
	- checkPerm($r,$byname = false) : PrÃ¼ft auf ein Recht
	- delgrpcon($grp,$recht) : LÃ¶scht Gruppe Recht Verbindung
	- creategrpcon($grp,$recht) : Erstellt Gruppe Recht Verbindung
	~ askdb("all",$name) : Nutzerdaten aus DB lesen
	~ wrtdb($upt=true)
	- installdb : Richtet die Datenbank fÃ¼r die erste Verwendung ein
	*/
	function userList()
	{ // Aus Original entnommen und angepasst
		$erg = "";
		$i = 0;
		if($this->mysqldb)
		{
			$dbu = $this->dbu;
			$ergebnis = $dbu->getall("username", $this->db);
			while($row = mysql_fetch_object($ergebnis))
			{
				$erg[$i] = $row->username;
				$i++;
			}
		}
		else
		{
			foreach ($this->dbu_local as $key => $value)
			{
				$erg[$i] = $key;
				$i++;
			}
		}
		return $erg;
	}
	
	function delete()
	{ // Aus Original entnommen und angepasst
		if ($this->getEdit() == 2 && $this->mysqldb) // Nur Admins dÃ¼rfen LÃ¶schen
		{
			$dbu = $this->dbu;
			$dbu->delete("username",$this->getName(),$this->db);
		}
	}
	
	function createUser($name, $npw, $npw2)
	{ // Fehlerhafte Daten Abfangen
		$erg = false;
		if ($this->checkPerm(3)) //Editierungs Berechtungsstufe 2
		{
			$nuser = new User(true);
			$nuser->setName($name);
			$erg = $nuser->setpw("",$npw,$npw2,$this->getName());
			$nuser->setGrp(2); // Gruppe 2 ist die Usergruppe
			$nuser->setActive(1);
			if ($erg) $nuser->wrtdb(false);
			else $this->dbchange = false; 
		}
		return $erg;
	}
	
	function grouplist()
	{
		if ($this->debug) echo '<br />Debug: Gruppen auflisten';
		if ($this->mysqldb)
		{
			$i = 0;
			$dbu = $this->dbu;
			$ergebnis = $dbu->q("SELECT id, gruppe,rechtebeschreibung FROM `".$this->mysqldbname."`.`gruppe`");
			while($row = mysql_fetch_object($ergebnis))
			{
				$erg[$i]['name'] = $row->gruppe;
				$erg[$i]['id'] = $row->id;
				$erg[$i]['beschr'] = $row->rechtebeschreibung;
				$i++;
			}
		}
		return $erg;
	}
	
	function rightlist()
	{
		if ($this->debug) echo '<br />Debug: Rechte auflisten';
		if ($this->mysqldb)
		{
			$i = 1;
			$dbu = $this->dbu;
			$ergebnis = $dbu->q("SELECT id, rechtname, rechtbeschreibung FROM `".$this->mysqldbname."`.`rechte`");
			while($row = mysql_fetch_object($ergebnis))
			{
				$erg[$i]['name'] = $row->rechtname;
				$erg[$i]['beschr'] = $row->rechtbeschreibung;
				$erg[$i]['id'] = $row->id;
				$i++;
			}
		}
		return $erg;
	}
	
	function showright($grp)
	{
		if ($this->debug) echo '<br />Debug: R&G Auflistung';
		if ($this->mysqldb)
		{
			$dbu = $this->dbu;
			$q = $dbu->q("SELECT * FROM `".$this->mysqldbname."`.`gruppe` WHERE `id` = '".$grp."';");
			if (!empty($q))
			{	
				$row = mysql_fetch_row($q);
			}
			$erg[0] = $row;
			$ii = 0;
			$recht =""; // Deklaration
			$ergebnis2 = $dbu->q("SELECT * FROM `".$this->mysqldbname."`.`gruppe_rechte` WHERE `gruppe` = ".$grp);
			while($row2 = mysql_fetch_object($ergebnis2))
			{
				$recht[$ii] = $row2->recht;
				$ii++;
			}
			$erg[1] = $recht;
			$ii= 0 ;
			if (empty($recht)) $recht= Array(0,0);
			$unrecht =""; // Deklaration
			$ergebnis = $dbu->q("SELECT * FROM `".$this->mysqldbname."`.`gruppe_rechte` WHERE NOT `gruppe` = ".$grp);
			while($row = mysql_fetch_object($ergebnis))
			{
				$schondrin = false;
				if (!empty($unrecht)){ if (in_array($row->recht, $unrecht)) $schondrin = true;}
				if(!in_array($row->recht, $recht) && !$schondrin)
				{
					$unrecht[$ii] = $row->recht;
					$ii++;
				}
			}
			$erg[2] = $unrecht;
		}
		return $erg;
	}
	
	function loadgroup()
	{
		if ($this->debug) echo '<br />Debug: Gruppenberechtigungen laden #'.$this->user['grp'];
		if ($this->mysqldb)
		{
			$dbu = $this->dbu;
		/*	$i = 0;
			$ergebnis = $dbu->q("SELECT id, gruppe FROM `".$this->mysqldbname."`.`gruppe`");
			while($row = mysql_fetch_object($ergebnis))
			{
				$grpid[$i] = $row->id;
				if (in_array($grpid[$i], $this->group))
				{ 
					$grpn[$i] = $row->gruppe;*/
					$ii = 0;
					$ergebnis2 = $dbu->q("SELECT * FROM `".$this->mysqldbname."`.`gruppe_rechte` WHERE `gruppe` = ".$this->user['grp']);
					
					while($row2 = mysql_fetch_object($ergebnis2))
					{
						$this->recht[$ii] = $row2->recht;
						if ($this->debug) echo ' -> '.$this->recht[$ii];
						$ii++;
					}
			/*	} 
				$i++;
			} */
		}
	}
	
	function getRightbyID($id) {
		if ($this->debug) echo '<br />Debug: Namen in ID auflò³¥®';
		$dbu = $this->dbu;
		$row= $dbu->q("SELECT rechtname FROM `".$this->mysqldbname."`.`rechte` WHERE `id` = ".$id);
		$ergebnis = mysql_fetch_row($row);
		return $ergebnis;
	}
	
	function checkPerm($r,$byname = false) {
		if ($this->debug) echo '<br />Debug: Checkperm: ';
		if ($byname)
		{
			$dbu = $this->dbu;
			$row= $dbu->q("SELECT id FROM `".$this->mysqldbname."`.`rechte` WHERE `rechtname` = ".$r);
			$erg = mysql_fetch_row($row);
		}
		else $erg = $r;
		$ergebnis = in_array($erg, $this->recht);
		if ($this->adminmode) $ergebnis = in_array(4, $this->recht); // Admincheck, Admins dð²¦¥n alles
		if ($this->debug) echo " -  ".$erg." -  ".$ergebnis;
		
		return  $ergebnis;
	}
	
	function delgrpcon($grp,$recht)
	{
		if ($this->debug) echo '<br />Debug: LÃ¶sche Gruppe - Recht VerknÃ¼pfung';
		if ($this->mysqldb && $this->checkPerm(3))
		{
			$dbu = $this->dbu;
			$dbu->q("DELETE FROM `".MYSQLDB."`.`gruppe_rechte`  WHERE `gruppe_rechte`.`gruppe` = ".$grp." AND `gruppe_rechte`.`recht` = ".$recht.";");
		}
	}
	
	function creategrpcon($grp,$recht)
	{
		if ($this->debug) echo '<br />Debug: Erstelle Gruppe - Recht VerknÃ¼pfung';
				if ($this->mysqldb && $this->checkPerm(3))
		{
			$dbu = $this->dbu;
			$dbu->q("INSERT INTO `".MYSQLDB."`.`gruppe_rechte`  (`gruppe`, `recht`) VALUES ('".$grp."', '".$recht."');");
		}
		//		INSERT INTO `intranet`.`gruppe_rechte` (`gruppe`, `recht`) VALUES ('4', '4');
	}
	
	private function askdb($q, $name)
	{
		$name = $this->val($name);
		if ($this->debug) echo '<br />Debug: Datenbank wird nach Nutzer abgefragt -> ';
		$info = "";
		if ($this->mysqldb)
		{
			$dbu = $this->dbu;
			switch($q)
			{
				case "pw":
					if ($this->debug) echo 'PW Abfrage';
					$user = $dbu->getrow($name,"username",$this->db); 
					$info = $user['4'];
				break;
				
				case "recht":
					if ($this->debug) echo 'Recht Abfrage';
					$user = $dbu->getrow($name,"username",$this->db); 
					$info = $user['8'];
				break;
				
				case "aktiv":
					if ($this->debug) echo 'Aktiv Abfrage';
					$user = $dbu->getrow($name,"username",$this->db); 
					$info = $user['7'];
				break;
				
				case "all":
					if ($this->debug) echo 'Alles';
					$user = $dbu->getrow($name,"username",$this->db); //DB abfrage
					$this->user = array(
						'username' => $user['0'], 
						'fullname' => $user['1'],
						'email' => $user['2'], 		
						'bio' => $user['3'],
						'pwhash' => $user['4'],
						'createdate' => $user['5'],
						'lastlogin' => $user['6'],
						'loginactive' => $user['7'],
						'grp' => $user['8'],
						'note' => $user['9']);
				break;
			}
		}
		else {
			switch($q)
			{
				case "pw":
					if ($this->debug) echo 'Lokal: PW Abfrage';
					$info = $this->dbu_local["$name"]['password'];
				break;
				
				case "recht":
					if ($this->debug) echo 'Lokal: Recht Abfrage';
					$info = $this->dbu_local["$name"]['grp'];
				break;
				
				case "aktiv":
					if ($this->debug) echo 'Aktiv Abfrage';
					$info = 1;
				break;
				
				case "all":
					if ($this->debug) echo 'Lokal: Alles';
					$this->user = array(
					'username' => $name, 
					'fullname' => $name,
					'email' => '', 		
					'bio' => '',
					'pwhash' => @$this->dbu_local["$name"]['password'],
					'createdate' => '',
					'lastlogin' => '',
					'loginactive' => 1,
					'grp' => @$this->dbu_local["$name"]['grp'],
					'note' => '');
				break;
			}
		}
		// Leere Logins filtern
		if ($this->user['username'] == '')
		{
			if ($this->debug) echo ' -> leerer Login gefiltert!';
			$this->user['username'] = 'gast';
			$this->user['grp'] = 0;
			$this->user['pwhash'] = '###';
			$this->auth = false;
		}
		else $this->auth = true;
		return $info;
	}
	
	private function wrtdb($upt=true)
	{
		$datenbank = $this->mysqldbname;
		$this->dbchange = false; 
		if ($this->debug) echo '<br />Debug: DB Schreibzugriff: ';
		if ($this->mysqldb && $upt)
		{
			if ($this->debug) echo 'Update ';
			$dbu = $this->dbu;
			// UPDATE  `intranet`.`user` SET  `bio` =  'b ' WHERE  `user`.`username` =  'Chris';
			// 
			$dbu->q("UPDATE `$datenbank`.`user` SET 
			`name` = '".$this->user['fullname']."',
			`email` = '".$this->user['email']."',
			`bio` = '".$this->user['bio']."',
			`password` = '".$this->user['pwhash']."',
			`lastlogin` = NOW(),
			`loginactive` = '".$this->user['loginactive']."',
			`grp` = '".$this->user['grp']."'
			WHERE `user`.`username` = '".$this->user['username']."';");
		}
		else if ($this->mysqldb)
		{
			if ($this->debug) echo 'Insert ';
			$dbu = $this->dbu;
			$dbu->q("INSERT INTO `$datenbank`.`user` (`username`, `name`, `email`, `bio`, `password`, `lastlogin`, `loginactive`, `grp`) VALUES ('".$this->user['username']."', '".$this->user['fullname']."', '".$this->user['email']."', '".$this->user['bio']."', '".$this->user['pwhash']."', NOW(), '".$this->user['loginactive']."', '".$this->user['grp']."');");

		}
	}
	
	function installdb($name='admin',$pw='1234')
	{
		if ($this->mysqldb) // Funktion vorläufig deaktiviert
		{
			$datenbank = $this->mysqldbname;
			$dbu = $this->dbu;
			// user neu erstellen
			$dbu->q("DROP TABLE `$datenbank`.`user`");
			$dbu->q("CREATE TABLE `$datenbank`.`user` (
				`username` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL PRIMARY KEY,
				`name` VARCHAR( 20 ) CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
				`email` VARCHAR( 30 ) CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
				`bio` TINYTEXT CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
				`password` VARCHAR( 265 ) CHARACTER SET ucs2 COLLATE ucs2_general_ci NOT NULL ,
				`createdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
				`lastlogin` TIMESTAMP NULL DEFAULT NULL ,
				`loginactive` INT( 1 ) NOT NULL ,
				`grp` INT( 2 ) NOT NULL ,
				`note` TINYTEXT CHARACTER SET ucs2 COLLATE ucs2_general_ci NULL ,
				`iwas` INT( 1 ) NULL 
				) ENGINE = InnoDB;");
			// Die Standartnutzer
			$dbu->q("INSERT INTO `$datenbank`.`user` (`username`, `name`, `email`, `bio`, `password`, `createdate`, `lastlogin`, `loginactive`, `grp`, `note`, `iwas`) VALUES ('Kuhjunge', 'Chris Deter', 'chrisdash@posteo.de', 'Ich bin total kuhl!', '".$this->pwhash('55431')."', CURRENT_TIMESTAMP, NOW(), '1', '3', 'Admin', NULL);"); 
		$npass = $this->pwhash($pw);
			$dbu->q("INSERT INTO `$datenbank`.`user` (`username`, `name`, `email`, `bio`, `password`, `createdate`, `lastlogin`, `loginactive`, `grp`, `note`, `iwas`) VALUES ('$name', 'Administrator', '', '', '$npass', CURRENT_TIMESTAMP, NOW(), '1', '3', 'Admin', NULL);"); // pw: Admin
			// Gruppentabelle wird erstellt
			$dbu->q("CREATE TABLE  `intranet`.`gruppe` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`gruppe` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,`rechtebeschreibung` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, `rang` INT NULL ) ENGINE = INNODB;");
			$dbu->q("INSERT INTO  `intranet`.`gruppe` (`ID` ,`Gruppe` ,`Rechtebeschreibung` ,`rang`) VALUES (NULL ,  'Gast',  'Gast Gruppe',0);");
			$dbu->q("INSERT INTO  `intranet`.`gruppe` (`ID` ,`Gruppe` ,`Rechtebeschreibung` ,`rang`) VALUES (NULL ,  'User',  'User Gruppe',1);");
			$dbu->q("INSERT INTO  `intranet`.`gruppe` (`ID` ,`Gruppe` ,`Rechtebeschreibung` ,`rang`) VALUES (NULL ,  'Administrator',  'Diese Administratorengruppe hat maximale Rechte',9);");
			// Rechtetabelle wird erstellt
			$dbu->q("CREATE TABLE  `intranet`.`rechte` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`rechtname` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,`rechtbeschreibung` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL) ENGINE = INNODB;");
			$dbu->q("INSERT INTO `intranet`.`rechte` (`id`, `rechtname`, `rechtbeschreibung`) VALUES (NULL, 'Userrecht', 'Standartrecht um Seiten zu sehen');");
			$dbu->q("INSERT INTO `intranet`.`rechte` (`id`, `rechtname`, `rechtbeschreibung`) VALUES (NULL, 'News', 'Die News auf der Startseite aktualisieren');");
			$dbu->q("INSERT INTO `intranet`.`rechte` (`id`, `rechtname`, `rechtbeschreibung`) VALUES (NULL, 'Nutzerverwaltung', 'Kann Nutzer anlegen, verwalten, administieren');");
			$dbu->q("INSERT INTO `intranet`.`rechte` (`id`, `rechtname`, `rechtbeschreibung`) VALUES (NULL, 'Administrator', 'Administratorrechte');");
			// gruppe_rechte
			$dbu->q("CREATE TABLE  `intranet`.`gruppe_rechte` (`gruppe` INT NOT NULL ,`recht` INT NOT NULL ,PRIMARY KEY (  `gruppe` ,  `recht` )) ENGINE = INNODB;");
			$dbu->q("INSERT INTO `intranet`.`gruppe_rechte` (`gruppe`, `recht`) VALUES ('3', '1');");
			$dbu->q("INSERT INTO `intranet`.`gruppe_rechte` (`gruppe`, `recht`) VALUES ('3', '2');");
			$dbu->q("INSERT INTO `intranet`.`gruppe_rechte` (`gruppe`, `recht`) VALUES ('3', '3');");
			$dbu->q("INSERT INTO `intranet`.`gruppe_rechte` (`gruppe`, `recht`) VALUES ('3', '4');");
			$dbu->q("INSERT INTO `intranet`.`gruppe_rechte` (`gruppe`, `recht`) VALUES ('2', '1');");
		}
	}
	
	// -- Kleine Helfer --
	/*
	~ val($str)
	~ pwhash($pw) : Hasht das Passwort.
	*/
	private function val($val)
	{
		if($this->mysqldb) $val = mysql_real_escape_string(strip_tags($val)); 
		return $val;
	}

	private function pwhash($pw) 
	{
		if ($this->debug) echo "<br />Debug: PW Prüfung: ";
		if ($this->hashsys)
		{
			if ($this->debug) echo "Erstelle PW Hash";
			$hash = hash('sha256',$pw.$this->salt);
		}
		else $hash = $this->val($pw);
		return $hash;
	}
	// -- Dev Funktionene --
	/*

	
	*/
	

}
?>