<?php
/**
 * Der PEAR Errorhandler
 * @param unknown_type $error_obj
 */
function handle_pear_error($error_obj)
{
	print '<pre><b>PEAR-Error</b><br />';
    echo $error_obj->getMessage().': '.$error_obj->getUserinfo();
   	print '</pre>';
}

/**
 * Wandelt Bytes in die naechste sinnvolle Einheit um
 * @param integer $bytes Die Bytes
 * @param integer $decimal [optional] Nachkommastellen
 * @param boolean $praefix [optional] Umrechnungsfaktor ist 1000? Ansonsten 1024
 * @param boolean $short [optional] Kuerzel fuer die Einheit ansonsten Langform
 * @return string
 */
function convert_byte($bytes, $decimal = 2, $praefix = true, $short = true)
{
	$factor = ($praefix) ? 1000 : 1024;
	$decimal = (is_int($decimal) && $decimal >= 0) ? $decimal : 0;
	if ($praefix)
	{
		$unit = ($short == 1) ? array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB') : array('Byte', 'Kilobyte', 'Megabyte', 'Gigabyte', 'Terabyte', 'Petabyte', 'Exabyte', 'Zettabyte', 'Yottabyte');
	}
	else
	{
        $unit = ($short == 1) ? array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB') : array('Byte', 'Kibibyte', 'Mebibyte', 'Gibibyte', 'Tebibyte', 'Pebibyte', 'Exbibyte', 'Zebibyte', 'Yobibyte');
	}
	$i = 0;
	while ($bytes >= $factor && $i < count($unit)-1)
	{
		$bytes /= $factor;
		$i++;
	}
	return number_format($bytes, $decimal, ',', '.').'&nbsp;'.$unit[$i];
}

/**
 * Erstellt Thumbnails von JP(E)G-Bildern
 * @param string $path_abs Der Absolute Pfad zum Originalbild (ohne / am Ende)
 * @param string $source Dateiname des Originalbildes
 * @param integer $max_width Die max. Breite des Thumbnails
 * @param integer $max_height Die max. Hoehe des Thumbnails
 * @param string $destination Das Zielverzeichnis fuer das Thumbnail (wenn nicht gesetzt dann selber Pfad wie $path_abs) (ohne / am Ende)
 * @return boolean
 */
function thumbnailJPEG($path_abs, $source, $max_width, $max_height, $destination = '')
{
	$source_image = $path_abs.'/'.$source;
	if (!file_exists($source_image))
	{
		return false;
	}

	$destination_dir = (empty($destination)) ? $path_abs : $destination;
	if (!is_writeable($destination_dir))
	{
		return false;
	}

	// Bild-Daten holen
	$source_info = getimagesize($source_image);

	// Seitenverhaeltnis bestimmen
    $ratio = (float)$source_info[0] / (float)$source_info[1];

    // Groesse des Thumbnail berechnen
    if ($ratio <= 1)
    {
		// Breite <= Hoehe -> Hoehe auf $max_height festlegen -> Breite anpassen
        $thumb_height = $max_height;
        $thumb_width = ceil($max_height * $ratio);
    }
    else
    {
		// Breite > Hoehe -> Breite auf $max_width festlegen -> Hoehe anpassen
        $thumb_width = $max_width;
        $thumb_height = ceil($max_width / $ratio);
     }

	global $watermark;
	global $db;

	$prefix = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('THUMB_PREFIX', 'text').')'));

	// Source ohne prefix
	$source_no_prefix = substr(strstr($source, '_'), 1);

	// Thumbnail erstellen
	if ($source_info[2] == 2) {
		// 96 DPI Bild erstellen
		$webnail = $destination_dir.'/'.$prefix[2].$source_no_prefix;
		$input = ImageCreateFromJPEG($source_image);
		$web = ImageCreateTrueColor($source_info[0], $source_info[1]);
		ImageCopy($web, $input, 0, 0, 0, 0, $source_info[0], $source_info[1]);
		ImageJPEG($web, $webnail, 90);
		unset($input);
		// Thumbnail erstellen
		$thumbnail = $destination_dir.'/'.$prefix[1].$source_no_prefix;
		$input = ImageCreateFromJPEG($source_image);
		$thumb = ImageCreateTrueColor($thumb_width, $thumb_height);
		ImageCopyResized($thumb, $input, 0, 0, 0, 0, $thumb_width, $thumb_height, $source_info[0], $source_info[1]);
		ImageJPEG($thumb, $thumbnail, 90);
		unset($input);
		// Wasserzeichen auf Thumbnail setzen
		$wm_info = getimagesize($watermark['png']);
		$input = ImageCreateFromJPEG($thumbnail);
		$wm = ImageCreateFromPNG($watermark['png']);
		ImageCopy($input, $wm, ($thumb_width-$wm_info[0]), ($thumb_height-$wm_info[1]), 0, 0, $wm_info[0], $wm_info[1]);
		ImageJPEG($input, $thumbnail, 90);
	} else {
		return false;
	}
	unset($prefix);
	return true;
}

