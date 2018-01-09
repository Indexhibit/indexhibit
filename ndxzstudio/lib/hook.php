<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Hooks class
* 
* @version 1.0
* @author Vaska 
* @author PHP Architect 04-2006
*/
class Hook
{
	public $action_table 	= array();
	public $filter_table 	= array();
	public $options		= array();
	public $var;
	public $arr;
	public $plugins_header = array();
	public $registered_hook = array();
	
	/**
	* Returns loaded database object or error
	*
	* @param void
	* @return array
	*/
	public function __construct()
	{
		
	}
	
	public function register_hook($hook)
	{
		$this->registered_hook[$hook] = true;
	}
	
	public function registered_hook($hook)
	{
		$OBJ =& get_instance();

		return (isset($this->registered_hook[$hook])) ? true : false;
	}

	
	public function add_action($file, $tag, $function_to_add, $priority = 10, $accept_args = 1)
	{  
		if ( isset($this->action_table[$tag][$priority]) ) 
		{  
			foreach($this->action_table[$tag][$priority] as $filter) 
			{  
				if ( $filter['function'] == $function_to_add ) {  
					return false;  
				}  
			}  
		}  

		$this->action_table[$tag][$priority][] =  array('file' => $file, 'function' => $function_to_add, 'accept_args' => $accept_args); 
 
		return true;      
	}
	
	
	public function add_filter($tag, $function_to_add, $priority = 10, $accept_args = 1) 
	{  
		if ( isset($this->filter_table[$tag][$priority]) ) 
		{  
			foreach($this->filter_table[$tag][$priority] as $filter) 
			{  
				if ( $filter[‘function’] == $function_to_add ) {  
					return false;  
				}  
			}  
		}  

		$this->filter_table[$tag][$priority][] =  array('function' => $function_to_add, 'accept_args' => $accept_args); 
 
		return true;      
	}
	
	
	public function remove_filter($tag, $function_to_remove, $priority = 10) 
	{ 
		$toret = false;
		
		if ( isset($this->filter_table[$tag][$priority]) ) 
		{  
			foreach($this->filter_table[$tag][$priority] as $filter) 
			{  
				if ( $filter['function'] != $function_to_remove ) {  
					$new_function_list[] = $filter;  
				}  
				else 
				{  
					$toret = true;  
				}  
			}  

			$this->filter_table[$tag][$priority] = $new_function_list;  
		}  
		
		return $toret;  
	}
	
	
	public function remove_action($tag, $function_to_remove, $priority = 10) 
	{ 
		$toret = false;
		
		if ( isset($this->action_table[$tag][$priority]) ) 
		{  
			foreach($this->action_table[$tag][$priority] as $filter) 
			{  
				if ( $filter['function'] != $function_to_remove ) {  
					$new_function_list[] = $filter;  
				}  
				else 
				{  
					$toret = true;  
				}  
			}  

			$this->action_table[$tag][$priority] = $new_function_list;  
		}  
		
		return $toret;  
	}
	
	
	// setup the hooks
	// in the cms
	public function load_hooks()
	{
		$OBJ =& get_instance();
		
		$space = ($OBJ->go['q'] != 'index') ? $OBJ->go['a'] . ':' . $OBJ->go['q'] : $OBJ->go['a'];

		$hooks = $OBJ->db->fetchArray("SELECT * FROM ".PX."plugins 
			WHERE (pl_type = 'global') 
			OR (pl_type = 'module' AND pl_space = '" . $space . "')
			OR (pl_type = 'module' AND pl_space = '" . $OBJ->go['a'] . "') 
			GROUP BY pl_id");
		
		if ($hooks)
		{
			foreach ($hooks as $hook)
			{
				// we get the 'hook' from data in the file itself
				// might need ordering info here...
				$OBJ->hook->add_action($hook['pl_file'], $hook['pl_hook'], $hook['pl_function'], $hook['pl_order']);
				
				$this->registered_hook[$hook['pl_hook']] = true;
				
				if ($hook['pl_options'] != '')
				{	
					// easy way to move our variables around	
					//$OBJ->hook->options[$hook['pl_function']][$hook['pl_order']] = unserialize($hook['pl_options']);
					
					// need to remove the ":"
					$tmp = explode(':', $hook['pl_function']);
					$OBJ->hook->options[$tmp[0]] = unserialize($hook['pl_options']);
				}
			}
		}
	}
	
	
	// setup the hooks
	public function load_hooks_front()
	{
		$OBJ =& get_instance();
		
		$hooks = $OBJ->db->fetchArray("SELECT * FROM ".PX."plugins 
			WHERE (pl_type = 'front' OR pl_type = 'format')");
		
		if ($hooks)
		{
			foreach ($hooks as $hook)
			{
				// these are a little different as they call themselves up
				// so they don't always have a real hook. but there will
				// like be some hooks available - think about this
				// if anything, the options parts are very useful
				if ($hook['pl_hook'] != '')
				{
					$OBJ->hook->add_action($hook['pl_file'], $hook['pl_hook'], $hook['pl_function'], $hook['pl_order']);
					$OBJ->hook->register_hook($hook['pl_hook']);
				}
				
				if ($hook['pl_options'] != '')
				{	
					// easy way to move our variables around
					// need to remove the ":"
					$tmp = explode(':', $hook['pl_function']);
					$OBJ->hook->options[$tmp[0]] = unserialize($hook['pl_options']);
				}
			}
		}
	}
	
	
	public function do_action_array($tag, $arg='') 
	{
		$OBJ =& get_instance();
		
		$extra_args = array_slice(func_get_args(), 2);  
		$args = array_merge(array($arg), $extra_args);
		
		if ( !isset($OBJ->hook->action_table[$tag]) ) 
		{  
			return;
		}  
		else 
		{  
			ksort($OBJ->hook->action_table[$tag]);  
		}
		
		$str = array();

		foreach ($OBJ->hook->action_table[$tag] as $priority => $functions) 
		{
			if ( !is_null($functions) ) 
			{  
				foreach($functions as $function) 
				{	
					$func_name = $function['function'];
					
					////////////// EXP
					$tmp = explode(':', $func_name);
					
					$func_name = $tmp[0];
					$method = isset($tmp[1]) ? $tmp[1] : '';
					
					//echo $func_name . '->' . $method; exit;
					//////////////////
					
					/// need to fix this up  
					$accept_args = 1;
					
					if (file_exists(DIRNAME . '/ndxzsite/plugin/' . $function['file'])) 
						include_once(DIRNAME . '/ndxzsite/plugin/' . $function['file']);
						
					if (class_exists($func_name)) 
					{
						// make sure the class is in the $function['file']
						// this isn't the best place to extend models
						//$str .= $OBJ->call_class($func_name);
						$tmp = new $func_name;
						$str[] = $tmp->$method();
					}	
					else if (function_exists($func_name)) 
					{
						if ( $accept_args == 1 ) {  
							$the_args = array($arg);  
						} elseif ( $accept_args > 1 ) {  
							$the_args = array_slice($args, 0, $accept_args);  
						} elseif ( $accept_args == 0 ) {  
							$the_args = NULL;  
						} else {  
							$the_args = $args;  
						}
						
						$str[] = call_user_func_array($func_name, $args);
					}  
				}  
			}
		}

		return $str; 
	}
	
	
	public function do_action($tag, $arg='') 
	{
		$OBJ =& get_instance();
		
		$extra_args = array_slice(func_get_args(), 2);  
		$args = array_merge(array($arg), $extra_args);
		
		if ( !isset($OBJ->hook->action_table[$tag]) ) 
		{  
			return;
		}  
		else 
		{  
			ksort($OBJ->hook->action_table[$tag]);  
		}
		
		$str = '';

		foreach ($OBJ->hook->action_table[$tag] as $priority => $functions) 
		{
			if ( !is_null($functions) ) 
			{  
				foreach($functions as $function) 
				{	
					$func_name = $function['function'];
					
					////////////// EXP
					$tmp = explode(':', $func_name);
					
					$func_name = $tmp[0];
					$method = isset($tmp[1]) ? $tmp[1] : '';
					
					//echo $func_name . '->' . $method; exit;
					//////////////////
					
					/// need to fix this up  
					$accept_args = 1;
					
					if (file_exists(DIRNAME . '/ndxzsite/plugin/' . $function['file'])) 
						include_once(DIRNAME . '/ndxzsite/plugin/' . $function['file']);
						
					if (class_exists($func_name)) 
					{
						// make sure the class is in the $function['file']
						// this isn't the best place to extend models
						//$str .= $OBJ->call_class($func_name);
						$tmp = new $func_name;
						$str .= $tmp->$method();
					}	
					else if (function_exists($func_name)) 
					{
						if ( $accept_args == 1 ) {  
							$the_args = array($arg);  
						} elseif ( $accept_args > 1 ) {  
							$the_args = array_slice($args, 0, $accept_args);  
						} elseif ( $accept_args == 0 ) {  
							$the_args = NULL;  
						} else {  
							$the_args = $args;  
						}
						
						$str .= call_user_func_array($func_name, $args);
					}  
				}  
			}
		}

		return $str; 
	}


	public function apply_filters($tag, $string) 
	{  
		$args = array_slice(func_get_args(), 2);  

		if ( !isset($filter_table[$tag]) ) 
		{  
			return $string;  
		}  
		else 
		{  
			ksort($filter_table[$tag]);  
		} 
		
		$str = '';

		foreach ($filter_table[$tag] as $priority => $functions) 
		{
			if ( !is_null($functions) ) {  
				foreach($functions as $function) {  

					$all_args = array_merge(array($string), $args);  
					$func_name = $function['function'];  
					$accept_args = $function['accepted_args'];
					
					if (file_exists(DIRNAME . '/ndxzstudio/extend/' . $func_name . '.php')) 
						include_once(DIRNAME . '/ndxzstudio/extend/' . $func_name . '.php');
					
					if (function_exists($func_name)) 
					{
						if ( $accept_args == 1 ) {  
							$the_args = array($arg);  
						} elseif ( $accept_args > 1 ) {  
							$the_args = array_slice($args, 0, $accept_args);  
						} elseif ( $accept_args == 0 ) {  
							$the_args = NULL;  
						} else {  
							$the_args = $args;  
						}  

						$str .= call_user_func_array($func_name, $the_args);
					}
				}  
			}  
		}
  
		return $str;  
	} 
	
	
	public function get_plugins_header($from_folder = PLUGINS_FOLDER) 
	{
		$i = 0; $arr = Array();

		if ($handle = @opendir ( $from_folder )) 
		{
			while ( $file = readdir ( $handle ) ) 
			{
				if (is_file ( $from_folder . $file )) 
				{
					if (preg_match("/^plugin./i", $file))
					{
						$fp = fopen ( $from_folder . $file, 'r' );
						$plugin_data = fread ( $fp, 8192 );
						fclose ( $fp );
						
						preg_match_all ( "/Plugin Name:(.*?)End/smi", $plugin_data, $plug );
						
						foreach ($plug[0] as $plugin_data)
						{
							preg_match ( '|Plugin Name:(.*)$|mi', $plugin_data, $name );
							preg_match ( '|Plugin URI:(.*)$|mi', $plugin_data, $uri );
							preg_match ( '|Version:(.*)|mi', $plugin_data, $version );
							preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
							preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
							preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
							preg_match ( '|Options Builder:(.*)$|mi', $plugin_data, $builder );
							
							$arr[$file][$i]['pl_name'] = (isset($name[1])) ? $name[1] : '';
							$arr[$file][$i]['pl_uri'] = (isset($uri[1])) ? $uri[1] : '';
							$arr[$file][$i]['pl_version'] = (isset($version[1])) ? $version[1] : '';
							$arr[$file][$i]['pl_desc'] = (isset($description[1])) ? $description[1] : '';
							$arr[$file][$i]['pl_creator'] = (isset($author_name[1])) ? $author_name[1] : '';
							$arr[$file][$i]['pl_www'] = (isset($author_uri[1])) ? $author_uri[1] : '';
							$arr[$file][$i]['pl_options'] = (isset($builder[1])) ? $builder[1] : '';
							
							/*
							foreach ( array ('name', 'uri', 'version', 'description', 'author_name', 'author_uri' ) as $field ) {
								if (! empty ( ${$field} ))
									${$field} = trim ( ${$field} [1] );
								else
									${$field} = '';
							}
							*/
							
							$i++;
						}

						/*
						print_r($arr);
						
						/*foreach ( array ('name', 'uri', 'version', 'description', 'author_name', 'author_uri' ) as $field ) {
							if (! empty ( ${$field} ))
								${$field} = trim ( ${$field} [1] );
							else
								${$field} = '';
						}
						
						if (($file == '') || ($name == ''))
						{
						
						}
						else
						{
							$plugin_data = array ('filename' => $file, 'Name' => $name, 'Title' => $name, 'PluginURI' => $uri, 'Description' => $description, 'Author' => $author_name, 'AuthorURI' => $author_uri, 'Version' => $version );

							$this->plugins_header [] = $plugin_data;
						}
						*/
					}
				} 
				else if ((is_dir ( $from_folder . $file )) && ($file != '.') && ($file != '..')) 
				{
					//$this->get_plugins_header ( $from_folder . $file . '/' );
				}
			}
			
			closedir ( $handle );
			
			print_r($arr);
		}

		return $this->plugins_header;
	}
	
	
	public function get_format_header($from_folder = PLUGINS_FOLDER) 
	{
		$this->plugins_header = array();

		if ($handle = @opendir ( $from_folder )) 
		{
			while ( $file = readdir ( $handle ) ) 
			{
				if (is_file ( $from_folder . $file )) 
				{
					//if (strpos ( $from_folder . $file, '.plugin.php' )) 
					if (preg_match("/^format./i", $file))
					{
						$fp = fopen ( $from_folder . $file, 'r' );
						// Pull only the first 8kiB of the file in.
						$plugin_data = fread ( $fp, 8192 );
						fclose ( $fp );
						
						//$file = $file;
						preg_match ( '|Format Name:(.*)$|mi', $plugin_data, $name );
						preg_match ( '|Format URI:(.*)$|mi', $plugin_data, $uri );
						preg_match ( '|Version:(.*)|i', $plugin_data, $version );
						preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
						preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
						preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
						preg_match ( '|Options Builder:(.*)$|mi', $plugin_data, $options );
						preg_match ( '|Params:(.*)$|mi', $plugin_data, $params );

						// dealing with array things
						$tmp = $file;
						
						foreach ( array ('file', 'name', 'uri', 'version', 'description', 'author_name', 'author_uri', 'options', 'params', 'objects' ) as $field ) 
						{
							if (!empty ( ${$field} ))
								${$field} = trim( ${$field}[1] );
							else
								${$field} = '';
						}
						
						if (($file == '') || ($name == ''))
						{
						
						}
						else
						{
							$plugin_data = array ('filename' => $tmp, 'name' => $name, 'title' => $name, 'pluginURI' => $uri, 'description' => $description, 'author' => $author_name, 'authorURI' => $author_uri, 'version' => $version, 'options' => $options, 'params' => $params, 'objects' => $objects );

							$this->plugins_header [] = $plugin_data;
						}
					}
				} 
				else if ((is_dir ( $from_folder . $file )) && ($file != '.') && ($file != '..')) 
				{
					$this->get_plugins_header ( $from_folder . $file . '/' );
				}
			}
			
			closedir ( $handle );
		}

		return $this->plugins_header;
	}
	
	
	public function get_format_header_single($from_folder=PLUGINS_FOLDER, $filename) 
	{
		if ($handle = @opendir ( $from_folder )) 
		{
			//while ( $file = readdir ( $handle ) ) 
			//{
				if (is_file ( $from_folder . $filename )) 
				{
					//if (strpos ( $from_folder . $file, '.plugin.php' )) 
					if (preg_match("/^format./i", $filename))
					{
						$fp = fopen ( $from_folder . $filename, 'r' );
						// Pull only the first 8kiB of the file in.
						$plugin_data = fread ( $fp, 8192 );
						fclose ( $fp );
						
						$filename = $filename;
						preg_match ( '|Format Name:(.*)$|mi', $plugin_data, $name );
						preg_match ( '|Format URI:(.*)$|mi', $plugin_data, $uri );
						preg_match ( '|Version:(.*)|i', $plugin_data, $version );
						preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
						preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
						preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
						preg_match ( '|Options Builder:(.*)$|mi', $plugin_data, $options );
						preg_match ( '|Params:(.*)$|mi', $plugin_data, $params );
						preg_match ( '|Objects:(.*)$|mi', $plugin_data, $objects );
						
						foreach ( array ('file', 'name', 'uri', 'version', 'description', 'author_name', 'author_uri', 'options', 'params', 'objects' ) as $field ) 
						{
							if (! empty ( ${$field} ))
								${$field} = trim ( ${$field} [1] );
							else
								${$field} = '';
						}
						
						if (($filename == '') || ($name == ''))
						{
						
						}
						else
						{
							$plugin_data = array ('filename' => $filename, 'name' => $name, 'title' => $name, 'pluginURI' => $uri, 'description' => $description, 'author' => $author_name, 'authorURI' => $author_uri, 'version' => $version, 'options' => $options, 'params' => $params, 'objects' => $objects );

							$this->plugins_header = $plugin_data;
						}
					}
				} 
				else if ((is_dir ( $from_folder . $filename )) && ($filename != '.') && ($filename != '..')) 
				{
					$this->get_plugins_header ( $from_folder . $filename . '/' );
				}
			//}
			
			closedir ( $handle );
		}

		return $this->plugins_header;
	}
}