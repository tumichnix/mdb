<?php
require_once './_core/libs/nestedset.class.php';
$ns = new NestedSet($db, $nsOptions);
echo('<h2>Datenbank</h2>');
echo('<table border="0" cellpadding="0" cellspacing="0">
<colgroup>
    <col width="20%" />
    <col width="80%" />
  </colgroup>
<tr>
<td valign="top"><div class="dtree">
	<script type="text/javascript">
	<!--
	d = new dTree("d", "'.URL_WWW.'/medias/icons/dtree/");
	d.add(0, -1, \'Alle: <a href="javascript:d.openAll();">&ouml;ffnen<\/a> &bull; <a href="javascript:d.closeAll();">schlie&szlig;en<\/a>\');');
	$tree = $ns->getTree();
	foreach($tree as $item) {
		$url = ((int)$item[5] != 0) ? '' : 'index.php?module=index&id='.$item[0];
		echo('d.add('.$item[0].', '.(int)$item[9].', "'.$item[2].'", "'.$url.'", "'.$item[2].'");'."\n");
	}
	unset($tree);
echo('document.write(d);
	//-->
	</script>
</div>
</td><td valign="top">');
$search_cat = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').'');
if ((int)$search_cat === 1) {
	$query = $db->query('SELECT root_id as rid, cat_name, lft, rft, (SELECT conf_view FROM '.TAB_CATS.' WHERE lft = 1 AND root_id = rid) as view_type, (SELECT COUNT(id) FROM '.TAB_MEDIAS.' WHERE cat_id = '.$db->quote(ID, 'integer').') as num_medias FROM '.TAB_CATS.' WHERE id = '.$db->quote(ID, 'integer').'');
	$data = $query->fetchRow();
	$query->free();
	if ((int)$data['num_medias'] === 0 && ((int)$data['rft']-(int)$data['lft']) > 1) {
		$get_cat = '';
		$query = $db->query('SELECT id FROM '.TAB_CATS.' WHERE root_id = '.$db->quote($data['rid'], 'integer').' AND lft BETWEEN '.$data['lft'].' AND '.$data['rft'].'');
		while ($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED)) {
			$get_cat .= $row[0].',';
		}
		$query->free();
		$where = 'cat_id IN('.substr($get_cat, 0, strlen($get_cat)-1).')';
		unset($get_cat);
	} else {
		$where = 'cat_id = '.$db->quote(ID, 'integer').'';
	}
	$pagerOptions['perPage'] = 45;

	$order = (!isset($_GET['order'])) ? 'media_name' : ORDER;

	$sql = 'SELECT id, media_name, media_dir, media_file, media_desc, (static_dl_web + static_dl_print) as dls, time_create as time FROM '.TAB_MEDIAS.' WHERE media_access = '.$db->quote('1', 'integer').' AND '.$where.' ORDER BY '.$order.' '.SORT.'';
	require_once './_core/libs/Pager_Wrapper.php';
	$pager = Pager_Wrapper_MDB2($db, $sql, $pagerOptions);
	$div_pager = '<div class="pager">Seiten: '.$pager['links'].'</div>';
	echo('<span class="small">'.$ns->getBreadcrumb(ID, false, 'index.php?module=index&amp;id=').'</span>');
	echo('<h3>'.$data['cat_name'].'</h3>');
	echo('<p class="small right"><b>Sortierung</b>: Name <a href="index.php?module=index&amp;id='.ID.'&amp;order=media_name&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=index&amp;id='.ID.'&amp;order=media_name&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a> &bull; Downloads <a href="index.php?module=index&amp;id='.ID.'&amp;order=dls&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=index&amp;id='.ID.'&amp;order=dls&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a> &bull; Datum <a href="index.php?module=index&amp;id='.ID.'&amp;order=time&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=index&amp;id='.ID.'&amp;order=time&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></p>');
	echo $div_pager;
	if ((int)$data['view_type'] === 1) {
		// Listenansicht
		$query = $db->query($sql);
		if ($query->numRows() > 0) {
			echo('<ul>');
			while (list($key, $val) = each($pager['data'])) {
				echo('<li><a href="index.php?module=media&amp;id='.$pager['data'][$key]['id'].'">'.$pager['data'][$key]['media_name'].'</a> <span class="small">('.convert_number($pager['data'][$key]['dls']).' Downloads)</span><br />
				'.textcutter(nl2br($pager['data'][$key]['media_desc']), 150).'</li>');
			}
			echo('</ul>');
		} else {
			echo('<p class="center bold">'.$msg['no_files_in_dir'].'</p>');
		}
	} else {
		// Thumbnailansicht
		$query = $db->query($sql);
		if ($query->numRows() > 0) {
			$prefix = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('THUMB_PREFIX', 'text').')'));
			echo('<table border="0" cellpadding="0" cellspacing="0">');
			$i = 0;
			while (list($key, $val) = each($pager['data'])) {
				if ($i == 0) {
					echo('<tr>');
					$i = 1;
				}
				echo('<td class="center"><a class="picture" href="index.php?module=media&amp;id='.$pager['data'][$key]['id'].'">');
				$thumb = getThumbnailInfos($pager['data'][$key]['media_dir'], $pager['data'][$key]['media_file']);
				if ((bool)$thumb['noerr'] === true) {
					echo('<img border="0" src="'.$thumb['url'].'" width="'.$thumb['w'].'" height="'.$thumb['h'].'" alt="'.$pager['data'][$key]['media_name'].'" />');
				}
				unset($thumb);
				echo('<br /><span class="bold">'.$pager['data'][$key]['media_name'].'</span></a>');
				if ($i == 1) {
					echo('</td>');
					$i = 2;
				} elseif ($i == 2) {
					echo('</td>');
					$i = 3;
				} elseif ($i == 3) {
					echo('</td></tr>');
					$i = 0;
				}
			}
			unset($i);
			echo('</table>');
			unset($prefix);
		} else {
			echo('<p class="center bold">'.$msg['no_files_in_dir'].'</p>');
		}
		$query->free();
	}
	echo $div_pager;
	unset($pager);
} else {
	$count_medias = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_MEDIAS.' WHERE media_access = 1');
	$count_main_cats = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_CATS.' WHERE lft = 1');
	$count_sub_cats = $db->extended->getOne('SELECT COUNT(id) FROM '.TAB_CATS.' WHERE lft != 1');
	echo('<h3>Willkommen in der '.$cfg['site_name'].'</h3>');
	echo('<p>Die Datenbank beinhaltet zur Zeit '.convert_number($count_medias).' Medien.<br />Diese befinden sich in '.convert_number($count_main_cats).' Hauptverzeichnissen mit insgesamt '.convert_number($count_sub_cats).' Unterverzeichnissen.</p>');
	echo('<p>Bitte w&auml;hlen Sie einen Ordner um mit dem Durchst&ouml;bern der '.$cfg['site_name'].' anzufangen.</p>');
	echo('<p>Alternativ k&ouml;nnen Sie auch die <a href="index.php?module=search">Suche</a> verwenden.</p>');
}
unset($ns);
echo('</td></tr></table>');
?>