<?php
if ($auth->isAdmin())
{
	$search_cat = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').'');
	if ((int)$search_cat === 1)
	{
		echo('<h2>Ordner bearbeiten</h2>');
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if (isset($_POST['send_edit']))
		{
			$form->addRule('cat_name', 'Bitte einen Ordnernamen festlegen!', 'required');
			if ($form->validate())
			{
				$view = ($form->getSubmitVar('mod') == 1) ? $form->getSubmitVar('opt_view') : 1;
				$db->query('UPDATE '.TAB_CATS.' SET cat_name = '.$db->quote($form->getSubmitVar('cat_name'), 'text').', conf_view = '.$db->quote($view, 'integer').' WHERE id = '.$db->quote(ID, 'integer').'');
				echo $form->getMsg('Der Ordner wurde erfolgreich bearbeitet!');
				setLog('action', 'Ordner ID: '.ID.' bearbeitet');
			}
			else
			{
				echo $form->getErrors();
			}
		}
		$edit_cat_query = $db->query('SELECT cat_name, lft, conf_view FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').'');
		$edit_cat = $edit_cat_query->fetchRow();
		$edit_cat_query->free();
		echo('<form method="post" action="index.php?module=secure_cat_edit&amp;id='.ID.'">
		'.$form->addElement('hidden', 'mod', NULL, $edit_cat['lft']).'
		<fieldset>
			<legend>Ordner bearbeiten</legend>
			<ul>
				<li>'.$form->addElement('text', 'cat_name', 'Ordnername', $edit_cat['cat_name']).'</li>');
				if ((int)$edit_cat['lft'] === 1)
				{
					echo('<li>'.$form->addElement('select', 'opt_view', 'Darstellungstyp', $edit_cat['conf_view'], $view_types).'</li>');
				}
		echo('	<li>'.$form->addElement('submit', 'send_edit', 'Speichern').'</li>
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
}
else
{
	echo $msg_err['401'];
}
?>