/**
 * Formatierung des Datums
 * @param integer $timestamp Der Unix-Timestamp
 * @param string $mode Wie soll das Datum formatiert werden
 * @return string
 */
function convert_time($timestamp, $mode)
{
	$timestamp = (int)$timestamp;
	$mode = strtoupper($mode);
	$arr_modes = array('FULL' => 'd.m.Y H:i', 'DATE' => 'd.m.Y', 'TIME' => 'H:i');
	if (array_key_exists($mode, $arr_modes))
	{
		return date("$arr_modes[$mode]", $timestamp);
	}
	else
	{
		return 'Modus not supported!';
	}
}

/**
 * Wechselt Zeilenweise zwischen zwei definierten CSS-Klassen
 * @param integer $nr Eie fortlaufende Nummer
 * @param string $css_a Die erste CSS-Klasse
 * @param string $css_b Die zweite CSS-Klasse
 * @return string
 */
function changeCss($nr, $css_a, $css_b)
{
	return ($nr%2 == 0) ? $css_a : $css_b;
}

/**
 * Schreibt Log-Eintraege
 * @param string $type action|error
 * @param string $msg Der Log-Eintrag
 * @return void
 */
function setLog($type, $msg)
{
	global $db;
	global $session;
	$db->query('INSERT INTO '.TAB_LOGS.' (log_time, log_uid, log_type, log_message) VALUES ('.$db->quote(time(), 'integer').', '.$db->quote($session->getSession(SESSION_USERID), 'integer').', '.$db->quote($type, 'text').', '.$db->quote($msg, 'text').')');
}

/**
* hex2rgb
* Wandelt einen Hex-Farbcode in RGB-Farbcode um
* @param string $str_hex Der Hex-Farbcode ohne #
* @return array Array mit den RGB-Werten
*/
function hex2rgb($hex)
{
	$hex = preg_replace("/[^a-fA-F0-9]/", "", $hex);
	$rgb = array();
	if (strlen($hex) == 3)
	{
		$rgb[0] = hexdec ($hex[0].$hex[0]);
		$rgb[1] = hexdec ($hex[1].$hex[1]);
		$rgb[2] = hexdec ($hex[2].$hex[2]);
	}
	elseif (strlen($hex) == 6)
	{
		$rgb[0] = hexdec($hex[0].$hex[1]);
		$rgb[1] = hexdec($hex[2].$hex[3]);
		$rgb[2] = hexdec($hex[4].$hex[5]);
	}
	else
	{
		return "ERROR: Incorrect colorcode, expecting 3 or 6 chars (a-f, A-F, 0-9)";
	}
	return $rgb;
}

/**
 * Intelligentes kuerzen von Texten wie z.B. Teasern
 * @param string $text Der Text
 * @param integer $length Die anzuzeigende Laenge
 * @param string [optional] $tail Weiterfuerende Zeichen
 * @return string
 */
function textcutter($text, $length, $tail = ' ...')
{
    $text = trim($text);
    $txtl = strlen($text);
    if ($txtl > $length)
    {
        for($i = 1; $text[$length-$i] != ' '; $i++)
        {
        	if ($i == $length) return substr($text, 0, $length).$tail;
        }
    	for(; $text[$length-$i] == ',' || $text[$length-$i] == '.' || $text[$length-$i] == ' '; $i++)
        {
        	;
        }
        $text = substr($text, 0, $length-$i+1).$tail;
    }
    return $text;
}

/**
 * Loescht die Datein (inc. Ordner) zu dem entsprechenden Datenbankeintrag
 * @param string $path Der Name des Verzeichnisses ohne "/" am Ende
 * @return integer 0 = OK, -1 = kein Verzeichnis, -2 = Fehler beim entfernen, -3 = ein Eintrag war fehlerhaft
 */
