<?php
// Superuser ID
define('SUPERUSERID', 1);

/**
 * Schaltet die Seite in einen Wartungsmodus
 * Moegliche Werte sind "on" oder "off"
 */
$cfg['maintenance'] = 'off';

/**
 * Einstellungen fuer den Benutzer sowie fuer das Passwort
 */
$cfg['login']['min'] = 5; // min. Laenge fuer Benutzer
$cfg['login']['max'] = 40; // max. Laenge fuer Benutzer
$cfg['pwd']['min'] = 6; // min. Laenge fuer Passwort
$cfg['pwd']['max'] = 24; // max. Laenge fuer Passwort

/**
 * Session-Namen
 */
define('SESSION_PREFIX', 'mdb_');
define('SESSION_IP', SESSION_PREFIX.'ip');
define('SESSION_USERID', SESSION_PREFIX.'uid');
define('SESSION_HASH', SESSION_PREFIX.'hash');

/**
* Zeit in Minuten nachdem eine Session ohne Aktivitaet zwangslaeufig beendet wird
*/
$cfg['sessionLifeTimeNoAct'] = 60;

/**
 * Verschiedene Regexe
 */
$regex['user_login'] = '/^[a-z0-9\.\-]+$/i';
$regex['user_pwd'] = '/^[a-z0-9_\-[:space:]]+$/i';
$regex['file_ext'] = '/^\.{1}[a-z0-9]+/i';

/**
 * Site-Vars
 */
$cfg['site_name'] = 'Multimedia-Datenbank';

/**
 * Wasserzeichen fuer Bilder
 */
$watermark['png'] = PATH_ABS.'/watermark.png'; // Transparentes PNG welches ueber JPGs gelegt wird



// Dont change below!

/**
 * Blacklist fuer Module welche nicht per URL eingebunden werden duerfen
 * ohne ".inc.php"!
 */
$blacklist_modules = array('login', 'terms_of_use_confirm');

/**
 * Konstanten fuer die Seite
 */
$module = (empty($_GET['module'])) ? 'index':strtolower(preg_replace("#[^a-zA-Z_-]#",'', $_GET['module']));
define('MODULE', $module);
unset($module);

$id = (empty($_GET['id'])) ? 0:(int)preg_replace("#[^0-9]#",'', $_GET['id']);
define('ID', $id);
unset($id);

$show = (empty($_GET['show'])) ? 0:(int)preg_replace("#[^0-1]{1}#",'', $_GET['show']);
define('SHOW', $show);
unset($show);

$action = (empty($_GET['action'])) ? '':strtolower(preg_replace("#[^a-zA-Z]#",'', $_GET['action']));
define('ACTION', $action);
unset($action);

$page = (empty($_GET['page'])) ? 1:(int)preg_replace("#[^0-9]#",'', $_GET['page']);
define('PAGE', $page);
unset($page);

$order = (empty($_GET['order'])) ? 'id':strtolower(preg_replace("#[^a-z_]#",'', $_GET['order']));
define('ORDER', $order);
unset($order);

$sort = (empty($_GET['sort']) || !in_array(strtoupper($_GET['sort']), array('ASC', 'DESC'))) ? 'ASC':$_GET['sort'];
define('SORT', $sort);
unset($sort);
?>