<?php if (!defined('SITE')) exit('No direct script access allowed');


function backgrounder()
{
	$OBJ =& get_instance();
	
	if (($OBJ->vars->exhibit['color'] == '') && ($OBJ->vars->exhibit['bgimg'] = '')) return;
	
	$style = (strtolower($OBJ->vars->exhibit['color']) != 'ffffff') ? "background-color: #" . $OBJ->vars->exhibit['color'] . ";" : '';
	
	$tiling = ($OBJ->vars->exhibit['tiling'] != 1) ? 'no-repeat' : 'repeat';
	
	$style .= ($OBJ->vars->exhibit['bgimg'] != '') ? "\nbackground-image: url(" . $OBJ->baseurl . "/files/" . $OBJ->vars->exhibit['bgimg'] . ");\nbackground-repeat: $tiling;\nbackground-position: 215px 0;\nbackground-attachment: fixed;\n" : '';
	
	// nothing to add
	if ($style == '') return;
	
	$OBJ->page->exhibit['dyn_css'][] = "body { $style }";
}