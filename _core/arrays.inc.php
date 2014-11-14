<?php
/**
 * Bilder fuer die Sortierung
 */
$cfg['sort_up'] = '<img alt="aufsteigend" src="'.URL_WWW.'/medias/icons/up.gif" border="0" width="14" height="10" />';
$cfg['sort_down'] = '<img alt="absteigend" src="'.URL_WWW.'/medias/icons/down.gif" border="0" width="14" height="10" />';

/**
 * Einstellungen fuer PEAR:Pager
 * Default-Werte (koennen spaeter direkt im Script ueberschrieben werden wenn noetig!)
 */
$pagerOptions['urlVar'] = 'page';
$pagerOptions['mode'] = 'Sliding';
$pagerOptions['delta'] = 2;
$pagerOptions['perPage'] = 45; // sollte durch 3 teilbar sein!
$pagerOptions['linkClass'] = 'pager';
$pagerOptions['curPageLinkClassName'] = 'pager_current';
$pagerOptions['altFirst'] = 'zur ersten Seite';
$pagerOptions['altPrev'] = 'eine Seite zur&uuml;ck';
$pagerOptions['altNext'] = 'eine Seite vor';
$pagerOptions['altLast'] = 'zur letzten Seite';
$pagerOptions['altPage'] = 'Seite';
$pagerOptions['separator'] = '';
$pagerOptions['spacesBeforeSeparator'] = 1;
$pagerOptions['spacesAfterSeparator'] = 1;
$pagerOptions['clearIfVoid'] = false;
$pagerOptions['fixFileName'] = true;
$pagerOptions['append'] = true;

/**
 * Einstellungen fuer die NestedSet-Klasse
 */
$nsOptions['tab'] = TAB_CATS;
$nsOptions['id'] = 'id';
$nsOptions['rootid'] = 'root_id';
$nsOptions['name'] = 'cat_name';
$nsOptions['lft'] = 'lft';
$nsOptions['rft'] = 'rft';

/**
 * Benutzer-Gruppen
 * Benutzer: nur zum anschauen Rechte
 * Administrator: volle Rechte
 * Darf nicht veraendert werden
 */
$user_group = array(1 => 'Benutzer', 2 => 'Administrator');

/**
 * Benutzer-Status
 */
$user_access = array(1 => 'gesperrt', 2 => 'entsperrt');

/**
 * Darstellung-Kategorie-Ansichten
 */
$view_types = array(1 => 'Listen-Ansicht', 2 => 'Thumbnail-Ansicht');

/**
 * Ja - Nein
 */
$arr_confirm = array('nein', 'ja');

/**
 * Suchen
 */
$arr_search_dir = array(0 => 'in allen Ordnern suchen', 1 => 'nur ab dem angegebenen Ordner suchen');
$arr_search_mod = array('OR' => 'nach irgendeinem Schlagwort suchen', 'AND' => 'nach allen Schlagw&ouml;rtern suchen');
?>