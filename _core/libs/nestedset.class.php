<?php
/**
 * Klasse zur Verwaltung von NestedSets
 * PHP version 5
 * @author Hannes Becker <hannesbecker@googlemail.com>
 * @version 0.8
 * @package Mediendatenbank
 */

class NestedSet
{
	/**
	 * Das Datenbank-Objekt (PEAR:MDB2)
	 * @access private
	 * @var object
	 */
	private $db;

	/**
	 * Der Tabellen-Name
	 * @access private
	 * @var string
	 */
	private $table;

	/**
	 * ID-Spalte
	 * @access private
	 * @var string
	 */
	private $col_id;

	/**
	 * Root-ID-Spalte
	 * @access private
	 * @var string
	 */
	private $col_rootid;

	/**
	 * Name-Spalte
	 * @access private
	 * @var string
	 */
	private $col_name;

	/**
	 * LFT-Spalte
	 * @access private
	 * @var string
	 */
	private $col_lft;

	/**
	 * RFT-Spalte
	 * @access private
	 * @var string
	 */
	private $col_rft;

	/**
	 * Konstruktor
	 * @access public
	 * @param object $db Instanz der Datenbank (PEAR:MDB2)
	 * @param array $options Array mit den Optionen
	 * @example array('tab' => 'sql_table', 'id' => 'col_id', 'rootid' =>  'col_rootid', 'name' => 'col_name', 'lft' => 'col_lft', 'rft' => 'col_rft')
	 * @return void
	 */
	public function __construct($db, $options)
	{
		$this->db = $db;
		$this->table = $options['tab'];
		$this->col_id = $options['id'];
		$this->col_rootid = $options['rootid'];
		$this->col_name = $options['name'];
		$this->col_lft = $options['lft'];
		$this->col_rft = $options['rft'];
	}

	/**
	 * Gibt die root_id der letzten erstellten Hauptwurzel zurueck
	 * @access private
	 * @return integer
	 */
	private function getLastRootNodeId()
	{
		$query = $this->db->query('SELECT COUNT('.$this->col_id.'), '.$this->col_rootid.' FROM '.$this->table.' GROUP BY '.$this->col_id.' ORDER BY '.$this->col_rootid.' DESC');
		$data = $query->fetchRow(MDB2_FETCHMODE_ORDERED);
		$query->free();
		return ((int)$data[0] === 0) ? 0 : $data[1];
	}

