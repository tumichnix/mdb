<?php
/**
 * Array mit Fehlermeldungen
 */
$msg_err['401'] = '<h2>Unauthorized</h2><p class="error">Sie haben nicht die n&ouml;tigen Zugriffsrechte um diese Seite aufzurufen!</p>';
$msg_err['404'] = '<h2>Not found</h2><p class="error">Die angeforderte Datei wurde nicht gefunden!</p>';
$msg_err['406'] = '<h2>Not acceptable</h2><p class="error">Ung&uuml;ltige Parameter!</p>';

/**
 * Sonstige Meldungen
 */
$msg['guide_user'] = 'Der Benutzername darf nur aus den Zeichen a-z A-z 0-9 sowie Punkt und Bindestrich bestehen!';
$msg['guide_pwd'] = 'Das Passwort darf nur aus den Zeichen a-z A-z 0-9 - _ sowie Leerzeichen bestehen!';
$msg['user_length_min'] = 'Der Benutzer muss mind. '.$cfg['login']['min'].' Zeichen lang sein!';
$msg['user_length_max'] = 'Der Benutzer darf max. '.$cfg['login']['max'].' Zeichen lang sein!';
$msg['pwd_length_min'] = 'Das Passwort muss mind. '.$cfg['pwd']['min'].' Zeichen lang sein!';
$msg['pwd_length_max'] = 'Das Passwort darf max. '.$cfg['pwd']['max'].' Zeichen lang sein!';
$msg['no_files_in_dir'] = 'In diesem Ordner befinden sich zur Zeit keine Medien.';
?>