<?php

class Jxs_ndxzbox
{
	var $rs = array();
	var $centered = false;
	var $html;
	var $height;
	var $x;
	var $y;
	var $description;

	public function __construct()
	{
		$OBJ =& get_instance();
		global $default;
		
		$this->height = (int) $_POST['height'];

		$media_id = (isset($_POST['i'])) ? (int) $_POST['i'] : 0;

		$this->rs = $OBJ->db->fetchRecord("SELECT * FROM ".PX."objects, ".PX."media 
			WHERE media_id = '$media_id' 
			AND media_ref_id = id");
			
		//sleep(1);

		$this->centered();
	}
	
	public function output()
	{
		$x['output'] = $this->html;
		$x['width'] = $this->x;
		$x['height'] = $this->y;
		$x['description'] = ($this->description != '') ? $this->description : '';
		echo json_encode($x); 
		exit;
	}
	
	public function centered()
	{
		$OBJ =& get_instance();
		global $default;

		$vids = array_merge($default['media'], $default['services']);
	
		if (in_array($this->rs['media_mime'], $vids))
		{
			// we need the base index.php file for this one
			require_once(DIRNAME . '/ndxzsite/plugin/index.php');

			// if it's a movie
			$file = DIRNAME . '/files/' . $this->rs['media_file'];

			// height and width of thumbnail
			$size[0] = $this->rs['media_x'];
			$size[1] = $this->rs['media_y'];
			
			$this->x = $size[0];
			$this->y = $size[1];

			$temp_x = $this->rs['media_x'] + 30;
			$temp_y = $this->rs['media_y'] + 30;
			
			$next_style = "style='display: block; position: absolute; top: 0; right: 0px; z-index: 2; width: 15%;'";
			$prev_style = "style='display: block; position: absolute; top: 0; left: 0px; z-index: 2; width: 15%;'";
			
			$a = "<div id='o" . $this->rs['media_id'] . "' class='dialog-content' style='display: none; z-index: 12;'>\n";
			
			$a .= "<a href='#' class='link next' id='a" . $this->rs['media_id'] . "' $next_style onclick=\"next(); return false;\"><!-- --></a>";
			$a .= "<a href='#' class='link previous' id='a" . $this->rs['media_id'] . "' $prev_style onclick=\"previous(); return false;\"></a>";
			
			$a .= "<div id='innerd' style='text-align: center; margin: 0; position: relative;'>";
			
			// this forms the box around the image...possible white background and/or shadow
			$a .= "<div style='display: inline-block; position: relative;'>";
			
			$mime = $this->rs['media_mime'];
			
			// we need to account for services here!
			if (function_exists($mime))
			{
				$file = ($this->rs['media_dir'] == '') ? $this->rs['media_file'] : $this->rs['media_dir'] . '/' .  $this->rs['media_file'];
				$a .= $mime($file, $this->rs['media_x'], $this->rs['media_y'], $this->rs['media_thumb']);
				//$a .= $mime;
			}
			
			$a .= "</div>\n";

			$a .= "</div>\n\n"; // close 'innerd'
			$a .= "<div>\n";
			
			// information
			$d = ($this->rs['media_title'] != '') ? "<div id='dialog-title'>" . $this->rs['media_title'] . "</div>\n" : "";
			//if ($this->rs['media_caption'] != '') $d .= "<div style='width: 250px;'><div id='toggle' style='background: black; padding: 3px 6px 6px 0;'>" . $this->rs['media_caption'] . "\n";
			$d .= "</div></div>\n";
			
			$this->description = "<div style='padding: 27px 0 0 27px;'>" . $d . "</div>";
		}
		else // photos
		{
			$file = DIRNAME . '/files/gimgs/' . $this->rs['id'] . '_' . $this->rs['media_file'];

			// height and width of thumbnail
			$size = getimagesize($file);
			
			$this->x = $size[0];
			$this->y = $size[1];

			$temp_x = $size[1] + 30;
			$temp_y = $size[0] + 30;
			
			$next_style = "style='display: block; position: absolute; top: 0; right: 0px; z-index: 1; width: 15%;'";
			$prev_style = "style='display: block; position: absolute; top: 0; left: 0px; z-index: 1; width: 15%;'";
			
			$a = "<div id='o" . $this->rs['media_id'] . "' class='dialog-content' style='display: none; z-index: 12;'>\n";
			
			$a .= "<a href='#' class='link next' id='a" . $this->rs['media_id'] . "' $next_style onclick=\"next(); return false;\"><!-- --></a>";
			$a .= "<a href='#' class='link previous' id='a" . $this->rs['media_id'] . "' $prev_style onclick=\"previous(); return false;\"></a>";
			
			$a .= "<div id='innerd' style='text-align: center; margin: 0; position: relative;'>";
			
			// picture holder
			$a .= "<div id='pic-holder'>\n";
			$a .= "<a href='#' onclick=\"next(); return false;\"><img src='" . $OBJ->baseurl . "/files/gimgs/" . $this->rs['id'] . "_" . $this->rs['media_file'] . "' width='$size[0]' height='$size[1]' alt='yep' /></a>";
			$a .= "</div>\n";
	
			$a .= "</div>\n\n"; // close 'innerd'
			$a .= "</div>\n";
			
			// information
			$d = ($this->rs['media_title'] != '') ? "<div id='dialog-title'>" . $this->rs['media_title'] . "</div>\n" : "";
			if ($this->rs['media_caption'] != '') $d .= "<div id='dialog-box-text'><div id='dialog-toggle'>" . $this->rs['media_caption'] . "\n";
			$d .= "</div></div>\n";
			
			$this->description = "<div style='padding: 27px 0 0 27px;'>" . $d . "</div>";
		}
		
		$this->html = $a;
		return;
	}
}