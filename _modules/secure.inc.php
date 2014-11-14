<?php
if ($auth->isAdmin())
{
	echo('<h2>Verwaltung</h2>');
	echo('<div class="two_cols_left">
	<h3>Benutzer</h3>
	<ul>
		<li><a href="index.php?module=secure_user_list">Alle Benutzer auflisten</a></li>
		<li><a href="index.php?module=secure_user_add">Neuen Benutzer erstellen</a></li>');
	if ((int)$arr_user['id'] === (int)SUPERUSERID)
	{
		echo('<li><a href="index.php?module=secure_user_batch">Mehrere Benutzer per csv-Datei anlegen</a></li>');
	}
	echo('</ul>
	<h3>Ordner</h3>
	<ul>
		<li><a href="index.php?module=secure_cat_list">Ordnerstruktur verwalten</a></li>
		<li><a href="index.php?module=secure_cat_add">Neuen Ordner erstellen</a></li>
	</ul>
	</div>
	<div class="two_cols_right">
	<h3>Medien</h3>
	<ul>
		<li><a href="index.php?module=secure_media_list&amp;show=1">Freigeschaltene Medien auflisten</a></li>
		<li><a href="index.php?module=secure_media_list&amp;show=0">Nicht freigeschaltene Medien auflisten</a></li>
		<li><a href="index.php?module=secure_media_add">Neues Medium erstellen</a></li>
		<li><a href="index.php?module=secure_static&amp;order=dls&amp;sort=DESC">Statistik</a></li>
	</ul>
	<h3>Sonstiges</h3>
	<ul>
		<li><a href="index.php?module=secure_setup">Einstellungen</a></li>');
	if (SUPERUSERID == $arr_user['id']) {
		echo('<li><a href="index.php?module=secure_terms_of_use">Nutzungsbedingungen</a></li>
		<li><a href="index.php?module=secure_ext">Dateinamenerweiterungsverwaltung</a></li>');
	}
	echo('<li><a href="index.php?module=secure_log&amp;order=log_time&amp;sort=DESC">Log-Eintr&auml;ge</a></li>
	</ul>
	</div>
	<div class="clear"></div>');
}
else
{
	echo $msg_err['401'];
}
?>