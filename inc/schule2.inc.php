<?php
// ----------------------- Eigentlicher Quellcode ------------------- 
/*
PHP MYSQL Schule Aufgabe
Version: 1.0
Stand: 28.2.12
by Chris Deter
*/
	$dbs = new Datenbank('db_hauptstadt');
	$indexname = "index.php"; //Name dieses Dokumentes wenn nicht in Intranet includiert
	// Neues Land eintragen
	if (isset($_GET['modify'])) // Bearbeitungsmodus
	{
	
	}
	// Neues Land aufnehmen
	if (isset($_GET['edit'])) // Bearbeitungsmodus
	{
?>
			<h2>Bitte Land eintragen</h2>
			<p>
			<form method="post" action="index.php?id=schule">
			Land: <input name="Land" class="leftdata" />  <br />
			Hauptstadt: <input name="Hauptstadt" class="leftdata" />  <br />
			Einwohner: <input name="Einwohner" class="leftdata" />  <br />
			<input class="button" type=submit name=submit value="Bearbeiten">
			</form>
			</p>
			<br />
<?php
	}
	$sql = 'select * from tbl_hauptstadt, tbl_erdteil, tbl_hauptstadt_erdteil
		where (erdteil = "asien" OR erdteil = "europa")
		and tbl_hauptstadt_erdteil.erdteil_id = tbl_erdteil.erdteil_id
		and tbl_hauptstadt_erdteil.hauptstadt_id = tbl_hauptstadt.hauptstadt_id;';
		$result = $dbs->get_array($sql);
		echo 	"<table><th>Land</th><th>Hauptstadt</th><th>Einwohner</th><th>Erdteil</th>";
		while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			echo "<tr><td>$row2[land] <br /></td>
			<td>$row2[hauptstadt]</td>
			<td>$row2[einwohner]</td>
			<td>$row2[erdteil]</td>
			</tr>";
		}
		echo 	"</table>";
?>
			<br />
			<form method="post" action="index.php?id=schule2&edit=1">
			<input class="button" type=submit name=submit value="Bearbeiten">
			</form>
<br />
<a href="index.php?id=schule">Schulstand</a>