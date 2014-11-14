<?php
if ($auth->isAdmin())
{
	$search_media_query = $db->query('SELECT COUNT(id) as num_medias, media_name, media_dir FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote(ID, 'integer').' GROUP BY id');
	$search_media = $search_media_query->fetchRow();
	$search_media_query->free();
	if ((int)$search_media === 1)
	{
		echo('<h2>Medium l&ouml;schen</h2>');
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if (isset($_POST['send_del']))
		{
			$rmPath = PATH_ABS.'/files/'.$search_media['media_dir'];
			$rm = removeDir($rmPath);
			if ($rm == 0) {
				$db->query('DELETE FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote(ID, 'integer').'');
				setLog('action', 'Media "'.$search_media['media_name'].'" (ID: '.ID.') gel&ouml;scht');
				echo $form->getMsg('Das Medium "'.$search_media['media_name'].'" wurde erfolgreich gel&ouml;scht! <a href="index.php?module=secure">Weiter zur Verwaltung</a>.');
			} else {
				setLog('error', $search_media['dir'].' konnte nicht gel&ouml;scht werden');
				echo $form->getMsg('Verzeichnis "'.$search_media['dir'].'" konnte nicht gel&ouml;scht werden! Das Medium konnte nicht gel&ouml;scht werden! Fehlercode: '.$rm);
			}
		}
		else
		{
			echo('<form method="post" action="index.php?module=secure_media_del&amp;id='.ID.'">
			<fieldset>
				<legend>Medium l&ouml;schen</legend>
				<ul>
					<li>'.$form->getMsg('Soll das Medium "'.$search_media['media_name'].'" wirklich gel&ouml;scht werden?').'</li>
					<li class="center">'.$form->addElement('submit', 'send_del', 'Ja, l&ouml;schen').'</li>
				</ul>
			</fieldset>
			</form>
			</div>');
		}
		unset($form);
	}
	else
	{
		echo $msg_err['401'];
	}
}
else
{
	echo $msg_err['401'];
}
?>