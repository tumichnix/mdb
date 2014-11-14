<?php
ob_start();
session_start();
require_once './_core/path.inc.php';
require_once './_core/define.inc.php';
require_once './_core/database.inc.php';
require_once './_core/libs/global.func.inc.php';
require_once 'PEAR.php';
require_once 'MDB2.php';

$db =& MDB2::connect($dsn, $dsn_options);
if (PEAR::isError($db))
{
	die('Verbindung zur Datenbank konnte nicht hergstellt werden!');
}
$db->loadModule('Extended');

require_once './_core/libs/session.class.php';
require_once './_core/libs/auth.class.php';

$session = new Session;
$auth = new Auth($db, $session);
if ((bool)$auth->confAuth() === true)
{
	$log_file = PATH_ABS.'/files/log.txt';
	$logs = '';
	$query_logs = $db->query('SELECT '.TAB_LOGS.'.id, log_time, log_type, log_message, user_login FROM '.TAB_LOGS.' LEFT JOIN '.TAB_USERS.' ON '.TAB_USERS.'.id = '.TAB_LOGS.'.log_uid ORDER BY log_time DESC');
	while ($row = $query_logs->fetchRow(MDB2_FETCHMODE_ASSOC))
	{
		$logs .= convert_time($row['log_time'], 'FULL').' - '.$row['user_login'].' >> '.$row['log_message']."\n";
	}
	$query_logs->free();
	unset($query_logs);
	$fp = fopen($log_file, 'w');
	fwrite($fp, $logs);
	fclose($fp);
	header('Expires: 0');
	header('Pragma: no-cache');
	header('Content-Type: application/octet-stream');
	header('Content-Length: '.(string)filesize($log_file));
	header('Content-Disposition: attachment; filename=log.txt');
	header('Content-Transfer-Encoding: binary');
	readfile($log_file);
	unlink($log_file);
}
else
{
	echo('Unauthorized!');
}
$db->disconnect();
unset($auth);
unset($session);
unset($db);
?>