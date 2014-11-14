<?php
/**
 * Script zur Verwaltung von Medien (Medienbibliothek)
 *
 * Im Auftrag des "Bildungszentrum der Bundesfinanzverwaltung Muenster"
 *
 * @copyright Hannes Becker <hb@screennetz.de>
 * @license GNU Public License Version 2 <http://opensource.org/licenses/gpl-license.php>
 * @package Multimediadatenbank
 */
ob_start();
session_start();
require_once './_core/path.inc.php';
require_once './_core/define.inc.php';
require_once './_core/database.inc.php';
require_once './_core/libs/global.func.inc.php';
require_once 'PEAR.php';
require_once 'MDB2.php';

/**
 * Pruefe ob die install.php noch vorhanden ist
 * Wenn ja beende das Script
 */
if (file_exists('./install.php') || file_exists('./update.php'))
{
	die('Bitte erst die install.php und update.php vom Server entfernen!');
}

/**
 * PEAR Error-Handler setzen
 * Sollte im Produktiv-System auskommentiert werden!
 * Da sonst zu viele Informationen bei Fehlern fuer Dritte sichtbar werden!
 */
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'handle_pear_error');

/**
 * Datenbank-Verbindung aufbauen und in $db sopeichern
 */
$db =& MDB2::connect($dsn, $dsn_options);
if (PEAR::isError($db))
{
	die('Verbindung zur Datenbank konnte nicht hergstellt werden!');
}
$db->loadModule('Extended'); // Extension-Modul von MDB2 laden
// Setze bestimmte Optionen fuer PEAR:MDB2
$db->setFetchMode(MDB2_FETCHMODE_ASSOC); // Standard-Rueckgabe-Modus von Queries
$db->setOption('quote_identifier', true);

// Veraltete oder nicht mehr aktive Sessions loeschen
$db->query('DELETE FROM '.TAB_SESSIONS.' WHERE (session_time < '.$db->quote((time()-(60*$cfg['sessionLifeTimeNoAct'])), 'integer').' OR session_start < '.$db->quote((time()-(60*60*12)), 'integer').')');

// Einbindung von weiteren Klassen und Datein
require_once './_core/libs/session.class.php'; // Session-Klasse einbinden
require_once './_core/libs/auth.class.php'; // Auth-Klasse einbinden
require_once './_core/arrays.inc.php'; // Arrays
require_once './_core/messages.inc.php'; // Mitteilungen
// Ausgabe des Headers
echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>
	<title>'.$cfg['site_name'].'</title>
	<link type="text/css" rel="stylesheet" media="screen" href="'.URL_WWW.'/medias/css/screen.css" />
	<link type="text/css" rel="stylesheet" media="screen" href="'.URL_WWW.'/medias/css/dtree.css" />
	<script type="text/javascript" language="JavaScript" src="'.URL_WWW.'/medias/js/dtree.js"></script>
</head>
<body>');
/**
 * Pruefe ob die Seite im Wartungsmodus ist
 * Wenn ja dann entsprechende Meldung ausgeben
 * Wenn nein dann normale Seite ausgeben
 */
if ((string)$cfg['maintenance'] === 'on')
{
	require PATH_ABS.'/_modules/maintenance.inc.php';
}
else
{
	//Klassen Session und Auth instazieren
	$session = new Session;
	$auth = new Auth($db, $session);
	/**
	 * Prueft ob der User noch eine gueltige Session hat
	 * Wenn nicht dann Login-Formular ansonsten der Interne-Bereich
	 */
	if ((bool)$auth->confAuth() === true)
	{
		//Benutzerdaten sammeln und in $arr_user[] (ASSOC) speichern
		$user_result = $db->query('SELECT id, user_login, user_group, check_terms_of_use, time_register FROM '.TAB_USERS.' WHERE id = '.$db->quote($session->getSession(SESSION_USERID), 'integer').'');
		$arr_user = $user_result->fetchRow();
		$user_result->free();
		echo('<div id="header"><h1>'.$cfg['site_name'].'</h1><small>Status: '.$user_group[$arr_user['user_group']].'</small></div>');
		echo('<div id="user_info">Willkommen <strong>'.$arr_user['user_login'].'</strong> [<a href="index.php?module=logout" title="abmelden">abmelden</a>]</div>');
		echo('<div id="mainmenu"><ul><li><a href="index.php">Datenbank</a></li><li><a href="index.php?module=search">Suchen</a></li><li><a href="index.php?module=request">Anfrage</a></li><li><a href="index.php?module=myaccount">mein Konto</a></li>');
		if ($auth->isAdmin())
		{
			echo('<li><a href="index.php?module=secure">Verwaltung</a></li>');
		}
		echo('</ul></div>');
		echo('<div id="content">');
		/**
		 * Pruefe ob User die TERMS_OF_USE akzeptiert hat
		 * Wenn noch nicht immer wieder die TERMS_OF_USE includen ansonsten normale Seite anzeigen
		 */
		if (MODULE != 'logout' && (bool)$arr_user['check_terms_of_use'] === false)
		{
			include_once PATH_ABS.'/_modules/terms_of_use_confirm.inc.php';
		}
		else
		{
			/**
			 * Module aus /_modules includen welche per URL (Parameter) aufgerufen wurden
			 * insofern diese nicht auf der Blacklist ($blacklist_modules) stehen
			 */
			$modules_inc = PATH_ABS.'/_modules/'.MODULE.'.inc.php';
			if (file_exists($modules_inc) && !in_array(MODULE, $blacklist_modules))
			{
				include_once $modules_inc;
			}
			else
			{
				echo $msg_err['404'];
			}
		}
		echo('</div>');
		// Ausgabe des Footers
		echo('<div id="footer"><a href="index.php?module=terms_of_use">Nutzungsbedingungen</a> &bull; <a href="index.php?module=impressum">Impressum</a> &bull; Software-Version: 1.4.1 (<a href="index.php?module=changelog">Changelog</a>)<br />Im Auftrag des "Bildungszentrum der Bundesfinanzverwaltung M&uuml;nster"<br />Technische Umsetzung  <a href="http://www.screennetz.de">Hannes Becker</a> &bull; Diese Software steht unter den Bedingungen der <a href="http://opensource.org/licenses/gpl-license.php">GPL v2</a></div>');
	}
	else
	{
		require PATH_ABS.'/_modules/login.inc.php';
	}
	// Instanzierte Klassen wieder loeschen
	unset($auth);
	unset($session);
}
// HTML-Ausgabe der Fusszeilen
echo('</body></html>');
$db->disconnect();
unset($db);
?>