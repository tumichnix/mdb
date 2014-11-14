<?php
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
	$search_media = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote(ID, 'integer').' AND media_access = 1');
	if ((int)$search_media === 1)
	{
		$query = $db->query('SELECT media_dir, media_file, media_name FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote(ID, 'integer').' AND media_access = 1');
		$row = $query->fetchRow(MDB2_FETCHMODE_ORDERED);
		$query->free();

		$prefix = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('THUMB_PREFIX', 'text').')'));

		if (ACTION == 'stream')
		{
			$exp = explode('.', $row[1]);
			$media_file = $exp[0].'.flv';
			unset($exp);
			$count_dl = false;
		}
		else
		{
			if (SHOW == 0) {
				$media_file = $row[1];
				$up_static = 'static_dl_print = static_dl_print + 1';
			} else {
				$media_file = $prefix[2].substr(strstr($row[1], '_'), 1);
				$up_static = 'static_dl_web = static_dl_web + 1';
			}
			$count_dl = true;
		}
		if (file_exists(PATH_ABS.'/files/'.$row[0].'/'.$media_file))
		{
			if ((bool)$count_dl)
			{
				$db->query('UPDATE '.TAB_MEDIAS.' SET '.$up_static.' WHERE id = '.$db->quote(ID, 'integer').'');
				$dl_user = $db->extended->getOne('SELECT user_login FROM '.TAB_USERS.' WHERE id = '.$db->quote($session->getSession(SESSION_USERID), 'integer').'');
				setLog('action', 'Benutzer "'.$dl_user.'" hat Medium "'.$row[2].'" runtergeladen');
			}
			header('Expires: 0');
			header('Pragma: no-cache');
			//header('Content-Type: application/octet-stream');
			header('Content-Length: '.(string)filesize(PATH_ABS.'/files/'.$row[0].'/'.$media_file));
			header('Content-Disposition: attachment; filename='.$media_file.'');
			header('Content-Transfer-Encoding: binary');
			readfile(PATH_ABS.'/files/'.$row[0].'/'.$media_file);
		}
		else
		{
			echo('Error - file not found '.$media_file.'');
		}
	}
	else
	{
		echo('Error - wrong ID');
	}
}
$db->disconnect();
unset($auth);
unset($session);
unset($db);
?>