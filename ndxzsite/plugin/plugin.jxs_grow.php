<?php

class Jxs_grow
{
	public function __construct()
	{
		
	}
	
	function output()
	{
		$OBJ =& get_instance();
		global $default;
	
		// we need this since we are using some of it's tools later down the chain
		// doesn't hurt if we don't use them as well...
		//$OBJ->lib_class('page', true, 'lib');

		$media_id = (isset($_POST['i'])) ? (int) $_POST['i'] : 0;
	
		$rs = $OBJ->db->fetchRecord("SELECT * FROM ".PX."objects, ".PX."media 
			WHERE media_id = '$media_id' 
			AND media_ref_id = id");	
	
		$caption = ($rs['media_title'] == '') ? '' : "<div class='title'>" . $rs['media_title'] . "</div>";
		$caption .= ($rs['media_caption'] == '') ? '' : "<div class='caption'>" . $rs['media_caption'] . "</div>";
	
		// tmep
		$vids = array_merge($default['media'], $default['services']);
	
		$path = ($rs['media_dir'] != '') ? "/files/$rs[media_dir]/" : '/files/gimgs/';
	
		if (in_array($rs['media_mime'], $vids))
		{	
			// if it's a movie
			$file = DIRNAME . $path . $rs['media_file'];
	
			// height and width of thumbnail
			$size[0] = $rs['media_x'];
			$size[1] = $rs['media_y'];
		
			$right_margin = (isset($OBJ->hook->options['visual_index_defaults']['margin'])) ? 
				$OBJ->hook->options['visual_index_defaults']['margin'] : 25;
			$bottom_margin = (isset($OBJ->hook->options['visual_index_defaults']['bottom_margin'])) ? 
				$OBJ->hook->options['visual_index_defaults']['bottom_margin'] : 25;

			$temp_x = $rs['media_x'] + $right_margin;
			$temp_y = $rs['media_y'] + $bottom_margin;
		
			// we need the base index.php file for this one
			require_once(DIRNAME . '/ndxzsite/plugin/index.php');

			$a = "<div class='picture_holder' id='node$rs[media_id]' style='width: {$temp_x}px; height: " . ($temp_y + 30) . "px;'>\n";
			$a .= "<div class='picture' style='width: $size[0]px; height: $size[1]px;'>\n";
			$a .= "<div style='position: relative;'>\n";
		
			$f = $rs['media_mime'];
			
			$file = ($rs['media_dir'] != '') ? "$rs[media_dir]/" . $rs['media_file'] : $rs['media_file'];
			
			// need a flag for autoplay
			$a .= $f($file, $rs['media_x'], $rs['media_y'], $rs['media_thumb']);
			//$a .= "<script type='text/javascript'>$.fn.jwplayer(); return false;</script>";
			
			$close = "[<a href='#' class='link' id='a$rs[media_id]' onclick=\"$.fn.ndxz_grow.grower(this, false); return false;\">Close</a>]";
			
			$caption = ($rs['media_title'] == '') ? "<div class='title'>$close</div>" : "<div class='title'>$close " . $rs['media_title'] . "</div>";
			$caption .= ($rs['media_caption'] == '') ? '' : "<div class='caption'>" . $rs['media_caption'] . "</div>";
			
			// close things
			//$a .= "<div style='background: white; position: absolute; top: 0; right: 0; z-index: 1;'><a href='#' class='link' id='a$rs[media_id]' onclick=\"$.fn.ndxz_grow.grower(this, false); return false;\">Close</a></div>";
		
			$a .= "</div>\n";
			$a .= "</div>\n";
			$a .= "<div class='captioning' style='width: $size[0]px; text-align: left;'>$caption</div>\n";
			$a .= "</div>\n\n";
		}
		else
		{
			// do a second check on the thumb here...
		
		
			$file = DIRNAME . '/files/gimgs/' . $rs['id'] . '_' . $rs['media_file'];
	
			// height and width of thumbnail
			$size = getimagesize($file);

			$right_margin = (isset($OBJ->hook->options['visual_index_defaults']['margin'])) ? 
				$OBJ->hook->options['visual_index_defaults']['margin'] : 25;
			$bottom_margin = (isset($OBJ->hook->options['visual_index_defaults']['bottom_margin'])) ? 
				$OBJ->hook->options['visual_index_defaults']['bottom_margin'] : 25;
			$text_block_height = (isset($OBJ->hook->options['visual_index_defaults']['text_box_height'])) ? 
				$OBJ->hook->options['visual_index_defaults']['text_box_height'] : 18;

			$temp_x = $size[0] + $right_margin;
			$temp_y = $size[1] + $bottom_margin;

			$a = "<div class='picture_holder' id='node$rs[media_id]' style='width: {$temp_x}px; padding-bottom: {$bottom_margin}px;'>\n";
			$a .= "<div class='picture' style='width: $size[0]px; height: $size[1]px; position: relative;'>\n";
			$a .= "<div>\n";
			$a .= "<a href='#' class='link' id='a$rs[media_id]' onclick=\"$.fn.ndxz_grow.grower(this, false); return false;\">";
			$a .= "<img src='" . $OBJ->vars->exhibit['baseurl'] . '/files/gimgs/' . $rs['id'] . "_" . $rs['media_file'] . "' width='$size[0]' height='$size[1]' alt='yep' />";
			$a .= "</a>\n";
			$a .= "</div>\n";
			//if ($caption != '') $a .= "<div style='width: $size[0]px; text-align: left; z-index: 1; bottom: 0; left: 0; position: absolute;'><div style='padding: 6px; background: #000; color: transparent; opacity: 0.35;'>$caption</div></div>\n";
			//if ($caption != '') $a .= "<div style='width: $size[0]px; text-align: left; z-index: 2; bottom: 0; left: 0; position: absolute; color: white;'><div style='padding: 6px; color: white;'>$caption</div></div>\n";
			$a .= "</div>\n";
			if ($caption != '') $a .= "<div style='width: $size[0]px; text-align: left;'>$caption</div>\n";
			$a .= "</div>\n\n";
		}
	
		return $a;
	}
}