function removeDir($path) {
	if (!is_dir($path)) {
		return -1;
	}
	$dir = opendir($path);
	if (!$dir) {
		return -2;
	}
	while (($entry = readdir($dir)) !== false) {
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		if (is_dir($path.'/'.$entry)) {
			$res = removeDir($path.'/'.$entry);
			if ($res == -1) {
				closedir($dir);
				return -2;
			} elseif ($res == -2) {
				closedir($dir);
				return -2;
			} elseif ($res == -3) {
				closedir($dir);
				return -3;
			} elseif ($res != 0) {
				closedir($dir);
				return -2;
			}
		} elseif (is_file($path.'/'.$entry) || is_link ($path.'/'.$entry)) {
			$res = unlink($path.'/'.$entry);
			if (!$res) {
				closedir($dir);
				return -2;
			}
		} else {
			closedir($dir);
			return -3;
		}
	}
	closedir($dir);
	$res = rmdir($path);
	if (!$res) {
		return -2;
	}
	return 0;
}

/**
 * Konvertiert Zahlen in ein entsprechendes Ausgabeformat
 * @param integer $number Die Nummer
 * @param integer $zero [optional] Auffuellung der Zahlen von links nach rechts mit Nullen
 * @param integer $decimals [optional] Die Dezimalstellen
 * @return string
 */
function convert_number($number, $zero = 0, $decimals = 0)
{
	if (!empty($zero) && strlen($number) < $zero)
	{
		return str_pad($number, $zero, '0', STR_PAD_LEFT);
	}
	else
	{
		return number_format($number, $decimals, ',', '.');
	}
}

// Wandelt umlaute um
function text2html($str)
{
	$decode = array(196 => "&Auml;", 228 => "&auml;", 214 => "&Ouml;", 246 => "&ouml;", 220 => "&Uuml;", 252 => "&uuml;", 223 => "&szlig;");
	while(list($key, $val) = each($decode)){
		$str = str_replace("".chr("".$key."")."","".$val."",$str);
	}
	return $str;
}

/**
* Gibt unterschiedliche Medientypen aus
* @param integer $media_id Die Media-ID
* @param string $media_dir Media-Dir
* @param string $media_name Media-Name
* @param string $media_file Media-File
* @param string $media_path Download-Path
* @param boolean $media_dl Sollen die Download-Links angezeigt werden?
* @return string
*/
function getMediaPreview($media_id, $media_dir, $media_name, $media_file, $media_path, $media_dl = true) {
	// Array mit Dateinamen und Dateinamenerweiterung
	$exp_file = explode('.', $media_file);

	// existiert im Verzeichnis eine .flv Datei? Wenn ja dann den Flash-Player anzeigen
	if (file_exists($media_path.'/'.$exp_file[0].'.flv')) {
		$show_dl = ($media_dl) ? 'true' : 'false';
		$flashvar = URL_WWW.'/medias/flvplayer/flvplayer.swf?file='.URL_WWW.'/files/'.$media_dir.'/'.$exp_file[0].'.flv&amp;image='.URL_WWW.'/files/'.$media_dir.'/preview.jpg&amp;autostart=false&amp;showdownload='.$show_dl.'&amp;bufferlength=8&amp;link='.URL_WWW.'/download.php?id='.$media_id.'&amp;usekeys=true';
		$output = '<h3>Video</h3><div class="center">';
		$output .= '<object type="application/x-shockwave-flash" data="'.$flashvar.'" width="350" height="240">
		<param name="allowScriptAccess" value="sameDomain" />
		<param name="movie" value="'.URL_WWW.'/medias/flvplayer/flvplayer.swf?file='.URL_WWW.'/files/'.$media_dir.'/'.$exp_file[0].'.flv" />
		<param name="quality" value="high" />
		<param name="scale" value="noScale" />
		<param name="wmode" value="transparent" /></object>';
		if ($media_dl) {
			$output .= '<br /><a href="download.php?id='.$media_id.'">Video downloaden</a>';
		}
		$output .= '</div>';
		return $output;
	}

	global $db;

	// existiert ein Thumbnail? Wenn ja das anzeigen
	$sub = substr(strstr($media_file, '_'), 1);
	$prefix = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('THUMB_PREFIX', 'text').')'));
	if (file_exists($media_path.'/'.$prefix[1].$sub)) {
		$output = '<h3>Bild-Vorschau</h3><div class="center">';
		$info = getimagesize($media_path.'/'.$prefix[1].$sub);
		$output .= '<img src="'.URL_WWW.'/files/'.$media_dir.'/'.$prefix[1].$sub.'" width="'.$info[0].'" height="'.$info[1].'" alt="'.$media_name.'" />';
		unset($info);
		if ($media_dl) {
			$output .= '<br /><a href="download.php?id='.$media_id.'&amp;show=1">Download f&uuml;r Webmedien</a><br /><a href="download.php?id='.$media_id.'">Download f&uuml;r Printmedien</a>';
			$output .= '<p><strong>Hinweis:</strong> Wasserzeichen ist nur in der Vorschau enthalten</p>';
		}
		$output .= '</div>';
		return $output;
	}
	unset($prefix);

	// befindet sich eine MP3 in diesem Verzeichnis? Wenn ja dann MP3-Player anzeigen
	if (strtolower($exp_file[1]) == 'mp3') {
		$show_dl = ($media_dl) ? 'true' : 'false';
		$flashvar = URL_WWW.'/medias/flvplayer/flvplayer.swf?file='.URL_WWW.'/files/'.$media_dir.'/'.$media_file.'&amp;;autostart=false&amp;showdownload='.$show_dl.'&amp;link='.URL_WWW.'/download.php?id='.$media_id.'&amp;usekeys=true&amp;showeq=true';
		$output = '<h3>Audio (MP3)</h3><div class="center">';
		$output .= '<object type="application/x-shockwave-flash" data="'.$flashvar.'" width="350" height="70">
		<param name="allowScriptAccess" value="sameDomain" />
		<param name="movie" value="'.URL_WWW.'/medias/flvplayer/flvplayer.swf?file='.URL_WWW.'/files/'.$media_dir.'/'.$media_file.'" />
		<param name="quality" value="high" />
		<param name="scale" value="noScale" />
		<param name="wmode" value="transparent" /></object>';
		if ($media_dl) {
			$output .= '<br /><a href="download.php?id='.$media_id.'">Audio downloaden</a>';
		}
		$output .= '</div>';
		return $output;
	}

	// Wenn MIME-Type application oder text ist dieses als Script behandeln
	$mime = $db->extended->getOne('SELECT media_type FROM '.TAB_MEDIAS.' WHERE (id = '.$db->quote($media_id, 'integer').')');
	$exp_mime = explode('/', $mime);
	if (($exp_mime[0] == 'application' || $exp_mime[0] == 'text') && $media_dl) {
			$output = '<h3>Download</h3>
			<ul>
				<li><a href="download.php?id='.$media_id.'">Script &ouml;ffnen</a></li>
				<li><a href="index.php?module=order&amp;id='.$media_id.'">Script bestellen</a></li>
			</ul>';
			return $output;
	}
	return '';
}

