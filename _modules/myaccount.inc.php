<?php
$own_medias = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_MEDIAS.' WHERE user_id = '.$db->quote($session->getSession(SESSION_USERID), 'integer').'');
echo('<h2>Mein Account</h2>');
echo('<div class="two_cols_left">
<h3>&Uuml;bersicht</h3>
<ul>
	<li><div class="wrapper">Benutzer</div>'.$arr_user['user_login'].'</li>
	<li><div class="wrapper">Status</div>'.$user_group[$arr_user['user_group']].'</li>
</ul>
<h3>Statistik</h3>
<ul>
	<li><div class="wrapper">Registrierung am</div>'.convert_time($arr_user['time_register'], 'FULL').'</li>
	<li><div class="wrapper">Eigene Medien</div>'.convert_number($own_medias).'</li>
</ul>
</div>');
echo('<div class="two_cols_right">
<div id="form">
<form method="post" action="index.php?module=myaccount">
<fieldset>
<legend>Eigenes Passwort &auml;ndern</legend>');
require_once './_core/libs/form.class.php';
$form = new Form;
if (isset($_POST['send_pwd']))
{
	$form->addRule('new_pwd', $msg['pwd_length_min'], 'minlength', $cfg['pwd']['min']);
	$form->addRule('new_pwd', $msg['pwd_length_max'], 'maxlength', $cfg['pwd']['max']);
	$form->addRule('new_pwd', $msg['guide_pwd'], 'regex', $regex['user_pwd']);
	$form->addRule('new_pwd', 'Das Passwort stimmt nicht mit der Wiederholung &uuml;berein!', 'compare', $form->getSubmitVar('new_confirm_pwd'));
	if ($form->validate())
	{
		$val_pwd = hash('sha256', $auth->getSaltHash().$form->getSubmitVar('new_pwd'));
		$db->query('UPDATE '.TAB_USERS.' SET user_pwd = '.$db->quote($val_pwd, 'text').' WHERE id = '.$db->quote($session->getSession(SESSION_USERID), 'integer').'');
		echo $form->getMsg('Das Passwort wurde erfolgreich ge&auml;ndert.<br />Bitte melden Sie sich neu an!');
	}
	else
	{
		echo $form->getErrors();
	}
}
echo('<ul>
	<li>'.$form->addElement('password', 'new_pwd', 'Neues Passwort').'</li>
	<li>'.$form->addElement('password', 'new_confirm_pwd', 'Neues Passwort (W.)').'</li>
	<li class="center">'.$form->addElement('submit', 'send_pwd', 'Passwort &auml;ndern').'</li>
</ul>
</fieldset>
</form>
</div>');
unset($form);
echo('</div>');
echo('<div class="clear"></div>');
?>