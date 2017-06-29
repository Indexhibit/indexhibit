<?php


function jxs_box()
{
	$OBJ =& get_instance();
	global $default;

	$media_id = (isset($_POST['i'])) ? (int) $_POST['i'] : 0;
	
	$rs = $OBJ->db->fetchRecord("SELECT * FROM ".PX."objects, ".PX."media 
		WHERE media_id = '$media_id' 
		AND media_ref_id = id");
	
	$title = ($rs['media_title'] == '') ? '' : "<div class='title'>" . $rs['media_title'] . "</div>";
	$caption = ($rs['media_caption'] == '') ? '' : "<div class='caption'>" . $rs['media_caption'] . "</div>";
	
	if (in_array($rs['media_mime'], $default['media']))
	{
		// if it's a movie
		$file = DIRNAME . '/files/gimgs/' . $rs['id'] . '_' . $rs['media_file'];
	
		// height and width of thumbnail
		$size[0] = $rs['media_x'];
		$size[1] = $rs['media_y'];

		$temp_x = $rs['media_x'] + 30;
		$temp_y = $rs['media_y'] + 30;
		
		// we need the base index.php file for this one
		require_once(DIRNAME . '/ndxzsite/plugin/index.php');

		$a = "<div class='picture_holder' id='node$rs[media_id]' style='width: {$temp_x}px; height: " . ($temp_y + 30) . "px;'>\n";
		$a .= "<div class='picture' style='width: $size[0]px; height: $size[1]px;'>\n";
		$a .= "<div>\n";
		
		//echo $rs['media_mime']; exit;
		$f = $rs['media_mime'];
		
		$a .= $$f($rs['media_file'], $rs['media_x'], $rs['media_y'], $rs['media_thumb']);
		//$a .= "<a href='#' class='link' id='a$rs[media_id]' onclick=\"$.fn.ndxz_grow.grower(this, false);\">Close</a>\n";
		$a .= "</div>\n";
		$a .= "</div>\n";
		$a .= "<div class='captioning' style='width: $size[0]px; text-align: left;'>$title $caption [<a href='#' class='link' id='a$rs[media_id]' onclick=\"$.fn.ndxz_box.grower(this, false); return false;\">Close</a>]</div>\n";
		$a .= "</div>\n\n";
	}
	else
	{
		// do a second check on the thumb here...
		
		
		$file = DIRNAME . '/files/gimgs/' . $rs['id'] . '_' . $rs['media_file'];
	
		// height and width of thumbnail
		$size = getimagesize($file);

		$temp_x = $size[0] + 30;
		$temp_y = $size[1] + 30;
		
		$a .= "<div style='padding: 50px 0 0 50px;'>\n";
		$a .= "<div class='picture_holder' id='node$rs[media_id]' style='width: {$temp_x}px; height: " . ($temp_y + 30) . "px;'>\n";
		$a .= "<div class='picture' style='width: $size[0]px; height: $size[1]px;'>\n";
		$a .= "<div>\n";
		$a .= "<a href='#' class='link' id='a$rs[media_id]' onclick=\"$.fn.ndxz_box.grower(this, false); return false;\">";
		$a .= "<img src='" . $OBJ->core->rs['baseurl'] . "/files/gimgs/" . $rs['id'] . "_" . $rs['media_file'] . "' width='$size[0]' height='$size[1]' alt='yep' />";
		$a .= "</a>\n";
		$a .= "</div>\n";
		$a .= "</div>\n";
		$a .= 'eeeeeeeeeeeeee';
		$a .= "<div class='captioning' style='width: $size[0]px; text-align: left;'>$title $caption</div>\n";
		$a .= "</div>\n";
		$a .= "</div>\n\n";
	}
	
	return $a;
}