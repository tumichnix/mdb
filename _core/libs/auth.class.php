<?php
/**
 * Steuert die Authentifizierung von Usern
 * PHP version 5
 * @author Hannes Becker <hannesbecker@googlemail.com>
 * @version 1.0
 * @package Mediendatenbank
 */

class Auth
{
	/**
	 * Instanz der Datenbank-Klasse
	 * @access private
	 * @var object
	 */
	private $db;

	/**
	 * Instanz der Session-Klasse
	 * @access private
	 * @var object
	 */
	private $session;

	/**
	 * Der Salt-Hash
	 * @access protected
	 * @var string
	 */
	protected $salt = 'dps2hir5r1QlH6CIXvHwflPgZavrx8NGgKnYyumEKiIqOGvKaWNbFjuQqXrGFraHJ';

	/**
	 * Konstruktor
	 * @access public
	 * @param object $db Instanz der Datenbank-Klasse
	 * @param object $session Instanz der Session-Klasse
	 * @param string $salt Der Salt-Hash
	 * @return void
	 */
	public function __construct($db, $session)
	{
		$this->db = $db;
		$this->session = $session;
	}

	/**
	 * Gibt den Salt-Hash zurueck
	 * @access public
	 * @return string
	 */
	public function getSaltHash()
	{
		return $this->salt;
	}

	/**
	 * Prueft ob der Login noch gueltig ist
	 * @access public
	 * @return boolean
	 */
	public function confAuth()
	{
		
		if ((string)$this->session->getSession(SESSION_IP) === (string)getenv("REMOTE_ADDR"))
		{
			$user_id = (int)$this->session->getSession(SESSION_USERID);
			$hash = (string)$this->session->getSession(SESSION_HASH);
			$result = $this->db->extended->getRow("SELECT COUNT(id) as num_user, (SELECT COUNT(session_id) FROM ".TAB_SESSIONS." WHERE (session_id = ".$this->db->quote($this->session->getSID(), 'text')." AND user_id = ".$this->db->quote($user_id, 'integer').")) as num_session FROM ".TAB_USERS." WHERE (id = ".$this->db->quote($user_id, 'integer')." AND MD5(user_pwd) = '".md5($this->db->quote($hash, 'text', false))."') GROUP BY id", NULL, NULL, NULL, MDB2_FETCHMODE_ASSOC);
			if ((int)$result['num_user'] === 1 && (int)$result['num_session'] === 1)
			{
				$this->db->query('UPDATE '.TAB_SESSIONS.' SET session_time = '.$this->db->quote(time(), 'integer').' WHERE (session_id = '.$this->db->quote($this->session->getSID(), 'text').' AND user_id = '.$this->db->quote($user_id, 'integer').')');
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	/**
	 * Setzt die Sessions nach erfolgreichem Login
	 * @access private
	 * @param integer $uid Die User-Id
	 * @param string $hash Der Passwort-Hash
	 * @return void
	 */
	private function setAuth($uid, $hash)
	{
		$ip = @getenv("REMOTE_ADDR");
		$this->session->newSessionID();
		$this->session->setSession(SESSION_IP, $ip);
		$this->session->setSession(SESSION_USERID, (int)$uid);
		$this->session->setSession(SESSION_HASH, (string)$hash);
		$startSession = $this->db->query('INSERT INTO '.TAB_SESSIONS.' (session_id, user_id, session_start, session_time, ip) VALUES ('.$this->db->quote($this->session->getSID(), 'text').', '.$this->db->quote($uid, 'integer').', '.$this->db->quote(time(), 'integer').', '.$this->db->quote(time(), 'integer').', '.$this->db->quote($ip, 'text').')');
		unset($ip);
	}

	/**
	 * Prueft die Logindaten gegen die Daten aus der Datenbank
	 * @access public
	 * @param string $login Der Login-Name
	 * @param string $pwd Das unverschluesselte Passwort
	 * @return boolean
	 */
	public function login($login, $pwd)
	{
		$val_pwd = hash('sha256', $this->getSaltHash().$pwd);
		unset($pwd);
		$query = $this->db->query("SELECT COUNT(id), id, (SELECT COUNT(session_id) FROM ".TAB_SESSIONS." WHERE user_id = id) as num_sessions FROM ".TAB_USERS." WHERE (MD5(user_login) = '".md5($this->db->quote($login, 'text', false))."' AND MD5(user_pwd) = '".md5($this->db->quote($val_pwd, 'text', false))."') GROUP BY id");
		$result = $query->fetchRow(MDB2_FETCHMODE_ORDERED);
		$query->free();
		if ((int)$result[0] === 1)
		{
			if ((int)$result[2] != 0)
			{
				$this->db->query('DELETE FROM '.TAB_SESSIONS.' WHERE (user_id = '.$this->db->quote($result[1], 'integer').')');
			}
			$this->setAuth((int)$result[1], $val_pwd);
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Prueft ob der Benutzer ein Administrator ist
	 * @access public
	 * @return boolean
	 */
	public function isAdmin()
	{
		global $arr_user;
		if ((int)$arr_user['user_group'] === 2)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Prueft ob bereits eine Session vorhanden ist in der Datenbank
	* @access private
	* @param string $session_id Die Session-ID

	/**
	 * Logt den Benutzer wieder aus
	 * @access public
	 * @return void
	 */
	public function logout()
	{
		$this->db->query('DELETE FROM '.TAB_SESSIONS.' WHERE (session_id = '.$this->db->quote($this->session->getSID(), 'text').' AND user_id = '.$this->db->quote($this->session->getSession(SESSION_USERID), 'integer').')');
		$this->session->delSession(SESSION_IP);
		$this->session->delSession(SESSION_USERID);
		$this->session->delSession(SESSION_HASH);
	}
}
?>