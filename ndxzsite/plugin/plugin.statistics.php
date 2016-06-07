<?php

/*
Plugin Name: Indexhibit Statistics
Plugin URI: http://www.indexhibit.org/plugin/statistics/
Description: Controls statistics.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: pre_load
Function: statistics:start_stats
End

Plugin Name: Indexhibit Statistics
Plugin URI: http://www.indexhibit.org/plugin/statistics/
Description: Controls statistics.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: closing
Function: statistics:do_stats
End
*/

class Statistics
{
	var $system;

	function __construct()
	{
		$OBJ =& get_instance();
		$this->system = ($OBJ->vars->exhibit['cms'] == true) ? true : false;
	}

	function start_stats()
	{
		if ($this->system == true) return;

		$OBJ =& get_instance();
		$OBJ->page->add_jquery('statistics.js', 51);
	}
	
	function do_stats()
	{
		if ($this->system == true) return;

		return "<script type='text/javascript'>do_statistics();</script>";
	}
}