<?php 
$content = $db->get("content","id", "1","content");
if (isset($_GET['edit']) && $user->checkPerm(2))
{ ?>
<div>
	<form method="post" action="index.php?id=update">
			<h2>Willkommen</h2>
			im Intranet Kuhfreunde.<br /><br />

			<h3>Newsbereich</h3>
			<!-- Content News -->
			<textarea name="news" cols="50" rows="5" ><?php echo $content; ?></textarea> <br />	
			<!-- Content News close-->
			<div>
			<br />
				<h3>WLanZugang</h3>
				<b>WlanID:</b> Kuhfreunde <br />
				<b>WlanPW:</b> EmilieAutumn
			</div>
			<p><input class="button" type=submit name=submit value="Speichern"></p>
		</form>
</div>	
<?php } else { ?>
	<div>
	<h2>Willkommen</h2>
	im Intranet Kuhfreunde.<br /><br />

	<h3>Newsbereich</h3>
		<?php echo $content; ?>
	</div>
<?php 
	if ($user->checkPerm(2))
	{?>
		<p>
		<form method="post" action="index.php?id=index&edit=post">
		<input class="button" type=submit name=submit value="Bearbeiten">
		</form>
		</p>
<?php
	}	
}
?>
