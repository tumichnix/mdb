<?php
if ($auth->isAdmin())
{
	echo('<h2>Einstellungen</h2>');
	echo('<div id="form">');
	require_once './_core/libs/form.class.php';
	$form = new Form;
	if (isset($_POST['send_setup']))
	{
		$form->addRule('email_order', 'Bitte eine Email f&uuml;r Bestellungen angeben!', 'required');
		$form->addRule('email_contact', 'Bitte mind. eine Email als Anfragem&ouml;glichkeit angeben!', 'required');
		if (SUPERUSERID == $arr_user['id']) {
			$form->addRule('thumb_width', 'Bitte eine Thumbnailbreite angeben!', 'required');
			$form->addRule('thumb_width', 'Die Thumbnailbreite muss numerisch sein!', 'numeric');
			$form->addRule('thumb_height', 'Bitte eine Thumbnailh&ouml;he angeben!', 'required');
			$form->addRule('thumb_height', 'Die Thumbnailh&ouml;he muss numerisch sein!', 'numeric');
		}
		if ($form->validate())
		{
			$db->query('UPDATE '.TAB_DATA.' SET data_string = '.$db->quote($form->getSubmitVar('email_order'), 'text').' WHERE data_name = '.$db->quote('EMAIL_ORDER', 'text').'');
			$db->query('UPDATE '.TAB_DATA.' SET data_string = '.$db->quote($form->getSubmitVar('email_contact'), 'text').' WHERE data_name = '.$db->quote('EMAIL_CONTACT', 'text').'');
			if (SUPERUSERID == $arr_user['id']) {
				$db->query('UPDATE '.TAB_DATA.' SET data_integer = '.$db->quote($form->getSubmitVar('thumb_width'), 'integer').' WHERE data_name = '.$db->quote('THUMB_WIDTH', 'text').'');
				$db->query('UPDATE '.TAB_DATA.' SET data_integer = '.$db->quote($form->getSubmitVar('thumb_height'), 'integer').' WHERE data_name = '.$db->quote('THUMB_HEIGHT', 'text').'');
			}
			echo $form->getMsg('Die Einstellungen wurden erfolgreich gespeichert!');
		}
		else
		{
			echo $form->getErrors();
		}
	}
	/**
	 * Hole die Einstellungen aus der Datenbank
	 */
	$arr_setup = array();
	$setup_query = 'SELECT data_name, data_integer, data_string FROM '.TAB_DATA;
	$setup_result = $db->query($setup_query);
	while ($row = $setup_result->fetchRow())
	{
		$arr_setup[$row['data_name']]['int'] = (int)$row['data_integer'];
		$arr_setup[$row['data_name']]['str'] = (string)$row['data_string'];
	}
	$setup_result->free();
	echo('<form method="post" action="index.php?module=secure_setup">
	<fieldset>
		<legend>Einstellungen</legend>
		<ul>
			<li>'.$form->addElement('text', 'email_order', 'Email-Bestellung', $arr_setup['EMAIL_ORDER']['str']).'</li>
			<li>'.$form->addElement('textarea', 'email_contact', 'Emails-Anfrage', $arr_setup['EMAIL_CONTACT']['str'], array('cols' => 50, 'rows' => 8)).' <span class="small">(Eine Email pro Zeile)</span></li>');
			if (SUPERUSERID == $arr_user['id']) {
				echo('<li>'.$form->addElement('text', 'thumb_width', 'Thumbnailbreite', $arr_setup['THUMB_WIDTH']['int']).' (in Pixeln)</li>
				<li>'.$form->addElement('text', 'thumb_height', 'Thumbnailh&ouml;he', $arr_setup['THUMB_HEIGHT']['int']).' (in Pixeln)</li>');
			}
	echo('	<li>'.$form->addElement('submit', 'send_setup', 'Speichern').'</li>
		</ul>
	</fieldset>
	</form>');
	unset($form);
	echo('</div>');
	unset($arr_setup);
}
else
{
	echo $msg_err['401'];
}
?>