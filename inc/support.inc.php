<?php 
$user->gesperrt();
if ($user->checkPerm(2)) $recht = 7;
else $recht = 3;
$site = "liste";
if(isset($_GET['site'])) $site = $_GET['site'];
// Deklarationen Variablen
$typ = "";
$id = 1;
$name = "";
$ort = "";
$kontakt = "";
$hardware = "";
$prio = "";
$titel = "";
$beschreibung = "";
$loesung = "";
$createdate = "";
$lastedit = "";
$ersteller = "";
$bearbeiter = "";
$status = "";
$dbname = MYSQLDB;

// Ticket anzeigen und bearbeiten
if ($site == "ticket") {

	// Datenbank auslesen
	if (isset($_GET['inid']) || isset($_POST['inid']))
	{
		if (isset($_GET['inid'])) $id = $_GET['inid']; // Setze ID
		else $id = $_POST['inid'];
		if ($recht > 4) $q = $db->q("SELECT * FROM `$dbname`.`incident`  WHERE `id` = '".$id."';"); // Lade den rest
		else $q = $db->q("SELECT * FROM `$dbname`.`incident`  WHERE `id` = '".$id."' AND `ersteller` = '".$user->getName()."';"); 
		$incident = mysql_fetch_row($q); // Verarbeitung
		$name = $incident['1']; // Informationen in die jeweilige Variable laden.
		$ort = $incident['2'];
		$kontakt = $incident['3'];
		$hardware = $incident['4'];
		$prio = $incident['5'];
		$titel = $incident['6'];
		$beschreibung = $incident['7'];
		$loesung = $incident['8'];
		$createdate = $incident['9'];
		$lastedit = $incident['10'];
		$ersteller = $incident['11'];
		$bearbeiter = $incident['12'];
		$status = $incident['13'];
	}
	// Werte aus der Post übergabe übernehmen
	if (isset($_POST['inid'])) 
	{
		$typ = $_POST['best']; // Neu oder Update?
		$name = $_POST['name'];
		$ort = $_POST['ort'];
		$kontakt = $_POST['kontakt'];
		$hardware = $_POST['hardware'];
		$prio = $_POST['prio'];
		$titel = $_POST['titel'];
		$beschreibung = $_POST['beschreibung'];
		$loesung = $_POST['loesung'];
		$ersteller = $_POST['user'];
		$bearbeiter = $_POST['user'];

		// Wenn neuer Datensatz erstellt wird erstellen
		if ($typ == 'eröffnen')
		{
			$status = 1; 
			$db->q("INSERT INTO  `$dbname`.`incident` (`name` ,`ort` ,`kontakt` ,`hardware` ,`prio` ,`titel` ,`beschreibung` ,`loesung`, `ersteller`,`status`) VALUES ('".$name."', '".$ort."', '".$kontakt."', '".$hardware."', '".$prio."','".$titel."', '".$beschreibung."', '".$loesung."', '".$ersteller."', '".$status."');");
			Header("Location: index.php?id=support&site=liste");
			//	sendmail($titel); - Mailfunktion deaktiviert
		}
		// sonst Datensatz updaten
		else if ($recht > 4)
		{
			if ($typ == 'speichern')$status = 2;
			else $status = 3;
			$db->q("UPDATE  `$dbname`.`incident` SET `name` ='".$name."',`ort` = '".$ort."' ,`kontakt` ='".$kontakt."',`hardware` = '".$hardware."',`prio` ='".$prio."' ,`titel` ='".$titel."',`beschreibung`= '".$beschreibung."' ,`loesung` = '".$loesung."',`bearbeiter`= '".$bearbeiter."' ,`status` = '".$status."' WHERE `id`=$id;");
			if ($status == 3) Header("Location: index.php?id=support&site=liste");
		}
		$erf = true;
		
	}
// Eigentliche Website
?>
		<h2>Supportcenter - Ticketbearbeitung</h2>
		<form method="post" action="index.php?id=support&site=ticket"  enctype="multipart/form-data">
		<input type="hidden" name="inid" value="<?php echo $id ?>" />
		<input type="hidden" name="user" value="<?php echo $user->getName();?>" />
		
		Name: <input type="text" name="name" value="<?php echo $name; ?>" /><br />
		Ort: <input type="text" name="ort" value="<?php echo $ort; ?>" /><br />
		Kontakt: <input type="text" name="kontakt" value="<?php echo $kontakt;?>" /><br />
		Hardware: <input type="text" name="hardware" value="<?php echo $hardware; ?>" /><br />
		Datei Upload: <input type="file" name="datei" /><br />
		Prioritaet: <select name="prio" size="1">
			<option>1</option>
			<option <?php if ($prio == 2) echo ' selected="selected"';?>>2</option>
			<option <?php if ($prio == 3) echo ' selected="selected"';?>>3</option>
		 </select><br />
		Titel: <input type="text" size="61" name="titel" value="<?php echo $titel;?>" /><br />
		Beschreibung: <textarea rows="10" cols="47" name="beschreibung" ><?php echo $beschreibung;?></textarea><br />
		Loesung <?php if ($recht > 4) echo '<textarea rows="10" cols="47" name="loesung" >'.$loesung.'</textarea>';
					else echo "$loesung<input type='hidden' name='loesung' value='$loesung' />" ;
				if ($name == "") $typ = "eröffnen";
				else $typ  = "speichern" ;
		if ($recht > 4  || $typ == "eröffnen")   {?>
			<br />
			<input class="button" type="submit" name="best" value="<?php echo $typ ;?>" />
			<?php if ($typ != "eröffnen") echo '<input class="button" type="submit" name="best" value="abschließen" />';
			echo "</form>";
			if (isset($erf)) echo '<div>Eintrag erfolgreich!</div>';
		}
	}
		// --- Liste aller Supporttickets anzeigen ---
	else { // if ($site == "liste"){ 
		$li_filter = "offen";
		if(isset($_GET['filter'])) $li_filter = $_GET['filter'];?>
		<h2>Supportcenter - Ticketliste</h2><div><br />
		<? if ($recht > 4) { // Klammert Filterfunktion für Normaluser aus?>
		<form method="get" action="index.php">
		 <input type="hidden" name="id" value="support" />
		 <input type="hidden" name="site" value="liste" />
		 <select name="filter" size="1">
			<option>offen</option>
			<option <?php if ($li_filter == "abgeschlossen") echo ' selected="selected"';?>>abgeschlossen</option>
			<option <?php if ($li_filter == "alle") echo ' selected="selected"';?>>alle</option>
			<option <?php if ($li_filter == "meine") echo ' selected="selected"';?>>meine</option>
		 </select><input class="button" type="submit" value="filtern" /></form><br />
		<?php
		}
		$i = 0;
		if ($recht <= 4) {$a = $db->q("SELECT * FROM `$dbname`.`incident` WHERE `ersteller` = '".$user->getName()."' ORDER BY `status`,`prio`");}
		else if ($li_filter == "offen") {$a = $db->q("SELECT * FROM `$dbname`.`incident` WHERE NOT `status` = 3 ORDER BY `status`,`prio`" );}
		else if ($li_filter == "abgeschlossen") $a = $db->q("SELECT * FROM `$dbname`.`incident` WHERE `status` = 3 ORDER BY `status`,`prio`");
		else if ($li_filter == "meine") $a = $db->q("SELECT * FROM `$dbname`.`incident` WHERE `bearbeiter` = '".$user->getName()."' ORDER BY `status`,`prio`");
		else $a = $db->q("SELECT * FROM `$dbname`.`incident` ORDER BY `status`,`prio`");
		
			while($array = mysql_fetch_assoc($a)){ // solange nicht nur eine zeile als ergebnis garantiert ist
				$li_titel = $array['titel'];
				$li_id = $array['id'];
				$li_prio= $array['prio'];
				$li_status = $array['status'];
				$li_name = $array['name'];
				$li_ort = $array['ort'];
				$li_kontakt = $array['kontakt'];
				$li_hardware = $array['hardware'];
				$li_beschreibung = $array['beschreibung'];
				$li_bearbeiter = $array['bearbeiter'];
				$li_ersteller = $array['ersteller'];
				$li_createdate = $array['createdate'];
				// Bearbeitung
				$i++; // Zähler einen weiter
				// -> Prio
				$li_prio_text="unwichtig";
				if ($li_prio == 1) $li_prio_text="sehr wichtig";
				else if ($li_prio == 2) $li_prio_text="wichtig";
				else if ($li_prio == 3) $li_prio_text="normal";
				// -> Status
				$li_status_text = "abgeschlossen";
				if ($li_status == 1) $li_status_text="aufgenommen";
				else if ($li_status == 2) $li_status_text="in Bearbeitung";
				// -> Farbe
				if($i % 2 == 0) $list_aussehen = "2";
				else $list_aussehen = "";
				// Ausgabe
				echo "
				<div class='ticket_wrap'>
					<div class='ticket_list$list_aussehen'>
						<div class='ticket_prio'>$li_prio_text </div> <div class='right'> $li_status_text</div> <div><span class='fett'> <a href='index.php?id=support&site=ticket&inid=$li_id'> $li_titel </a></span> </div>
					</div>
					<div class='ticket_listdetails'><span class='fett'>Name:</span> $li_name <br /> <span class='fett'>Ort:</span>  $li_ort  <span class='fett'>Kontakt:</span>  $li_kontakt  <span class='fett'>Computer:</span>  $li_hardware <br/>
					<br /><span class='fett'>Beschreibung:</span>  $li_beschreibung
					<br />
					<span class='schmal'>von <a href='index.php?id=user&name=$li_ersteller '>$li_ersteller</a> am $li_createdate	erstellt - aktueller Bearbeiter: <a href='index.php?id=user&name=$li_bearbeiter'>$li_bearbeiter</a></span></div>
				</div>";
			}
			echo '<script>
				$(".ticket_wrap").click(function() {
					$(this).toggleClass("ticket_wrap_active");
				});
				</script>';
		echo "<br />Insgesammt $i Tickets!</div>";

} // --- Supportcenter übersicht ---

?> 
<div><br /><a href="index.php?id=support&site=liste">Übersicht</a> | <a href="index.php?id=support&site=ticket">Ticket (neu)</a></div>