/**
* Gibt die Dateinamenerweiterung zurueck
* @param string $file Der Name der Datei
* @return string
*/
function getFileExt($file) {
	return trim(strtolower(strrchr($file, '.')));
}

/**
* Gibt ein Array zurueck welches Informationen zu einem Thumbnail enthaelt
* @param string $dir Das Verzeichnis (ohne "/" am Ende)
* @param string $file Der Dateiname
* @return array [noerr] => true|false , [url] => URL zum Thumbnail , [w] => Breite , [h] => Hoehe
*/
function getThumbnailInfos($dir, $file) {
	global $prefix;
	$arr = array();
	$arr['noerr'] = false;

	$abs = PATH_ABS.'/files/'.$dir;

	// auf normales Thumbnail pruefen
	$thumb = $abs.'/'.$prefix[1].substr(strstr($file, '_'), 1);;
	if (file_exists($thumb)) {
		$info = getimagesize($thumb);
		$arr['w'] = $info[0];
		$arr['h'] = $info[1];
		unset($info);
		$arr['url'] = URL_WWW.'/files/'.$dir.'/'.$prefix[1].substr(strstr($file, '_'), 1);
		$arr['noerr'] = true;
	}
	unset($thumb);

	// auf Video-Thumbnail pruefen wenn kein normales Thumbnail gefunden wurde
	if ((bool)$arr['noerr'] === false) {
		$movie = substr($file, 0, strlen($file)-strlen(getFileExt($file))).'.jpg';
		$thumb = $abs.'/'.$prefix[1].$movie;
		if (file_exists($thumb)) {
			$info = getimagesize($thumb);
			$arr['w'] = $info[0];
			$arr['h'] = $info[1];
			unset($info);
			$arr['url'] = URL_WWW.'/files/'.$dir.'/'.$prefix[1].$movie;
			$arr['noerr'] = true;
		}
	}
	return $arr;
}
?>