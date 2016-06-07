<?php

/*
Plugin Name: Expanding Menus
Plugin URI: http://www.indexhibit.org/plugin/expanding-menus/
Description: Enables expanding menus. Original version by Ross Cairns.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: pre_load
Function: expanding_menus:go
End
*/

class expanding_menus
{
	function go()
	{
		$OBJ =& get_instance();
		
		// do we need to do a file check for this?
		$OBJ->page->add_jquery('jquery.ndxz_expander.js', 25);	
		$OBJ->page->add_jquery_onready("$('ul.section').ndxzExpander();", 8);
		
		return null;
	}
}