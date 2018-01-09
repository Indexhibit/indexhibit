<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* Frontend template class
*
* Used for generating frontend template
* (This really needs some work still - but it's functional for now)
* 
* @version 1.0
* @author Vaska 
*/
class Front
{
	public $result = array();
	public $exhibit = array();
	public $lib_js_add;
	
	/**
	* Returns results array and exhibition plugin
	*
	* @param void
	* @return mixed
	*/
	public function __construct()
	{
		global $rs;
		
		$this->result = $rs;
		$this->init_front();
	}
	
	/**
	* Returns exhibition format parameters
	*
	* @param void
	* @return string
	*/
	public function init_front()
	{
		if (file_exists(DIRNAME.BASENAME.'/site/plugin/exhibit.'.$this->result['format'].'.php'))
		{
			include DIRNAME.BASENAME.'/site/plugin/exhibit.'.$this->result['format'].'.php';
		}
		else
		{
			// thie default format
			include DIRNAME.BASENAME.'/site/plugin/exhibit.grow.php';
		}
		
		return $this->exhibit = $exhibit;
	}
	
	/**
	* Returns index
	*
	* @param string $function
	* @return string
	*/
	public function front_index($function='')
	{
		return (function_exists($function)) ? $function() : getNavigation();
	}
	
	/**
	* Returns exhibition
	*
	* @param void
	* @return string
	*/
	public function front_exhibit()
	{
		if ($this->exhibit['exhibit'] == '')
		{
			return;
		}
		else
		{
			// showImages() is a default method - but we don't use it anymore
			return ($this->exhibit['exhibit'] == '') ? showImages($this->result['id']) : $this->exhibit['exhibit'];
		}
	
	}
	
	/**
	* Returns ccs file info
	*
	* @param void
	* @return string
	*/
	public function front_lib_css()
	{
		$out = '';
		
		if (!isset($this->exhibit['lib_css'])) return;
		
		if ($this->exhibit['lib_css'] != '')
		{
			if (is_array($this->exhibit['lib_css']))
			{
				foreach ($this->exhibit['lib_css'] as $css)
				{
					$out .= "<style type='text/css'> @import url(".BASEURL.BASENAME."/site/css/$css); </style>\n";
				}
			}
			else
			{
				$out .= "<style type='text/css'> @import url(".BASEURL.BASENAME."/site/css/".$this->exhibit['lib_css']."); </style>\n";
			}
		}
		
		return $out;
	}
	
	/**
	* Returns js file info
	*
	* @param void
	* @return string
	*/
	public function front_lib_js()
	{
		$out = '';
		
		if (!isset($this->exhibit['lib_js'])) return;
		
		if ($this->exhibit['lib_js'] != '')
		{
			if (is_array($this->exhibit['lib_js']))
			{
				foreach ($this->exhibit['lib_js'] as $js)
				{
					$out .= "<script type='text/javascript' src='".BASEURL.BASENAME."/site/js/$js'></script>\n";
				}
			}
			else
			{
				$out .= "<script type='text/javascript' href='".BASEURL.BASENAME."/site/js/".$this->exhibit['lib_js']."'></script>\n";
			}
		}
		
		return $out;		
	}

	
	/**
	* Returns css - dynamically generated
	*
	* @param void
	* @return string
	*/
	public function front_dyn_css()
	{
		if (isset($this->exhibit['dyn_css']))
		{
			return "<style type='text/css'>\n" . $this->exhibit['dyn_css'] . "\n</style>\n";
		} 
		else
		{
			return '';
		}
	}
	
	/**
	* Returns js - dynamically generated
	*
	* @param void
	* @return string
	*/
	public function front_dyn_js()
	{
		if (isset($this->exhibit['dyn_js'])) 
		{
			return "<script type='text/javascript'>\n" . $this->exhibit['dyn_js'] . "\n</script>\n";
		} 
		else
		{
			return '';
		}
	}
	
	
	/**
	* Default template 'Eatock'
	* EDIT AT YOUR OWN RISK
	*
	* @param void
	* @return string
	*/
	public function front_eatock()
	{
		return "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'
			'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>

		<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
		<head>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>

		<title><%title%> : <%obj_name%></title>

		<link rel='stylesheet' href='<%baseurl%><%basename%>/site/<%obj_theme%>/style.css' type='text/css' />
		<!--[if lte IE 6]>
		<link rel='stylesheet' href='<%baseurl%><%basename%>/site/<%obj_theme%>/ie.css' type='text/css' />
		<![endif]-->
		<plug:front_lib_css />
		<plug:front_dyn_css />
		<script type='text/javascript' src='<%baseurl%><%basename%>/site/js/jquery.js'></script>
		<script type='text/javascript' src='<%baseurl%><%basename%>/site/js/cookie.js'></script>
		<plug:front_lib_js />
		<script type='text/javascript'>
		path = '<%baseurl%>/files/gimgs/';

		$(document).ready(function()
		{
			setTimeout('move_up()', 1);
		});
		</script>
		<plug:front_dyn_js />
		<plug:backgrounder color='<%color%>', img='<%bgimg%>', tile='<%tiling%>' />
		</head>

		<body class='section-<%section_id%>'>
		<div id='menu'>
		<div class='container'>

		<%obj_itop%>
		<plug:front_index />
		<%obj_ibot%>
		
		<!-- you must provide a link to Indexhibit on your site someplace - thank you -->
		<ul>
		<li>Built with <a href='http://www.indexhibit.org/'>Indexhibit</a></li>
		</ul>

		</div>	
		</div>	

		<div id='content'>
		<div class='container'>

		<!-- text and image -->
		<plug:front_exhibit />
		<!-- end text and image -->

		</div>
		</div>

		</body>
		</html>";
	}
}