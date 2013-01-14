<?php 
	// $db->q("DROP DATABASE ".MYSQLDB);
	if ($db->q('use '.MYSQLDB) && !$user->checkPerm(4)) { Header("Location: index.php"); }
	else 
	{
		if (isset($_GET['install'])) // Bearbeitungsmodus
		{
			$db->install();
			if (isset($_POST["user"]) && isset($_POST["neupasswort"]))
			{
				$user->installdb($_POST["user"],$_POST["neupasswort"]);
			}
			Header("Location: index.php");
		}
?>
<h2>Installation</h2>
<form method="post" action="index.php?id=install&install=1">
	<div class="left" style="line-height: 1.5;">
		Nutzername: <br />
		Passwort: <br />
	</div>
	<div style="line-height: 1.5;" class="left">
		<input name="user"  size="10" /> <br />
		<input name="neupasswort" type=password size="10" /> <br />
	</div>
	<div class="clear" > </div> <!-- bricht div Container Verschachtelung auf -->
	<br />
	<input class="button" type=submit name=submit value="DB Einrichtung">
</form>
<?php } 
	if ($user->checkPerm(4) && isset($_GET["del"])) 
	{
		$db->q("DROP DATABASE ".MYSQLDB);
	}
?>