<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: No Show
Format URI: http://www.indexhibit.org/format/no-show/
Description: Indexhibit format.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Params: format
Source: exhibit
*/

/**
* No Show - no images
*
* Exhbition format
* 
* @version 2.0
* @author Vaska 
*/

class Exhibit
{
	public function __construct()
	{
		
	}
	
	function createExhibit()
	{
		$OBJ =& get_instance();
	
		$OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content'];
		return $OBJ->page->exhibit['exhibit'];
	}
}