<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Plugins class
* 
* @version 1.0
* @author Vaska 
*/
class Plugins
{
	public $file;
	public $plugin = array();
	public $counter = 0;
	public $plugin_path;
	
	/**
	* Returns loaded database object or error
	*
	* @param void
	* @return array
	*/
	public function __construct()
	{
		$this->plugin_path = DIRNAME . '/ndxzsite/plugin/';
	}
	
	public function get_plugins_info() 
	{
		$this->counter = 0; $arr = Array();

		if ($handle = @opendir ( $this->plugin_path )) 
		{
			while ( $file = readdir ( $handle ) ) 
			{
				$this->file = $file;
				$this->get_plugin_info();
			}
			
			closedir ( $handle );
		}
	}
	
	
	public function get_plugin_info()
	{
		if (is_file ( $this->plugin_path . $this->file )) 
		{
			if (preg_match("/^plugin./i", $this->file))
			{
				$fp = fopen ( $this->plugin_path . $this->file, 'r' );
				$plugin_data = fread ( $fp, 8192 );
				fclose ( $fp );
				
				preg_match_all ( "/Plugin Name:(.*?)End/smi", $plugin_data, $plug );
				
				if (isset($plug[0][0]))
				{
				$temp = $plug[0][0];
			
				// just the first match
				preg_match ( '|Plugin Name:(.*)$|mi', $temp, $name );
				preg_match ( '|Plugin URI:(.*)$|mi', $temp, $uri );
				preg_match ( '|Version:(.*)|mi', $temp, $version );
				preg_match ( '|Description:(.*)$|mi', $temp, $description );
				preg_match ( '|Author:(.*)$|mi', $temp, $author_name );
				preg_match ( '|Author URI:(.*)$|mi', $temp, $author_uri );
				preg_match ( '|Type:(.*)$|mi', $temp, $type );
				preg_match ( '|Hook:(.*)|mi', $temp, $hook );
				preg_match ( '|Function:(.*)$|mi', $temp, $function );
				preg_match ( '|Space:(.*)$|mi', $temp, $space );
				preg_match ( '|Order:(.*)$|mi', $temp, $order );
			
				if (isset($name[1]))
				{
					$this->plugin[$this->file]['pl_name'] = (isset($name[1])) ? trim($name[1]) : '';
					$this->plugin[$this->file]['pl_uri'] = (isset($uri[1])) ? trim($uri[1]) : '';
					$this->plugin[$this->file]['pl_version'] = (isset($version[1])) ? trim($version[1]) : '';
					$this->plugin[$this->file]['pl_desc'] = (isset($description[1])) ? trim($description[1]) : '';
					$this->plugin[$this->file]['pl_creator'] = (isset($author_name[1])) ? trim($author_name[1]) : '';
					$this->plugin[$this->file]['pl_www'] = (isset($author_uri[1])) ? trim($author_uri[1]) : '';
					$this->plugin[$this->file]['pl_options_build'] = (isset($builder[1])) ? trim($builder[1]) : '';
					$this->plugin[$this->file]['pl_type'] = (isset($type[1])) ? trim($type[1]) : '';
					$this->plugin[$this->file]['pl_hook'] = (isset($hook[1])) ? trim($hook[1]) : '';
					$this->plugin[$this->file]['pl_function'] = (isset($function[1])) ? trim($function[1]) : '';
					$this->plugin[$this->file]['pl_space'] = (isset($space[1])) ? trim($space[1]) : '';
					$this->plugin[$this->file]['pl_order'] = (isset($order[1])) ? trim($order[1]) : '';
			
					$this->counter++;
				}
				}
			}
		}
	}
	
	
	public function get_plugin_header()
	{
		if (is_file ( $this->plugin_path . $this->file )) 
		{
			if (preg_match("/^plugin./i", $this->file))
			{
				$fp = fopen ( $this->plugin_path . $this->file, 'r' );
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
					preg_match ( '|Type:(.*)$|mi', $plugin_data, $type );
					preg_match ( '|Hook:(.*)$|mi', $plugin_data, $hook );
					preg_match ( '|Function:(.*)$|mi', $plugin_data, $fcn );
					preg_match ( '|Space:(.*)$|mi', $plugin_data, $space );
					preg_match ( '|Order:(.*)$|mi', $plugin_data, $order );
				
					if ($name[1] != '')
					{
					$this->plugin[$this->file][$this->counter]['pl_name'] = (isset($name[1])) ? trim($name[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_uri'] = (isset($uri[1])) ? trim($uri[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_version'] = (isset($version[1])) ? trim($version[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_desc'] = (isset($description[1])) ? trim($description[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_creator'] = (isset($author_name[1])) ? trim($author_name[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_www'] = (isset($author_uri[1])) ? trim($author_uri[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_options_build'] = (isset($builder[1])) ? trim($builder[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_type'] = (isset($type[1])) ? trim($type[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_hook'] = (isset($hook[1])) ? trim($hook[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_function'] = (isset($fcn[1])) ? trim($fcn[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_space'] = (isset($space[1])) ? trim($space[1]) : '';
					$this->plugin[$this->file][$this->counter]['pl_order'] = (isset($order[1])) ? trim($order[1]) : '';
					
					$this->counter++;
					}
				}
			}
		}
	}
	
	
	// multiple
	public function get_plugins_header() 
	{
		$this->counter = 0; $arr = Array();

		if ($handle = @opendir ( $this->plugin_path )) 
		{
			while ( $file = readdir ( $handle ) ) 
			{
				$this->file = $file;
				$this->get_plugin_header();
			}
			
			closedir ( $handle );
		}
	}
	
	
	public function get_format_header($from_folder = PLUGINS_FOLDER) 
	{
		if ($handle = @opendir ( $from_folder )) 
		{
			while ( $file = readdir ( $handle ) ) 
			{
				if (is_file ( $from_folder . $file )) 
				{
					//if (strpos ( $from_folder . $file, '.plugin.php' )) 
					if (preg_match("/^exhibit./i", $file))
					{
						$fp = fopen ( $from_folder . $file, 'r' );
						// Pull only the first 8kiB of the file in.
						$plugin_data = fread ( $fp, 8192 );
						fclose ( $fp );
						
						$file = $file;
						preg_match ( '|Format Name:(.*)$|mi', $plugin_data, $name );
						preg_match ( '|Format URI:(.*)$|mi', $plugin_data, $uri );
						preg_match ( '|Version:(.*)|i', $plugin_data, $version );
						preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
						preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
						preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
						preg_match ( '|Options Builder:(.*)$|mi', $plugin_data, $options );
						preg_match ( '|Params:(.*)$|mi', $plugin_data, $params );
						preg_match ( '|Source:(.*)$|mi', $plugin_data, $source );
						
						foreach ( array ('file', 'name', 'uri', 'version', 'description', 'author_name', 'author_uri', 'options', 'params', 'source' ) as $field ) 
						{
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
							$plugin_data = array ('filename' => $file, 'name' => $name, 'title' => $name, 'pluginURI' => $uri, 'description' => $description, 'author' => $author_name, 'authorURI' => $author_uri, 'version' => $version, 'options' => $options, 'params' => $params, 'source' => $source );

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
}

?>