<?php if (!defined('SITE')) exit('No direct script access allowed');


function ndxz_title()
{
	$OBJ =& get_instance();
	
	// need to deal with section tops too
	return ($OBJ->vars->exhibit['section_top'] == 1) ? 
		strip_tags($OBJ->vars->exhibit['sec_desc']) : 
		strip_tags($OBJ->vars->exhibit['title']);
}