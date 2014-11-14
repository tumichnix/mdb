<?php
$search_media = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_MEDIAS.' WHERE media_access = 1 AND id = '.$db->quote(ID, 'integer').'');
if ((int)$search_media === 1)
{
	$media_query = $db->query('SELECT cat_id, media_name, media_desc, media_tags, media_dir, media_file, media_type, static_dl_web, static_dl_print, (static_dl_web + static_dl_print) as sum_dls, time_create, cat_name, user_login FROM '.TAB_MEDIAS.' INNER JOIN '.TAB_CATS.' ON '.TAB_CATS.'.id = '.TAB_MEDIAS.'.cat_id INNER JOIN '.TAB_USERS.' ON '.TAB_USERS.'.id = '.TAB_MEDIAS.'.user_id WHERE '.TAB_MEDIAS.'.id = '.$db->quote(ID, 'integer').'');
	if (PEAR::isError($media_query)) {
		die($media_query->getMessage());
	}
	$media = $media_query->fetchRow();
	$media_query->free();
	$media_type = explode('/', $media['media_type']);
	$path_dl = PATH_ABS.'/files/'.$media['media_dir'];
	require_once './_core/libs/nestedset.class.php';
	$ns = new NestedSet($db, $nsOptions);
	echo('<span class="small">'.$ns->getBreadcrumb($media['cat_id'], false, 'index.php?module=index&amp;id=').'</span>');
	echo('<h2>&raquo; '.$media['media_name'].'</h2>');
	echo('<div class="two_cols_left">
	<h3>&Uuml;bersicht</h3>
	<ul>
		<li><div class="wrapper">Media</div>'.$media['media_name'].'&nbsp;</li>
		<li><div class="wrapper">Beschreibung</div>'.nl2br($media['media_desc']).'&nbsp;</li>
		<li><div class="wrapper">Schlagw&ouml;rter</div>'.$media['media_tags'].'&nbsp;</li>
		<li><div class="wrapper">Erstellt</div>'.convert_time($media['time_create'], 'FULL').'</li>
	</ul>
	<h3>Details</h3>
	<ul>
		<li><div class="wrapper">Dateityp</div>'.$media_type[1].'&nbsp;</li>
		<li><div class="wrapper">Dateigr&ouml;&szlig;e</div>&asymp; '.convert_byte(filesize($path_dl.'/'.$media['media_file'])).'</li>');
		if ($media_type[0] == 'image')
		{
			$info = getimagesize($path_dl.'/'.$media['media_file']);
			echo('<li><div class="wrapper">Bildgr&ouml;&szlig;e</div>'.$info[0].'x'.$info[1].'px</li>');
			echo('<li><div class="wrapper">Downloads-Print</div>'.convert_number($media['static_dl_print']).'</li>');
			echo('<li><div class="wrapper">Downloads-Web</div>'.convert_number($media['static_dl_web']).'</li>');
			unset($info);
		}
		elseif($media_type[0] == 'audio')
		{
			require_once './_core/libs/getid3/getid3.php';
			$mp3 = new getID3;
			$mp3info = $mp3->analyze($path_dl.'/'.$media['media_file']);
			echo('<li><div class="wrapper">Format</div>'.$mp3info['audio']['dataformat'].'&nbsp;</li>');
			if (isset($mp3info['id3v2']['comments']['artist'][0])) {
				echo('<li><div class="wrapper">K&uuml;nstler</div>'.htmlentities($mp3info['id3v2']['comments']['artist'][0], ENT_QUOTES, 'UTF-8').'</li>');
			}
			if (isset($mp3info['id3v2']['comments']['title'][0])) {
				echo('<li><div class="wrapper">Titel</div>'.htmlentities($mp3info['id3v2']['comments']['title'][0], ENT_QUOTES, 'UTF-8').'</li>');
			}
			if (isset($mp3info['id3v2']['comments']['album'][0])) {
				echo('<li><div class="wrapper">Album</div>'.htmlentities($mp3info['id3v2']['comments']['album'][0], ENT_QUOTES, 'UTF-8').'</li>');
			}
			echo('<li><div class="wrapper">L&auml;nge</div>'.$mp3info['playtime_string'].'&nbsp;</li>');
			echo('<li><div class="wrapper">Downloads</div>'.convert_number($media['sum_dls']).'</li>');
			unset($mp3);
		}
		elseif($media_type[0] == 'video')
		{
			require_once './_core/libs/getid3/getid3.php';
			$movie = new getID3;
			$movieinfo = $movie->analyze($path_dl.'/'.$media['media_file']);
			echo('<li><div class="wrapper">Datei</div>'.$media['media_file'].'</li>');
			echo('<li><div class="wrapper">Format</div>'.$movieinfo['video']['dataformat'].'</li>');
			echo('<li><div class="wrapper">L&auml;nge</div>'.$movieinfo['playtime_string'].'</li>');
			echo('<li><div class="wrapper">Downloads</div>'.convert_number($media['sum_dls']).'</li>');
			unset($movie);
		}
	echo('</ul>');
	if ($auth->isAdmin())
	{
		$exp_media_admin = explode('.', $media['media_file']);
		echo('<h3>Administration</h3>
		<ul>');
		if ($media_type[0] == 'video')
		{
			echo('<li><a href="download.php?&amp;id='.ID.'&amp;action=stream">FLV-Datei downloaden</a></li>');
		}
		echo('<li><a href="index.php?module=secure_media_edit&amp;id='.ID.'">Medium bearbeiten</a></li>
			<li><a href="index.php?module=secure_media_del&amp;id='.ID.'">Medium l&ouml;schen</a></li>
		</ul>');
	}
	echo('</div>
	<div class="two_cols_right">'.getMediaPreview(ID, $media['media_dir'], $media['media_name'], $media['media_file'], $path_dl, true));
	if (isset($_GET['src']) && isset($_GET['mod']) && isset($_GET['dir']) && isset($_GET['cat'])) {
		require_once './_core/libs/form.class.php';
		$form = new Form('post');
		echo('<div class="center"><br /><br /><a href="index.php?module=search&amp;src='.urlencode($form->quote($_GET['src'])).'&amp;mod='.urlencode($form->quote($_GET['mod'])).'&amp;dir='.(int)$_GET['dir'].'&amp;cat='.(int)$_GET['cat'].'">&raquo; zur&uuml;ck zur Suche</a></div>');
		unset($form);
	}
	echo('</div>
	<div class="clear"></div>');
}
else
{
	echo $msg_err['406'];
}
?>