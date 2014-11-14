<?php
if ($auth->isAdmin() && (int)$arr_user['id'] === (int)SUPERUSERID)
{
	echo('<h2>Mehrere Benutzer per csv-Datei anlegen</h2>');
	echo('<div id="form">');
	require_once './_core/libs/form.class.php';
	$form = new Form;
	if (isset($_POST['send_batch']))
	{
		$form->addRule('pwd_a', $msg['pwd_length_min'], 'minlength', $cfg['pwd']['min']);
		$form->addRule('pwd_a', $msg['pwd_length_max'], 'maxlength', $cfg['pwd']['max']);
		$form->addRule('pwd_a', $msg['guide_pwd'], 'regex', $regex['user_pwd']);
		$form->addRule('pwd_a', 'Das Passwort stimmt nicht mit der Wiederholung &uuml;berein!', 'compare', $form->getSubmitVar('pwd_b'));
		if (empty($_FILES['csv']['tmp_name']))
		{
			$form->setError('Bitte eine csv-Datei ausw&auml;hlen!');
		}
		if ($form->validate())
		{
			$file_tmp_name = $_FILES['csv']['tmp_name'];
        	$file_name = $_FILES['csv']['name'];
        	$file_type = $_FILES['csv']['type'];
        	if ($file_type == 'text/comma-separated-values' || $file_type == 'text/csv')
        	{
				if (!@move_uploaded_file($file_tmp_name, 'files/'.$file_name))
        		{
        			echo $form->setError('Datei konnte nicht in das Zielverzeichnis kopiert werden!', true);
        		}
        		else
        		{
					$csv = file('files/'.$file_name);
					unlink('files/'.$file_name);
					$failed = array();
					$x = 0;
					foreach ($csv as $row)
					{
						$x++;
						$exp = explode(';', $row);
						if ((int)$exp[1] === 1 || (int)$exp[1] === 2)
						{
							if (!preg_match($regex['user_login'], $exp[0]) || strlen($exp[0]) < (int)$cfg['login']['min'] || strlen($exp[0]) > (int)$cfg['login']['max'])
							{
								$failed[] = 'Zeile '.$x.': ung&uuml;tiger Benutzername!';
							}
							else
							{
								$login = $form->quote($exp[0]);
								$search_user = $db->extended->getOne("SELECT COUNT(id) FROM ".TAB_USERS." WHERE MD5(user_login) = '".md5($db->quote($login, 'text', false))."'");
								if (!empty($search_user))
								{
									$failed[] = 'Zeile '.$x.': Benutzername "'.$login.'" bereits vorhanden!';
								}
								unset($login);
							}
						}
						else
						{
							$failed[] = 'Zeile '.$x.': ung&uuml;tige Benutzergruppe!';
						}
						unset($exp);
					}
					if ((int)count($failed) === 0 && (int)count($csv) === (int)$x)
					{
						$val_pwd = hash('sha256', $auth->getSaltHash().$form->getSubmitVar('pwd_a'));
						foreach ($csv as $row)
						{
							$exp = explode(';', $row);
							$login = $form->quote($exp[0]);
							$db->query('INSERT INTO '.TAB_USERS.' (user_login, user_pwd, user_group, time_register) VALUES ('.$db->quote($login, 'text').', '.$db->quote($val_pwd, 'text').', '.$db->quote($exp[1], 'integer').', '.$db->quote(time(), 'integer').')');
							setLog('action', 'Benutzer "'.$login.'" (ID: '.$db->lastInsertID(TAB_USERS, 'id').') angelegt');
							unset($login);
						}
						unset($val_pwd);
						echo $form->getMsg('Erfolgreich '.(int)count($csv).' Benutzer erstellt!');
					}
					else
					{
						$error_msgs = '';
						foreach ($failed as $var)
						{
							$error_msgs .= $var.'<br />';
						}
						echo $form->getMsg('Folgende Fehler traten beim Versuch die Benutzer anzulegen auf:<br /><br />'.$error_msgs);
						unset($error_msgs);
					}
					unset($failed);
					unset($csv);
				}
			}
			else
			{
				echo $form->setError('Datei ist keine csv-Datei!', true);
			}
		}
		else
		{
			echo $form->getErrors();
		}
	}
	echo('<form method="post" action="index.php?module=secure_user_batch" enctype="multipart/form-data">
	<fieldset>
	<legend>Einstellungen</legend>
	<ul>
		<li>'.$form->getMsg('Dateiformat: *.csv<br />Zeilenformat: (string)Benutzername;(int)Benutzergruppe (1 = Benutzer | 2 = Administrator)<br />Alle Benutzer erhalten das angegeben Passwort!').'</li>
		<li>'.$form->addElement('file', 'csv', 'csv-Datei').'</li>
		<li>'.$form->addElement('password', 'pwd_a', 'Stand.-Passwort').'</li>
		<li>'.$form->addElement('password', 'pwd_b', 'Stand.-Passwort (W.)').'</li>
		<li>'.$form->addElement('submit', 'send_batch', 'Anlegen').'</li>
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