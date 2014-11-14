<?php
/*
 * Verbindungsdaten fuer die Datenbank
 * TYPE://USER:PASSWORD@HOST/DATABASE
 */
$dsn = 'mysql://hbecker:starten1@localhost/bz';

/**
 * Verbindungs-Optionen
 */
$dsn_options['debug'] = 2;
$dsn_options['result_buffering'] = true;

/**
 * Tabellen-Prefix fur die Datenbank-Tabellen
 */
define('TAB_PREFIX', 'mdb_');

/**
 * Datenbank-Tabellen
 */
define('TAB_CATS', TAB_PREFIX.'categories');
define('TAB_DATA', TAB_PREFIX.'data');
define('TAB_LOGS', TAB_PREFIX.'logs');
define('TAB_MEDIAS', TAB_PREFIX.'medias');
define('TAB_SESSIONS', TAB_PREFIX.'sessions');
define('TAB_USERS', TAB_PREFIX.'users');
?>