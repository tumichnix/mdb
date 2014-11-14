<?php
if ($auth->isAdmin() && $arr_user['id'] == SUPERUSERID) {
	$actions = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('ACTIONS', 'text').')'));
	echo('<h2>Dateinamenerweiterungsverwaltung</h2>');
	echo('<div id="form">');
	require_once './_core/libs/form.class.php';
	$form = new Form;
	if (isset($_POST['send_add'])) {
		$form->addRule('new_ext', 'Bitte eine Dateiendung angeben!', 'required');
		if (!preg_match($regex['file_ext'], $form->getSubmitVar('new_ext'))) {
			$form->setError('Ung&uuml;tiges Format der Dateinamenserweiterung!');
		}
		if ($form->validate()) {
			$ext = $form->getSubmitVar('new_ext');
			$whitelist = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('WHITELIST_EXT', 'text').')'));
			if (!@array_key_exists($ext, $whitelist)) {
				$whitelist[$ext] = (int)$form->getSubmitVar('new_action');
				$whitelist_ser = serialize($whitelist);
				$update = $db->query('UPDATE '.TAB_DATA.' SET data_string = '.$db->quote($whitelist_ser, 'text').' WHERE (data_name = '.$db->quote('WHITELIST_EXT', 'text').')');
				if (PEAR::isError($update)) {
					die($update->getMessage());
				}
				echo $form->getMsg('Dateinamenerweiterung wurde erfolgreich eingetragen!');
				setLog('action', 'Neue Dateinamenerweiterung "'.$ext.'" angelegt');
			} else {
				echo $form->setError('Dateinamenerweiterung existiert bereits!', true);
			}
			unset($whitelist);
		} else {
			echo $form->getErrors();
		}
	}
	echo('<form method="post" action="index.php?module=secure_ext">
	<fieldset>
	<legend>Neue Dateinamenerweiterung</legend>
	<ul>
		<li>'.$form->addElement('text', 'new_ext', 'Dateinamenerweiterung', '').'</li>
		<li>'.$form->addElement('select', 'new_action', 'Aktion beim anlegen', 0, $actions).'</li>
		<li>'.$form->addElement('submit', 'send_add', 'Eintragen').'</li>
	</ul>
	</fieldset>
	</form>
	'.$form->getMsg('Nur die hier eingetragenen Dateinamenerweiterungen sind beim erstellen neuer Medien erlaubt!').'
	</div>');
	unset($form);
	echo('<h3>Eingetragene Dateinamenerweiterungen</h3>');
	$whitelist = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('WHITELIST_EXT', 'text').')'));
	echo('<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>Dateinamenerweiterung</th>
		<th>Aktion beim anlegen</th>
		<th>L&ouml;schen</th>
	</tr>');
	if ($whitelist != false) {
		$x = 1;
		ksort($whitelist);
		while (list($key, $val) = each($whitelist)) {
			$x++;
			echo('<tr class="'.changeCss($x, 'a', 'b').'">
				<td>'.$key.'</td>
				<td>'.$actions[$val].'</td>
				<td><a href="index.php?module=secure_ext_del&amp;ext='.$key.'" class="secure">L&ouml;schen</a></td>
			</tr>');
		}
	}
	echo('</table>');
	unset($actions);
	unset($whitelist);
} else {
	echo $msg_err['401'];
}
?>