<?php 
if (isset($_POST['news']) && $user->checkPerm(2))
{
	$db->update("'".$_POST["news"]."'", "content", "id", "1","content");
	Header("Location: index.php");
}
else if (isset($_POST['deluser']) && $user->checkPerm(3))
{
	$name = $_POST['deluser'];
	$user_c = new User(true);
	$user_c->loadbyname($name, $user);
	$user_c->delete();
	Header("Location: index.php?id=user");
}
else if (isset($_POST['deaktuser']) && $user->checkPerm(3))
{
	$name = $_POST['deaktuser'];
	$user_c = new User(true);
	$user_c->loadbyname($name, $user);
	if ($user_c->getActive() == 1) 	$user_c->setActive(0);
	else $user_c->setActive(1);
	Header("Location: index.php?id=user&name=$name");
}
else
Header("Location: index.php");
?>

