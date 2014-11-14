<?php
if ($auth->isAdmin())
{
	$pagerOptions['excludeVars'] = array('id');
	echo('<h2>Medien</h2>');
	if (SHOW == 0)
	{
		echo('<h3>nicht freigeschaltene</h3>');
	}
	else
	{
		echo('<h3>freigeschaltene</h3>');
	}
	$query = 'SELECT '.TAB_MEDIAS.'.id, media_name, time_create, user_login, cat_name FROM '.TAB_MEDIAS.' INNER JOIN '.TAB_USERS.' ON '.TAB_USERS.'.id = '.TAB_MEDIAS.'.user_id LEFT JOIN '.TAB_CATS.' ON '.TAB_CATS.'.id = '.TAB_MEDIAS.'.cat_id WHERE media_access = '.$db->quote(SHOW, 'integer').' ORDER BY '.ORDER.' '.SORT.'';
	require_once './_core/libs/Pager_Wrapper.php';
	$pager = Pager_Wrapper_MDB2($db, $query, $pagerOptions);
	$div_pager = '<div class="pager">Seiten: '.$pager['links'].'</div>';
	echo $div_pager;
	echo('<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>ID <a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=id&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=id&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Medium <a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=media_name&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=media_name&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Ordner</th>
		<th>Ersteller <a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=user_login&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=user_login&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Erstellt am <a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=time_create&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_media_list&amp;show='.SHOW.'&amp;order=time_create&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th colspan="2">Aktionen</th>
	</tr>');
	$x = 1;
	while (list($key, $val) = each($pager['data']))
	{
		$x++;
		$cat_name = ($pager['data'][$key]['cat_name'] == NULL) ? 'nicht einsortiert' : $pager['data'][$key]['cat_name'];
		echo('<tr class="'.changeCss($x, 'a', 'b').'">
		<td>'.$pager['data'][$key]['id'].'</td>
		<td><a href="index.php?module=media&amp;id='.$pager['data'][$key]['id'].'">'.$pager['data'][$key]['media_name'].'</a></td>
		<td>'.$cat_name.'</td>
		<td>'.$pager['data'][$key]['user_login'].'</td>
		<td>'.convert_time($pager['data'][$key]['time_create'], 'FULL').'</td>
		<td><a href="index.php?module=secure_media_edit&amp;id='.$pager['data'][$key]['id'].'" class="secure">Bearbeiten</a></td>
		<td><a href="index.php?module=secure_media_del&amp;id='.$pager['data'][$key]['id'].'" class="secure">L&ouml;schen</a></td>
		</tr>');
	}
	unset($x);
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