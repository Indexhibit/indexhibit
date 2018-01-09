<?php

/*
Plugin Name: Meta Tags
Plugin URI: http://www.indexhibit.org/plugin/meta-tags/
Description: Easy way to add keyword and description meta tags to your site.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: add_meta_tags
Function: add_meta_keyword_description:add
Order: 11
Options Builder: make_option
End
*/

class add_meta_keyword_description
{
	public function __construct()
	{
		
	}
	
	function add()
	{	
		$OBJ =& get_instance();

		$meta = '';

		if (isset($OBJ->hook->options['add_meta_keyword_description']['keywords']))
		{ 
			$meta .= "<meta name='keywords' content=\"" . $OBJ->hook->options['add_meta_keyword_description']['keywords'] . "\" />\n";
		}
	
		if (isset($OBJ->hook->options['add_meta_keyword_description']['description']))
		{ 
			$meta .= "<meta name='description' content=\"" . $OBJ->hook->options['add_meta_keyword_description']['description'] . "\" />\n";
		}
	
		if (isset($OBJ->hook->options['add_meta_keyword_description']['author']))
		{ 
			$meta .= "<meta name='author' content=\"" . $OBJ->hook->options['add_meta_keyword_description']['author'] . "\" />\n";
		}
	
		if (isset($OBJ->hook->options['add_meta_keyword_description']['copyright']))
		{ 
			$meta .= "<meta name='copyright' content=\"" . $OBJ->hook->options['add_meta_keyword_description']['copyright'] . "\" />\n";
		}
	
		if (isset($OBJ->hook->options['add_meta_keyword_description']['robots']))
		{ 
			$meta .= "<meta name='Robots' content=\"" . $OBJ->hook->options['add_meta_keyword_description']['robots'] . "\" />\n";
		}
	
		if (isset($OBJ->hook->options['add_meta_keyword_description']['revisit']))
		{ 
			$meta .= "<meta name='Revisit-after' content=\"" . $OBJ->hook->options['add_meta_keyword_description']['revisit'] . "\" />\n";
		}
	
		$meta .= "<meta name='generator' content=\"Indexhibit\" />";
	
		return $meta;
	}

	function make_option()
	{
		$keywords = (isset($this->options['keywords'])) ? $this->options['keywords'] : '';
		$description = (isset($this->options['description'])) ? $this->options['description'] : '';
		$copyright = (isset($this->options['copyright'])) ? $this->options['copyright'] : '';
		$author = (isset($this->options['author'])) ? $this->options['author'] : '';
		$robots = (isset($this->options['robots'])) ? $this->options['robots'] : '';
		$revisit = (isset($this->options['revisit'])) ? $this->options['revisit'] : '';
	
		$html = "<label>Keywords</label>\n";
		$html .= "<p><textarea name='option[keywords]' style='width: 400px; height: 85px;'>" . $keywords . "</textarea></p>\n";
	
		$html .= "<label>Description</label>\n";
		$html .= "<p><textarea name='option[description]' style='width: 400px; height: 85px;'>" . $description . "</textarea></p>\n";
	
		$html .= "<label>Author</label>\n";
		$html .= "<p><input name='option[author]' type='text' value=\"" . $author . "\" /></p>\n";
	
		$html .= "<label>Copyright</label>\n";
		$html .= "<p><input name='option[copyright]' type='text' value=\"" . $copyright . "\" /></p>\n";
	
		$html .= "<label>Robots</label>\n";
		$html .= "<p><select name='option[robots]'>\n";
		$html .= "<option value=''>Make Selection</option>\n";
		$html .= "<option value='INDEX,FOLLOW'" . $this->selected($robots, "INDEX,FOLLOW") . ">INDEX,FOLLOW</option>\n";
		$html .= "<option value='INDEX,NOFOLLOW'" . $this->selected($robots, "INDEX,NOFOLLOW") . ">INDEX,NOFOLLOW</option>\n";
		$html .= "<option value='NOINDEX,FOLLOW'" . $this->selected($robots, "NOINDEX,FOLLOW") . ">NOINDEX,FOLLOW</option>\n";
		$html .= "<option value='NOINDEX,NOFOLLOW'" . $this->selected($robots, "NOINDEX,NOFOLLOW") . ">NOINDEX,NOFOLLOW</option>\n";
		$html .= "</select></p>\n";
	
		$html .= "<label>Revisit After</label>\n";
		$html .= "<select name='option[revisit]'>\n";
		$html .= "<option value=''>Make Selection</option>\n";
		$html .= "<option value='1 Day'" . $this->selected($revisit, "1 Day") . ">1 Day</option>\n";
		$html .= "<option value='7 Days'" . $this->selected($revisit, "7 Days") . ">7 Days</option>\n";
		$html .= "<option value='31 Days'" . $this->selected($revisit, "31 Days") . ">31 Days</option>\n";
		$html .= "<option value='180 Days'" . $this->selected($revisit, "180 Days") . ">180 Days</option>\n";
		$html .= "<option value='365 Days'" . $this->selected($revisit, "365 Days") . ">365 Days</option>\n";
		$html .= "</select>\n";
	
		return $html;
	}

	function selected($var='', $check='')
	{
		return ($var == $check) ? " selected='selected'" : '';
	}
}