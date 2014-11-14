<?php
if ($auth->isAdmin())
{
	require_once './_core/libs/nestedset.class.php';
	$ns = new NestedSet($db, $nsOptions);
	require_once './_core/libs/form.class.php';
	$form = new Form;
	echo('<h2>Ordner erstellen</h2>');
	echo('<div id="form">');
	// wenn ein Hauptordner erstellt wurde
	if (isset($_POST['send_main_add']))
	{
		$form->addRule('main_name', 'Bitte einen Hauptordnernamen angeben!', 'required');
		$form->addRule('main_name', 'Der Hauptordnername darf max. 255 Zeichen lang sein!', 'maxlength', 255);
		if ($form->validate())
		{
			$ns->addRootNode($form->getSubmitVar('main_name'), $form->getSubmitVar('opt_view'));
			setLog('action', 'Neuer Hauptordner "'.$form->getSubmitVar('main_name').'" erstellt');
			echo $form->getMsg('Der Hauptordner wurde erfolgreich erstellt!');
		}
		else
		{
			echo $form->getErrors();
		}
	}
	echo('<form method="post" action="index.php?module=secure_cat_add">
	<fieldset>
		<legend>Neuen Hauptordner</legend>
		<ul>
			<li>'.$form->addElement('text', 'main_name', 'Hauptordnername').'</li>
			<li>'.$form->addElement('select', 'opt_view', 'Darstellungstyp', 1, $view_types).'</li>
			<li>'.$form->addElement('submit', 'send_main_add', 'Erstellen').'</li>
		</ul>
	</fieldset>
	</form>');
	// wenn ein neuer Ordner erstellt wurde
	if (isset($_POST['send_sub_add']))
	{
		$form->addRule('sub_name', 'Bitte einen Unterordnernamen angeben', 'required');
		$form->addRule('sub_name', 'Der Unterordnername darf max. 255 Zeichen lang sein!', 'maxlength', 255);
		$form->addRule('parent', 'Bitte erst einen Hauptordner anlegen!', 'required');
		if ($form->validate())
		{
			$ns->addNode($form->getSubmitVar('parent'), $form->getSubmitVar('sub_name'));
			setLog('action', 'Neuer Unterordner "'.$form->getSubmitVar('sub_name').'" erstellt');
			echo $form->getMsg('Der Unterordner wurde erfolgreich erstellt!');
		}
		else
		{
			echo $form->getErrors();
		}
	}
	echo('<form method="post" action="index.php?module=secure_cat_add">
	<fieldset>
		<legend>Neuer Unterordner</legend>
		<ul>
			<li>'.$form->addElement('text', 'sub_name', 'Unterordnername').'</li>
			<li>'.$form->addElement('select', 'parent', 'Im Ordner', 0, $ns->getTreeArray(0, true)).'</li>
			<li>'.$form->addElement('submit', 'send_sub_add', 'Erstellen').'</li>
		</ul>
	</fieldset>
	</form>
	</div>');
	unset($form);
	unset($ns);
}
else
{
	echo $msg_err['401'];
}
?>