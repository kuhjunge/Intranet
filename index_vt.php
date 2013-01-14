<?php
// VT Testseite - V.0.1
require('sys/config.php'); //Einstellungen
if (FEHLER) error_reporting(E_ALL);
else error_reporting(0);

$id = 'index';
if (isset($_GET["id"]))
{	
	$test = $_GET["id"];
	if(file_exists("inc/$test.inc.php"))
	{
		$id = $_GET["id"];
	}
}
/* Für Grundfunktionalität benötigte Includes einbinden */
require('sys/dbcon.php'); //Datenbankeinbindung
$db = new Datenbank;
include('sys/functions.php'); //Functions
require('sys/usermanagement.php');//Usermanagement
/* Session bearbeiten*/
session_start(); // zum Schluss die Session laden
if (DEBUG)
{
echo zarr(getBrowser())."<br />";
//echo serverinfo()."<br />";
}
// Gucken ob Login vorhanden - Wenn nicht dann standard
$user = new User;
if (!isset($_SESSION['login']))
{
	$_SESSION['login'] = "";
}
// zarr($_SESSION); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="de-DE">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Author" content="Kuhjunge" />
		<meta http-equiv="keywords" content="test, intranet, usw" />
		<meta http-equiv="description" content="Informationen zu unserer WG und so weiter" />
		<meta name="Tool" content="versatel, www.versatel.com" />
		<link rel="shortcut icon" href="art/notesundae.ico" />
		<title>Versatel Test</title>
		<link rel="stylesheet" type="text/css" href="css/normal_versa.css" />
		<?php // if(file_exists("css/$id.inc.css")) echo '<link rel="stylesheet" type="text/css" href="css/'.$id.'.inc.css" />';?>
		<style type="text/css"><!--
		/* ... Hier werden die Formate definiert ... */
		--></style>
		<!--[if IE]><link rel="stylesheet" type="text/css" href="css/iefix.css" /><![endif]-->
		<script src="js/jquery.js"></script>
		<?php // if(file_exists("js/$id.inc.js")) echo '<script src="js/'.$id.'.inc.js"></script>';?>
		<script>
		//	window.onload = function(){ alert("welcome"); }
		//	$("#nr7").hide();
		</script>
	</head>
	<body>
		<div id="wrap">
			<div id="container">
				<div id="header">
						<div id="nav">
							<ul class="nav">
								<li><a href="index.php?id=index">Home</a></li>
								<li><a href="index.php?id=admin">Admin</a></li>
								<li><a href="index.php?id=schule">Schule</a></li>
							<!--	<li><a href="index.php?id=pony">MLP:FIM</a></li> -->
								<li><a href="index.php?id=support">Support</a></li>
							</ul>
						</div><!-- nav -->
						<h1>Test</h1>
						<div class="clear" > </div> <!-- bricht div Container Verschachtelung auf -->
				</div><!-- Header -->
				<div id="main">
					<div id="content">
						<?php  include("inc/$id.inc.php"); ?>
					</div><!-- content -->
					<div id="sidebar">
						<?php  include("sys/sidebar.php"); ?>
					</div><!-- sidebar -->
					<div class="clear" > </div> <!-- bricht div Container Verschachtelung auf -->					
				</div><!-- main -->
				<?php if ($db->getstate() != 'online') echo '<div class="fehler"> Die Datenbank konnte nicht verbunden werden.</div>'; ?>
				<div id="footer">
					<p>
					<a href="#" title="Valid CSS"><img  title="Valid CSS" src="art/w3s.gif" alt="Valid CSS" width="80" height="15" /></a>
					<a href="#" title="Valid HTML"> <img title="Valid HTML" src="art/w3h.gif" alt="Valid HTML" width="80" height="15" /></a>
					&copy; <?php echo date('Y'); ?> - <a href="#" title="Intranet">Kuhfreunde Intranet</a> | All Rights Reserved. | Designed by 
					<a href="http://www.quhfan.de/" title="Homepage des Erstellers.">Kuhjunge</a>
					</p>
				</div><!-- Footer -->
			</div><!-- Container -->
		</div><!-- Wrap -->
	</body>
</html> 