<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: No Thumbs
Format URI: http://www.indexhibit.org/format/no-thumbs/
Description: Default Indexhibit format.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Params: format,images,placement,titling
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
		global $rs, $exhibit;
		
		// images only format
		$OBJ->vars->media = array('jpg', 'jpeg', 'png', 'gif');
	
		// get images
		$this->imgs = $OBJ->page->get_imgs();
		
		// ** DON'T FORGET THE TEXT ** //
		$s = $rs['content'];

		if (!$this->imgs) return $s;
		
		$OBJ->page->exhibit['dyn_css'][] = $this->dynamicCSS();
	
		$i = 1; $a = ''; $s = '';

		foreach ($this->imgs as $images)
		{
			foreach ($images as $go)
			{
				$title 		= ($go['media_title'] == '') ? '&nbsp;' : $go['media_title'];
				$caption 	= ($go['media_caption'] == '') ? '&nbsp;' : $go['media_caption'];
		
				if ($OBJ->vars->exhibit['break'] != 0)
				{
					if ($i == $OBJ->vars->exhibit['break'])
					{
						$i = 0;
						$break = "<div style='clear:left;'><!-- --></div>";
					}
					else
					{
						$break = '';
					}
				}
				else
				{
					$break = '';
				}
				
				$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "\n<span class='nothumb'><img src='" . BASEURL . GIMGS . "/" . $OBJ->vars->exhibit['id'] . "_$go[media_file]' /><strong>$title</strong> $caption</span>$break\n" : 
				"\n<span class='nothumb'><img src='" . BASEURL . GIMGS . "/" . $OBJ->vars->exhibit['id'] . "_$go[media_file]' /></span>$break\n";
		
				$i++;
			}
		}
	
		// images
		$s .= "<div id='img-container'>\n";
		$s .= ($OBJ->vars->exhibit['placement'] == 0) ? $OBJ->vars->exhibit['content'] . $a : 
			$a . "<div class='cl'><!-- --></div>" . $OBJ->vars->exhibit['content'];
		$s .= "</div>\n";
		
		return $s;
	}


	function dynamicCSS()
	{
		return ".nothumb { float: left; padding: 0 1px 1px 0;  }
.nothumb img { display: block; margin: 0; }";
	}
}