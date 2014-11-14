<?php
if ($auth->isAdmin())
{
	$where = ((int)SUPERUSERID === (int)$arr_user['id']) ? '' : ' AND user_group = 1';
	$search_user = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_USERS.' WHERE id = '.$db->quote(ID, 'integer').' AND id != '.$db->quote(SUPERUSERID, 'integer').' '.$where.'');
	if ((int)$search_user === 1)
	{
		$del_user = $db->extended->getOne('SELECT user_login FROM '.TAB_USERS.' WHERE id = '.$db->quote(ID, 'integer').'');
		echo('<h2>Benutzer l&ouml;schen</h2>');
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if (isset($_POST['send_del']))
		{
			$db->query('UPDATE '.TAB_MEDIAS.' SET user_id = '.$db->quote($arr_user['id'], 'integer').' WHERE user_id = '.$db->quote(ID, 'integer').'');
			$db->query('DELETE FROM '.TAB_USERS.' WHERE id = '.$db->quote(ID, 'integer').'');
			echo $form->getMsg('Der Benutzer "'.$del_user.'" wurde erfolgreich gel&ouml;scht! Evt. vorhandene Medien des Benutzers wurden an Sie &uuml;bertragen!');
			setLog('action', 'Benutzer "'.$del_user.'" (ID: '.ID.') gel&ouml;scht');
		}
		else
		{
			echo('<form method="post" action="index.php?module=secure_user_del&amp;id='.ID.'">
			<fieldset>
				<legend>Benutzer l&ouml;schen</legend>
				<ul>
					<li>'.$form->getMsg('Soll der Benutzer "'.$del_user.'" wirklich gelöscht werden?').'</li>
					<li class="center">'.$form->addElement('submit', 'send_del', 'Ja, l&ouml;schen').'</li>
				</ul>
			</fieldset>
			</form>
			</div>');
		}
		unset($form);
	}
	else
	{
		echo $msg_err['401'];
	}
}
else
{
	echo $msg_err['401'];
}
?>