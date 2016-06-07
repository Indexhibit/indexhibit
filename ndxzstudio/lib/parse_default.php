<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Parse class
*
* Used to parse frontend template for output
* 
* @version 1.0
* @author Vaska 
*/
class Parse_default
{
	public $plugins;
	public $cache_enabled;
	public $code;
	public $vars;
	
	// 'editor' variables
	public $html;
	public $css;
	public $content;
	public $content_id;
	public $process;
	public $advanced = false;
	public $canvas = 0;

	/**
	* Returns string
	*
	* @param void
	* @return string
	*/
	public function parsing()
	{
		$OBJ =& get_instance();
		global $default;

		// legacy regex for variables
		$output = str_replace(array("<%", "%>"), array('{{', '}}'), $this->code);

		$output = $this->parser($output);
		
		// after the first parse we should unset our $_POST variables
		// we don't want posts posting twice every time...
		if (isset($_POST)) unset($_POST);
		
		//$output = $this->legacy_parser($output);

		$output = $this->parser($this->doVariables($output));
		
		// media parser
		$output = $this->media_parser($this->doVariables($output));
		
		// allows us to compile js and css as we move forward
		$output = $this->last_parse($this->doVariables($output));
		
		// if parsing is enabled
		$output = ($default['parsing'] == true) ?
			eval('?>' . $output . '<?php ') :
			preg_replace('|<\?[php]?(.)*\?>|sUi', '', $output);
			
		// make a hook for post parsing
		return $output;
	}
	
	
	/// ????
	// only for content in the front_exhibit
	public function pre_parse($input='')
	{
		$OBJ =& get_instance();
		global $default;
		
		if ($input == '') return;

		// twice so we can have plugins and vars in our content var
		$output = $this->parser($this->doVariables($input));
		
		// if parsing is enabled
		return ($default['parsing'] == true) ?
			eval('?>' . $output . '<?php ') :
			preg_replace('|<\?[php]?(.)*\?>|sUi', '', $output);
	}
	
	
	/**
	* Returns callback'd string
	* Called at end of parsing cycle
	*
	* @param string $text
	* @return string
	*/
	public function last_parse($text)
	{
		// called up last so we can concantenate our js and css rules
		//$f = "/<last:(\S+)\b(.*)(?:(?<!br )(\/))?" . chr(62) . "(?(3)|(.+)<\/last:\1>)/sUi";
		$f = "/<last:(?P<function>\S+)\s+(?P<variables>.*)\/>/sUi";
		return preg_replace_callback($f, array($this, 'processTags'), $text);
	}


	/**
	* Returns callback'd string
	*
	* @param string $text
	* @return string
	*/
	public function parser($text)
	{
		$f = "/<plugin:(?P<function>\S+)\s+(?P<variables>.*)\/>/sUi";
		return preg_replace_callback($f, array($this, 'processTags'), $text);
	}
	
	
	public function legacy_parser($text)
	{
		$f = "/<plug:(\S+)\b(.*)(?:(?<!br )(\/))?" . chr(62) . "(?(3)|(.+)<\/plug:\1>)/sUi";
		return preg_replace_callback($f, array($this, 'processTags'), $text);
	}
	
	
	/**
	* Returns callback'd string
	*
	* @param string $text
	* @return string
	*/
	public function media_parser($text)
	{
		$f = "/<media:(?P<function>\S+)\s+(?P<variables>.*)\/>/sUi";
		return preg_replace_callback($f, array($this, 'processTags'), $text);
	}


	/**
	* Returns callback'd system variables
	*
	* @param string $text
	* @return string
	*/
	public function doVariables($text)
	{
		$f = "|{{(.*)}}|Ui";
		
		return preg_replace_callback($f, array($this, 'getVar'), $text);
	}


	/**
	* Returns system variables
	*
	* @param string $name
	* @return string
	*/
	public function getVar($name)
	{
		$OBJ =& get_instance();

		$theVar = trim($name[1]);

		// excluded
		if ($theVar == 'password') return null;
		
		// need to put it back just like we found it if not found
		if (isset($OBJ->vars->exhibit[$theVar]))
		{
			if ($OBJ->vars->exhibit[$theVar] == '')
			{
				return null;
			}
			else
			{
				return $OBJ->vars->exhibit[$theVar];
			}
		}
		else
		{
			return '{{' . $theVar . '}}'; 
		}
	}


	/**
	* Returns adjusted array for tags
	* (we aren't using this right now)
	*
	* @param string $input
	* @return string
	*/
	public function converTags($input)
	{
		if ($input == '') return NULL;
		$tags_array = implode('|', explode(',', $input));
		return $tags_array;
	}


	/**
	* Returns callback for function
	*
	* @param array $match
	* @return string
	*/
	public function processTags($match)
	{
		$OBJ =& get_instance();
		
		$arg_list = func_get_args();

		$tmp_func = explode(':', $arg_list[0]['function']);
		$this->func = trim($tmp_func[0]);
		$this->method = (isset($tmp_func[1])) ? $tmp_func[1] : '';
		$this->variables = (isset($arg_list[0]['variables'])) ? $arg_list[0]['variables'] : '';
		
		// make the variables
		$args = explode(',', $this->variables);
		$args = array_map('trim', $args);
		$args = $this->getArgs($args);

		// we check first in the user plugin folder to see if there is an override
		// template specific option
		if (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/plugin.' . $this->func . '.php'))
		{
			include_once DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/plugin.' . $this->func . '.php';
		}
		else
		{
			if (file_exists(DIRNAME . '/ndxzsite/plugin/plugin.' . $this->func . '.php'))
				include_once DIRNAME . '/ndxzsite/plugin/plugin.' . $this->func . '.php';
		}

		if (class_exists($this->func)) 
		{
			$tmp = $this->func;
			$OBJ->dyn_class($this->func);
			
			// only if it exists
			if (method_exists($OBJ->$tmp, trim($this->method)))
			{
				return call_user_func_array(array($OBJ->$tmp, trim($this->method)), $args);
			}
		}	
		elseif (function_exists($this->func))
		{
			return call_user_func_array($this->func, $args);
		}
		else
		{
			return;
		}
	}

	/**
	* Returns parameters for function
	*
	* @param array $args
	* @return string
	*/
	public function getArgs($args)
	{
		if ($args == NULL) return;

		foreach ($args as $arg)
		{
			// var
			$arg = preg_replace('/^.*=/', '', $arg);
			// front
			$arg = preg_replace('/^(\'|")/', '', $arg);
			// back
			$var[] = preg_replace('/(\'|")$/', '', $arg);
		}

		return $var;
	}
}