<?php
/**
 * Erzeugung von Formularfeldern und Validierung dieser
 * PHP version 5
 * @author Hannes Becker <hannesbecker@googlemail.com>
 * @version 0.8
 * @package Mediendatenbank
 */
class Form
{
	/**
	 * Sammeln der Fehlermeldungen
	 * @access private
	 * @var array
	 */
	private $error_msgs;

	/**
	 * Die verschiedenen Element-Typen
	 * @access protected
	 * @var array
	 */
	protected $elements = array('file', 'hidden', 'password', 'reset', 'select', 'submit', 'text', 'textarea');

	/**
	 * Die Validierungs-Optionen
	 * @access protected
	 * @var array
	 */
	protected $validations = array('alphanumeric', 'compare', 'email', 'maxlength', 'minlength', 'numeric', 'regex', 'required');

	/**
	 * Prueft ob das Formular schon einmal gesendet wurde
	 * @access private
	 * @var boolean
	 */
	private $is_submit;

	/**
	 * Der verwendete Zeichensatz
	 * @access protected
	 * @var string
	 */
	protected $charset = 'UTF-8';

	/**
	 * Klassen-Konstruktor
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->error_msgs = array();
		$this->is_submit = false;
	}

	/**
	 * Bereinigt eine Variable zur sicheren Verwendung
	 * @access public
	 * @param mixed $var Die Variable
	 * @param boolean $entities [optional] In HTML-Entities umwandeln
	 * @return mixed
	 */
	public function quote($var)
	{
		// Entfernen von Leerzeichen am Anfang und Ende
		$validate = trim($var);

		$validate = strip_tags($validate);

		// wenn MagicQuotes an ist die Variable von gesetzen Splahes bereinigen
		if (get_magic_quotes_gpc())
		{
			$validate = stripslashes($validate);
		}

		// wenn es sich um einen String handelt in HTML maskieren
		if (!is_numeric($validate) || $validate[0] == '0')
		{
			$validate = htmlentities($validate, ENT_QUOTES, "$this->charset");
		}

		return $validate;
	}

	/**
	 * Validiert die Namen/Ids der Formularfelder
	 * @access private
	 * @param string $name Der Name/Id des Feldes
	 * @return string
	 */
	private function validateName($name)
	{
		$name = strtolower(trim($name));
		return preg_replace("#[^a-z0-9_]#",'', $name);
	}

	/**
	 * Erzeugt ein HTML-Formular-Element
	 * @access public
	 * @param string $element Der Element Type
	 * @param string $name Der Name bzw. die ID des Feldes
	 * @param string $desc Der Beschreibungstext
	 * @param mixed $value [optional] Der Vorgabewert
	 * @param array $options [optional] Array mit den Optionen fuer Select (options) bzw Textareas (cols, rows, readonly)
	 * @return string
	 */
	public function addElement($element, $name, $desc, $value = NULL, $options = array())
	{
		$element = strtolower($element);
		if (!in_array($element, $this->elements))
		{
			return 'Wrong element-type!';
		}
		$ro = (array_key_exists('readonly', $options)) ? 'readonly' : '';
		$name = $this->validateName($name);
		$html = '<label for="'.$name.'">'.$desc.'</label>';
		$value = ($this->is_submit) ? $this->getSubmitVar($name) : $this->quote($value);
		switch ($element)
		{
			case 'file':
				$html .= '<input type="file" name="'.$name.'" id="'.$name.'" size="50" />';
			break;
			case 'hidden':
				return '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'" />';
			break;
			case 'password':
				$html .= '<input type="password" name="'.$name.'" id="'.$name.'" />';
			break;
			case 'reset':
				return '<input type="reset" name="'.$name.'" id="'.$name.'" class="button" value="'.$desc.'" />';
			break;
			case 'select':
				if (!is_array($options))
				{
					return 'Kein Array mit den Optionen angegeben';
				}
				$html .= '<select name="'.$name.'" id="'.$name.'">';
				while (list($key, $val) = each($options))
				{
					$selected = ($key == $value) ? 'selected' : '';
					$html .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
				}
				return $html .= '</select>';
			break;
			case 'submit':
				return '<input type="submit" name="'.$name.'" id="'.$name.'" class="button" value="'.$desc.'" />';
			break;
			case 'text':
				$html .= '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$ro.' />';
			break;
			case 'textarea':
				$cols = (array_key_exists('cols', $options)) ? (int)$options['cols'] : 15;
				$rows = (array_key_exists('rows', $options)) ? (int)$options['rows'] : 5;
				$html .= '<textarea name="'.$name.'" id="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" '.$ro.'>'.$value.'</textarea>';
			break;
		}
		return $html;
	}

