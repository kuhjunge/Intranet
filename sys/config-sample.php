<?php
// Allgemeine Einstellungen
define("DEBUG",     false); // DEBUG Modus
define("FEHLER",     true); // Fehler Anzeigen Modus
// MySQL Verbindungen
define("MYSQLSERVER",     'localhost'); // MySQL Server
define("MYSQLUSER",     'root'); // MySQL User
define("MYSQLPW",     ''); // MySQL Passwort
define("MYSQLDB",     'intranet'); // MySQL Passwort
// User Management
define("USEMYSQL",     true); // Nutze MySQL Datenbank
define("HASHSYS",     true); // Benutze Passwort Hashes
define("SALT",     '$5$rounds=5000$usesomesillystringforsalt$'); // Benutze Passwort Hashes
define("CASESENSITIVE",     false); //Groß und Kleinschreibung beachten bei Usernamen
?>