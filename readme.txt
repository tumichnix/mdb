Mediendatenbank
===============


Vorrausetzungen
------------------

- PHP 5 oder hoeher
- MySQL 5 oder hoeher
- PEAR-Pakete: PEAR, MDB2, MDB2#mysql, Pager
- ffmpeg (inc. mp3-Support)


Installation
------------------

1) "./files" schreibbar machen fuer Webserver Benutzer
2) "./_core/database.inc.php" an den MySQL-Server anpassen
3) "./_core/path.inc.php" anpassen
4) "./_core/define.inc.php" anpassen (optional)
5) "./_core/shell/conv_movie" Ausfuerungsrechte als Benutzer des Webservers (i.d.R www-data) geben
6) im Browser die "./install.php" aufrufen
7) "./install.php" umbenennen oder entfernen
8) im Browser die "./index.php" aufrufen
9) sich freuen und hoffen das alles geklappt hat :)


Weiteres
------------------

- php.ini muss fuer große Uploads konfiguriert sein
  post_max_size => upload_max_filesize
  file_uploads = On
- PHP: system() - Befehl muss erlaubt sein
- das "./watermark.png" kann ersetzt werden - jedoch nur durch ein PNG und es sollte die selbe Groesse wie die Thumnails haben!