	/**
	 * Erzeugt eine Regel
	 * @access public
	 * @param string $name Der Name bzw. die ID des Feldes
	 * @param string $msg Die Fehlernachricht
	 * @param string $validation Die Pruefungsmethode
	 * @param mixed $option Optionen fuer die Pruefungsmethode
	 * @return void
	 */
	public function addRule($name, $msg, $validation, $option = false)
	{
		$validation = strtolower($validation);
		if (!in_array($validation, $this->validations))
		{
			$this->error_msgs[] = 'Wrong validation method!';
		}
		// Die Variable holen
		$var = $this->getSubmitVar($name);
		switch ($validation)
		{
			case 'alphanumeric':
				if (!preg_match('/^[a-z0-9\-_[:space:]]+$/i', $var))
				{
					$this->setError($msg);
				}
			break;
			case 'compare':
				if ($var != $option)
				{
					$this->setError($msg);
				}
			break;
			case 'maxlength':
				if ((int)$option <= 0)
				{
					$this->setError('Wrong option on rule maxlength!');
				}
				if (strlen($var) > (int)$option)
				{
					$this->setError($msg);
				}
			break;
			case 'minlength':
				if ((int)$option <= 0)
				{
					$this->setError('Wrong option on rule minlength!');
				}
				if (strlen($var) < (int)$option)
				{
					$this->setError($msg);
				}
			break;
			case 'numeric':
				if (!preg_match('/^[0-9]+$/i', $var))
				{
					$this->setError($msg);
				}
			break;
			case 'regex':
				if (!preg_match($option, $var))
				{
					$this->setError($msg);
				}
			break;
			case 'required':
				if (empty($var))
				{
					$this->setError($msg);
				}
			break;
		}
	}

	/**
	 * Holt den Wert aus einer Formular-Variablen
	 * @access public
	 * @param string $name Der Feldname
	 * @return string
	 */
	public function getSubmitVar($name)
	{
		return (isset($_POST[$name])) ? $this->quote($_POST[$name]) : '';
	}

	/**
	 * Ueberprueft ob alle Regeln eingehalten wurden
	 * @access public
	 * @return boolean
	 */
	public function validate()
	{
		if (empty($this->error_msgs))
		{
			$this->is_submit = false;
			return true;
		}
		else
		{
			$this->is_submit = true;
			return false;
		}
	}

	/**
	 * Gibt die gesammelten Fehlermeldungen formatiert zurueck
	 * @access public
	 * @param string $css [optional] Die CSS-Klasse/ID
	 * @return string
	 */
	public function getErrors($css = 'error')
	{
		$get_errors = '<div class="error"><ul>';
		if (!$this->validate())
		{
			foreach ($this->error_msgs as $error_msg)
			{
				$get_errors .= '<li>'.$error_msg.'</li>';
			}
			$this->error_msgs = array();
		}
		else
		{
			$get_errors .= '<li>No errors!</li>';
		}
		return $get_errors.'</ul></div>';
	}

	/**
	 * Leer alle $_POST Variablen
	 * @access public
	 * @return void
	 */
	public function clearPost()
	{
		$_POST = array();
		unset($_POST);
	}

	/**
	 * Hinzufuegen einer Fehlermeldung
	 * @access public
	 * @param string $msg Die Fehlermeldung
	 * @param boolean $get_errors [optional] Sollen die Fehlermeldungen danach direkt ausgegeben werden?
	 * @return void / string
	 */
	public function setError($msg, $get_errors = false)
	{
		$this->error_msgs[] = $msg;
		if ((bool)$get_errors)
		{
			return $this->getErrors();
		}
	}

	/**
	 * Gibt eine Bestaetigungs-Meldung aus
	 * @access public
	 * @param string $msg Die Mitteilung
	 * @return string
	 */
	public function getMsg($msg)
	{
		return '<div id="message">'.$msg.'</div>';
	}

	/**
	 * Der Klassen-Descructor
	 * @access public
	 * @return void
	 */
	public function __destructor()
	{
		$this->clearPost();
		$this->is_submit = false;
		$this->error_msgs = array();
	}
}
?>