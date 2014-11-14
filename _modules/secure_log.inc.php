<?php
if ($auth->isAdmin())
{
	$query = 'SELECT '.TAB_LOGS.'.id, log_time, log_type, log_message, user_login FROM '.TAB_LOGS.' LEFT JOIN '.TAB_USERS.' ON '.TAB_USERS.'.id = '.TAB_LOGS.'.log_uid WHERE (log_time > '.$db->quote((time()-(60*60*24*7)), 'integer').') ORDER BY '.ORDER.' '.SORT.'';
	echo('<h2>Log-Eintr&auml;ge</h2>');
	require_once './_core/libs/Pager_Wrapper.php';
	$pager = Pager_Wrapper_MDB2($db, $query, $pagerOptions);
	require_once './_core/libs/form.class.php';
	$div_pager = '<div class="pager"><a href="getlogs.php">Logs downloaden</a> &bull; Seiten: '.$pager['links'].'</div>';
	echo $div_pager;
	echo('<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>ID <a href="index.php?module=secure_log&amp;order=id&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_log&amp;order=id&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Zeitpunkt <a href="index.php?module=secure_log&amp;order=log_time&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_log&amp;order=log_time&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Type</th>
		<th>Benutzer</th>
		<th>Mitteilung</th>
	</tr>');
	$x = 1;
	while (list($key, $val) = each($pager['data']))
	{
		$x++;
		echo('<tr class="'.changeCss($x, 'a', 'b').'">
		<td>'.convert_number($pager['data'][$key]['id'], 5).'</td>
		<td>'.convert_time($pager['data'][$key]['log_time'], 'FULL').'</td>
		<td>'.$pager['data'][$key]['log_type'].'</td>
		<td>'.$pager['data'][$key]['user_login'].'</td>
		<td>'.$pager['data'][$key]['log_message'].'</td>
		</tr>');
	}
	echo('</table>');
	echo $div_pager;
	unset($div_pager);
	unset($pager);
}
else
{
	echo $msg_err['401'];
}
?>