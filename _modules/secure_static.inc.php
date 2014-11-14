<?php
if ($auth->isAdmin())
{
	echo('<h2>Statistik</h2>');
	echo('<h3>Medien</h3>');
	$query = 'SELECT id, media_name, (static_dl_web + static_dl_print) as dls FROM '.TAB_MEDIAS.' WHERE media_access = 1 ORDER BY '.ORDER.' '.SORT.'';
	require_once './_core/libs/Pager_Wrapper.php';
	$pager = Pager_Wrapper_MDB2($db, $query, $pagerOptions);
	$div_pager = '<div class="pager">Seiten: '.$pager['links'].'</div>';
	echo $div_pager;
	echo('<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<th>ID <a href="index.php?module=secure_static&amp;order=id&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_static&amp;order=id&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Medium <a href="index.php?module=secure_static&amp;order=media_name&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_static&amp;order=media_name&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
		<th>Downloads <a href="index.php?module=secure_static&amp;order=dls&amp;sort=ASC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_up'].'</a><a href="index.php?module=secure_static&amp;order=dls&amp;sort=DESC&amp;page='.$pager['page_numbers']['current'].'">'.$cfg['sort_down'].'</a></th>
	</tr>');
	$x = 1;
	while (list($key, $val) = each($pager['data']))
	{
		$x++;
		echo('<tr class="'.changeCss($x, 'a', 'b').'">
		<td>'.$pager['data'][$key]['id'].'</td>
		<td><a href="index.php?module=media&amp;id='.$pager['data'][$key]['id'].'">'.$pager['data'][$key]['media_name'].'</a></td>
		<td>'.convert_number($pager['data'][$key]['dls']).'</td>
		</tr>');
	}
	unset($x);
	echo('</table>');
	echo $div_pager;
	unset($pager);
}
else
{
	echo $msg_err['401'];
}
?>