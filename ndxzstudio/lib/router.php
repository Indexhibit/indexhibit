<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* Router class
*
* Helps us get from url to the correct class and method
* 
* @version 1.0
* @author Vaska 
*/
class Router extends Core
{
	public $method;
	public $go;

	/**
	* Returns $go array from $_GET values and validates
	*
	* @param void
	* @return array
	*/
	public function __construct()
	{
		// don't access this space directly
		// work above or below it
		parent::__construct();
		
		// hackish so the front end will work
		$this->auto_load();
		
		// from entrance helper - sets defaults
		directions(); 
		
		// global $go of default $_GET values
		// assign to router
		global $go;
		$this->go = $go;
		
		//$OBJ =& get_instance();
		$this->vars->route = $go;
		
		$this->check_routes();
	}
	
	/**
	* Returns null or loads error procedure
	*
	* @param void
	* @return mixed
	*/
	public function check_routes()
	{
		global $go, $default;
		
		$modules = array();

		// core
		if ($fp = @opendir('module')) 
		{
			while (($module = readdir($fp)) !== false)
			{
				if ((!preg_match("/^_/i",$module)) && (!preg_match("/^CVS$/i",$module)) && (!preg_match("/.php$/i",$module)) && (!preg_match("/.html$/i",$module)) && (!preg_match("/.DS_Store/i",$module)) && (!preg_match("/\./i",$module)))
				{      
					$modules[] = $module;
				}
			} 
		} 

		if ($fp) closedir($fp); 
		sort($modules);
		
		// check if the 'class' route exists - default
		if (!in_array($go['a'], $modules)) show_error('router err 1');
		
		return;
	}
	
	
	/**
	* Return boolean
	*
	* @param array $method
	* @return boolean
	*/
	// review this later
	public function get_method($methods)
	{
		if ((!is_array($_POST)) || (!is_array($methods))) return FALSE;
		
		foreach ($methods as $method)
		{
			if (isset($_POST[$method]))
			{
				$this->method = $method;
				return TRUE;
			}
		}
	}
	
	
	/**
	* Returns callback'd function results
	*
	* @param array $methods
	* @param array $library
	* @return string
	*/
	public function posted($methods, $library)
	{
		if (isset($_POST) && $this->get_method($library))
		{
			if (method_exists($methods, 'sbmt_' . $this->method)) 
			{
				return call_user_func(array(&$methods, 'sbmt_' . $this->method));
			}
		}
		else
		{
			return;
		}
	}
	
	
	/**
	* Returns callback or error
	*
	* @param object $INDX
	* @param string $class
	* @param string $method
	* @return mixed
	*/
	// where do we want to go now?
	public function tunnel(&$INDX, $class, $method)
	{
		$OBJ =& get_instance();
		
		// it won't always be the last!
		$temp = array_pop($OBJ->is_loaded);
			
		// check and process submits?
		$OBJ->$temp->_submit();
			
		if (method_exists($OBJ->$temp, 'page_' . $method))
		{
			call_user_func(array(&$OBJ->$temp, 'page_' . $method), null);
		}
		else
		{
			// error
			show_error('router err 1');
		}
	}
}


?>