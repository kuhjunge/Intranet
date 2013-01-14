<?php
function zarr($arr)
{
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

function val($str)
{
	$val = mysql_real_escape_string(strip_tags($str)); 
	return $val;
}	

function check_string($string) {
	// erlaubte zeichen a-z,A-Z,0-9,-,_
	if((preg_match('/^[a-zA-Z0-9\-\_]+$/',$string))) return true;	
	return false;
}
/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
	$url = 'http://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}
	return $url;
}

// Sendet eine EMail - erzeugt keinen Fehler
function sendmail($inci) {
	$sender = "Admin@localhost";
	$empfaenger = "quhberta@googlemail.com";
	$betreff = "Supportmail";
	$mailtext = "Moin Max!<br>Es ist einer neuer Incident <b>$inci</b> eingegangen.";
	@mail($empfaenger, $betreff, $mailtext, "From: $sender\n" . "Content-Type: text/html; charset=iso-8859-1\n"); 
}

function pwgen($number)
{
	$password = '';
	$zeichen = "qwertzupasdfghkyxcvbnm";
	$zeichen .= "123456789";
	$zeichen .= "WERTZUPLKJHGFDSAYXCVBNM";
	srand((double)microtime()*1000000); 
  	  //Startwert für den Zufallsgenerator festlegen
	for($i = 0; $i < $number; $i++)
	{
	  $password .= substr($zeichen,(rand()%(strlen ($zeichen))), 1);
	}
	return $password;
}

function cookie($read = true)
{
	if (!read) setcookie("kuhfreunde", $_SESSION['username'], $_SESSION['login'], time()+(3600*24*365));
	else {
		if (isset($_COOKIE['kuhfreunde']) && $_COOKIE['kuhfreunde'] != "") { 
			//	$_SESSION['username'] = $_COOKIE['kuhfreunde']; 
		} else { 
			//	$session = ""; 
		}
	}
}

function haushalt()
{
	if(date("W") % 3 == 0) 
		echo "Chris: Bad<br />Paddy: Flur<br />Kevin: K&uuml;che";
	else if((date("W") + 1) % 3 == 0)
		echo "Chris: Flur<br />Paddy: K&uuml;che<br />Kevin: Bad"; 
	else 
		echo "Chris: K&uuml;che<br />Paddy: Bad<br />Kevin: Flur"; 
	// mülltonnen
	echo '<br /><br /><b>M&uuml;lltonnen</b><br />';
	if(date("W") % 2 == 0) 
		echo "Blau (Di)<br />Braun (Di)<br />Schwarz (Di)<br />Gelb (So)";
	else 
		echo "Schwarz (Di)";
}

function serverinfo()
{
	if (DEBUG) echo $_SERVER['HTTP_HOST'];
/*	switch ($_SERVER['HTTP_HOST']) {
		case "pietdev.versatel.local":
			$env_out = '&nbsp;&nbsp;&nbsp;<b><font color="#0000FF" size="6">TESTSYSTEM</font></b>';
			echo "TESTSYSTEM";
			break;
		case "piet.versatel.local":
			$env_out = '';
			echo "Projekt-Information-Erfassungs-Tool";
			break;
		case "pietvm01fl.versatel.local":
			$env_out = '';
			echo "Projekt-Information-Erfassungs-Tool";
			break;
		case "pietvm01fl":
			$env_out = '';
			echo "Projekt-Information-Erfassungs-Tool";
			break;
		default:
			$env_out = '&nbsp;&nbsp;&nbsp;<b><font color="#FF0000" size="6">ENTWICKLUNG</font></b>';
			echo "ENTWICKLUNG";
	}*/
	return $_SERVER['HTTP_HOST'];
	//return $env_out;
}

function getBrowser()  
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
    
	// CSS3 Check
	$css = false;
	switch($ub)
	{
	case "Crome":
			$css = true;
	break;
	}
	
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
		'css3'   => $css,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 
?>
