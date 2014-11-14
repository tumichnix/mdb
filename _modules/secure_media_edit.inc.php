<?php
if ($auth->isAdmin())
{
	$search_media = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote(ID, 'integer').'');
	if ((int)$search_media === 1)
	{
		require_once './_core/libs/nestedset.class.php';
		$ns = new NestedSet($db, $nsOptions);
		echo('<h2>Medium bearbeiten</h2>');
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if (isset($_POST['send_edit']))
		{
			$form->addRule('media_tags', 'Es wurden keine Schlagw&ouml;rter f&uuml;r die Suchfunktion angegeben!', 'required');
			if ($ns->isRootNode($form->getSubmitVar('media_cat'))) {
				$form->setError('Das Medium darf sich nicht in einem Hauptordner befinden!');
			}
			if ($form->validate())
			{
				$db->query('UPDATE '.TAB_MEDIAS.' SET cat_id = '.$db->quote($form->getSubmitVar('media_cat'), 'integer').', media_access = '.$db->quote($form->getSubmitVar('media_access'), 'integer').', media_name = '.$db->quote($form->getSubmitVar('media_title'), 'text').', media_desc = '.$db->quote($form->getSubmitVar('media_desc'), 'text').', media_tags = '.$db->quote($form->getSubmitVar('media_tags'), 'text').', time_change = '.$db->quote(time(), 'integer').' WHERE id = '.$db->quote(ID, 'integer').'');
        		setLog('action', 'Media "'.$form->getSubmitVar('media_title').'" (ID: '.ID.') bearbeitet');
        		echo $form->getMsg('Das Medium wurde erfolgreich bearbeitet!');
			}
			else
			{
				echo $form->getErrors();
			}
		}
		$edit_media_query = $db->query('SELECT cat_id, media_type, media_access, media_dir, media_name, media_file, media_desc, media_tags, time_change FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote(ID, 'integer').'');
		if (PEAR::isError($edit_media_query) ){
			die($edit_media_query->getMessage());
		}
		$edit_media = $edit_media_query->fetchRow();
		$edit_media_query->free();
		$path_dl = PATH_ABS.'/files/'.$edit_media['media_dir'];
		$media_type = explode('/', $edit_media['media_type']);
		echo('<form method="post" action="index.php?module=secure_media_edit&amp;id='.ID.'">
		<fieldset>
		<legend>Medium: '.$edit_media['media_name'].'</legend>
		<div class="two_cols_left">
			'.$form->addElement('hidden', 'media_title', '', $edit_media['media_name']).'
			<ul>
				<li>'.$form->addElement('select', 'media_access', 'Freigeschaltet', $edit_media['media_access'], $arr_confirm).'</li>
				<li>'.$form->addElement('select', 'media_cat', 'Im Ordner', $edit_media['cat_id'], $ns->getTreeArray(0, true)).'</li>
				<li>'.$form->addElement('textarea', 'media_desc', 'Kurze Beschreibung', $edit_media['media_desc'], array('cols' => 25, 'rows' => 5)).'</li>
				<li>'.$form->addElement('text', 'media_tags', 'Schlagw&ouml;rter', $edit_media['media_tags']).'</li>
				<li>'.$form->addElement('submit', 'send_edit', 'Speichern').'</li>
			</ul>

		</div>
		<div class="two_cols_right">
		'.getMediaPreview(ID, $edit_media['media_dir'], $edit_media['media_name'], $edit_media['media_file'], $path_dl, false).'
		</div>
		<div class="clear"></div>
		</fieldset>
		</form>
		</div>');
		unset($form);
		unset($ns);
	}
	else
	{
		echo $msg_err['406'];
	}
}
else
{
	echo $msg_err['401'];
}
?>