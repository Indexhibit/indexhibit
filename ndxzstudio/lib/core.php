<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Core class
*
* Loading tools
* 
* @version 1.0
* @author Vaska 
*/
class Core
{
	public $is_loaded;
	public $rs = array();
	public $template_override = false;
	public $image_file;
	public $image_ext;
	public $image_replace = false;
	public $temp_uri;
	public $baseurl;
	public $default;
	public $site_vars = array();
	
	/**
	* Returns loaded database object or error
	*
	* @param void
	* @return array
	*/
	public function __construct()
	{
		global $default;
		
		$this->default = $default;
		
		$this->load_db();
	}
	
	/**
	* Return language and core classes
	*
	* @param void
	* @return array
	*/
	public function auto_load()
	{
		$this->load_lang();
		$this->assign_core();
	}
	
	
	// testing
	public function unset_var($var, $item)
	{
		unset($this->default[$var][$item]);
	}
	
	
	/**
	* Returns core classes
	*
	* @param void
	* @return array
	*/
	public function assign_core()
	{
		foreach (array('vars', 'template', 'access', 'hook', 'abstracts') as $val)
		{
			$class = strtolower($val);
			if (!is_object($class)) $this->$class =& load_class($val, TRUE, 'lib');
			$this->is_loaded[] = $class;
		}
	}	
	
	/**
	* Returns core classes for front
	*
	* @param void
	* @return array
	*/
	public function assign_core_front()
	{
		foreach (array('vars', 'hook', 'abstracts') as $val)
		{
			$class = strtolower($val);
			if (!is_object($class)) $this->$class =& load_class($val, TRUE, 'lib');
			$this->is_loaded[] = $class;
		}
	}
	
	/**
	* Returns language file
	*
	* @param void
	* @return array
	*/
	public function load_lang()
	{
		$class = strtolower('lang');
		if (!is_object($class)) $this->$class =& load_class($class, TRUE, 'lang');
		$this->is_loaded[] = $class;
	}
	
	/**
	* Returns loaded database object or error
	*
	* @param void
	* @return array
	*/
	public function load_db()
	{
		$class = strtolower('db');
		if (!is_object($class)) $this->$class =& load_class($class, TRUE, 'db');
		$this->is_loaded[] = $class;
	}
	
	
	/**
	* Return loaded object
	*
	* @param string $class
	* @return array
	*/
	// POSSIBLE ADDITION
	public function extend_class($extend_class, $file)
	{
		//if ($class == '') return;
		
		// is extended? do we need to use interfaces for this?
		
		//$class = strtolower($class);
		//if (!is_object($class)) $this->$class =& load_class($class, true, 'lib');
		//$this->is_loaded[] = $class;
	}
	
	
	/**
	* Return loaded object
	*
	* @param string $class
	* @return array
	*/
	public function lib_class($class)
	{
		if ($class == '') return;
		
		$class = strtolower($class);
		if (!is_object($class)) $this->$class =& load_class($class, true, 'lib');
		$this->is_loaded[] = $class;
	}
	
	
	/**
	* Return loaded object
	*
	* @param string $class
	* @return array
	*/
	public function parse_class($class)
	{
		if ($class == '') return;
		
		$class = strtolower($class);

		if (file_exists(DIRNAME . '/ndxzsite/plugin/plugin.' . $class . '.php'))
		{
			require_once(DIRNAME . '/ndxzsite/plugin/plugin.' . $class . '.php');
			if (!is_object('parse')) $this->parse =& load_class($class, true, 'local');
		}
		else
		{
			if (!is_object('parse')) $this->parse =& load_class($class, true, 'lib');
		}
		
		$this->is_loaded[] = 'parse';
	}
	
	
	/**
	* Includes interface file
	*
	* @param string $class
	* @return array
	*/
	public function lib_interface($file)
	{
		if (file_exists(DIRNAME . '/ndxzstudio/lib/interface.' . $file . '.php'))
		{
			// our interfaces
			require_once(DIRNAME . '/ndxzstudio/lib/interface.' . $file . '.php');
		}
		
		// throw and error
	}
	
	
	// REVIEW
	/**
	* Return loaded object
	*
	* @param string $class
	* @return array
	*/
	public function goto_module($class)
	{
		if ($class == '') return;
		
		$class = strtolower($class);
		if (!is_object($class)) $this->$class =& load_class($class, true, 'mod', true);
		$this->is_loaded[] = $class;
	}
	
	
	
	/**
	* Return loaded object
	*
	* @param string $class
	* @return array
	*/
	public function load_collector($a, $oid = 0)
	{
		// $oid makes it really possible
		if (($a != 'collect') || ($oid == 0)) return;
		
		$OBJ =& get_instance();

		$OBJ->collect->get_object();
		$class = $OBJ->collect->obj_ref_type;
		
		$class = strtolower($class);
		if (!is_object($class)) $this->$class =& load_class($class, true, 'collect', true);
		$this->is_loaded[] = $class;
	}
	
	
	// REVIEW
	/**
	* Return loaded object
	*
	* @param string $class
	* @return array
	*/
	public function call_class($class)
	{
		if ($class == '') return;
		
		if (class_exists($class))
		{
			$OUT = new $class;
			return $OUT->output();
		}
	}
	
	
	// REVIEW
	/**
	* Return loaded object
	*
	* @param string $class
	* @return array
	*/
	public function extend_module($arr=array())
	{
		$OBJ =& get_instance();
		
		// this is VERY messy work
		foreach ($arr as $a)
		{
			foreach ($a as $b)
			{
				if (file_exists(DIRNAME . '/ndxzsite/plugin/' . $b['file'])) 
					include_once(DIRNAME . '/ndxzsite/plugin/' . $b['file']);
			
				if (class_exists($b['function'])) 
				{
					$class = strtolower($b['function']);
					if (!is_object($class)) $this->$class =& load_class($class, true, 'extend', true);
					$this->is_loaded[] = $class;
				}
			}
		}
	}
	
	
	// REVIEW THIS ONE
	public function dyn_class($class)
	{
		$class = strtolower($class);
		if (!is_object($class)) $this->$class =& load_class($class, TRUE, 'local');
		$this->is_loaded[] = $class;		
	}
}