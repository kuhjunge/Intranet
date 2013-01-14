<?php // Intranet Einbindunn
// ----------------------- Eigentlicher Quellcode ------------------- 
/*
PHP MYSQL Schule Aufgabe
Version: 1.2
Stand: 28.2.12
by Chris Deter
define("MYSQLSERVER",     'localhost'); // MySQL Server
define("MYSQLUSER",     'root'); // MySQL User
define("MYSQLPW",     ''); // MySQL Passwort
define("MYSQLDB",     'intranet'); // MySQL Passwort
*/
	$erg = "";
	// Seitenrelevante Variablen
	$indexname = "index.php"; //Name dieses Dokumentes wenn nicht in Intranet includiert
	// DB Connection
	$db_server = MYSQLSERVER;       // Web-Adresse des Servers, auf dem MySQL installiert ist.
    $db_user = MYSQLUSER;				// zugeteilten Dantenbank-Benutzername ein
    $db_passwort = MYSQLPW;			// zugeteiltes Datenbank-Passwort einsetzen
	$db_link = mysql_connect ($db_server, $db_user, $db_passwort);
	if (!$db_link) { die ('keine Verbindung möglich: ' . mysql_error()); }
	$result=mysql_query("SET NAMES 'utf8'");  
	$sql = 'use db_hauptstadt';
    $result = mysql_query($sql, $db_link);
	 if (!$result) { die ('Ungültige Abfrage: ' . mysql_error()); }

	// Kontinentliste erstellen
	if (isset($_GET['createcsv'])) // Bearbeitungsmodus
	{
		$kontinent = "";
		$data = "";
		$kontinent = $_GET['kontinent']; 
		$result = mysql_query("SELECT erdteil_id FROM  tbl_erdteil WHERE erdteil='$kontinent';");
		if (!$result) {die ('Ungültige Abfrage1: ' . mysql_error()); }
		$row = mysql_fetch_array($result);
		$kid =$row['erdteil_id'];
		$sql = 'select * from tbl_hauptstadt, tbl_erdteil, tbl_hauptstadt_erdteil
		where tbl_hauptstadt_erdteil.erdteil_id = tbl_erdteil.erdteil_id
		and tbl_hauptstadt_erdteil.hauptstadt_id = tbl_hauptstadt.hauptstadt_id
		and tbl_hauptstadt_erdteil.erdteil_id = '."$kid".';'; 	
		$result = mysql_query($sql);
		if (!$result) {die ('Ungültige Abfrage1: ' . mysql_error()); }
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$data = "$data $row[land] ; $row[hauptstadt] ; $row[einwohner] ; \r\n";
		}
		writetxt("$kontinent.csv",$data);
		$erg = "Kontinent csv erstellt!";
	}

	// Neues Land eintragen
	if (isset($_GET['modifyl'])) // Bearbeitungsmodus
	{	
		$land = "";
		$hauptstadt = "";
		$einwohner = "";
		$kontinent = "";
		$land = $_GET['land']; 
		$hauptstadt = $_GET['hauptstadt'];
		$einwohner = $_GET['einwohner'];
		$kontinent = $_GET['kontinent'];
		$zahl = count($_GET['kontinent']); 
		for($i=0; $i < $zahl; $i++) 
		   { 
				$kontinent[$i] = $_GET['kontinent'][$i]; 
		   } 
		if ($hauptstadt != "") {
			$result = mysql_query("SELECT hauptstadt_id FROM tbl_hauptstadt WHERE land='$land'");
			if (!$result)
			{
			   die ('Ungültige Abfrage-main: ' . mysql_error());
			}
			if(mysql_num_rows($result)==1) $erg="Der Datensatz '$land' - '$hauptstadt' existiert bereits";
			else
			{
				$sql = "INSERT INTO tbl_hauptstadt (land , hauptstadt, einwohner) VALUES ('$land' , '$hauptstadt' , '$einwohner' );";
				   $result = mysql_query($sql, $db_link); 
				if (!$result)	{ die ('Ungültige Abfrage-main: ' . mysql_error()); }
				
				$result = mysql_query("SELECT hauptstadt_id FROM  tbl_hauptstadt WHERE hauptstadt='$hauptstadt';");
				if (!$result) {die ('Ungültige Abfrage2: ' . mysql_error()); }
				$row = mysql_fetch_array($result);
				$hid = $row['hauptstadt_id'];
				
				for($i=0; $i < $zahl; $i++) 
				{
					$result = mysql_query("SELECT erdteil_id FROM  tbl_erdteil WHERE erdteil='".$kontinent[$i]."';");
					if (!$result) {die ('Ungültige Abfrage1: ' . mysql_error()); }
					$row = mysql_fetch_array($result);
					$kid =$row['erdteil_id'];
					$result = mysql_query("SELECT erdteil_id FROM  tbl_hauptstadt_erdteil WHERE erdteil_id='$kid' and hauptstadt_id='$hid';");
					if (!$result) {die ('Ungültige Abfrage-main: ' . mysql_error()); }
					if(mysql_num_rows($result)==1) $erg="Die Verkn&uuml;pfung '$kontinent' - '$hauptstadt' existiert bereits";
					else
					{
						$sql = "INSERT INTO tbl_hauptstadt_erdteil ( erdteil_id, hauptstadt_id ) VALUES ('$kid','$hid');";
						$result = mysql_query($sql, $db_link); 
						if (!$result)
						{
						   die ('Ungültige Abfrage-main: ' . mysql_error());
						}
						$erg = "neues Land wurde eingetragen und mit Kontinent verkn&uuml;pft";
					}
				}
			}
		}
		else $erg = "Sie haben nicht alle Felder ausgefuellt.";
	}
	
	// Neuen Kontinent eintragen
	if (isset($_GET['modifyk'])) // Bearbeitungsmodus
	{	
		$kontinent = "";
		$kontinent = $_GET['kontinent']; 
		if ($kontinent != "") {
			$result = mysql_query("SELECT erdteil_id FROM  tbl_erdteil WHERE erdteil='$kontinent'");
			if (!$result)
			{
			   die ('Ungültige Abfrage-main: ' . mysql_error());
			}
			if(mysql_num_rows($result)==1) $erg="Der Datensatz '$kontinent' existiert bereits";
			else
			{
				$sql = "INSERT INTO tbl_erdteil ( erdteil ) VALUES ('$kontinent');";
					$result = mysql_query($sql, $db_link); 
				if (!$result) {die ('Ungültige Abfrage-main: ' . mysql_error()); }
				$erg = "neuen Kontinent '$kontinent' eingetragen";
			}
		}
		else $erg = "Sie haben nicht alle Felder ausgefuellt.";
	}
	
	// Neue Verknüpfung eintragen
	if (isset($_GET['modifypf'])) // Bearbeitungsmodus
	{	
		$kontinent = "";
		$hauptstadt = "";
		$kontinent = $_GET['kontinent'];
		$hauptstadt = $_GET['hauptstadt'];
		if ($kontinent != "" && $hauptstadt != "") {
			$result = mysql_query("SELECT erdteil_id FROM  tbl_erdteil WHERE erdteil='$kontinent';");
			if (!$result) {die ('Ungültige Abfrage1: ' . mysql_error()); }
			$row = mysql_fetch_array($result);
			$kid =$row['erdteil_id'];
			$result = mysql_query("SELECT hauptstadt_id FROM  tbl_hauptstadt WHERE hauptstadt='$hauptstadt';");
			if (!$result) {die ('Ungültige Abfrage2: ' . mysql_error()); }
			$row = mysql_fetch_array($result);
			$hid = $row['hauptstadt_id'];
			$result = mysql_query("SELECT erdteil_id FROM  tbl_hauptstadt_erdteil WHERE erdteil_id='$kid' and hauptstadt_id='$hid';");
			if (!$result) {die ('Ungültige Abfrage-main: ' . mysql_error()); }
			if(mysql_num_rows($result)==1) $erg="Die Verkn&uuml;pfung '$kontinent' - '$hauptstadt' existiert bereits";
			else
			{
				$sql = "INSERT INTO tbl_hauptstadt_erdteil ( erdteil_id, hauptstadt_id ) VALUES ('$kid','$hid');";
				$result = mysql_query($sql, $db_link); 
				if (!$result)
				{
				   die ('Ungültige Abfrage-main: ' . mysql_error());
				}
				$erg = "neuen Kontinent eingetragen";
			}
		}
		else $erg = "Sie haben nicht alle Felder ausgefuellt.";
	}
	
	// ----------- Ausgabe der Tabelle -----------
	echo '<h2>Tabelle mit L&auml;ndern</h2><br />';
		// Filter
		$kontinent = "";
		$hauptstadt = "";
		if (isset($_GET['filter']))
		{
			$kontinent = $_GET['kontinent']; 
			$hauptstadt = $_GET['hauptstadt'];
		}
		echo '
		<form method="get" action="'.$indexname.'">
		<input type="hidden" name="id"  value="schule"/>
		<input type="hidden" name="filter"  value="j"/>
		Hauptstadt: <select name="hauptstadt" size="1">';
		$sql = 'select * from tbl_hauptstadt';
		$result = mysql_query($sql, $db_link) or die ("MySQL-Fehler: " . mysql_error()); 
		echo '<option></option>';
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			echo "<option>$row[hauptstadt]</option>";
		}
		echo '</select>
		Kontinent: <select name="kontinent" size="1">';
		$sql = 'select * from tbl_erdteil';
		$result = mysql_query($sql, $db_link) or die ("MySQL-Fehler: " . mysql_error()); 
		echo '<option></option>';
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			echo "<option>$row[erdteil]</option>";
		}
		echo '
		</select>
		&nbsp;<input class="button" type=submit name=submit value="filtern">
		</form> ';
		if ($kontinent != "") { // Wenn Kontinent  (und Hauptstadt) gesetzt
			if ($hauptstadt != "") $sql = 'select * from tbl_hauptstadt, tbl_erdteil, tbl_hauptstadt_erdteil
			where tbl_hauptstadt_erdteil.erdteil_id = tbl_erdteil.erdteil_id
			and tbl_hauptstadt_erdteil.hauptstadt_id = tbl_hauptstadt.hauptstadt_id
			and tbl_hauptstadt.hauptstadt = "'.$hauptstadt.'"	
			and tbl_erdteil.erdteil = "'.$kontinent.'";';
			else 		$sql = 'select * from tbl_hauptstadt, tbl_erdteil, tbl_hauptstadt_erdteil
			where tbl_hauptstadt_erdteil.erdteil_id = tbl_erdteil.erdteil_id
			and tbl_hauptstadt_erdteil.hauptstadt_id = tbl_hauptstadt.hauptstadt_id
			and tbl_erdteil.erdteil = "'.$kontinent.'";'; 
			}
		else if ($hauptstadt != "") $sql = 'select * from tbl_hauptstadt, tbl_erdteil, tbl_hauptstadt_erdteil
			where tbl_hauptstadt_erdteil.erdteil_id = tbl_erdteil.erdteil_id
			and tbl_hauptstadt_erdteil.hauptstadt_id = tbl_hauptstadt.hauptstadt_id
			and tbl_hauptstadt.hauptstadt = "'.$hauptstadt.'";';
		else $sql = 'select * from tbl_hauptstadt, tbl_erdteil, tbl_hauptstadt_erdteil
			where tbl_hauptstadt_erdteil.erdteil_id = tbl_erdteil.erdteil_id
			and tbl_hauptstadt_erdteil.hauptstadt_id = tbl_hauptstadt.hauptstadt_id;'; 	
		$result = mysql_query($sql);
		if (!$result) {die ('Ungültige Abfrage1: ' . mysql_error()); }
		echo 	"<br /><table><th>Land</th><th>Hauptstadt</th><th>Einwohner</th><th>Erdteil</th>";
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			echo "<tr><td>$row[land] <br /></td>
			<td>$row[hauptstadt]</td>
			<td>$row[einwohner]</td>
			<td>$row[erdteil]</td>
			</tr>";
		}
		echo 	"</table><br />";
		// Edit Funktionen
	if (isset($_GET['edit'])) // Bearbeitungsmodus
	{
		if ($_GET['edit']=="land") // Bearbeitungsmodus land
		{ ?>
			<h2>Bitte Land eintragen</h2>
			<p>
				<form method="get" action="<?php echo $indexname; ?>">
					<input type="hidden" name="id"  value="schule"/>
					<input type="hidden" name="modifyl"  value="j"/>
					Land: <input name="land" class="leftdata" />  <br />
					Hauptstadt: <input name="hauptstadt" class="leftdata" />  <br />
					Einwohner: <input name="einwohner" class="leftdata" />  <br />
					Kontinent: <select name="kontinent[]" size="1" multiple>
					<?php $sql = 'select * from tbl_erdteil';
					$result = mysql_query($sql, $db_link) or die ("MySQL-Fehler: " . mysql_error()); 
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						echo "<option>$row[erdteil]</option>";
					} ?>
					</select></br>
					<input class="button" type=submit name=submit value="Eintragen">
				</form>
			</p>
		<?php }		
		else if ($_GET['edit']=="kontinent") // Bearbeitungsmodus kontinent
		{	?>
			<h2>Bitte Kontinent eintragen</h2>
			<p>
				<form method="get" action="<?php echo $indexname; ?>">
					<input type="hidden" name="id"  value="schule"/>
					<input type="hidden" name="modifyk"  value="j"/>
					Kontinent: <input name="kontinent" class="leftdata" />  <br />
					<input class="button" type=submit name=submit value="Eintragen">
				</form>
			</p>
			<br />
		<?php }		
		else if ($_GET['edit']=="pf") // Bearbeitungsmodus pf
		{ ?>
			<h2>Bitte Verkn&uuml;pfung erstellen</h2>
			<p>
				<form method="get" action="<?php echo $indexname; ?>">
					<input type="hidden" name="id"  value="schule"/>
					<input type="hidden" name="modifypf"  value="j"/>
  					Hauptstadt: <select name="hauptstadt" size="1">
					<?php 
					$sql = 'select * from tbl_hauptstadt';
					$result = mysql_query($sql, $db_link) or die ("MySQL-Fehler: " . mysql_error()); 
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						echo "<option>$row[hauptstadt]</option>";
					}?>
					</select><br />
					Kontinent: <select name="kontinent" size="1">
					<?php 
					$sql = 'select * from tbl_erdteil';
					$result = mysql_query($sql, $db_link) or die ("MySQL-Fehler: " . mysql_error()); 
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						echo "<option>$row[erdteil]</option>";
					}?>
					</select><br />
					<input class="button" type=submit name=submit value="Verkn&uuml;pfen">
				</form>
			</p>
			<br />
