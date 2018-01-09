<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Plugin Name: Mobile Ready Indexhibit
Plugin URI: http://www.indexhibit.org/plugin/mobile-ready/
Description: Enables an adaptive (mobile ready) version of Indexhibit
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: update_defaults
Function: mobile_ready:enable
Order: 12
End
*/

class Mobile_ready
{
	public function __construct()
	{
		
	}
	
	function enable()
	{
		$OBJ =& get_instance();
		
		// we should do a bit more (later)
		// check that the theme folder and exhibit format exist
		
		// mobile check class
		$browser =& load_class('browser', true, 'lib');

		// add a hook for enabling this
		// if hook and isMobile then true...
		$OBJ->vars->default['isMobile'] = $browser->isMobile();
			
		if ($OBJ->vars->default['isMobile'] == true)
		{ 	
			// these call up our adaptive (mobile) theme and format
			$OBJ->vars->exhibit['obj_theme'] = 'mobile';
			$OBJ->vars->exhibit['format'] = 'mobile';
		}
		
		return false;
	}
}