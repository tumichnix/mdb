<?php
$terms = $db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE data_name = '.$db->quote('TERMS_OF_USE', 'text').'');
echo('<p>'.nl2br(text2html($terms)).'</p>');
unset($terms);
?>