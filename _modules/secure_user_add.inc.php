<?php
if ($auth->isAdmin())
{
	echo('<h2>Neuen Benutzer erstellen</h2>');
	echo('<div id="form">');
	require_once './_core/libs/form.class.php';
	$form = new Form;
	if (isset($_POST['send_add']))
	{
		$form->addRule('user_login', 'Bitte eine Benutzer angeben!', 'required');
		$form->addRule('user_login', $msg['guide_user'], 'regex', $regex['user_login']);
		$form->addRule('user_login', $msg['user_length_min'], 'minlength', $cfg['login']['min']);
		$form->addRule('user_login', $msg['user_length_max'], 'maxlength', $cfg['login']['max']);
		$form->addRule('user_pwd_a', $msg['pwd_length_min'], 'minlength', $cfg['pwd']['min']);
		$form->addRule('user_pwd_a', $msg['pwd_length_max'], 'maxlength', $cfg['pwd']['max']);
		$form->addRule('user_pwd_a', $msg['guide_pwd'], 'regex', $regex['user_pwd']);
		$form->addRule('user_pwd_a', 'Das Passwort stimmt nicht mit der Wiederholung &uuml;berein!', 'compare', $form->getSubmitVar('user_pwd_b'));
		if ($form->validate())
		{
			$val_pwd = hash('sha256', $auth->getSaltHash().$form->getSubmitVar('user_pwd_a'));
			$add_user = $db->query('INSERT INTO '.TAB_USERS.' (user_login, user_pwd, user_group, time_register) VALUES ('.$db->quote($form->getSubmitVar('user_login'), 'text').', '.$db->quote($val_pwd, 'text').', '.$db->quote($form->getSubmitVar('user_group'), 'integer').', '.$db->quote(time(), 'integer').')');
			unset($val_pwd);
			if (PEAR::isError($add_user))
			{
				echo $form->setError('Dieser Benutzer existiert bereits!', true);
			}
			else
			{
				echo $form->getMsg('Der Benutzer wurde erfolgreich erstellt!');
				setLog('action', 'Benutzer "'.$form->getSubmitVar('user_login').'" (ID: '.$db->lastInsertID(TAB_USERS, 'id').') angelegt');
			}
		}
		else
		{
			echo $form->getErrors();
		}
	}
	if ((int)SUPERUSERID === (int)$arr_user['id'])
	{
		$access_groups = $user_group;
	}
	else
	{
		$access_groups[1] = $user_group[1];
	}
	echo('<form method="post" action="index.php?module=secure_user_add">
	<fieldset>
	<legend>Neuer Benutzer</legend>
	<ul>
		<li>'.$form->addElement('text', 'user_login', 'Benutzer').'</li>
		<li>'.$form->addElement('password', 'user_pwd_a', 'Passwort').'</li>
		<li>'.$form->addElement('password', 'user_pwd_b', 'Passwort (W.)').'</li>
		<li>'.$form->addElement('select', 'user_group', 'Gruppe', 1, $access_groups).'</li>
		<li>'.$form->addElement('submit', 'send_add', 'Anlegen').'</li>
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
?>