	/**
	 * Erstellt einen neuen Hauptwurzel-Knoten
	 * @access public
	 * @param string $name Der Name der neuen Hauptwurzel
	 * @param integer $view Key des Darstellungsarrays
	 * @return void
	 */
	public function addRootNode($name, $view = 1)
	{
		$new_root_id = $this->getLastRootNodeId() + 1;
		$sth = $this->db->prepare('INSERT INTO '.$this->table.' VALUES (?, ?, ?, ?, ?, ?)', array('integer', 'integer', 'text', 'integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
		$sth->execute(array(NULL, $new_root_id, $name, 1, 2, $view));
		unset($sth);
		unset($new_root_id);
	}

	/**
	 * Entfernt eine ganze Hauptwurzel
	 * @access public
	 * @param integer $id Die ID der Hauptwurzel
	 * @return void
	 */
	public function delRootNode($id)
	{
		$root_id = $this->db->extended->getOne('SELECT '.$this->col_rootid.' FROM '.$this->table.' WHERE '.$this->col_id.' = '.$this->db->quote($id, 'integer').'');
		$this->db->query('DELETE FROM '.$this->table.' WHERE '.$this->col_rootid.' = '.$this->db->quote($root_id, 'integer').'');
		$this->db->query('DELETE FROM '.$this->table.' WHERE '.$this->col_id.' = '.$this->db->quote($id, 'integer').'');
	}

	/**
	 * Stellt fest ob es sich um eine Hauptwurzel handelt
	 * @access public
	 * @param integer $node_id Die Node-Id
	 * @return boolean
	 */
	public function isRootNode($node_id)
	{
		$query = $this->db->query('SELECT '.$this->col_lft.' FROM '.$this->table.' WHERE '.$this->col_id.' = '.$this->db->quote($node_id, 'integer').'');
		$data = $query->fetchRow(MDB2_FETCHMODE_ORDERED);
		$query->free();
		return ((int)$data[0] === 1) ? true : false;
	}

	/**
	 * Legt einen neuen Node an
	 * @access public
	 * @param integer $parent_id Die ID des Elternknotenpunktes
	 * @param string $name Der Name des neuen Nodes
	 * @return void
	 */
	public function addNode($parent_id, $name)
	{
		$query = $this->db->query('SELECT '.$this->col_rootid.', '.$this->col_lft.', '.$this->col_rft.' FROM '.$this->table.' WHERE '.$this->col_id.' = '.$this->db->quote($parent_id, 'integer').'');
		$data = $query->fetchRow(MDB2_FETCHMODE_ORDERED);
		$query->free();
		$this->lockTable();
		$this->db->query('UPDATE '.$this->table.' SET '.$this->col_lft.' =  '.$this->col_lft.' + 2 WHERE '.$this->col_rootid.' = '.$this->db->quote($data[0], 'integer').' AND '.$this->col_lft.' > '.$this->db->quote($data[2], 'integer').'');
   		$this->db->query('UPDATE '.$this->table.' SET '.$this->col_rft.' = '.$this->col_rft.' + 2 WHERE '.$this->col_rootid.' = '.$this->db->quote($data[0], 'integer').' AND '.$this->col_rft.' >= '.$this->db->quote($data[2], 'integer').'');
   		$rft_new = (int)$data[2] + 1;
		$sth = $this->db->prepare('INSERT INTO '.$this->table.' VALUES (?, ?, ?, ?, ?, ?)', array('integer', 'integer', 'text', 'integer', 'integer', 'integer'), MDB2_PREPARE_MANIP);
		$sth->execute(array(NULL, $data[0], $name, $data[2], $rft_new, 1));
		unset($sth);
		$this->unlockTable();
	}

	/**
	 * Verschiebt ein Node nach oben
	 * @access public
	 * @param integer $node_id
	 * @return boolean
	 */
	public function moveNode($node_id)
	{
		$node_id = (int)$node_id;
		if ($node_id > 0)
		{
			$query = $this->db->query('SELECT n1.'.$this->col_rootid.', n1.'.$this->col_lft.', n1.'.$this->col_rft.', n2.'.$this->col_lft.', n2.'.$this->col_rft.' FROM '.$this->table.' AS n1 LEFT OUTER JOIN '.$this->table.' AS n2 ON (n1.'.$this->col_lft.' = (n2.'.$this->col_rft.'+1) AND n1.'.$this->col_rft.' > n2.'.$this->col_rft.' AND n1.'.$this->col_rootid.' = n2.'.$this->col_rootid.') WHERE n1.'.$this->col_id.' = '.$this->db->quote($node_id, 'integer').'');
			$data = $query->fetchRow(MDB2_FETCHMODE_ORDERED);
			$query->free();
			unset($query);
			$root_id = (int)$data[0];
			$desc = (int)$data[1]-(int)$data[3];
			$inc = (int)$data[2]-(int)$data[4];
			$this->db->query("UPDATE $this->table SET $this->col_lft = $this->col_lft + IF($this->col_lft < $data[1], $inc, -$desc), $this->col_rft = $this->col_rft + IF($this->col_rft < $data[1], $inc, -$desc) WHERE $this->col_rootid = ".$this->db->quote($root_id, 'integer')." AND $this->col_lft >= ".$this->db->quote($data[3], 'integer')." AND $this->col_rft <= ".$this->db->quote($data[2], 'integer')."");
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Entfernt einen Teilbaum (inc. deren Kinder)
	 * @access public
	 * @param integer $node_id Die Node-ID
	 * @return boolean
	 */
	public function delNode($node_id)
	{
		$node_id = (int)$node_id;
		if ($node_id > 0)
		{
			$this->lockTable();
			$query = $this->db->query('SELECT '.$this->col_rootid.', '.$this->col_lft.', '.$this->col_rft.' FROM '.$this->table.' WHERE '.$this->col_id.' = '.$this->db->quote($node_id, 'integer').'');
			$data = $query->fetchRow(MDB2_FETCHMODE_ASSOC);
			$query->free();

			$move = floor(($data[$this->col_rft]-$data[$this->col_lft])/2);
			$move = 2*(1+$move);

			if ((int)$data[$this->col_rootid] > 0)
			{
				$this->db->query('DELETE FROM '.$this->table.' WHERE '.$this->col_rootid.' = '.$this->db->quote($data[$this->col_rootid], 'integer').' AND '.$this->col_lft.' BETWEEN '.$data[$this->col_lft].' AND '.$data[$this->col_rft].'');
				$this->db->query('UPDATE '.$this->table.' SET '.$this->col_lft.' = '.$this->col_lft.'-'.$move.' WHERE '.$this->col_rootid.' = '.$this->db->quote($data[$this->col_rootid], 'integer').' AND '.$this->col_lft.' > '.$this->db->quote($data[$this->col_rft], 'integer').'');
				$this->db->query('UPDATE '.$this->table.' SET '.$this->col_rft.' = '.$this->col_rft.'-'.$move.' WHERE '.$this->col_rft.' > '.$this->db->quote($data[$this->col_rft], 'integer').' AND '.$this->col_rootid.' = '.$this->db->quote($data[$this->col_rootid], 'integer').'');
				$this->unlockTable();
				return true;
			}
			else
			{
				$this->unlockTable();
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Loescht ein Kind
	 * @access public
	 * @param integer $node_id Die Node-ID
	 * @return boolean
	 */
	public function delChild($node_id)
	{
		$node_id = (int)$node_id;
		if ($node_id > 0)
		{
			$this->lockTable();
			$query = $this->db->query('SELECT '.$this->col_rootid.', '.$this->col_lft.', '.$this->col_rft.' FROM '.$this->table.' WHERE '.$this->col_id.' = '.$this->db->quote($node_id, 'integer').'');
			$data = $query->fetchRow(MDB2_FETCHMODE_ASSOC);
			$query->free();
			$this->db->query('DELETE FROM '.$this->table.' WHERE '.$this->col_id.' = '.$this->db->quote($node_id, 'integer').'');
			$this->db->query('UPDATE '.$this->table.' SET '.$this->col_lft.' = '.$this->col_lft.' - 2 WHERE '.$this->col_rootid.' = '.$this->db->quote($data[$this->col_rootid], 'integer').' AND '.$this->col_lft.' > '.$this->db->quote($data[$this->col_rft], 'integer').'');
			$this->db->query('UPDATE '.$this->table.' SET '.$this->col_rft.' = '.$this->col_rft.' - 2 WHERE '.$this->col_rootid.' = '.$this->db->quote($data[$this->col_rootid], 'integer').' AND '.$this->col_rft.' > '.$this->db->quote($data[$this->col_rft], 'integer').'');
			$this->unlockTable();
			return true;
		}
		else
		{
			$this->unlockTable();
			return false;
		}
	}

	/**
	 * Gibt ein Array mit allen Informationen zur Darstellung des Baumes zurueck
	 * @access public
	 * @return array (0 => ID, 1 => ROOT-ID, 2 => NAME, 3 => LFT, 4 => RFT,  5=> CHILDS, 6 => LEVEL, 7 => LOWER, 8 =>UPPER, 9 => PARENT-ID)
	 */
	public function getTree()
	{
		$arr_nodes = array();
		$sql= 'SELECT 
			n.'.$this->col_id.',
			n.'.$this->col_rootid.', 
			n.'.$this->col_name.', 
			n.'.$this->col_lft.', 
			n.'.$this->col_rft.', 
			ROUND((n.'.$this->col_rft.'-n.'.$this->col_lft.'-1)/2,0) AS childs, COUNT(*)+(n.'.$this->col_lft.'>1) AS level, 
			((MIN(p.'.$this->col_rft.')-n.'.$this->col_rft.'-(n.'.$this->col_lft.'>1))/2) > 0 AS lower, 
			(((n.'.$this->col_lft.'-MAX(p.'.$this->col_lft.')>1))) AS upper,
			IF(n.'.$this->col_lft.' = 1, 0, (SELECT 
												MAX('.$this->col_id.')
											FROM 
												'.$this->table.' 
											WHERE 
												'.$this->col_rootid.' = n.'.$this->col_rootid.'
												AND '.$this->col_lft.' < n.'.$this->col_lft.'
												AND '.$this->col_rft.' > n.'.$this->col_rft.')) AS parentid
		FROM 
			'.$this->table.' n, 
			'.$this->table.' p
		WHERE 
			n.'.$this->col_lft.' 
			BETWEEN p.'.$this->col_lft.' 
			AND p.'.$this->col_rft.' 
			AND (p.'.$this->col_rootid.' = n.'.$this->col_rootid.') 
			AND (p.'.$this->col_id.' != n.'.$this->col_id.' 
			OR n.'.$this->col_lft.' = 1) 
		GROUP BY 
			n.'.$this->col_rootid.', 
			n.'.$this->col_id.' 
		ORDER BY 
			n.'.$this->col_rootid.', 
			n.'.$this->col_lft.'';		
		$query = $this->db->query($sql);
		if (PEAR::isError($query)) {
			die($query->getMessage());
		}
		while ($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED)) {
			$arr_nodes[] = $row;
		}
		$query->free();
		return $arr_nodes;
	}
	
	/**
	 * Erstellt ein Array der Form NODE_ID => NAME zur spaeteren Weiterverarbeitung
	 * @access public
	 * @param integer $root_id Die root_id einer Hauptwurzel, wenn 0 werden alle Hauptwurzeln ausgegeben
	 * @param boolean $format Wenn true dann findet eine Einrueckung (Baumansicht) statt
	 * @return array
	 */
	public function getTreeArray($root_id = 0, $format = true)
	{
		$arr_nodes = array();
		$fill = '&mdash;';
		if ((int)$root_id === 0)
		{
			$roots_result = $this->db->query('SELECT '.$this->col_rootid.' FROM '.$this->table.' WHERE '.$this->col_lft.' = '.$this->db->quote('1', 'integer').' ORDER BY '.$this->col_name.' ASC');
			while ($roots = $roots_result->fetchRow(MDB2_FETCHMODE_ORDERED))
			{
				$tree_result = $this->db->query('SELECT n.'.$this->col_id.', n.'.$this->col_name.', COUNT(*)-1 AS level FROM '.$this->table.' AS n, '.$this->table.' AS p WHERE n.'.$this->col_rootid.' = '.$this->db->quote($roots[0], 'integer').' AND p.'.$this->col_rootid.' = '.$this->db->quote($roots[0], 'integer').' AND n.'.$this->col_lft.' BETWEEN p.'.$this->col_lft.' AND p.'.$this->col_rft.' GROUP BY n.'.$this->col_lft.' ORDER BY n.'.$this->col_lft.' ASC');
				while($row = $tree_result->fetchRow(MDB2_FETCHMODE_ORDERED))
				{
					$str = ((bool)$format) ? $this->getStringXTimes($fill, $row[2]).$row[1] : $row[1];
					$arr_nodes[$row[0]] = $str;
				}
				$tree_result->free();
			}
			$roots_result->free();
		}
		else
		{
			$tree_result = $this->db->query('SELECT n.'.$this->col_id.', n.'.$this->col_name.', COUNT(*)-1 AS level FROM '.$this->table.' AS n, '.$this->table.' AS p WHERE n.'.$this->col_rootid.' = '.$this->db->quote($root_id, 'integer').' AND p.'.$this->col_rootid.' = '.$this->db->quote($root_id, 'integer').' AND n.'.$this->col_lft.' BETWEEN p.'.$this->col_lft.' AND p.'.$this->col_rft.' GROUP BY n.'.$this->col_lft.' ORDER BY n.'.$this->col_lft.' ASC');
			while($row = $tree_result->fetchRow(MDB2_FETCHMODE_ORDERED))
			{
				$str = ((bool)$format) ? $this->getStringXTimes($fill, $row[2]).$row[1] : $row[1];
				$arr_nodes[$row[0]] = $str;
			}
			$tree_result->free();
		}
		return $arr_nodes;
	}
	
	/**
	* Erstellt eine sogenannte Breadcrumb-Navigation
	* @access public
	* @param integer $id Die ID von der aus die Nvaigation zurueck verfolgt werden soll (meistens die aktuelle ID)
	* @param boolean $blank Dei true wird der letzte (der aktuelle) Navigations-Punkt nicht mit angezeigt
	* @param string $url Die URL mit welcher verlinkt werden soll (wenn leer dann keine Verlinkung)
	* @param string $sep Der Seperator zwischen den einzelnen Eintraegen
	* @return string
	*/
	public function getBreadcrumb($id, $blank = true, $url = '', $sep = '&raquo;') {
		$root_id = $this->db->extended->getOne('SELECT 
			'.$this->col_rootid.' 
		FROM
			'.$this->table.'
		WHERE 
			'.$this->col_id.' = '.$this->db->quote($id, 'integer').'');
		if (PEAR::isError($root_id)) {
			die($root_id->getMessage());
		}		
		$query = $this->db->query('SELECT
			b.'.$this->col_id.',
			b.'.$this->col_name.'
		FROM 
			'.$this->table.' AS a, 
			'.$this->table.' AS b
		WHERE
			b.'.$this->col_lft.' <= a.'.$this->col_lft.'
			AND b.'.$this->col_rft.' >= a.'.$this->col_rft.'
			AND a.'.$this->col_id.' = '.$this->db->quote($id, 'integer').'
			AND b.'.$this->col_rootid.' = '.$this->db->quote($root_id, 'integer').'
		ORDER 
			BY b.'.$this->col_lft.'');
		if (PEAR::isError($query)) {
			die($query->getMessage());
		}
		$tmp = '';
		while($row = $query->fetchRow(MDB2_FETCHMODE_ORDERED)) {
			if ((int)$row[0] === (int)$id && (bool)$blank) {
			} else {
				if (empty($url) || $id != $row[0]) {
					$tmp .= $row[1];
				} else {
					$tmp .= '<a href="'.$url.$row[0].'">'.$row[1].'</a>';
				}
				$tmp .= ' '.$sep.' ';
			}
		}
		$query->free();
		unset($query);
		$breadcrumb = substr($tmp, 0, (strlen($tmp)-strlen($sep)-2));
		unset($tmp);
		return $breadcrumb;
	}

	/**
	 * Speert die Tabelle zum schreiben
	 * @access private
	 * @return void
	 */
	private function lockTable()
	{
		$this->db->query('LOCK TABLES '.$this->table.' WRITE');
	}

	/**
	 * Entsperrt die Tabelle wieder
	 * @access private
	 * @return void
	 */
	private function unlockTable()
	{
		$this->db->query('UNLOCK TABLES');
	}

	/**
	 * Wiederholt einen String so oft wie angegeben
	 * @access private
	 * @param string $var Der zu wiederholende String
	 * @param integer $x Wie oft soll wiederholt werden
	 * @return string
	 */
	private function getStringXTimes($var, $x)
	{
		$string = '';
		$x = (int)$x;
		for ($i = 0; $i < $x; $i++) $string .= $var;
		return $string;
	}
}
?>