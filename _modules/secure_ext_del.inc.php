<?php
if ($auth->isAdmin() && $arr_user['id'] == SUPERUSERID) {
	if (isset($_GET['ext']) && !empty($_GET['ext']) && preg_match($regex['file_ext'], $_GET['ext'])) {
		$ext = $_GET['ext'];
	} else {
		$ext = false;
	}
	$whitelist = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('WHITELIST_EXT', 'text').')'));
	if ($ext == false || !@array_key_exists($ext, $whitelist)) {
		echo $msg_err['406'];
	} else {
		echo('<h2>Dateinamenerweiterung l&ouml;schen</h2>');
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if (isset($_POST['send_del'])) {
			$new_whitelist = array();
			while (list($key, $value) = each($whitelist)) {
				if ($key != $ext) {
					$new_whitelist[$key] = $value;
				}
			}
			$new_whitelist_ser = serialize($new_whitelist);
			$update = $db->query('UPDATE '.TAB_DATA.' SET data_string = '.$db->quote($new_whitelist_ser, 'text').' WHERE (data_name = '.$db->quote('WHITELIST_EXT', 'text').')');
			if (PEAR::isError($update)) {
				die($update->getMessage());
			}
			echo $form->getMsg('Dateinamenerweiterung wurde erfolgreich gel&ouml;scht!');
			setLog('action', 'Dateinamenerweiterung "'.$ext.'" gel&ouml;scht');
			unset($new_whitelist);
			unset($new_whitelist_ser);
		} else {
			echo('<form method="post" action="index.php?module=secure_ext_del&amp;ext='.$ext.'">
			<fieldset>
				<legend>Dateinamenerweiterung l&ouml;schen</legend>
				<ul>
					<li>'.$form->getMsg('Soll die Dateinamenerweiterung "'.$ext.'" wirklich gel&ouml;scht werden?').'</li>
					<li class="center">'.$form->addElement('submit', 'send_del', 'Ja, l&ouml;schen').'</li>
				</ul>
			</fieldset>
			</form>');
		}
		echo('</div>');
		unset($form);
	}
	unset($whitelist);
} else {
	echo $msg_err['401'];
}
?>