<?php
if ($auth->isAdmin())
{
	require_once './_core/libs/nestedset.class.php';
	$ns = new NestedSet($db, $nsOptions);
	echo('<h2>Ordnerstruktur verwalten</h2>');
	if(ACTION == 'movenode' && ID > 0)
	{
		echo('<div id="form">');
		require_once './_core/libs/form.class.php';
		$form = new Form;
		if ($ns->moveNode(ID))
		{
			echo $form->getMsg('Der Ordner wurde erfolgreich verschoben!');
		}
		else
		{
			echo $form->getMsg('Der Ordner konnte nicht verschoben werden!');
		}
		echo('</div>');
		unset($form);
	}
	// Kategorien Sammeln in denen Medien vorhanden sind
	$query = $db->query('SELECT cat_id FROM '.TAB_MEDIAS.' GROUP BY cat_id');
	$cats = $query->fetchRow(MDB2_FETCHMODE_ORDERED);
	$query->free();
	echo('<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>Ordner</th>
		<th>Ebene</th>
		<th>Unterordner</th>
		<th colspan="3">Aktionen</th>
	</tr>');
	$x = 1;
	$tree = $ns->getTree();
	foreach($tree as $item)
	{
		$indent = '';
		for($i = 1; $i < $item[6]; $i++)
		{
			$indent .= '&mdash;';
		}
		$x++;
		if ((int)$item[3] === (int)1)
		{
			$del_action = 'delroot';
		}
		else
		{
			$del_action = ((int)$item[5] === (int)0) ? 'delchild' : 'delnode';
		}
		echo('<tr class="'.changeCss($x, 'a', 'b').'">
			<td>');
			echo ((int)$item[3] === 1) ? '<span class="bold">'.$indent.$item[2].'</span>' : $indent.$item[2];
		echo('</td>
			<td>'.$item[6].'</td>
			<td>'.$item[5].'</td>
			<td><a href="index.php?module=secure_cat_edit&amp;id='.$item[0].'" class="secure">Bearbeiten</a></td>
			<td>');
			if ((int)$item[8] > 0)
			{
				echo('<a href="index.php?module=secure_cat_list&amp;id='.$item[0].'&amp;action=movenode" class="secure">Nach oben schieben</a>');
			}
		echo('</td>
			<td><a href="index.php?module=secure_cat_del&amp;id='.$item[0].'&amp;action='.$del_action.'" class="secure">L&ouml;schen</a></td>
		</tr>');
	}
	echo('</table>');
	unset($ns);
}
else
{
	echo $msg_err['401'];
}
?>