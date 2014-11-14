<?php
if ($auth->isAdmin())
{
	echo('<h2>Benutzer</h2>');
	// Benutzer loeschen
	if (ID != 0 && ID != SUPERUSERID)
	{
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if ((int)$session->getSession(SESSION_USERID) === ID)
		{
			echo $form->getMsg('Sie k&ouml;nnen sich nicht selbst l&ouml;schen!');
		}
		else
		{
			$search_user_query = $db->query('SELECT COUNT(id) as num_users FROM '.TAB_USERS.' WHERE id = '.$db->quote(ID, 'integer').'');
			$search_user = $search_user_query->fetchRow();
			$search_user_query->free();
			if ((int)$search_user['num_users'] === 1)
			{
				$db->query('UPDATE '.TAB_MEDIAS.' SET user_id = '.$db->quote($session->getSession(SESSION_USERID), 'integer').' WHERE user_id = '.$db->quote(ID, 'integer').'');
				$db->query('DELETE FROM '.TAB_USERS.' WHERE id = '.$db->quote(ID, 'integer').'');
				echo $form->getMsg('Der Benutzer wurde erfolgreich gel&ouml;scht! Evt. vorhandene Medien des Benutzers wurden an Sie &uuml;bertragen!');
				setLog('action', 'Benutzer ID: '.ID.' gel&ouml;scht');
			}
			else
			{
				echo $form->getMsg('Es wurde kein Benutzer zum l&ouml;schen gefunden!');
			}
		}
		unset($form);
	}
	$query = 'SELECT id, user_login, user_group, check_terms_of_use, time_register FROM '.TAB_USERS.' WHERE id != '.SUPERUSERID.' ORDER BY '.ORDER.' '.SORT.'';
	require_once './_core/libs/Pager_Wrapper.php';
	$pager = Pager_Wrapper_MDB2($db, $query, $pagerOptions);
	$div_pager = '<div class="pager">Seiten: '.$pager['links'].'</div>';
	echo $div_pager;
	echo('<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>ID <a href="index.php?module=secure_user_list&amp;order=id&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_user_list&amp;order=id&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Benutzer <a href="index.php?module=secure_user_list&amp;order=user_login&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_user_list&amp;order=user_login&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Gruppe <a href="index.php?module=secure_user_list&amp;order=user_group&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_user_list&amp;order=user_group&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Registriert am <a href="index.php?module=secure_user_list&amp;order=time_register&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_user_list&amp;order=time_register&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Nutzungsbedingungen <a href="index.php?module=secure_user_list&amp;order=check_terms_of_use&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_user_list&amp;order=check_terms_of_use&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th colspan="2">Aktionen</th>
	</tr>');
	$x = 1;
	while (list($key, $val) = each($pager['data']))
	{
		$x++;
		echo('<tr class="'.changeCss($x, 'a', 'b').'">
		<td>'.$pager['data'][$key]['id'].'</td>
		<td>'.$pager['data'][$key]['user_login'].'</td>
		<td>'.$user_group[$pager['data'][$key]['user_group']].'</td>
		<td>'.convert_time($pager['data'][$key]['time_register'], 'FULL').'</td>
		<td class="center">'.$arr_confirm[$pager['data'][$key]['check_terms_of_use']].'</td>
		<td>');
		if ((int)$arr_user['id'] === (int)SUPERUSERID || (int)$pager['data'][$key]['user_group'] === (int)1)
		{
			echo('<a href="index.php?module=secure_user_edit&amp;id='.$pager['data'][$key]['id'].'" class="secure">Bearbeiten</a>');
		}
		echo('</td>
		<td>');
		if ((int)$arr_user['id'] === (int)SUPERUSERID || (int)$pager['data'][$key]['user_group'] === (int)1)
		{
			echo('<a href="index.php?module=secure_user_del&amp;id='.$pager['data'][$key]['id'].'" class="secure">L&ouml;schen</a>');
		}
		echo('</td>
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