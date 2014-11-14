<?php
/**
 * Einfache Verwaltung von Sessions und Cookies
 * PHP version 5
 * @author Hannes Becker <hannesbecker@googlemail.com>
 * @version 1.1
 * @package Mediendatenbank
 */
class Session
{
	/**
	 * Konstruktor
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		@session_start();
	}
	
	/**
	* Generiert eine neue Session-ID
	* @access public
	* @return void
	*/
	public function newSessionID()
	{
		session_regenerate_id(false);
	}	

	/**
	 * Setzt eine neue Session
	 * @access public
	 * @param string $name Der Name der Session
	 * @param mixed $value Der Inhalt
	 * @return void
	 */
	public function setSession($name, $value)
	{
		$_SESSION[$name] = trim($value);
	}

	/**
	 * Gibt den Inhalt einer Session zurueck
	 * @access public
	 * @param string $name Der Session Name
	 * @return string wenn Session existiert ansonsten boolean
	 */
	public function getSession($name)
	{
		return (isset($_SESSION[$name])) ? $_SESSION[$name] : false;
	}

	/**
	 * Loescht eine einzelne Session
	 * @access public
	 * @param string $name Der Name der Session
	 * @return void
	 */
	public function delSession($name)
	{
		unset($_SESSION[$name]);
	}

	/**
	 * Gibt die aktuelle Session-ID aus
	 * @access public
	 * @return string
	 */
	public function getSID()
	{
		return session_id();
	}

	/**
	 * Erstellt ein Cookie
	 * @access public
	 * @param string $name Name des Cookies
	 * @param string $value Der Inhalt des Cookies
	 * @param integer [optional] $lifetime Zeit in Sekunden wie lange der Cookie existiert
	 * @return void
	 */
	public function setCookie($name, $value, $lifetime = 259200)
	{
		if (!is_int($lifetime) || $lifetime <= 0)
		{
			$lifetime = 259200;
		}
		setcookie($name, $value, time()+$lifetime, "/");
	}

	/**
	 * Holt den Inhalt eines Cookies
	 * @access public
	 * @param string $name Name des Cookies
	 * @return string wenn der Cookie existiert ansonsten boolean
	 */
	public function getCookie($name)
	{
		return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : false;
	}

	/**
	 * Loescht einen Cookie
	 * @access public
	 * @param string $name Name des Cookies
	 * @return void
	 */
	public function delCookie($name)
	{
		if (isset($_COOKIE[$name]))
		{
			setcookie($name, '', time()-86400, "/");
		}
	}
}
?>