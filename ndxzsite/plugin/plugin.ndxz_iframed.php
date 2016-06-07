<?php

function ndxz_iframed($url='')
{
	if ($url == '') return;
	
	$OBJ =& get_instance();
	global $rs;
	
	$temp = 'plugin.' . $rs['obj_theme'] . '_iframed.php';
	
	if (file_exists(DIRNAME . '/ndxzsite/plugin/' . $temp))
	{
		return load_plugin(DIRNAME . '/ndxzsite/plugin/' . $temp);
	}
	
	$css = "#exhibit .container { padding: 0; }";

$js = "function iframer() 
{ 
	// get width of #exhibit
	var frame_x = $('#exhibit').width(); 
	// get height of #index
	var frame_y = $('#index').height(); 
	
	// apply height and width 
	$('#iframed').css('width', frame_x); 
	$('#iframed').css('height', frame_y); 
} 

$(window).resize( function() { iframer(); } );";

	$OBJ->page->add_jquery('blank.js', 19);
	$OBJ->page->add_jquery_onready("iframer();", 15);

	//$OBJ->page->exhibit['onready'][] = "iframer();";
	$OBJ->page->exhibit['dyn_js'][] = $js;
	$OBJ->page->exhibit['dyn_css'][] = $css;
		
	return "<iframe src='$url' frameborder='0' id='iframed'></iframe>\n";
}