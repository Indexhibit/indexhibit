<?php

/*
Plugin Name: Movement script for #menu
Plugin URI: http://www.indexhibit.org/plugin/movement/
Description: Allows #menu to keep its position if scrolled.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: pre_load
Function: movement:go
End
*/

class Movement
{
	function go()
	{
		$OBJ =& get_instance();

		$OBJ->page->add_jquery('movement.js', 35);
		$OBJ->page->add_jquery_onready("move_up(); $('#menu a').bind('click', function(event) { do_click(); });", 10);
		
		return null;
	}
}