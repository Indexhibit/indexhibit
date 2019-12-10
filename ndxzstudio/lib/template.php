<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* indexhibit template class
*
* Still needs more planning - messy
* 
* @version 1.0
* @author Vaska 
*/
class Template
{
	public $title;
	public $body;
	public $index;
	public $css			= array();
	public $js				= array();
	public $ex_js			= array();
	public $data			= array();
	public $location;
	public $location_override;
	public $sub_location 	= array();
	public $toggler		= array();
	public $add_script;
	public $action;
	public $action_error;
	public $action_update;
	public $form_type		= FALSE;
	public $form_onsubmit	= FALSE;
	public $form_action	= '';
	public $notifier		= array();
	public $special_js;
	public $onload			= array();
	public $onunload		= array();
	public $onready		= array();
	public $module_js		= array();
	public $module_css		= array();
	public $extended		= false;
	public $ex_css			= array();
	
	// for popups
	public $pop_location;
	public $pop_links		= array();
	public $pref_nav;
	
	
	/**
	* Returns basic stuff
	*
	* @param void
	* @return null
	*/
	public function __construct()
	{
		// default settings
		$this->title = 'Indexhibit™';
		$this->add_css('style.css');
		$this->add_js('common.js');
	}
	
	public function tpl_update_available()
	{
		$OBJ =& get_instance();
		$k = '';
		$v = '';

		///
		$params = array();
		$params['v']		= VERSION;
		$params['method']	= 'version';
		$params['lang']		= $OBJ->vars->settings['site_lang'];

		$encoded_params = array();

		foreach ($params as $k => $v)
		{
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}

		$rest = 'http://api.indexhibit.org/' . '?' . implode('&', $encoded_params);
	
		// we'll need to deal with errors here eventually
		$rsp = array();
		$rsp = @file_get_contents($rest);
		///

		$result = unserialize($rsp);

		// new version available?
		if ($result['success'] == true)
		{
			return $result['msg'];
		}
		
		if (VERSION > $OBJ->vars->settings['version'])
		{
			return "<div style='height: 36px; background: #fff20d;'><div style='padding: 12px;'>Update to version " . VERSION . " - <a href='?a=system&q=upgrade'>click here</a></div></div>";
		}
	}
	
	
	/**
	* Returns string - our basic instrument for outputting pages
	*
	* @param string $tpl
	* @return string
	*/
	public function tpl_test($tpl)
	{
		$OBJ =& get_instance();

		header ('Content-type: text/html; charset=utf-8');

		ob_start();
		include_once TPLPATH . $tpl . '.php';
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
		//exit;
	}
	
	
	/**
	* Returns update notification string
	*
	* @param void
	* @return string
	*/
	public function tpl_notify()
	{
		if ($this->notifier == '') return;
		
		$out = "\n";
		
		foreach ($this->notifier as $notify)
		{
			$out .= p($notify);
		}
		
		return div($out, "style='margin-bottom: 18px; border: 1px solid #c00;'");
	}
	
	
	/**
	* Returns doctype
	* (in the future we'll need to account for more options)
	*
	* @param void
	* @return string
	*/
	public function tpl_type()
	{
		return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
	}

	
	/**
	* Returns array of css files
	*
	* @param string $css
	* @return array
	*/
	public function add_css($css)
	{
		if (!isset($this->css[$css])) $this->css[$css] = $css;
	}
	
	
	/**
	* Returns array of css files
	*
	* @param string $css
	* @return array
	*/
	public function del_css($css)
	{
		if (isset($this->css[$css])) unset($this->css[$css]);
	}
	
	
	/**
	* Returns css includes
	*
	* @param void
	* @return string
	*/
	public function tpl_css()
	{
		// review this later
		if (empty($this->css)) return;
		
		$out = '';
		
		foreach ($this->css as $css)
		{
			$out .= "<link type='text/css' rel='stylesheet' href='" . CSS . "$css' />\n";
		}
		
		if ($this->module_css != '')
		{
			global $go;
			
			foreach ($this->module_css as $css)
			{
				$out .= "<link type='text/css' rel='stylesheet' href='module/$go[a]/$css' />\n";
			}
		}
		
		return $out;
	}
	
	
	/**
	* Returns array of js files
	*
	* @param string $js
	* @return array
	*/
	public function add_js($js)
	{
		if (!isset($this->js[$js])) $this->js[$js] = $js;
	}
	
	
	/**
	* Returns array of js files
	*
	* @param string $js
	* @return array
	*/
	public function add_extended_js($js)
	{
		if (!isset($this->ex_js[$js])) $this->ex_js[$js] = $js;
	}
	
	
	/**
	* Returns array of js files
	*
	* @param string $js
	* @return array
	*/
	public function del_js($js)
	{
		if (isset($this->js[$js])) unset($this->js[$js]);
	}
	
	
	/**
	* Returns js includes
	*
	* @param void
	* @return string
	*/
	public function tpl_js()
	{
		if ($this->js == '') return;
		
		$out = '';
		$out .= "\n";

		if ($this->js != '')
		{
			foreach ($this->js as $js)
			{
				$out .= "<script type='text/javascript' src='" . JS . "$js'></script>\n";
			}
		}
		
		if ($this->module_js != '')
		{
			global $go;
			$OBJ =& get_instance();

			$path = ($OBJ->template->extended == true) ? '../ndxzsite/extend/module/' : 'module/';
			
			
			foreach ($this->module_js as $js)
			{
				$out .= "<script type='text/javascript' src='{$path}$go[a]/$js'></script>\n";
			}
		}
		
		return $out;
	}
	
	
	/**
	* Returns string template output
	*
	* @param string $template
	* @return string
	*/
	public function output($template)
	{
		$OBJ =& get_instance();
		
		return $this->tpl_test($template);
	}
	
	
	/**
	* Returns string template output
	*
	* @param string $template
	* @return string
	*/
	public function popup($template)
	{
		$OBJ =& get_instance();
		
		return $this->tpl_test($template);
	}

	
	/**
	* Returns preferences to page
	*
	* @param void
	* @return string
	*/
	public function tpl_prefs()
	{
		$out = '';
		
		if (is_array($this->tpl_prefs_nav()))
		{
			foreach ($this->pref_nav as $pref)
			{
				$attr = (!isset($pref['attr'])) ? null : $pref['attr'];
				$out .= ' ' . href($pref['pref'], $pref['link'], $attr);
			}
		}
		
		return $out;
	}
	
	
	/**
	* Returns top navigation
	*
	* @param void
	* @return string
	*/
	public function tpl_site_menu()
	{
		global $go;
		
		$OBJ =& get_instance();

		$first = ''; $navs = ''; $out = '';
		
		if (!is_array($this->tpl_modules())) show_error('no menu created');
		
		$nav = $this->tpl_modules();
		
		$navs .= "<ul id='nav'>\n";
		
		foreach ($nav as $key => $doit)
		{
			// exhibits is always first
			if ($doit == 'exhibits')
			{
				$active = ($go['a'] == $doit) ? TRUE : FALSE;
				$onoff = ($active == TRUE) ? "class='on'" : "class='off'";
				$first = li(href(ucwords($this->tpl_indexhibit()), "?a=$doit"), $onoff);
			}
			else
			{
				$active = ($go['a'] == $doit) ? TRUE : FALSE;
				$onoff = ($active == TRUE) ? "class='on'" : "class='off'";
				$out .= li(href(ucwords($OBJ->lang->word($doit)), "?a=$doit"), $onoff);
			}
		}
		
		$navs .= $first . $out;
		
		$navs .= "</ul>\n";
		
		return $navs;
	}
	

