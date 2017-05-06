<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Database Class
*
* @version 1.0
* @author Paul Schrieber
* @author Vaska
*/

class Db
{
	var $theQuery;
	var $link;


	/**
	* Construct
	*
	* @param void
	* @return mixed
	*/
	public function __construct()
	{
		$this->initialize();
		$this->setNames();
		$this->setSqlMode();
	}


	/**
	* Return database connection
	*
	* @param void
	* @return mixed
	*/
	public function initialize()
	{
		global $indx;

		if (!$indx['host']) $this->db_out_of_order();

		$this->link = mysqli_connect($indx['host'], $indx['user'], $indx['pass']);

		if (!$this->link) $this->db_out_of_order();

		mysqli_select_db($this->link, $indx['db']);
		register_shutdown_function(array(&$this, 'close'));
	}


	/**
	* Returns query
	*
	* @param string $query
	* @return mixed
	*/
	public function query($query='')
	{
		$this->theQuery = $query;
		if (!$this->theQuery) return false;
		return mysqli_query($this->link, $this->theQuery);
	}


	/**
	* Sets the database to be utf-8
	*
	* @param void
	* @return null
	*/
	public function setNames()
	{
		$this->query("SET NAMES 'utf8'");
	}

    /**
     * Set SQL_MODE, needed to work with MySQL 5.6.
     */
	public function setSqlMode()
    {
        $this->query("SET SESSION sql_mode = 'ANSI'");
    }

	/**
	* Returns count
	* (are we even using this?)
	*
	* @param string $query
	* @return integer
	*/
	public function getCount($query='')
	{
		if ($rs = $this->query($query))
		{
			$row = $rs->fetch_row();
			mysqli_free_result($rs);
			return $row[0];
		}

		return 0;
	}


	/**
	* Returns array of records
	*
	* @param string $query
	* @return mixed
	*/
	public function fetchArray($query='')
	{
		$rs = $this->query($query);

		if ($rs) {
			if (mysqli_num_rows($rs) > 0)
			{
				while ($arr = mysqli_fetch_assoc($rs)) $out[] = $arr;
				return $out;
			}
		}

		return false;
	}


	/**
	* Returns array of record
	*
	* @param string
	* @return mixed
	*/
	public function fetchRecord($query='', $debug = false)
	{
		if ($debug == true) { echo $query; exit; }

		$rs = $this->query($query);

		if ($rs) {
			if (mysqli_num_rows($rs) > 0)
			{
				$arr = mysqli_fetch_assoc($rs);
				return $arr;
			}
		}

		return false;
	}


	/**
	* Returns id of inserted record
	*
	* @param string $query
	* @return mixed
	*/
	public function insertRecord($query)
	{
		if ($rs = $this->query($query))
		{
			$lastid = mysqli_insert_id($this->link);
			if ($lastid) return $lastid;
		}

		return false;
	}


	/**
	* Returns array of record(s)
	*
	* @param string $table
	* @param array $array
	* @param string $type
	* @param string $cols
	* @return mixed
	*/
	public function selectArray($table, $array, $type='array', $cols='')
	{
		$cols = ($cols == '') ? '*' : $cols;

		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				$select[] = "$key = " . $this->escape($value) . " ";
			}

			$query = "SELECT $cols FROM $table WHERE
				" . implode(' AND ', $select) . "";

			if ($type == 'array')
			{
				return $this->fetchArray($query);
			}
			else
			{
				return $this->fetchRecord($query);
			}
		}

		return false;
	}


	/**
	* Returns id of inserted record
	*
	* @param string $table
	* @param array $array
	* @return mixed
	*/
	public function insertArray($table, $array, $debug=false)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				$fields[] = $key;
				$values[] = $this->escape($value);
			}

			$query = "INSERT INTO $table
				(" . implode(', ', $fields) . ")
				VALUES
				(" . implode(', ', $values) . ")";
				
			//echo $query; exit;

			if ($debug == true) { echo $query; exit; }

			return $this->insertRecord($query);
		}

		return false;
	}


	/**
	* Returns boolean
	*
	* @param string $table
	* @param array $array
	* @param string $id
	* @return bool
	*/
	public function updateArray($table, $array, $id, $debug=false)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				$updates[] = "$key = " . $this->escape($value) . " ";
			}

			$query = "UPDATE $table SET
				" . implode(', ', $updates) . "
				WHERE $id";

			if ($debug == true) { echo $query; exit; }

			return $this->updateRecord($query);
		}

		return false;
	}


	/**
	* Returns boolean
	*
	* @param string $table
	* @param string $id
	* @return bool
	*/
	public function deleteArray($table, $id)
	{
		$query = "DELETE FROM $table WHERE $id";
		return $this->deleteRecord($query);
	}


	/**
	* Returns string
	*
	* @param string $str
	* @return string
	*/
	public function escape($str)
	{
		switch (gettype($str))
		{
			case 'string'	:	$str = "'" . $this->escape_str($str) . "'";
				break;
			case 'boolean'	:	$str = ($str === FALSE) ? 0 : 1;
				break;

			//review
			default			:	$str = (($str == NULL) || ($str == ''))  ? "''" : "'" . $this->escape_str($str) . "'";
				break;
		}

		return $str;
	}


	/**
	* Returns string
	*
	* @param string $str
	* @return string
	*/
	public function escape_str($str)
	{
		if (function_exists('get_magic_quotes_gpc'))
		{
			if (get_magic_quotes_gpc()) $str = stripslashes($str);
		}

		if (function_exists('mysqli_real_escape_string'))
		{
			return mysqli_real_escape_string($this->link, $str);
		}
		else
		{
			return addslashes($str);
		}
	}


	/**
	* Returns boolean
	*
	* @param string $query
	* @return bool
	*/
	public function deleteRecord($query)
	{
		if ($rs = $this->query($query))
		{
			return true;
		}

		return false;
	}


	/**
	* Returns boolean
	*
	* @param string $query
	* @return bool
	*/
	public function updateRecord($query='')
	{
		if ($rs = $this->query($query))
		{
			return true;
		}

		return false;
	}


	/**
	* Returns object - closes conenction
	*
	* @param void
	* @return objet
	*/
	public function close()
	{
		mysqli_close($this->link);
	}


	/**
	* Returns error
	*
	* @param void
	* @return string
	*/
	public function db_out_of_order()
	{
		show_error('Database is unavailable');
		exit;
	}

}