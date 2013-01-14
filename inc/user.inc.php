<?php
// Inputs abschließen XHTML?
$user->gesperrt();
$url ='';
$user_c = new User(true);
if (isset($_GET['name'])) 
{
	$rname = $_GET['name'];
	$user_c->loadbyname($rname, $user);
	$url = "&name=$rname";
}
?>
	<h2>Benutzerverwaltung</h2><br />
	<?php
	if (isset($_GET['edit'])) // Bearbeitungsmodus
	{
		if ($_GET['edit'] == "all")
		{
		if  (isset ($_POST["grp"]) || 
			(isset ($_POST["passwort"]) && isset($_POST["neupasswort"])&& isset($_POST["neupasswort2"])) ||
			isset ($_POST["mail"]) ||
			isset ($_POST["bio"])) {
				if (isset ($_POST["grp"])) $user_c->setGrp($_POST["grp"]);
				if (isset ($_POST["passwort"]) && isset($_POST["neupasswort"])&& isset($_POST["neupasswort2"])) $user_c->setpw($_POST["passwort"], $_POST["neupasswort"], $_POST["neupasswort2"]);
				if (isset ($_POST["mail"])){$user_c->setMail($_POST["mail"]);}
				if (isset ($_POST["bio"])) $user_c->setBio($_POST["bio"]);
				Header("Location: index.php?id=user$url");
				}
			else
			{?>
			<form method="post" action="index.php?id=user&edit=all<?php echo $url?>">
				<div>			
					<p>Mail:<input class="leftdata" value="<?php echo $user_c->getMail().''; ?>" name="mail" type=text maxlength="50" /></p>
					<p style="height: 100px;">Bio: <textarea class="leftdata" style="margin-top: 2px; margin-bottom: 3px; height: 90px;" name="bio" ><?php echo "".$user_c->getBio().''; ?></textarea></p>
					<br />
					<p>Altes Passwort: <input class="leftdata" name="passwort" type=password maxlength="20" /></p>
					<p>Neues Passwort: <input class="leftdata" name="neupasswort" type=password maxlength="20" /></p>
					<p>Wiederholen: <input class="leftdata" name="neupasswort2" type=password maxlength="20" /></p>
					<?php if ($user_c->getEdit() > 1){ ?>
					<br /><p>Recht: <input class="leftdata" name="right" type=text maxlength="1" value="<?php echo $user_c->getGrp(); ?>"/></p>
					<?php } ?>
				</div>

				<div class="clear" > </div> <!-- bricht div Container Verschachtelung auf -->
				<br />
				<input class="button" type=submit name=submit value="&Auml;ndern">
			</form>
	<?php   }
		}
		if ($_GET['edit'] == "newuser") // Neuen Nutzer erstellen
		{
			if (isset($_POST["user"]) && isset($_POST["neupasswort"])&& isset($_POST["neupasswort2"]))
			{
				$newname = $_POST["user"];
				$erg = $user->createUser($newname, $_POST["neupasswort"], $_POST["neupasswort2"]);
				Header("Location: index.php?id=user&name=$newname");
				if (!$erg) echo '<div class="fehler">Fehlerhafte Eingabe - Passwort falsch oder Nutzer schon vorhanden?</div>';
			}
			else
			{	?>
			<form method="post" action="index.php?id=user&edit=newuser">
				<div class="left" style="line-height: 1.5;">
					Nutzername: <br />
					Passwort: <br />
					Wiederholen: <br />
				</div>
				<div style="line-height: 1.5;" class="left">
					<input name="user"  size="10" /> <br />
					<input name="neupasswort" type=password size="10" /> <br />
					<input name="neupasswort2" type=password size="10" /> <br />
				</div>
				<div class="clear" > </div> <!-- bricht div Container Verschachtelung auf -->
				<br />
				<input class="button" type=submit name=submit value="Erstellen">
			</form>
	<?php   }
		}
	}
	else if (isset($_GET['name'])) // Wenn Nutzerprofil aufgerufen wird
	{ 
		if ($rname != $user_c->getName()) echo '<div class="fehler">Fehler! Diesen Nutzer gibt es nicht!</div>';
		else // und der Nutzer auch existiert
		{
		?>
		<div class="right" ><img src="<?php echo get_gravatar($user_c->getMail());?>" width="80" height="80" alt="Benutzerbild" title="Benutzerbild" /></div>
			<div class="left" style="padding-right: 15px;">
			Name: <br />
			Mail: <br />
			Bio:  <br />
		</div>
		<div>
			<?php echo $user_c->getName(); ?><br />
			<a href="mailto:<?php echo $user_c->getMail(); ?>" ><?php echo $user_c->getMail(); ?></a><br />
			<?php echo $user_c->getBio(); ?><br />
		</div>
			<?php if ($user_c->getEdit() > 0) { ?>
		<br />
		<form method="post" action="index.php?id=user&edit=all<?php echo $url?>">
				<input class="button" type=submit name=edit value="Daten &auml;ndern">
		</form>
		<?php 
		if ($user_c->getEdit() > 1){ ?>
		<form method="post" action="index.php?id=update">
			<input type="hidden" name="deaktuser" value="<?php echo $user_c->getName(); ?>">
			<input class="button" type=submit name=edit value="<?php if ($user_c->getActive() == 1) echo "Deaktivieren"; else echo "Aktivieren";?>">
		</form>
		<form method="post" action="index.php?id=update">
			<input type="hidden" name="deluser" value="<?php echo $user_c->getName(); ?>" > 
			<input class="button" type=submit name=edit value="Nutzer l&ouml;schen">
		</form>
		<?php } 
			}
		}
	}
	else if (isset($_GET['group'])) // Wenn Nutzerprofil aufgerufen wird
	{
		$grpid = $_GET['group'];
		$grp = $user->showright($grpid);
		$right = $user->rightlist();
		echo "<h3>Bearbeite Gruppe: ".$grp[0][1]." </h3>Beschreibung: ".$grp[0][2]."<br />\n
		Eingetragene Rechte:<br /><ul>";
		$temp = $grp[1];
		if (!empty($temp))
		foreach($temp as $gruppe) echo "<li>".$right[$gruppe]['name']."</li>";
		echo '</ul>
		Recht hinzuf&uuml;gen: <select name="hauptstadt" size="1">';
		for($i = 0;$i < count($grp[2]);$i++)
		{
			echo "<option>".$right[$grp[2][$i]]['name']."</option>";
		}
		echo '</select><br />	
		Recht entfernen: <select name="hauptstadt" size="1">';
		for($i = 0;$i < count($grp[1]);$i++)
		{
			echo "<option>".$right[$grp[1][$i]]['name']."</option>";
		}
		echo '</select>';
		zarr($grp);
	}	
	else // Allgemeine Nutzerverwaltungsseite
	{
		echo "<h3>registrierte Nutzer: </h3>";
		$name = $user->userList();
		foreach ($name as $value) {
			echo "<a href='index.php?id=user&name=$value'>$value</a><br />\n";
		}			
		if ($user->checkPerm(3))
		{ ?>
			<br />
			<form method="get" action="index.php">
				<input type="hidden" name="id" value="user">
				<input type="hidden" name="edit" value="newuser">
				<input class="button" type=submit name=button value="Nutzer erstellen">
			</form>
			<br />
<?php	}
		echo "<h3>aktive Gruppen: </h3>\n";
		$group = $user->grouplist();
		for($i = 0;$i < count($group);$i++)
		{
			echo "<a title='".$group[$i]['beschr']."' href='index.php?id=user&group=".$group[$i]['id']."'>".$group[$i]['name']."</a><br />\n";
		}
		if ($user->checkPerm(3))
		{ ?>
			<br />
			<form method="get" action="index.php">
				<input type="hidden" name="id" value="user">
				<input type="hidden" name="edit" value="newgroup">
				<input class="button" type=submit name=button value="Gruppe erstellen">
			</form>
			<a href="#"><div  class="button">Testlink</div></a>
			<br />
<?php	}
	}
?>