	/**
	* Returns array of installed modules
	*
	* @param void
	* @return array
	*/
	public function tpl_modules()
	{
		// let's get the folders and info...
		$modules = array();
		$path = DIRNAME . BASENAME . '/module/';

		if (is_dir($path))
		{
			if ($fp = opendir($path)) 
			{
				while (($module = readdir($fp)) !== false) 
				{
					if ((!preg_match("/^_/i",$module)) && (!preg_match("/^CVS$/i",$module)) && (!preg_match("/.php$/i",$module)) && (!preg_match("/.html$/i",$module)) && (!preg_match("/.DS_Store/i",$module)) && (!preg_match("/\./i",$module)) && (!preg_match("/system/i",$module))  && (!preg_match("/dispatch/i", $module)))
					{      
						$modules[] = $module;
					}
				} 
			}
			closedir($fp);
		}
		/*
		$path2 = DIRNAME . '/ndxzsite/extend/module/';
		//$m = array();

		if (is_dir($path2))
		{
			if ($fp = opendir($path2)) 
			{
				while (($m = readdir($fp)) !== false) 
				{
					if ((!preg_match("^_",$m)) && (!preg_match("^CVS$",$m)) && (!preg_match(".php$",$m)) && (!preg_match(".html$",$m)) && (!preg_match(".DS_Store",$m)) && (!preg_match("\.",$m)) && (!preg_match("system",$m)))
					{      
						$modules[] = $m;
					}
				} 
			}
			closedir($fp);
		}
		*/

		sort($modules);
		clearstatcache();
		return $modules;
	}
	
	
	/**
	* Returns array of preference parts
	*
	* @param void
	* @return array
	*/
	public function tpl_prefs_nav()
	{
		$OBJ =& get_instance();
		
		//$this->pref_nav['view'] = array(
		//	'pref' => '<strong>' . $OBJ->lang->word('view site') . '</strong>', 'link' => BASEURL . '/', 'attr' => "class='prefs'");
		
		if ($OBJ->access->is_admin())
		{
			$this->pref_nav['settings'] = array(
			'pref' => $OBJ->lang->word('admin'), 'link' => '?a=system', 'attr' => "class='prefs'");
		}
		
		$this->pref_nav['prefs'] = array(
			'pref' => $OBJ->lang->word('preferences'), 'link' => '?a=system&q=preferences', 'attr' => "class='prefs'");
			
		$this->pref_nav['help'] = array(
			'pref' => $OBJ->lang->word('help'), 'link' => 'http://www.indexhibit.org/forum/', 'attr' => "class='prefs'");
			
		$this->pref_nav['logout'] = array(
			'pref' => $OBJ->lang->word('logout'), 'link' => '?a=system&amp;q=logout', 'attr' => "class='prefs'");
			
		return $this->pref_nav;
	}
	
	
	/**
	* Returns string
	*
	* @param void
	* @return string
	*/
	public function tpl_indexhibit()
	{
		$OBJ =& get_instance();
		return $OBJ->lang->word('indexhibit');
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_foot_left()
	{
		return "&copy; 2007-" . date('Y'); 
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_foot_right()
	{
		$OBJ =& get_instance();

		//return "<a href='http://www.indexhibit.org/'>Indexhibit<small><sup>™</sup></small> v" . $OBJ->access->settings['version'] . "</a> | <a href='#' onclick=\"OpenWindow('" . BASEURL . "/ndxzstudio/?a=system&amp;q=credits', 'Credits', 375, 425, 'yes'); return false;\">" . $OBJ->lang->word('credits') . "</a>";
		return "<a href='http://www.indexhibit.org/'>Indexhibit<small><sup>™</sup></small> v" . $OBJ->access->settings['version'] . "</a>";
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_location()
	{
		global $go;
		$OBJ =& get_instance();
		
		$addition = (isset($this->location)) ? "$this->location": '';
		
		//$location = ($this->location_override == '') ? $OBJ->lang->word($go['a']) : $this->location_override;

		return $addition;
		//return $location . $addition;
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_action()
	{
		$OBJ =& get_instance();
		
		//$color = ($this->action_error != '') ? 'action-error' : 'action';
		
		if ($this->action_update != '')
		{
			return " <span class='action'>" . $OBJ->lang->word($this->action_update) . "</span>";
		}
		
		if ($this->action_error != '')
		{
			return " <span class='action-error'>" . $OBJ->lang->word($this->action_error) . "</span>";
		}
		
		return '';

	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_sub_location()
	{
		if ($this->sub_location == '') return;
		
		$OBJ =& get_instance();
		
		$out = '';
		
		foreach ($this->sub_location as $sub)
		{
			$attr = (!isset($sub[2])) ? null : $sub[2];
			
			$out .= ' ' . href($OBJ->lang->word($sub[0]), $sub[1], $attr);
		}
		
		return $out;
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_form_type()
	{
		return ($this->form_type == TRUE) ? " enctype='multipart/form-data'" : '';
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_form_action()
	{
		return ($this->form_action == '') ? '' : $this->form_action;
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_form_onsubmit()
	{
		return ($this->form_type != FALSE) ? " onsubmit=\"$this->form_onsubmit\"" : '';
	}
	
	
	/**
	* Return array
	*
	* @param void
	* @return array
	*/
	public function tpl_add_script()
	{
		$out = '';
		
		if ($this->ex_css != '')
		{
			$out .= "\n<style type='text/css'>\n";
			
			foreach ($this->ex_css as $css)
			{
				$out .= "$css\n";
			}
			
			$out .= "</style>\n\n";
		}

		if (($this->ex_js != '') || ($this->onready != '') || ($this->onload != ''))
		{
			$out .= "\n<script type='text/javascript'>\n";

			if ($this->ex_js != '')
			{
				$jsx = '';
				
				foreach ($this->ex_js as $js)
				{
					if ($js != '') $jsx .= "$js\n";
				}
				
				$out .= ($jsx != '') ? $jsx : '';
			}
		
			if ($this->onready != '')
			{
				$jsx = '';
			
				foreach ($this->onready as $js)
				{
					if ($js != '') $jsx .= "$js\n";
				}
				
				$out .= ($jsx != '') ? "\n$(document).ready(function()\n{\n" . $jsx . "});\n" : '';
			}
			
			if ($this->onload != '')
			{
				$jsx = '';
				
				foreach ($this->onload as $js)
				{
					if ($js != '') $jsx .= "$js\n";
				}
				
				$out .= ($jsx != '') ? "\nwindow.onload = function() {\n" . $jsx . "};\n" : '';
			}
			
			/*
			if ($this->onunload[0] != '')
			{	
				$jsx = '';

				foreach ($this->onunload as $js)
				{
					if ($js != '') $jsx .= "$js\n";
				}
				
				$out .= ($jsx != '') ? "\nwindow.onunload = function() {\n" . $jsx . "};\n" : '';
			}
			*/
		
			$out .= "</script>\n";
		}
		
		return $out;
	}

	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_toggler()
	{
		if ($this->toggler == '') return;
		
		$OBJ =& get_instance();
		
		$out = '';
		
		foreach ($this->toggler as $key => $tab)
		{
			$attr = (!isset($tab[2])) ? 'left' : 'right';
			$float = ($attr == 'right') ? "float:right;" : "float:left;";
			$show = ($key == 0) ? " class='tabOn'" : " class='tabOff'";

			$out .= li(href($tab[0],"#"),"id='a$tab[0]' style='$float' onclick=\"editTab('$tab[0]');\"$show");
		}
		
		return ul($out,"class='tabs'").div('<!-- -->',"class='cl'");
	}
	
	
	/**
	* Return pagination array
	*
	* @param integer $row
	* @param integer $lim
	* @param string $string
	* @param string $string
	* @return array
	*/
function tpl_paginate($row, $lim, $query, $string='')
{
	$OBJ =& get_instance();
	global $go;
	
	// not happy with this...
	$rs = $OBJ->db->fetchArray($query);
	$num = ($rs === false) ? 0 : count($rs);

	$var = $row - $lim;
		
	if (($row != 0) && (($row - $lim) >= 0) && ($row != ""))
	{
		$back = href('&laquo; '.$OBJ->lang->word('previous'), $string."&amp;page=$var");
	} 
	else 
	{ 
		$back = "&nbsp;";	
	}


	if (($row + $lim) < $num) 
	{ 
		$var = $row + $lim;
		
		$next = href(" ".$OBJ->lang->word('next')." &raquo;", $string."&amp;page=$var");	
	} 
	else 
	{
		$next = '';
	}

	$s['total'] = $num;
	$s['back']	= $back;
	$s['next']	= $next;
		
	return $s;
}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function tpl_pop_links()
	{
		if ($this->pop_links == '') return;
		
		$OBJ =& get_instance();
		
		$out = '';
		
		foreach ($this->pop_links as $sub)
		{
			$attr = (!isset($sub[2])) ? null : $sub[2];
			
			$out .= ' ' . href($OBJ->lang->word($sub[0]), $sub[1], $attr);
		}
		
		return $out;
	}
	
	
	/**
	* Returns array of js files
	*
	* @param string $js
	* @return array
	*/
	public function add_module_js($js)
	{
		if (!isset($this->module_js[$js])) $this->module_js[$js] = $js;
	}
	
	
	/**
	* Returns array of css files
	*
	* @param string $css
	* @return array
	*/
	public function add_module_css($css)
	{
		if (!isset($this->module_css[$css])) $this->module_css[$css] = $css;
	}
	
	
	/**
	* Return string
	*
	* @param void
	* @return string
	*/
	public function get_special_js()
	{
		if ($this->special_js != '') 
		{
			return $this->special_js;
		}
		else
		{
			return '';
		}
	}
	
	
	public function tpl_speed()
	{
		return;
		global $default;

		$time_end = microtime_float();
		$time = number_format($time_end - $default['timer'], 3);

		return ($default['timer'] == true) ? "<div style='margin-bottom: 18px;'>$time seconds</div>\n" : '';
	}
}


?>