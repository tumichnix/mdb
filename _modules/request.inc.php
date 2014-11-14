<?php
echo('<h2>Anfrage erstellen</h2>');
echo('<p>Sie haben nicht das Richtige gefunden? Dann haben Sie hier die M&ouml;glichkeit Ihren pers&ouml;nlichen Wunsch an uns zu richten.</p>');
echo('<div id="form">');
require_once './_core/libs/form.class.php';
$form = new Form;
if (isset($_POST['send_request']))
{
	$form->addRule('con_email', 'Bitte eine Email-Adresse angeben!', 'required');
	$form->addRule('con_subject', 'Bitte einen Betreff angeben!', 'required');
	$form->addRule('con_msg', 'Bitte eine Mitteilung angeben!', 'required');
	if ($form->validate())
	{
		$db_emails = $db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE data_name = '.$db->quote('EMAIL_CONTACT', 'text').'');
		$emails = explode("\n", $db_emails);
		// Email-Inhalt
		$message = "".$cfg['site_name']."\n\nAnfrage\n\nFolgende Anfrage wurde am ".date("d.m.y", time())." um ".date("H:i", time())." von der IP ".$session->getSession(SESSION_IP)." erstellt.\n\n";
		$message .= "Von: ".$arr_user['user_login']." (ID: ".$session->getSession(SESSION_USERID).")\n";
		$message .= "Betreff: ".$form->getSubmitVar('con_subject')."\n\n";
		$message .= "Mitteilung:\n\n".wordwrap($form->getSubmitVar('con_msg'), 70);
		// Email-Headers
		$header = "From: ".$arr_user['user_login']." <".$form->getSubmitVar('con_email').">\n";
		$header .= "Reply-To: ".$arr_user['user_login']." <".$form->getSubmitVar('con_email').">\n";
		$header .= "Subject: ".$cfg['site_name'].": Anfrage (".$form->getSubmitVar('con_subject').")\n";
		$header .= "Content-type: text/plain; charset=ISO-8859-15\n";
		$header .= "X-Mailer: PHP ".phpversion()."\n";
		foreach ($emails as $email)
		{
			mail(trim($email), "".$cfg['site_name'].": Anfrage (".$form->getSubmitVar('con_subject').")", $message, $header);
		}
		echo $form->getMsg('Die Anfrage wurde erfolgreich versendet!');
	}
	else
	{
		echo $form->getErrors();
	}
}
echo('<form method="post" action="index.php?module=request">
	<fieldset>
		<legend>Ihre Anfrage</legend>
		<ul>
			<li>'.$form->addElement('text', 'con_email', 'Ihre Email-Adresse').'</li>
			<li>'.$form->addElement('text', 'con_subject', 'Betreff').'</li>
			<li>'.$form->addElement('textarea', 'con_msg', 'Mitteilung', '', array('cols' => 60, 'rows' => 10)).'</li>
			<li>'.$form->addElement('submit', 'send_request', 'Senden').'</li>
		</ul>
	</fieldset>
</form>
</div>');
unset($form);
?>