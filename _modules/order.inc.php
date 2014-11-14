<?php
$search_media = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_MEDIAS.' WHERE media_access = 1 AND id = '.$db->quote(ID, 'integer').'');
if ((int)$search_media === 1)
{
	$media_query = $db->query('SELECT media_name, media_dir, media_file FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote(ID, 'integer').'');
	$media = $media_query->fetchRow();
	$media_query->free();
	echo('<h2>Script bestellen</h2>');
	require_once './_core/libs/form.class.php';
	$form = new Form;
	echo('<div id="form">');
	if (isset($_POST['send_order']))
	{
		if ((int)$form->getSubmitVar('order_x') < 1) $form->setError('Der Wert f&uuml;r die Anzahl ist ung&uuml;ltig!');
		$form->addRule('order_loc', 'Wohin soll geliefert werden?', 'required');
		$form->addRule('order_time', 'Bitte einen Lieferzeitpunkt angeben!', 'required');
		$ord_time = $form->getSubmitVar('order_time');
		$day = substr($ord_time, 0, 2);
		$month = substr($ord_time, 3, 2);
		$year = substr($ord_time, 6, 4);
		$hour = substr($ord_time, 11, 2);
		$min = substr($ord_time, -2);
		if (mktime($hour, $min, 0, $month, $day, $year) < time())
		{
			$form->setError('Der Zeitpunkt liegt in der Vergangenheit oder ist ung&uuml;ltig!');
		}
		if ($form->validate())
		{
			$email = $db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE data_name = '.$db->quote('EMAIL_ORDER', 'text').'');
			$message = "".$cfg['site_name']."\n\nDruckauftrag: ".$media['media_name']."\n\nFolgender Auftrag wurde am ".date("d.m.y", time())." um ".date("H:i", time())." von der IP ".$session->getSession(SESSION_IP)." erstellt.\n\n";
			$message .= "Von: ".$arr_user['user_login']." (ID: ".$session->getSession(SESSION_USERID).")\n";
			$message .= "Was: ".$media['media_name']."\n";
			$message .= "Wie viel: ".$form->getSubmitVar('order_x')." Stueck\n";
			$message .= "Wohin: ".$form->getSubmitVar('order_loc')."\n";
			$message .= "Wann: ".$ord_time."\n\n";
			$message .= "Die benoetigte Datei (".$media['media_file'].") befindet sich im Anhang.";
			$message .= "\n\nDies ist eine automatisch generierte Email, bitte nich drauf antworten!";
			$sep = strtoupper(md5(uniqid(time())));
			$header = "From: ".$cfg['site_name']." <".$email.">\n";
			$header .= "MIME-Version: 1.0\n";
			$header .= "Content-type: multipart/mixed; boundary=".$sep."\n";
			$header .= "This is a multi-part message in MIME format\n";
			$header .= "--".$sep."\n";
			$header .= "Content-Type: text/plain\n";
			$header .= "Content-Transfer-Encoding: 8bit";
			$header .= "\n\n".$message."";
			$file = PATH_ABS.'/files/'.$media['media_dir'].'/'.$media['media_file'];
			$file_content = fread(fopen($file, "r"), filesize($file));
			$file_content = chunk_split(base64_encode($file_content));
			$header .= "\n--$sep";
			$header .= "\nContent-Type: application/octetstream; name=\"".$media['media_file']."\"";
			$header .= "\nContent-Transfer-Encoding: base64";
			$header .= "\nContent-Disposition: attachment, filename=\"".$media['media_file']."\"";
			$header .= "\n\n".$file_content."";
			$header .= "\n--".$sep."--";
			mail($email, "Druckauftrag: ".$media['media_name']."", $message, $header);
			unset($header);
			echo $form->getMsg('Ihr Druckauftrag wurde erfolgreich gespeichert!');
		}
		else
		{
			echo $form->getErrors();
		}
	}
	echo('<form method="post" action="index.php?module=order&amp;id='.ID.'">
	<fieldset>
		<legend>Druckauftrag f&uuml;r '.$media['media_name'].'</legend>
		<ul>
			<li>'.$form->addElement('text', 'order_x', 'Wie viel?', 0).' (in St&uuml;ck)</li>
			<li>'.$form->addElement('text', 'order_loc', 'Wohin?').' (z.B. Raum-Nr.)</li>
			<li>'.$form->addElement('text', 'order_time', 'Wann?', convert_time(time(), 'FULL')).' (dd.mm.yyyy HH.mm)</li>
			<li>'.$form->addElement('submit', 'send_order', 'Bestellen').'</li>
		</ul>
	</fieldset>
	</form>
	</div>');
	unset($form);
}
else
{
	echo $msg_err['406'];
}
?>