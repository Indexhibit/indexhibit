<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* URI class
* 
* @version 1.0
* @author Vaska 
*/
class Uri
{
	public $uri;
	public $go;
	
	/**
	* Returns loaded database object or error
	*
	* @param void
	* @return array
	*/
	public function segments()
	{
		
	}	

	/**
	* Returns uri
	*
	* @param void
	* @return string
	*/	
	public function get_uri()
	{
		if (MODREWRITE == false)
		{
			$pos = strpos(strtolower($_SERVER['PHP_SELF']), 'index.php');
			
			$this->uri = (is_int($pos)) ?
				$this->uri = substr(strtolower($_SERVER['PHP_SELF']), $pos + strlen('index.php')) : 
				$this->uri = '/';
		}
		else
		{
			$this->uri = $_SERVER['PHP_SELF'];
		}
		
		echo $this->uri;
		
		if ($this->uri == '') $this->uri = '/';
	}
}

?>