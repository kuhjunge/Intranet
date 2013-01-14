<div class="widget"><!-- Login -->
<?php if (!$user->checkPerm(1))
        { ?>
		<h4>Login</h4>
			<form method="post" action="index.php?id=login">
				<div class="left" >
					Username: <br />
					Passwort: <br />
				</div>
				<div class="right">
					<input name="loginname" class="login" />  <br />
					<input name="loginpasswort" type=password class="login"/> <br />
				</div>
				<div class="clear" > </div> <!-- bricht div Container Verschachtelung auf -->
				<div style="text-align: center; padding: 5px;"><input type=submit name=submit value="Einloggen" class="button"></div>
			</form>
	<?php
        }
	else // wenn eingeloggt
	{ ?><h4><a href="index.php?id=user&name=<?php echo $user->getName(); ?>" title="Benutzer">Hallo <?php echo $user->getName(); ?><a></h4>
	<a href="index.php?id=admin" title="Administration">Administration</a>
		<br /><a href="index.php?id=logout" title="Logout">Logout</a><?php } ?>
</div><!-- Login --> 

<!-- Haushaltsplan Widget -->
<div class="widget">
	<h4>Haushaltsplan KW <?php echo date("W"); ?></h4>
	<?php haushalt() ?>
 </div> <!-- Haushaltsplan Widget -->
 
  <div class="widget"><!-- WLan Widget -->
 	<h4>WLanZugang</h4>
	WlanID: Kuhfreunde <br />
	WlanPW: EmilieAutumn
</div><!-- WLAN Widget -->
 
 <div class="widget"><!-- Link Widget -->
 	<h4>Links</h4>
 	<ul>
	<li><a href="http://localhost/xampp">xampp</a></li>
	<li><a href="http://viona:8080">SabNZB</a></li>
	<li><a href="http://easy.box/">Router</a></li>
	<!--<li><a href="<?php // echo $PHP_SELF ?>?id=logout">Logout</a></li>-->
	</ul>
</div><!-- Link Widget -->