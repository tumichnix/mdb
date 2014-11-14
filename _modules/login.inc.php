<?php
echo('<div id="login">
<h1>'.$cfg['site_name'].' &raquo; Anmeldung</h1>
<div id="form">');
require_once './_core/libs/form.class.php';
$form = new Form('post');
if (isset($_POST['login_send']))
{
	$form->addRule('login_user', 'Kein Benutzer angegeben!', 'required');
	$form->addRule('login_pwd', 'Kein Passwort eingetragen!', 'required');
	if ($form->validate())
	{
		if ($auth->login($form->getSubmitVar('login_user'), $form->getSubmitVar('login_pwd')))
		{
			header("Location: index.php");
		}
		else
		{
			echo $form->setError('Benutzer konnte nicht angemeldet werden!', true);
		}
	}
	else
	{
		echo $form->getErrors();
	}
}
echo('<form method="post" action="index.php" name="form_login">
<ul>
	<li>'.$form->addElement('text', 'login_user', 'Benutzer').'</li>
	<li>'.$form->addElement('password', 'login_pwd', 'Passwort').'</li>
	<li class="center">'.$form->addElement('submit', 'login_send','Anmelden &raquo;').'</li>
</ul>
</form>
<h2>Benutzeraccount beantragen</h2>
<p>Um einen Benutzeraccount zu beantragen klicken Sie bitte auf diesen <a href="mailto:dummy@tld.de?subject='.$cfg['site_name'].' - Benutzeraccount beantragen&amp;body=Bitte erstellen Sie mir einen Benutzeraccount fuer die '.$cfg['site_name'].'. Vielen Dank">Link</a>.</p>
</div>');
unset($form);
echo('</div>');
?>