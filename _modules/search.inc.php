<?php
echo('<h2>Suchen</h2>');
require_once './_core/libs/nestedset.class.php';
$ns = new NestedSet($db, $nsOptions);
require_once './_core/libs/form.class.php';
$form = new Form('post');
if (isset($_GET['src']) && isset($_GET['mod']) && isset($_GET['dir']) && isset($_GET['cat'])) {
	$a = urldecode($form->quote($_GET['src']));
	$b = urldecode($form->quote($_GET['mod']));
	$c = (int)$_GET['dir'];
	$d = (int)$_GET['cat'];
} elseif ($form->getSubmitVar('search_str') != '') {
	$a = $form->getSubmitVar('search_str');
	$b = $form->getSubmitVar('search_mod');
	$c = $form->getSubmitVar('search_in_dir');
	$d = $form->getSubmitVar('search_cat');
} else {
	$a = '';
	$b = 'OR';
	$c = 0;
	$d = 0;
}
echo('<div id="form">
<form method="post" action="index.php?module=search#results">
<fieldset>
	<legend>Suchen</legend>
	<ul>
		<li>'.$form->addElement('text', 'search_str', 'Schlagw&ouml;rter', $a).'</li>
		<li>'.$form->addElement('select', 'search_mod', 'Suchmethode', $b, $arr_search_mod).'</li>
		<li>'.$form->addElement('select', 'search_in_dir', 'Suchebene', $c, $arr_search_dir).'</li>
		<li>'.$form->addElement('select', 'search_cat', 'Ordner', $d, $ns->getTreeArray(0, true)).'</li>
		<li>'.$form->addElement('submit', 'send_search', 'Suchen').'</li>
	</ul>
</fieldset>
</form>');
if (isset($_POST['send_search']) || (isset($_GET['src']) && isset($_GET['mod']) && isset($_GET['dir']) && isset($_GET['cat']))) {
	if (isset($_GET['src']) && isset($_GET['mod']) && isset($_GET['dir']) && isset($_GET['cat'])) {
		$str = urldecode($form->quote($_GET['src']));
		$mod = urldecode($form->quote($_GET['mod']));
		$dir = (int)$_GET['dir'];
		$cat = (int)$_GET['cat'];
	} else {
		$str = (string)$form->getSubmitVar('search_str');
		$mod = (string)$form->getSubmitVar('search_mod');
		$dir = (int)$form->getSubmitVar('search_in_dir');
		$cat = (int)$form->getSubmitVar('search_cat');
	}
	if (empty($str)) {
		echo $form->setError('Bitte mind. ein Schlagwort angeben!', true);
	} else {
		// Erstellen der Suchtags fuer die Whereklausel
		$search_option = ((string)$mod === 'OR' || (string)$mod === 'AND') ? $mod : 'OR';
		$search_tags = explode(' ', $str);
		$search_string = '';
		$search_count = count($search_tags);
		for ($i = 0; $i < $search_count; $i++) {
			if ($i > 0 && $i < $search_count) {
				$search_string .= $search_option.' ';
			}
			$search_string .= "LOWER(media_tags) LIKE '%".$db->quote(strtolower($search_tags[$i]), 'text', false)."%' ";
		}
		// Kategorie-IDs sammeln je nach der Suchebene
		if ($dir > 0) {
			$query = $db->query('SELECT root_id, lft, rft FROM '.TAB_CATS.' WHERE id = '.$db->quote($cat, 'integer').'');
			$data = $query->fetchRow(MDB2_FETCHMODE_ASSOC);
			$query->free();
			$get_cat = '';
			$query = $db->query('SELECT id FROM '.TAB_CATS.' WHERE root_id = '.$db->quote($data['root_id'], 'integer').' AND lft BETWEEN '.$data['lft'].' AND '.$data['rft'].'');
			while ($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED)) {
				$get_cat .= $row[0].',';
			}
			$query->free();
			$sql_in = ' AND cat_id IN('.substr($get_cat, 0, strlen($get_cat)-1).')';
		} else {
			$sql_in = '';
		}
		echo('<h2><a id="results">Suchergebnis</a></h2>');
		// Die WHERE-Klausel
		$where = $search_string.$sql_in;
		// Die Such-Query
		$search_query = 'SELECT '.TAB_MEDIAS.'.id, media_name, media_type, media_dir, media_file, cat_name, time_create, cat_id FROM '.TAB_MEDIAS.' INNER JOIN '.TAB_CATS.' ON '.TAB_CATS.'.id = '.TAB_MEDIAS.'.cat_id WHERE media_access = 1 AND '.$where.' ORDER BY media_name ASC';
		require_once './_core/libs/Pager_Wrapper.php';
		$pagerOptions['append'] = false;
		$pagerOptions['fileName'] = 'index.php?module=search&src='.urlencode($str).'&mod='.urlencode($mod).'&dir='.$dir.'&cat='.$cat.'&'.$pagerOptions['urlVar'].'=%d#results';
		$pager = Pager_Wrapper_MDB2($db, $search_query, $pagerOptions);
		if ($pager['page_numbers']['total'] > 0) {
			echo $form->getMsg('Es wurden insgesamt '.$pager['totalItems'].' Medien zu Ihren Suchkriterien gefunden.');
			$prefix = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('THUMB_PREFIX', 'text').')'));
			$div_pager = '<div class="pager">Seiten: '.$pager['links'].'</div>';
			echo $div_pager;
			echo('<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<th>Medium</th>
				<th>Ordner</th>
				<th>Erstellt am</th>
			</tr>');
			$x = 1;
			while (list($key, $val) = each($pager['data'])) {
				$x++;
				echo('<tr class="'.changeCss($x, 'a', 'b').'">
					<td><a href="index.php?module=media&amp;id='.$pager['data'][$key]['id'].'&amp;src='.urlencode($str).'&amp;mod='.urlencode($mod).'&amp;dir='.$dir.'&amp;cat='.$cat.'&amp;page='.$pager['page_numbers']['current'].'#results">');
				$thumb = getThumbnailInfos($pager['data'][$key]['media_dir'], $pager['data'][$key]['media_file']);
				if ((bool)$thumb['noerr'] === true) {
					echo('<img border="0" src="'.$thumb['url'].'" width="'.$thumb['w'].'" height="'.$thumb['h'].'" alt="'.$pager['data'][$key]['media_name'].'" />');
				} else {
					echo $pager['data'][$key]['media_name'];
				}
				unset($thumb);
				echo('</a></td>
					<td>'.$ns->getBreadcrumb($pager['data'][$key]['cat_id'], false, 'index.php?module=index&amp;id=').'</td>
					<td>'.convert_time($pager['data'][$key]['time_create'], 'FULL').'</td>
				</tr>');
			}
			unset($x);
			echo('</table>');
			echo $div_pager;
		} else {
			echo $form->getMsg('Es wurden keine Treffer erzielt!');
		}
		unset($pager);
	}
}
echo('</div>');
unset($form);
unset($ns);
?>