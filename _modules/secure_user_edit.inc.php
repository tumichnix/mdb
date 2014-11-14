<?php
if ($auth->isAdmin())
{
	$where = ((int)SUPERUSERID === (int)$arr_user['id']) ? '' : ' AND user_group = 1';
	$search_user = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_USERS.' WHERE id = '.$db->quote(ID, 'integer').' AND id != '.$db->quote(SUPERUSERID, 'integer').' '.$where.'');
	if ((int)$search_user === 1)
	{
		echo('<h2>Benutzer bearbeiten</h2>');
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if (isset($_POST['send_edit']))
		{
			$form->addRule('user_login', 'Bitte einen Benutzer angeben!', 'required');
			$form->addRule('user_login', $msg['guide_user'], 'regex', $regex['user_login']);
			$form->addRule('user_login', $msg['user_length_min'], 'minlength', $cfg['login']['min']);
			$form->addRule('user_login', $msg['user_length_max'], 'maxlength', $cfg['login']['max']);
			$pwd_a = $form->getSubmitVar('user_pwd_a');
			$pwd_b = $form->getSubmitVar('user_pwd_b');
			if (!empty($pwd_a))
			{
				$form->addRule('user_pwd_a', $msg['pwd_length_min'], 'minlength', $cfg['pwd']['min']);
				$form->addRule('user_pwd_a', $msg['pwd_length_max'], 'maxlength', $cfg['pwd']['max']);
				$form->addRule('user_pwd_a', $msg['guide_pwd'], 'regex', $regex['user_pwd']);
				$form->addRule('user_pwd_a', 'Das Passwort stimmt nicht mit der Wiederholung &uuml;berein!', 'compare', $form->getSubmitVar('user_pwd_b'));
			}
			if ($form->validate())
			{
				$update_user = $db->query('UPDATE '.TAB_USERS.' SET user_login = '.$db->quote($form->getSubmitVar('user_login'), 'text').', user_group = '.$db->quote($form->getSubmitVar('user_group'), 'integer').' WHERE id = '.$db->quote(ID, 'integer').'');
				if (PEAR::isError($update_user))
				{
					echo $form->setError('Dieser Benutzer existiert bereits!', true);
				}
				else
				{
					echo $form->getMsg('Der Benutzer wurde erfolgreich editiert!');
					if (!empty($pwd_a))
					{
						$val_pwd = hash('sha256', $auth->getSaltHash().$pwd_a);
						$update_pwd = $db->query('UPDATE '.TAB_USERS.' SET user_pwd = '.$db->quote($val_pwd, 'text').' WHERE id = '.$db->quote(ID, 'integer').'');
						unset($val_pwd);
						echo $form->getMsg('Das Passwort wurde erfolgreich neu gesetzt!');
					}
				}
			}
			else
			{
				echo $form->getErrors();
			}
			unset($pwd_a);
			unset($pwd_b);
		}
		$edit_user_query = $db->query('SELECT user_login, user_group FROM '.TAB_USERS.' WHERE id = '.$db->quote(ID, 'integer').' AND id != '.$db->quote(SUPERUSERID, 'integer').'');
		$edit_user = $edit_user_query->fetchRow();
		$edit_user_query->free();
		if ((int)SUPERUSERID === (int)$arr_user['id'])
		{
			$access_groups = $user_group;
		}
		else
		{
			$access_groups[1] = $user_group[1];
		}
		echo('<form method="post" action="index.php?module=secure_user_edit&amp;id='.ID.'">
		<fieldset>
			<legend>Benutzer</legend>
			<ul>
				<li>'.$form->addElement('text', 'user_login', 'Benutzer', $edit_user['user_login']).'</li>
				<li>'.$form->addElement('select', 'user_group', 'Gruppe', $edit_user['user_group'], $access_groups).'</li>
				<li>'.$form->getMsg('Passwortfelder freilassen wenn kein neues Passwort gesetzt werden soll!').'</li>
				<li>'.$form->addElement('password', 'user_pwd_a', 'Neues Passwort').'</li>
				<li>'.$form->addElement('password', 'user_pwd_b', 'Neues Passwort (W.)').'</li>
				<li>'.$form->addElement('submit', 'send_edit', 'Speichern').'</li>
			</ul>
		</fieldset>
		</form>
		</div>');
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