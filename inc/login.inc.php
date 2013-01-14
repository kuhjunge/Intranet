<?php
$logstate = $user->getlogin();
	?>
				<form method="post" action="index.php?id=login">
				<h2>Login</h2>
				<br />
				<span class="left">
					Username: <br />
					Passwort: <br />
				</span>
				<span class="right">
					<input name="loginname" class="login" Value="<?php echo $logstate; ?>" />  <br />
					<input name="loginpasswort" type=password class="login"/> <br />
				</span>
				<div class="clear" > </div> <!-- bricht div Container Verschachtelung auf -->
				<input type=submit name=submit value="Einloggen" class="button">
				<?php if ($logstate != 1 && $logstate != '') echo '<br /><br />Login Fehlgeschlagen!';?>
			</form> 