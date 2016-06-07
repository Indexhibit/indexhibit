<?php

function mp3_player()
{
	$OBJ =& get_instance();
	
	// get files
	$musics = $OBJ->db->fetchArray("SELECT * FROM ".PX."media 
		WHERE media_ref_id = '" . $OBJ->vars->exhibit['id'] . "' 
		AND media_mime = 'mp3' 
		AND media_hide = '0' 
		ORDER BY media_order ASC");
		
	if (!$musics) return;
	
	$s = "<div id='mp3_player'>\n<ol class='flat'>\n";
	
	foreach ($musics as $key => $music)
	{
		// we check if file exists first
		if (file_exists(DIRNAME . "/files/$music[media_file]"))
		{
			$title = ($music['media_title'] != '') ? $music['media_title'] : $music['media_file'];
			
			$s .= "<li><a href='" . BASEURL . "/files/$music[media_file]' class='sm2_link'>$title</a></li>\n";
		}
	}
	
	$s .= "</ol>\n</div>\n";
	
	$OBJ->page->add_lib_js('soundmanager2-nodebug-jsmin.js', 50);
	$OBJ->page->add_lib_js('inlineplayer.js', 51);
	$OBJ->page->exhibit['lib_css'][] = "audio.css";

 	$OBJ->page->add_jquery_onready("soundManager.url = '" . BASEURL . "/ndxzsite/img/';
soundManager.flashVersion = 9;
soundManager.useFlashBlock = false;", 6);
	
	return $s;
}