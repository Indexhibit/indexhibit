<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Random Image
Format URI: http://www.indexhibit.org/format/random-image/
Description: Returns a random image from the exhibit.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Params: format,images
Source: exhibit
Objects: exhibits
*/

class Exhibit
{
	public function __construct()
	{

	}
	
	function createExhibit()
	{
		$OBJ =& get_instance();
		global $default;
		
		// exhibit only source
		$this->source = $default['filesource'][0];
	
		// get images
		$OBJ->vars->images = $OBJ->page->get_imgs();

		// if no images return our text only
		if (!$OBJ->vars->images[0]) { $OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content']; return; }
		
		$total = count($OBJ->vars->images[0]);
		$rand = rand(0, ($total - 1));
		
		$s = "\n<div id='img-container' style='margin-bottom: 3px;'>\n";
		
		// if image
		if (in_array($OBJ->vars->images[0][$rand]['media_mime'], $default['images']))
		{
			$s .= "<img src='" . $OBJ->vars->images[0][$rand]['media_path'] . "' />";
		}
		else
		{
			$mime = $OBJ->vars->images[0][$rand]['media_mime'];

			$s .= $mime($OBJ->vars->images[0][$rand]['media_file'], 
				$OBJ->vars->images[0][$rand]['media_x'], 
				$OBJ->vars->images[0][$rand]['media_y'], 
				$OBJ->vars->images[0][$rand]['media_thumb']);
		}
		
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['exhibit'] = ($OBJ->vars->exhibit['placement'] == 1) ? 
			$s . $OBJ->vars->exhibit['content'] : 
			$OBJ->vars->exhibit['content'] . $s;

		return $OBJ->page->exhibit['exhibit'];
	}
}