<?php
if ($auth->isAdmin()) {
	require_once './_core/libs/nestedset.class.php';
	$ns = new NestedSet($db, $nsOptions);
	echo('<h2>Neues Medium erstellen</h2>');
	echo('<div id="form">');
	require_once './_core/libs/form.class.php';
	$form = new Form;
	if (isset($_POST['send_add'])) {
		if (empty($_FILES['media_file']['tmp_name'])) {
			$form->setError('Bitte eine Datei ausw&auml;hlen!');
		}
		if ($form->validate()) {
			// Daten des Uploads sammeln
			$file_tmp_name = $_FILES['media_file']['tmp_name'];
        	$file_name = $_FILES['media_file']['name'];
        	$file_type = $_FILES['media_file']['type'];
        	$file_error = $_FILES['media_file']['error'];

			// Liste der erlaubten Datein erstellen und vergleichen
			$whitelist = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('WHITELIST_EXT', 'text').')'));

			// Dateinamenerweiterung ermitteln
			$ext = getFileExt($file_name);
			// Dateinamen setzen
			$filename = preg_replace("#[^a-zA-Z0-9-_.]#",'_', $file_name);

			// Pruefen ob Datenamenserweiterung verwendet werden darf und ob Upload erfolgreich war
        	if ($_FILES['media_file']['error'] == UPLOAD_ERR_OK && array_key_exists($ext, $whitelist)) {

				// Verzeichnisse erstellen und Datei hier her verschieben
				$dir = hash('sha256', uniqid(rand()));
				mkdir(PATH_ABS.'/files/'.$dir, 0777);
				$path_dl = PATH_ABS.'/files/'.$dir;
        		if (!@move_uploaded_file($file_tmp_name, $path_dl.'/'.$filename)) {
        			echo $form->setError('Datei konnte nicht in das Zielverzeichnis kopiert werden!', true);
        		} else {

					// Thumbnaildaten sammeln
					$arr_image = array();
        			$setup_query = 'SELECT data_name, data_integer, data_string FROM '.TAB_DATA;
					$setup_result = $db->query($setup_query);
					while ($row = $setup_result->fetchRow()) {
						$arr_image[$row['data_name']]['int'] = (int)$row['data_integer'];
						$arr_image[$row['data_name']]['str'] = (string)$row['data_string'];
					}
					$setup_result->free();
					unset($setup_result);
					$prefix = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('THUMB_PREFIX', 'text').')'));

					$media_tags = '';

					// ermitteln anhand der Dateinamenserweiterung was mit der hochgeladenen Datei passieren soll
					$actions = unserialize($db->extended->getOne('SELECT data_string FROM '.TAB_DATA.' WHERE (data_name = '.$db->quote('ACTIONS', 'text').')'));

					// Video erstellen
					if ($actions[$whitelist[$ext]] == $actions[2]) {
						$fn_no_ext = substr($filename, 0, strlen($filename)-strlen($ext));
						system("".PATH_ABS."/_core/shell/conv_movie $path_dl $filename $fn_no_ext");
						// Thumbnails ermitteln
						$movie_thumbs = array();
						$handel_dir = opendir($path_dl);
						while($file = readdir($handel_dir)) {
							if ($file != '.' && $file != '..' && getFileExt($file) == '.jpg') {
								$movie_thumbs[] = $file;
							}
						}
						closedir($handel_dir);
						shuffle($movie_thumbs);
						$random = mt_rand(0, count($movie_thumbs));
						$my_thumbnail = $movie_thumbs[$random];
						rename($path_dl.'/'.$my_thumbnail, $path_dl.'/preview.jpg');
						foreach ($movie_thumbs as $file) {
							if (file_exists($path_dl.'/'.$file)) {
								unlink($path_dl.'/'.$file);
							}
						}
						unset($movie_thumbs);
						// Thumbnail für die Thumbnail-Ansicht erstellen
						if (copy($path_dl.'/preview.jpg', $path_dl.'/'.$prefix[0].'preview.jpg')) {
							$tn = $prefix[0].'preview.jpg';
						}
						thumbnailJPEG($path_dl, $tn, $arr_image['THUMB_WIDTH']['int'], $arr_image['THUMB_HEIGHT']['int']);
						// nicht gebrauchte erstellte Bilder wieder entfernen
						unlink($path_dl.'/'.$prefix[0].'preview.jpg');
						unlink($path_dl.'/'.$prefix[2].'preview.jpg');
						rename($path_dl.'/'.$prefix[1].'preview.jpg', $path_dl.'/'.$prefix[1].$fn_no_ext.'.jpg');
						// Daten fuer die Datenbank neu setzen
						$media_file = $fn_no_ext.'.wmv';
						$media_name = $media_file;
						$media_typ = 'video/x-ms-wmv';
						$media_tags = '';
					}

					// Thumbnail erstellen nur bei JPEG/JPG moeglich
					if ($actions[$whitelist[$ext]] == $actions[1]) {
						$media_name = $filename;
						$media_typ = $file_type;

						if (@rename($path_dl.'/'.$filename, $path_dl.'/'.$prefix[0].$filename)) {
							$filename = $prefix[0].$filename;
						}
		   				thumbnailJPEG($path_dl, $filename, $arr_image['THUMB_WIDTH']['int'], $arr_image['THUMB_HEIGHT']['int']);
        				unset($arr_image);
						unset($prefix);
						$media_file = $filename;
						$media_tags = '';

						// IPTC-Keywords als Tags suchen und speichern
						if (function_exists('iptcparse')) {
							$iptc_info = getimagesize($path_dl.'/'.$media_file, $iptc_data);
							if (is_array($iptc_data) && array_key_exists('APP13', $iptc_data)) {
								$iptc = iptcparse($iptc_data["APP13"]);
								if (is_array($iptc['2#025'])) {
									foreach ($iptc['2#025'] as $val) {
										$media_tags .= $val.' ';
									}
									trim($media_tags);
								}
								unset($iptc);
							}
							unset($iptc_data);
							unset($iptc_info);
						}
					}

					// wenn keine Aktion durchgefuehrt werden soll
					if ($actions[$whitelist[$ext]] == $actions[0]) {
						$media_name = $filename;
						$media_file = $filename;
						$media_typ = $file_type;
						$media_tags = '';
					}

					// Daten in Datenbank schreiben
        			$insert = $db->query('INSERT INTO '.TAB_MEDIAS.' (user_id, media_access, media_name, media_dir, media_file, media_type, time_create, time_change, media_tags) VALUES ('.$db->quote($arr_user['id'], 'integer').', 0, '.$db->quote($media_name, 'text').', '.$db->quote($dir, 'text').', '.$db->quote($media_file, 'text').', '.$db->quote($media_typ, 'text').', '.$db->quote(time(), 'integer').', '.$db->quote(time(), 'integer').', '.$db->quote($media_tags, 'text').')');
					if (PEAR::isError($insert)) {
						echo $form->setError('Der Dateiname ist bereits vorhanden!', true);
					} else {
						$lastID = $db->lastInsertID(TAB_MEDIAS);
        				setLog('action', 'Media "'.$media_name.'" (ID: '.$db->lastInsertID(TAB_MEDIAS).') hochgeladen');
        				header('Location: index.php?module=secure_media_edit&id='.$lastID.'');
					}
        		}
        	} else  {
        		if ($_FILES['media_file']['error'] == UPLOAD_ERR_INI_SIZE) {
        			$form->setError('Die Datei ist gr&ouml;&szlig;er als der Webserver verarbeiten kann!');
        		}
        		if ($_FILES['media_file']['error'] == UPLOAD_ERR_PARTIAL) {
        			$form->setError('Das Hochladen der Datei wurde unerwartet abgebrochen!');
        		}
				if (!@array_key_exists($ext, $whitelist)) {
					$form->setError('Die Dateinamenserweiterung "'.$ext.'" ist nicht erlaubt!');
				}
        		echo $form->getErrors();
        	}
		} else {
			echo $form->getErrors();
		}
	} else {
		echo $form->getMsg('Hinweis<br />Das erstellen von gro&szlig;en Datein (z.B. Videos) ben&ouml;tigt etwas Zeit. Daher bitte nicht das Laden der Seite abbrechen!');
	}
	echo('<form method="post" action="index.php?module=secure_media_add" enctype="multipart/form-data">
	<fieldset>
		<legend>Neues Medium hochladen</legend>
		<ul>
			<li>'.$form->addElement('file', 'media_file', 'Datei').'</li>
			<li>'.$form->addElement('submit', 'send_add', 'Weiter').'</li>
		</ul>
	</fieldset>
	</form>
	</div>');
	unset($form);
	unset($ns);
} else {
	echo $msg_err['401'];
}
?>