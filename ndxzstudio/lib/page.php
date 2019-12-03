<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* Frontend template class
*
* Used for generating frontend template
* (This really needs some work still - but it's functional for now)
* 
* @version 1.1
* @author Vaska 
*/
class Page
{
	public $result 		= array();
	public $exhibit 		= array();
	public $lib_js_add;
	public $exhibitz 		= array();
	public $protected 		= false;
	public $cached 		= true;
	public $js_lib_table 	= array();
	public $js_jquery_table 	= array();
	public $js_jquery_onready_table = array();
	public $js_prototype_table = array();
	public $js_prototype_onready_table = array();
	public $version = '';
	public $parsed = false;
	
	/**
	* Returns results array and exhibition plugin
	*
	* @param void
	* @return mixed
	*/
	public function __construct()
	{	
		$OBJ =& get_instance();

		//$this->result = $OBJ->vars->exhibit;
		if (isset($OBJ->vars->exhibit['version']))
		{
			$this->version = '?v=' . $OBJ->vars->exhibit['version'];
		}
		
		//if (isset($OBJ->vars->exhibit['ajax']))
		//{
			//if ($OBJ->vars->exhibit['ajax'] == false)
			//{
				// init the format
			//	$this->loadExhibit();
			//}
		//}
	}
	
	public function version()
	{
		return $this->version;
	}
	
	
	public function get_imgs()
	{
		$OBJ =& get_instance();
		global $default;

		//////////////////
		// load the interface
		$OBJ->lib_interface('filesource');

		// implement the interface
		$class = 'filesource' . $default['filesource'][$OBJ->vars->exhibit['media_source']];
		$F =& load_class($class, true, 'lib');

		// get our output
		return $F->getDisplayImages();
		//////////////////
	}

	
	/**
	* Returns exhibition format parameters
	*
	* @param void
	* @return string
	*/
	public function init_page()
	{
		$exhibit = array();
		
		$OBJ =& get_instance();
		
		// what about section passwords?
		if ($OBJ->vars->exhibit['sec_pwd'] != '')
		{
			$this->cached = false;
	
			$page = 'ndxz_sec_' . $OBJ->vars->exhibit['secid'];
			
			if (isset($_POST['ndxz_sec_pwd_sbmt']) && ($_POST['ndxz_hid'] == ''))
			{
				// it matches the password
				if (md5($_POST['ndxz_sec_pwd']) == md5($OBJ->vars->exhibit['sec_pwd']))
				{
					// set the cookie for one day
					setcookie($page, md5($_POST['ndxz_sec_pwd']), time()+3600*24, '/');
					
					// do we need to do a redirect here then?
					header('location:' . BASEURL . $OBJ->vars->exhibit['url']);
				}
				else // it does not
				{
					$this->protected = true;

					$out = $this->sec_password();
					$out .= "<p>Incorrect.</p>\n";
					
					$OBJ->page->exhibit['exhibit'] = $out;
					$OBJ->page->exhibit['lib_css'][] = "security.css";
					return;
				}
			}
			elseif (isset($_COOKIE[$page]))
			{	
				// it doesn't match the password
				if ($_COOKIE[$page] != md5($OBJ->vars->exhibit['sec_pwd']))
				{
					$this->protected = true;
					$OBJ->page->exhibit['exhibit'] = $this->sec_password();
					$OBJ->page->exhibit['lib_css'][] = "security.css";
					return;
				}
			}
			else
			{
				$this->protected = true;
				$OBJ->page->exhibit['exhibit'] = $this->sec_password();
				$OBJ->page->exhibit['lib_css'][] = "security.css";
				return;
			}
		}
		else
		{
			// built-in password check
			// checks only the active page
			if ($OBJ->vars->exhibit['pwd'] != '')
			{
				$this->cached = false;
	
				$page = 'ndxz_page_' . $OBJ->vars->exhibit['id'];
			
				if (isset($_POST['ndxz_pwd_sbmt']) && ($_POST['ndxz_hid'] == ''))
				{
					// it matches the password
					if (md5($_POST['ndxz_pwd']) == md5($OBJ->vars->exhibit['pwd']))
					{
						// set the cookie for one day
						setcookie($page, md5($_POST['ndxz_pwd']), time()+3600*24, '/');
					
						// do we need to do a redirect here then?
						header('location:' . BASEURL . $OBJ->vars->exhibit['url']);
					}
					else // it does not
					{
						$this->protected = true;

						$out = $this->password();
						$out .= "<p>Incorrect.</p>\n";
					
						$OBJ->page->exhibit['exhibit'] = $out;
						$OBJ->page->exhibit['lib_css'][] = "security.css";
						return;
					}
				}
				elseif (isset($_COOKIE[$page]))
				{	
					// it doesn't match the password
					if ($_COOKIE[$page] != md5($OBJ->vars->exhibit['pwd']))
					{
						$this->protected = true;
						$OBJ->page->exhibit['exhibit'] = $this->password();
						$OBJ->page->exhibit['lib_css'][] = "security.css";
						return;
					}
				}
				else
				{
					$this->protected = true;
					$OBJ->page->exhibit['exhibit'] = $this->password();
					$OBJ->page->exhibit['lib_css'][] = "security.css";
					return;
				}
			}
		}
	}
	
	
	// occoasionally handy functions
	public function sysvar($name='')
	{
		$OBJ =& get_instance();
		
		if (isset($OBJ->vars->exhibit[$name]))
		{
			return $OBJ->vars->exhibit[$name];
		}
	}
	
	
	public function changevar($name='', $value='')
	{
		$OBJ =& get_instance();
		
		if (isset($OBJ->vars->exhibit[$name]))
		{
			$OBJ->vars->exhibit[$name] = $value;
		}
	}
	
	
	public function makevar($name='', $value='')
	{
		$OBJ =& get_instance();
		
		if (!isset($OBJ->vars->exhibit[$name]))
		{
			$OBJ->vars->exhibit[$name] = $value;
		}
	}
	

	/**
	* Password check
	*
	* @param string $function
	* @return string
	*/
	public function sec_password()
	{
		$out = "<form name='ndxz_protect' id='ndxz-protect' method='post' action=''>\n";
		$out .= "<p>Password Protected Section</p>\n";
		$out .= "<p>Enter Password</p>\n";
		$out .= "<p><input name='ndxz_sec_pwd' type='text' maxlength='12' /></p>\n";
		$out .= "<p><input name='ndxz_hid' type='hidden' value='' /></p>\n";
		$out .= "<p><input name='ndxz_sec_pwd_sbmt' type='submit' value='submit' /></p>\n";
		$out .= "</form>\n";
		
		return $out;
	}
	
	
	/**
	* Password check
	*
	* @param string $function
	* @return string
	*/
	public function password()
	{
		$out = "<form name='ndxz_protect' id='ndxz-protect' method='post' action=''>\n";
		
		// this can be changed via a plugin (need to make the plugin)
		$out .= "<p>Password Protected Page (enter password):</p>\n";

		$out .= "<div id='ndxz_pwd'><input name='ndxz_pwd' type='text' maxlength='12' /></div>\n";
		$out .= "<div id='ndxz_pwd_sbmt'><button name='ndxz_pwd_sbmt' type='submit' />Submit</button></div>\n";
		$out .= "<input name='ndxz_hid' type='hidden' value='' />\n";
		$out .= "</form>\n";
		
		return $out;
	}
	
	/**
	* Returns index
	*
	* @param string $function
	* @return string
	*/
	public function index($function='')
	{
		// load 'theindex' class
		$NDX =& load_class('theindex', TRUE, 'lib');
		return $NDX->load_index();
	}
	
	public function javascript()
	{
		$out = $this->jquery();
		$out .= $this->prototype();
		$out .= $this->lib_js();
		$out .= $this->dyn_js();

		return $out;
	}
	
	/**
	* Returns exhibition
	*
	* @param void
	* @return string
	*/
	public function loadExhibit()
	{
		$OBJ =& get_instance();
		
		// check for password protection
		$this->password_protect();
		
		if (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/format.' . $OBJ->vars->exhibit['format'] . '.php'))
		{
			include_once DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/format.' . $OBJ->vars->exhibit['format'] . '.php';
				
			$OBJ =& get_instance();
			$OBJ->dyn_class('Exhibit');
		}
		elseif (file_exists(DIRNAME . '/ndxzsite/plugin/format.' . $OBJ->vars->exhibit['format'] . '.php'))
		{
			include_once DIRNAME . '/ndxzsite/plugin/format.' . $OBJ->vars->exhibit['format'] . '.php';

			$OBJ =& get_instance();
			$OBJ->dyn_class('Exhibit');
		}
		else
		{
			// thie default format
			if (file_exists(DIRNAME . '/ndxzsite/plugin/format.visual_index.php'))
			{
				include_once DIRNAME . '/ndxzsite/plugin/format.visual_index.php';

				$OBJ =& get_instance();
				$OBJ->dyn_class('Exhibit');
			}
		}
	}
	
	/**
	* Returns ccs file info
	*
	* @param void
	* @return string
	*/
	public function lib_css()
	{
		$out = '';
		
		if (!isset($this->exhibit['lib_css'])) return;
		
		if ($this->exhibit['lib_css'] != '')
		{
			if (is_array($this->exhibit['lib_css']))
			{
				foreach ($this->exhibit['lib_css'] as $css)
				{
					$out .= "<style type='text/css'> @import url(" . BASEURL . "/ndxzsite/css/$css" . $this->version . "); </style>\n";
				}
			}
			else
			{
				$out .= "<style type='text/css'> @import url(" . BASEURL . "/ndxzsite/css/" . $this->exhibit['lib_css'] . $this->version . "); </style>\n";
			}
		}
		
		return $out;
	}
	
	
	/**
	* Returns ccs file info
	*
	* @param void
	* @return string
	*/
	public function theme_css()
	{
		$OBJ =& get_instance();

		$out = '';
		
		if (!isset($this->exhibit['theme_css'])) return;
		
		$theme = $OBJ->vars->exhibit['obj_theme'];
		
		if ($this->exhibit['theme_css'] != '')
		{
			if (is_array($this->exhibit['theme_css']))
			{
				foreach ($this->exhibit['theme_css'] as $css)
				{
					$out .= "<style type='text/css'> @import url(" . BASEURL . "/ndxzsite/$theme/$css" . $this->version . "); </style>\n";
				}
			}
			else
			{
				$out .= "<style type='text/css'> @import url(" . BASEURL . "/ndxzsite/$theme/" . $this->exhibit['theme_css'] . $this->version . "); </style>\n";
			}
		}
		
		return $out;
	}
	
	
	public function css()
	{
		$out = $this->lib_css();
		$out .= $this->theme_css();
		$out .= $this->dyn_css();
		
		return $out;
	}
	
	
	public function append_index()
	{
		$OBJ =& get_instance();
		
		$s = '';

		if (isset($OBJ->page->exhibit['append_index']))
		{
			foreach ($OBJ->page->exhibit['append_index'] as $do)
			{
				$s .= $do;
			}
			
			return $s;
		}
	}
	
	
	public function append_page()
	{
		$OBJ =& get_instance();
		
		$s = '';

		if (isset($OBJ->page->exhibit['append_page']))
		{
			foreach ($OBJ->page->exhibit['append_page'] as $do)
			{
				$s .= $do;
			}
			
			return $s;
		}
	}
	
	
	public function preload_page()
	{
		$OBJ =& get_instance();
		
		$s = '';

		if (isset($OBJ->page->exhibit['preload_page']))
		{
			foreach ($OBJ->page->exhibit['preload_page'] as $do)
			{
				$s .= $do;
			}
			
			return $s;
		}
	}
	
	
	public function front_test()
	{
		echo ' testing ';
	}
	
	public function sidebar()
	{
		$OBJ =& get_instance();
		
		$s = '';

		if (isset($OBJ->page->exhibit['sidebar']))
		{
			foreach ($OBJ->page->exhibit['sidebar'] as $do)
			{
				$s .= $do;
			}
			
			return $s;
		}
	}
	
	
	public function password_protect()
	{
		$OBJ =& get_instance();
		
		if ($OBJ->vars->exhibit['cms'] == true)
		{
			$this->protected = false;
			return;
		}
		
		// what about section passwords?
		if ($OBJ->vars->exhibit['sec_pwd'] != '')
		{
			$this->cached = false;
	
			$page = 'ndxz_sec_' . $OBJ->vars->exhibit['secid'];
			
			if (isset($_POST['ndxz_sec_pwd_sbmt']) && ($_POST['ndxz_hid'] == ''))
			{
				// it matches the password
				if (md5($_POST['ndxz_sec_pwd']) == md5($OBJ->vars->exhibit['sec_pwd']))
				{
					// set the cookie for one day
					//setcookie($page, md5($_POST['ndxz_pwd']), time()+3600*24, $OBJ->vars->exhibit['url']);
					
					// is this bad?
					setcookie($page, md5($_POST['ndxz_sec_pwd']), time()+3600*24, '/');
					
					// do we need to do a redirect here then?
					header('location:' . BASEURL . ndxz_rewriter($OBJ->vars->exhibit['url']));
					exit;
				}
				else // it does not
				{
					$this->protected = true;

					$out = $this->sec_password();
					$out .= "<p>Incorrect.</p>\n";
					
					$OBJ->page->exhibit['exhibit'] = $out;
					return $OBJ->page->exhibit['exhibit'];
				}
			}
			elseif (isset($_COOKIE[$page]))
			{	
				// it doesn't match the password
				if ($_COOKIE[$page] != md5($OBJ->vars->exhibit['sec_pwd']))
				{
					$this->protected = true;
					$OBJ->page->exhibit['exhibit'] = $this->sec_password();
					return $OBJ->page->exhibit['exhibit'];
				}
			}
			else
			{
				$this->protected = true;
				$OBJ->page->exhibit['exhibit'] = $this->sec_password();
				return $OBJ->page->exhibit['exhibit'];
			}
		}
		else
		{
		// built-in password check
		// checks only the active page
		if ($OBJ->vars->exhibit['pwd'] != '')
		{
			$this->cached = false;
	
			$page = 'ndxz_page_' . $OBJ->vars->exhibit['id'];
			
			if (isset($_POST['ndxz_pwd_sbmt']) && ($_POST['ndxz_hid'] == ''))
			{
				// it matches the password
				if (md5($_POST['ndxz_pwd']) == md5($OBJ->vars->exhibit['pwd']))
				{
					// set the cookie for one day
					//setcookie($page, md5($_POST['ndxz_pwd']), time()+3600*24, $OBJ->vars->exhibit['url']);
					
					// is this bad?
					setcookie($page, md5($_POST['ndxz_pwd']), time()+3600*24, '/');
					
					// do we need to do a redirect here then?
					header('location:' . BASEURL . ndxz_rewriter($OBJ->vars->exhibit['url']));
					exit;
				}
				else // it does not
				{
					$out = $this->password();
					$out .= "<p>Incorrect.</p>\n";
					
					$OBJ->page->exhibit['exhibit'] = $out;
					return $OBJ->page->exhibit['exhibit'];
				}
			}
			elseif (isset($_COOKIE[$page]))
			{	
				// it doesn't match the password
				if ($_COOKIE[$page] != md5($OBJ->vars->exhibit['pwd']))
				{
					$this->protected = true;
					$OBJ->page->exhibit['exhibit'] = $this->password();
					return $OBJ->page->exhibit['exhibit'];
				}
			}
			else
			{
				$this->protected = true;
				$OBJ->page->exhibit['exhibit'] = $this->password();
				return $OBJ->page->exhibit['exhibit'];
			}
		}
		}
	}
	
	
	public function exhibit()
	{
		$OBJ =& get_instance();
		
		if ($OBJ->vars->exhibit['cms'] != true)
		{
			if ($this->protected == true)
			{
				return $OBJ->page->exhibit['exhibit'];
			}
		}
		
		// otherwise, we load our exhibit class
		return $OBJ->exhibit->createExhibit();
	}
	
	
	public function submedia()
	{
		$OBJ =& get_instance();
		
		phpinfo();
	}
	
	
	public function closing()
	{
		// this will be for hooks that come at the end
		// google analytics for instance
		
		$OBJ =& get_instance();

		// think we need to loop through an array of parts here
		if ($OBJ->hook->registered_hook('closing')) 
			return $OBJ->hook->do_action('closing');
	}
	
	public function statistics()
	{
		// this will be for hooks that come at the end
		// google analytics for instance
		$OBJ =& get_instance();

		// think we need to loop through an array of parts here
		if (isset($OBJ->abstracts->abstract['statistics'])) 
			return $OBJ->abstracts->abstract['statistics'];
	}
	
	public function meta()
	{
		$OBJ =& get_instance();
		
		if ($OBJ->hook->registered_hook('add_meta_tags')) 
			return $OBJ->hook->do_action('add_meta_tags');
	}
	
	public function favicon()
	{
		
	}
	
	/**
	* Returns js file info
	*
	* @param void
	* @return string
	*/
	public function lib_js()
	{
		$out = '';
		
		if (!empty($this->js_lib_table))
		{
			$this->js_lib_table = array_unique($this->js_lib_table);
			
			foreach ($this->js_lib_table as $js)
			{
				$out .= "<script type='text/javascript' src='" . BASEURL . "/ndxzsite/js/$js" . $this->version . "'></script>\n";
			}
			
			//$out .= $this->front_jquery();

			return $out;
		}	
	}
	
	
	/**
	* Returns jquery file info
	*
	* @param void
	* @return string
	*/
	public function lib_jquery()
	{
		$out = '';
		
		if (!empty($this->js_jquery_table))
		{
			$this->js_jquery_table = array_unique($this->js_jquery_table);
			
			// auto add jquery.js
			$out .= "<script type='text/javascript' src='" . BASEURL . "/ndxzsite/js/jquery.js" . $this->version . "'></script>\n";
			
			foreach ($this->js_jquery_table as $js)
			{
				$out .= "<script type='text/javascript' src='" . BASEURL . "/ndxzsite/js/$js" . $this->version . "'></script>\n";
			}
			
			$out .= $this->front_jquery();

			return $out;
		}	
	}

	
	/**
	* Returns css - dynamically generated
	*
	* @param void
	* @return string
	*/
	public function dyn_css()
	{
		$out = '';
		
		if (!isset($this->exhibit['dyn_css'])) return;
		
		if ($this->exhibit['dyn_css'] != '')
		{
			if (is_array($this->exhibit['dyn_css']))
			{
				$out .= "<style type='text/css'>\n";
				
				foreach ($this->exhibit['dyn_css'] as $css)
				{
					$out .= "$css\n";
				}
				
				$out .= "</style>\n";
			}
			else
			{
				$out .= "<style type='text/css'>\n$css" . $this->version . "\n</style>\n";
			}
		}
		
		return $out;
	}
	
	/**
	* Returns js - dynamically generated
	*
	* @param void
	* @return string
	*/
	public function dyn_js()
	{
		$out = '';
		
		// check on this later...
		if (empty($this->exhibit['dyn_js'])) return;
		
		if ($this->exhibit['dyn_js'] != '')
		{
			if (is_array($this->exhibit['dyn_js']))
			{
				$tmp = array_unique($this->exhibit['dyn_js']);

				$out .= "<script type='text/javascript'>\n";
				
				foreach ($tmp as $js)
				{
					$out .= "$js\n";
				}
				
				$out .= "</script>\n";
			}
		}
		
		return $out;
	}
	
	
	/**
	* Returns js onload parts
	*
	* @param void
	* @return string
	*/
	public function onload_js()
	{
		$out = '';
		
		if (!isset($this->exhibit['onload'])) return;
		
		if ($this->exhibit['onload'] != '')
		{	
			if (is_array($this->exhibit['onload']))
			{
				$out .= " onload=\"";
				
				foreach ($this->exhibit['onload'] as $js)
				{
					$out .= "$js ";
				}
				
				$out .= "\"";
			}
			else
			{
				$out .= " onload=\"$js\"\n";
			}
		}
		
		return $out;
	}
	
	
	/**
	* Returns js - dynamically generated
	*
	* @param void
	* @return string
	*/
	public function jquery()
	{
		$out = '';
		
		if (empty($this->js_jquery_table)) return;
		
		//array_unique($this->js_jquery_table);

		if (is_array($this->js_jquery_table))
		{
			$out .= "<script type='text/javascript' src='" . BASEURL . "/ndxzsite/js/jquery.js" . $this->version . "'></script>\n";
				
			foreach ($this->js_jquery_table as $js)
			{
				$out .= "<script type='text/javascript' src='" . BASEURL . "/ndxzsite/js/$js" . $this->version . "'></script>\n";
			}
		}
		
		return $out;
	}
	
	
	/**
	* Returns js - dynamically generated
	*
	* @param void
	* @return string
	*/
	public function prototype()
	{
		$out = '';
		
		if (empty($this->js_prototype_table)) return;

		if (is_array($this->js_prototype_table))
		{
			$out .= "<script type='text/javascript' src='" . BASEURL . "/ndxzsite/js/prototype.js" . $this->version . "'></script>\n";
				
			foreach ($this->js_prototype_table as $js)
			{
				$out .= "<script type='text/javascript' src='" . BASEURL . "/ndxzsite/js/$js" . $this->version . "'></script>\n";
			}
		}
		
		return $out;
	}
	
	
	public function onready()
	{
		$out = $this->jquery_onready();
		
		// not sure about this one yet
		//$out .= $this->front_prototype_onready();
		
		return $out;
	}
	
	
	/**
	* Returns js onready parts (for Jquery)
	*
	* @param void
	* @return string
	*/
	public function jquery_onready()
	{
		$out = '';
		
		if (empty($this->js_jquery_onready_table)) return;
		
		ksort($this->js_jquery_onready_table);
		$result = array_unique($this->js_jquery_onready_table);
		
		if (is_array($result))
		{
			$out .= "<script type='text/javascript'>\n";
			$out .= "$(document).ready(function()\n";
			$out .= "{\n";
				
			foreach ($result as $js)
			{
				$out .= "\t{$js}\n";
			}
				
			$out .= "});\n";
			$out .= "</script>\n";
		}
		
		return $out;
	}
	
	
	/**
	* Returns js onready parts (for Jquery)
	*
	* @param void
	* @return string
	*/
	public function prototype_onready()
	{
		$out = '';
		
		if (empty($this->js_prototype_onready_table)) return;

		if (is_array($this->js_prototype_onready_table))
		{
			$out .= "<script type='text/javascript'>\n";
			$out .= "$(document).ready(function()\n";
			$out .= "{\n";
				
			foreach ($this->js_prototype_onready_table as $js)
			{
				$out .= "\t{$js}\n";
			}
				
			$out .= "});\n";
			$out .= "</script>\n";
		}
		
		return $out;
	}
	
	
	public function add_jquery($file, $priority = 25) 
	{
		if (!isset($this->js_jquery_table[$priority]))
		{
			$this->js_jquery_table[$priority] = $file;
		}
		else
		{
			$this->add_jquery($file, $priority+1);
		}
		
		$this->js_jquery_table = array_unique($this->js_jquery_table);
	}
	
	
	public function add_prototype($file, $priority = 25) 
	{
		if (!isset($this->js_prototype_table[$priority]))
		{
			$this->js_prototype_table[$priority] = $file;
		}
	}
	

	public function add_jquery_onready($file, $priority = 25) 
	{
		if (!isset($this->js_jquery_onready_table[$priority]))
		{
			$this->js_jquery_onready_table[$priority] = $file;
		}
		else
		{
			$this->add_jquery_onready($file, $priority+1);
		}
		
		$this->js_jquery_onready_table = array_unique($this->js_jquery_onready_table);
	}
	
	
	public function add_prototype_onready($file, $priority = 25) 
	{
		if (!isset($this->js_prototype_onready_table[$priority]))
		{
			$this->js_prototype_onready_table[$priority] = $file;
		}
	}
	
	
	public function add_lib_js($file, $priority = 25) 
	{
		if (!isset($this->js_lib_table[$priority]))
		{
			$this->js_lib_table[$priority] = $file;
		}
	}


	public function remove_lib_js($file, $priority = 25) 
	{
		if ($this->js_lib_table[$priority] == $file)
		{
			unset($this->js_lib_table[$priority]);
		} 
	}
	
	
	public function delay_load_last($function='')
	{
		if ($function == '') return;
		
		$OBJ =& get_instance();
		
		return $this->$function();
	}
	
	
	/// for alternate parsing - PHP
	/**
	* Returns callback for function
	*
	* @param array $match
	* @return string
	*/
	public function load_plugin($match)
	{
		$OBJ =& get_instance();
	
		$this->func = $match;
		$arg_list = func_get_args();
		
		if ($arg_list[0][2] != '') 
		{
			$args = explode(',', $arg_list[0][2]);
			$args = array_map('trim', $args);
		} 
		else 
		{
			$args = NULL;
		}

		$args = $this->getArgs($args);
		
		////////////
		if (file_exists(DIRNAME . '/ndxzsite/plugin/plugin.' . $this->func . '.php'))
			include_once DIRNAME . '/ndxzsite/plugin/plugin.' . $this->func . '.php';

		if (class_exists($this->func)) 
		{
			$tmp = $this->func;
			$OBJ->dyn_class($this->func);
			
			// only if it exists
			if (method_exists($OBJ->$tmp, $args[0]) && $args[0] != '')
			{
				// it is possible to send variables to the method
				$args[1] = (isset($args[1])) ? $args[1] : null;
				$args[2] = (isset($args[2])) ? $args[2] : null;
				
				return call_user_func_array(array($OBJ->$tmp, $args[0]), array($args[1], $args[2]));
			}
			//else
			//{
			//	return call_user_func_array(array($OBJ->$tmp, '__construct'), array(null, null));
			//}
		}	
		else if (function_exists($this->func))
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