<?php
if ($auth->isAdmin())
{
	$search_cat = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').'');
	if ((int)$search_cat === 1)
	{
		echo('<h2>Ordnerstrukturen l&ouml;schen</h2>');
		$cat_name = $db->extended->getOne('SELECT cat_name FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').'');
		require_once './_core/libs/nestedset.class.php';
		$ns = new NestedSet($db, $nsOptions);
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if (ACTION == 'delnode')
		{
			$query = $db->query('SELECT root_id, lft, rft FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').'');
			$data = $query->fetchRow(MDB2_FETCHMODE_ASSOC);
			$query->free();
			if (isset($_POST['send_delnode']))
			{
				$get_cat = '';
				$query = $db->query('SELECT id FROM '.TAB_CATS.' WHERE root_id = '.$db->quote($data['root_id'], 'integer').' AND lft BETWEEN '.$data['lft'].' AND '.$data['rft'].'');
				while ($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED))
				{
					$get_cat .= $row[0].',';
				}
				$query->free();
				$query_medias = $db->query('SELECT id, media_dir, media_name FROM '.TAB_MEDIAS.' WHERE cat_id IN('.substr($get_cat, 0, strlen($get_cat)-1).')');
				while ($row = $query_medias->fetchRow(MDB2_FETCHMODE_ORDERED))
				{
					$rmPath = PATH_ABS.'/files/'.$row[1];
					$rm = removeDir($rmPath);
					$db->query('DELETE FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote($row[0], 'integer').'');
					setLog('action', 'Media "'.$row[2].'" (ID: '.ID.') gel&ouml;scht');
				}
				$ns->delNode(ID);
				setLog('action', 'Ordner "'.$cat_name.'" (ID: '.ID.') gel&ouml;scht');
				echo $form->getMsg('Der Ordner "'.$cat_name.'" (inc. Unterordnern und Medien) wurde erfolgreich gel&ouml;scht!');
				unset($get_cat);
			}
			else
			{
				echo('<form method="post" action="index.php?module=secure_cat_del&amp;id='.ID.'&amp;action=delnode">
				<fieldset>
					<legend>Ordner (inc. Unterordner) l&ouml;schen</legend>
					<ul>
						<li>'.$form->getMsg('Soll der Ordner "'.$cat_name.'" (inc. Unterordnern und Medien) wirklich gel&ouml;scht werden?').'</li>
						<li class="center">'.$form->addElement('submit', 'send_delnode', 'Ja, l&ouml;schen').'</li>
					</ul>
				</fieldset>
				</form>');
			}
		}
		elseif(ACTION == 'delroot')
		{
			if (isset($_POST['send_delroot']))
			{
				$root_id = $db->extended->getOne('SELECT root_id FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').' AND lft = 1');
				$num_childs = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_CATS.' WHERE root_id = '.$db->quote($root_id, 'integer').' AND lft > 1');
				if ((int)$num_childs > 0)
				{
					$get_cat = '';
					$query = $db->query('SELECT id FROM '.TAB_CATS.' WHERE root_id = '.$db->quote($root_id, 'integer').'');
					while ($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED))
					{
						$get_cat .= $row[0].',';
					}
					$query->free();
					$query = $db->query('SELECT id, media_dir, media_name FROM '.TAB_MEDIAS.' WHERE cat_id IN('.substr($get_cat, 0, strlen($get_cat)-1).')');
					while ($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED))
					{
						$rmPath = PATH_ABS.'/files/'.$row[1];
						$rm = removeDir($rmPath);
						$db->query('DELETE FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote($row[0], 'integer').'');
						setLog('action', 'Media "'.$row[2].'" (ID: '.$row[0].') gel&ouml;scht');
					}
				}
				$ns->delRootNode(ID);
				setLog('action', 'Hauptordner "'.$cat_name.'" (ID: '.ID.') gel&ouml;scht');
				echo $form->getMsg('Der Hauptordner "'.$cat_name.'" (inc. aller Unterordner und Medien) wurde erfolgreich gel&ouml;scht!');
			}
			else
			{
				echo('<form method="post" action="index.php?module=secure_cat_del&amp;id='.ID.'&amp;action=delroot">
				<fieldset>
					<legend>Hauptordner l&ouml;schen</legend>
					<ul>
						<li>'.$form->getMsg('Soll der Hauptordner "'.$cat_name.'" (inc. Unterordner und Medien) wirklich gel&ouml;scht werden?').'</li>
						<li class="center">'.$form->addElement('submit', 'send_delroot', 'Ja, l&ouml;schen').'</li>
					</ul>
				</fieldset>
				</form>');
			}
		}
		elseif(ACTION == 'delchild')
		{
			if (isset($_POST['send_delchild']))
			{
				$query = $db->query('SELECT id, media_dir, media_name FROM '.TAB_MEDIAS.' WHERE cat_id = '.$db->quote(ID, 'integer').'');
				while ($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED))
				{
					$rmPath = PATH_ABS.'/files/'.$row[1];
					$rm = removeDir($rmPath);
					$db->query('DELETE FROM '.TAB_MEDIAS.' WHERE id = '.$db->quote($row[0], 'integer').'');
					setLog('action', 'Media "'.$row[2].'" (ID: '.$row[0].') gel&ouml;scht');
				}
				$query->free();
				$ns->delChild(ID);
				setLog('action', 'Ordner "'.$cat_name.'" (ID: '.ID.') gel&ouml;scht');
				echo $form->getMsg('Der Ordner "'.$cat_name.'" (inc. Medien) wurde erfolgreich gel&ouml;scht!');
			}
			else
			{
				echo('<form method="post" action="index.php?module=secure_cat_del&amp;id='.ID.'&amp;action=delchild">
				<fieldset>
					<legend>Ordner l&ouml;schen</legend>
					<ul>
						<li>'.$form->getMsg('Soll der Ordner "'.$cat_name.'" (inc. Medien) wirklich gel&ouml;scht werden?').'</li>
						<li class="center">'.$form->addElement('submit', 'send_delchild', 'Ja, l&ouml;schen').'</li>
					</ul>
				</fieldset>
				</form>');
			}
		}
		echo('</div>');
		unset($form);
		unset($ns);
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