<?php	}
		else if ($_GET['edit']=="createcsv") // Bearbeitungsmodus kontinent
		{	?>
			<h2>Bitte Kontinent eintragen</h2>
			<p>
				<form method="get" action="<?php echo $indexname; ?>">
					<input type="hidden" name="id"  value="schule"/>
					<input type="hidden" name="createcsv"  value="j"/>
					Kontinent: <select name="kontinent" size="1">
					<?php 
					$sql = 'select * from tbl_erdteil';
					$result = mysql_query($sql, $db_link) or die ("MySQL-Fehler: " . mysql_error()); 
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						echo "<option>$row[erdteil]</option>";
					}?>
					</select><br />
					<input class="button" type=submit name=submit value="CSV erstellen.">
				</form>
			</p>
			<br />
		<?php }		
	} ?>
				<div class="buttondiv">
				<form method="post" action="<?php echo $indexname; ?>?id=schule&edit=kontinent">
				<input class="button" type=submit name=submit value="Edit Kontinent">
				</form>
			</div>
			<div class="buttondiv">
				<form method="post" action="<?php echo $indexname; ?>?id=schule&edit=land">
				<input class="button" type=submit name=submit value="Edit Land">
				</form>
			</div>
			<div class="buttondiv">
				<form method="post" action="<?php echo $indexname; ?>?id=schule&edit=pf">
				<input class="button" type=submit name=submit value="Edit Verkn&uuml;pfung">
				</form>
			</div>
			<div>
				<form method="post" action="<?php echo $indexname; ?>?id=schule&edit=createcsv">
				<input class="button" type=submit name=submit value="csv ausgeben">
				</form>
			</div>
		<?php if($erg != "") echo '<br /><div class="infotext">'.$erg.'</div>';?>
<br />
<a href="index.php?id=schule2">Dev</a><!-- Dev Link um nicht original zu zerstören -->