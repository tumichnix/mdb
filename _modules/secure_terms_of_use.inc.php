<?php
if ($auth->isAdmin() && $arr_user['id'] == SUPERUSERID) {
	echo('<h2>Nutzungsbedingungen</h2>');
	echo('<div id="form">');
	require_once './_core/libs/form.class.php';
	$form = new Form;
	if (isset($_POST['send_terms'])) {
		$form->addRule('terms_of_use', 'Keine Nutzerbedingungen gesetzt!', 'required');
		if ($form->validate()) {
			$db->query('UPDATE '.TAB_DATA.' SET data_string = '.$db->quote($_POST['terms_of_use'], 'text').' WHERE data_name = '.$db->quote('TERMS_OF_USE', 'text').'');
			$db->query('UPDATE '.TAB_USERS.' SET check_terms_of_use = 0 WHERE user_group = 1');
			echo $form->getMsg('Die Nutzungsbedingungen wurden erfolgreich aktualisiert!');
			setLog('action', 'Nutzungsbedingungen aktualisiert');
		} else {
			echo $form->getErrors();
		}
		$terms = $_POST['terms_of_use'];
	} else {
		$terms = $db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE data_name = '.$db->quote('TERMS_OF_USE', 'text').'');
	}
	echo('<form method="post" action="index.php?module=secure_terms_of_use">
	<fieldset>
		<legend>Nutzungsbedingungen</legend>
		<ul>
			<li><label for="terms_of_use">Nutzungsbedingungen</label><textarea name="terms_of_use" id="terms_of_use" cols="80" rows="15">'.$terms.'</textarea></li>
			<li class="center">'.$form->addElement('submit', 'send_terms', 'Speichern').'</li>
		</ul>
	</fieldset>
	</form>
	</div>');
	unset($form);
	unset($terms);
} else {
	echo $msg_err['401'];
}
?>