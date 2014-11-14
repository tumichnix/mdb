<?php
echo('<div id="form">');
require_once './_core/libs/form.class.php';
$form = new Form;
if (isset($_POST['terms_send'])) {
	$db->query('UPDATE '.TAB_USERS.' SET check_terms_of_use = '.$db->quote('1', 'integer').' WHERE id = '.$db->quote($session->getSession(SESSION_USERID), 'integer').'');
	echo $form->getMsg('Sie haben die Nutzungsbedingungen erfolgreich akzeptiert! Sie k&ouml;nnen diese Seite nun verwenden!');
} else {
	$terms = $db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE data_name = '.$db->quote('TERMS_OF_USE', 'text').'');
	echo('<form method="post" action="index.php">
	<fieldset>
		<ul>
			<li>'.nl2br(text2html($terms)).'</li>
			<li class="center">'.$form->addElement('submit', 'terms_send', 'Akzeptieren').'</li>
		</ul>
	</fieldset>
	</form></div>');
	unset($terms);
}
unset($form);
?>