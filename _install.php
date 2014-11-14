<?php
require_once './_core/path.inc.php';
require_once './_core/define.inc.php';
require_once './_core/database.inc.php';
require_once './_core/libs/global.func.inc.php';
echo('<h1>Installation</h1>');
echo('<h2>Schreibrechte testen</h2>');
if (!is_writeable(PATH_ABS.'/files'))
{
	die('Das Files-Verzeichnis ('.PATH_ABS.'/files) hat nicht gen&uuml;gend Schreibrechte!');
}
echo('<p>Schreibrechte: OK!</p>');
echo('<h2>Datenbank</h2>');
require_once 'PEAR.php'; // PEAR allgemein
require_once 'MDB2.php'; // PEAR MDB2-Datenbank
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'handle_pear_error');
$db =& MDB2::connect($dsn, $dsn_options);
if (PEAR::isError($db))
{
	die('Verbindung zur Datenbank konnte nicht hergstellt werden! Bitte die Daten in der '.PATH_ABS.'/_core/database.inc.php anpassen');
}
echo('<p>Verbindung: OK!</p>');
echo('<h2>Datenbank einrichten</h2>');
$sql_install_cats = "CREATE TABLE `".TAB_CATS."` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `root_id` mediumint(8) unsigned NOT NULL,
  `cat_name` varchar(255) collate utf8_bin NOT NULL,
  `lft` mediumint(8) unsigned NOT NULL,
  `rft` mediumint(8) unsigned NOT NULL,
  `conf_view` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6";
$db->query($sql_install_cats);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
$sql_insert_cats = "INSERT INTO `".TAB_CATS."` (`id`, `root_id`, `cat_name`, `lft`, `rft`, `conf_view`) VALUES
(1, 1, 0x417564696f, 1, 2, 1),
(2, 2, 0x42696c64657220756e6420466f746f73, 1, 2, 2),
(3, 3, 0x47726166696b656e, 1, 2, 1),
(4, 4, 0x536b7269707465, 1, 2, 1),
(5, 5, 0x566964656f73, 1, 2, 1)";
$db->query($sql_insert_cats);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
unset($sql_insert_cats);
unset($sql_install_cats);
echo('<p>'.TAB_CATS.' erfolgreich erstellt!');

$sql_install_data = "CREATE TABLE `".TAB_DATA."` (
  `data_name` varchar(255) collate utf8_bin NOT NULL,
  `data_integer` int(11) unsigned NOT NULL,
  `data_string` text collate utf8_bin NOT NULL,
  PRIMARY KEY  (`data_name`),
  UNIQUE KEY `data_name` (`data_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query($sql_install_data);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
$sql_insert_data = "INSERT INTO `".TAB_DATA."` (`data_name`, `data_integer`, `data_string`) VALUES
(0x454d41494c5f4f52444552, 0, 0x6d61696c406d61696c2e746c64),
(0x5448554d425f5749445448, 150, ''),
(0x5448554d425f484549474854, 150, ''),
(0x5445524d535f4f465f555345, 0, 0x446965204e75747a756e6773626564696e67756e67656e),
(0x454d41494c5f434f4e54414354, 0, 0x6d61696c31406d61696c2e746c640d0a6d61696c32406d61696c2e746c640d0a6d61696c33406d61696c2e746c64)";
$db->query($sql_insert_data);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
unset($sql_insert_data);
unset($sql_install_data);
echo('<br />'.TAB_DATA.' erfolgreich erstellt!');

$sql_install_medias = "CREATE TABLE `".TAB_MEDIAS."` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `cat_id` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `media_access` tinyint(1) unsigned NOT NULL default '0',
  `media_name` varchar(255) collate utf8_bin NOT NULL,
  `media_desc` text collate utf8_bin,
  `media_tags` text collate utf8_bin NOT NULL,
  `media_dir` varchar(64) collate utf8_bin NOT NULL,
  `media_file` varchar(100) collate utf8_bin NOT NULL,
  `media_type` varchar(255) collate utf8_bin NOT NULL,
  `static_dl_web` mediumint(8) unsigned NOT NULL default '0',
  `static_dl_print` mediumint(8) unsigned NOT NULL default '0',
  `time_create` int(11) unsigned NOT NULL,
  `time_change` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
UNIQUE KEY `media_file` (`media_file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query($sql_install_medias);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
unset($sql_install_medias);
echo('<br />'.TAB_MEDIAS.' erfolgreich erstellt!');

$sql_install_sessions = "CREATE TABLE ".TAB_SESSIONS." (
  `session_id` varchar(32) collate utf8_bin NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `session_start` int(11) unsigned NOT NULL,
  `session_time` int(11) unsigned NOT NULL,
  `ip` varchar(15) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`session_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
$db->query($sql_install_sessions);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
unset($sql_install_sessions);
echo('<br />'.TAB_SESSIONS.' erfolgreich erstellt!');

$sql_install_user = "CREATE TABLE `".TAB_USERS."` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `user_login` varchar(40) collate utf8_bin NOT NULL,
  `user_pwd` varchar(64) collate utf8_bin NOT NULL,
  `user_group` tinyint(1) unsigned NOT NULL default '1',
  `check_terms_of_use` tinyint(1) unsigned NOT NULL default '0',
  `time_register` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_login` (`user_login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query($sql_install_user);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
unset($sql_install_user);
$db->query("INSERT INTO ".TAB_USERS." (user_login, user_pwd, user_group, time_register) VALUES (".$db->quote('admin', 'text').", ".$db->quote(hash('sha256', 'dps2hir5r1QlH6CIXvHwflPgZavrx8NGgKnYyumEKiIqOGvKaWNbFjuQqXrGFraHJstarten1'), 'text').", 2, ".time().")");
echo('<br />'.TAB_USERS.' erfolgreich erstellt!');

$sql_install_log = "CREATE TABLE `".TAB_LOGS."` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `log_time` int(11) unsigned NOT NULL,
  `log_uid` mediumint(8) unsigned NOT NULL,
  `log_type` enum('action','error') collate utf8_bin NOT NULL,
  `log_message` varchar(255) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6";
$db->query($sql_install_log);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
$sql_insert_log = "INSERT INTO `".TAB_LOGS."` (`id`, `log_time`, `log_uid`, `log_type`, `log_message`) VALUES
(1, ".time().", 1, 0x616374696f6e, 0x4e657565722048617570746f72646e65722022417564696f222065727374656c6c74),
(2, ".time().", 1, 0x616374696f6e, 0x4e657565722048617570746f72646e6572202242696c64657220756e6420466f746f73222065727374656c6c74),
(3, ".time().", 1, 0x616374696f6e, 0x4e657565722048617570746f72646e6572202247726166696b656e222065727374656c6c74),
(4, ".time().", 1, 0x616374696f6e, 0x4e657565722048617570746f72646e65722022536b7269707465222065727374656c6c74),
(5, ".time().", 1, 0x616374696f6e, 0x4e657565722048617570746f72646e65722022566964656f73222065727374656c6c74)";
$db->query($sql_insert_log);
if (PEAR::isError($db))
{
	die($db->getMessage());
}
$db->disconnect();
unset($db);
unset($sql_insert_log);
unset($sql_install_log);
echo('<br />'.TAB_LOGS.' erfolgreich erstellt!</p>');
echo('<h2>Installation erfolgreich</h2>');
echo('<p>Sie k&ouml;nnen sich nun mit dem Benutzer "admin" und dem Passwort "starten1" anmelden!</p>');
?>