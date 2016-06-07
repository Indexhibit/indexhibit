<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
	The purpose of this function is to help IE6 layout Indexhibit better.
	In the past this was done with css and was a little odd.
	Javascript can do this better
*/

class IE_layout
{
	function layout()
	{
		$OBJ =& get_instance();
		$OBJ->lib_class('browser');

		if (($OBJ->browser->getBrowser() == Browser::BROWSER_IE) && ($OBJ->browser->getVersion() == 6)) 
		{
			$OBJ->page->add_jquery('ie.layout.js', 28);
		}
	}
}