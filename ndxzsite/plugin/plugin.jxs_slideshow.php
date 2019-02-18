<?php if (!defined('SITE')) exit('No direct script access allowed');

class Jxs_slideshow
{	
	public function output()
	{
		echo json_encode($this->output); 
		exit;
	}
	
	public function __construct()
	{
		$OBJ =& get_instance();
		global $default;

		$media_id = (isset($_POST['i'])) ? (int) $_POST['i'] : 0;
		$posted_z = (isset($_POST['z'])) ? (int) $_POST['z'] : 0;
	
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
			// if it's a movie else it's a service
			$file = (in_array($rs['media_mime'], $default['media'])) ? DIRNAME . $path . $rs['media_file'] : $rs['media_file'];
			$mime = $rs['media_mime'];
			
			$OBJ->vars->exhibit['id'] = $rs['id'];
			$OBJ->vars->exhibit['object'] = $rs['object'];
			$OBJ->abstracts->front_abstracts();
			
			// how do we get abstracts here?
			$height = (isset($OBJ->abstracts->abstract['height'])) ? $OBJ->abstracts->abstract['height'] : 0;
	
			// height and width of thumbnail
			$size[0] = $rs['media_x'];
			$size[1] = $rs['media_y'];
			
			// new dimensions 
			$new_height = $height;
			$new_width = round(($size[0] * $height) / $size[1]);
		
			$right_margin = (isset($OBJ->hook->options['slideshow_settings']['margin'])) ? 
				$OBJ->hook->options['slideshow_settings']['margin'] : 25;
			$bottom_margin = (isset($OBJ->hook->options['slideshow_settings']['bottom_margin'])) ? 
				$OBJ->hook->options['slideshow_settings']['bottom_margin'] : 25;

			$temp_x = $new_width + $right_margin;
			$temp_y = $new_height + $bottom_margin;
		
			// we need the base index.php file for this one
			require_once(DIRNAME . '/ndxzsite/plugin/index.php');
			
			$file = ($rs['media_dir'] != '') ? $rs['media_dir'].'/'.$rs['media_file'] : $rs['media_file'];

			$click_width = $size[0];
			
			$bottom_setting = ($size[1] - 90);
			
			$adjuster = ($size[0] - $click_width);
			
			// odd vimeo bug
			$mime_display = ($rs['media_mime'] == 'vimeo') ? '' : ' display: none;';
			
			// autoplay is true from this format
			$OBJ->vars->media['autoplay'] = true;
			
			$a = '<div id="slide' . $posted_z . '" class="picture videoslide" style="z-index: ' . $posted_z . '; position: absolute;' . $mime_display . '">';
			
			$a .= "<a href='#' onclick=\"next(); return false;\"><span class='nextlink'></span></a>";
			
			$a .= $mime($file, $new_width, $new_height, $rs['media_thumb']);

			if (($rs['media_title'] == '') && ($rs['media_caption'] == ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= (($rs['media_title'] !=  '') && ($rs['media_caption'] !=  '')) ? "<p>" : '';
				$a .= ($rs['media_title'] !=  '') ? $rs['media_title'] : '';
				$a .= ($rs['media_title'] !=  '') ? " " : '';
				$a .= ($rs['media_caption'] !=  '') ? strip_tags($rs['media_caption'], "a,i,b") : '';
				$a .= (($rs['media_title'] !=  '') && ($rs['media_caption'] !=  '')) ? "</p>" : '';

				$a .= "</div>\n";
				
				
				//$a .= "<div class='captioning'>\n";
				//if ($rs['media_title'] != '') $a .= "<div class='title'>$rs[media_title]</div>\n";
				//if ($rs['media_caption'] != '') $a .= "<div class='caption'>$rs[media_caption]</div>\n";
				//$a .= "</div>\n";
			}

			$a .= "</div>\n\n";

			$this->output['height'] = $rs['media_y'];
			$this->output['output'] = $a;
			return;
		}
		else if (in_array($rs['media_mime'], $default['images'])) // it's an image
		{
			$file = DIRNAME . '/files/gimgs/' . $rs['media_file'];
	
			// height and width of thumbnail
			$size = getimagesize($file);
			
			$OBJ->vars->exhibit['id'] = $rs['id'];
			$OBJ->vars->exhibit['object'] = $rs['object'];
			$OBJ->abstracts->front_abstracts();
			
			// how do we get abstracts here?
			$height = (isset($OBJ->abstracts->abstract['height'])) ? $OBJ->abstracts->abstract['height'] : 575;
			
			// new dimensions 
			$new_height = $height;
			$new_width = round(($size[0] * $new_height) / $size[1]);
			
			//echo $height; exit;
			//print_r($size);
			//echo $new_height . ' / ' . $new_width; exit;

			$click_width = $new_width;
			
			$bottom_setting = ($new_height - 90);
			
			$adjuster = ($new_width - $click_width);
			
			$a = "<div id='slide" . $posted_z . "' class='picture' style='z-index: " . $posted_z . "; position: absolute; display: none;'>";
			
			$a .= "<a style='width: {$click_width}px; height: {$bottom_setting}px;' href='#' onclick=\"next(); return false;\">";
			
			$name = 'rsz_h' . $new_height . '_' . $rs['id'] . '_' . $rs['media_id'] . '.' . $rs['media_mime'];
			
			if (!file_exists(DIRNAME . '/files/dimgs/' . $name))
			{
				$R = load_class('resize', true, 'lib');
			
				// we're going to resize and output
				$R->reformat($new_width, $new_height, $size, $rs, $rs['id'], $name, $rs['media_dir']);
			}
			
			//<img src='" . BASEURL . "/files/dimgs/$name' width='$new_width' height='$new_height' alt='' class='lazyload' />
			$a .= '<img src="' . $OBJ->baseurl . '/files/dimgs/' . $name . '" width="' . $new_width . '" height="' . $height . '" />';
			
			$a .= "</a>";
			
			if (($rs['media_title'] == '') && ($rs['media_caption'] == ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= (($rs['media_title'] !=  '') && ($rs['media_caption'] !=  '')) ? "<p>" : '';
				$a .= ($rs['media_title'] !=  '') ? $rs['media_title'] : '';
				$a .= ($rs['media_title'] !=  '') ? " " : '';
				$a .= ($rs['media_caption'] !=  '') ? strip_tags($rs['media_caption'], "a,i,b") : '';
				$a .= (($rs['media_title'] !=  '') && ($rs['media_caption'] !=  '')) ? "</p>" : '';

				$a .= "</div>\n";
			}		
		
			$a .= "</div>\n";
			
			$this->output['mime'] 	= $rs['media_mime'];
			$this->output['height'] = $size[1];
			$this->output['output'] = $a;
			return;
		}
		else // it's text only
		{
			$OBJ->vars->exhibit['id'] = $rs['id'];
			$OBJ->vars->exhibit['object'] = $rs['object'];
			$OBJ->abstracts->front_abstracts();
			
			// how do we get abstracts here?
			$height = (isset($OBJ->abstracts->abstract['height'])) ? $OBJ->abstracts->abstract['height'] : 575;

			// only if media_mime = txt
			$a .= '<div  id="slide' . $posted_z . '" class="picture" style="z-index: ' . $posted_z . '; position: absolute; height: ' . $height . 'px;">';
			
			// we need to get the text from the file
			$handle = fopen(DIRNAME . '/files/' . $rs['media_file'], 'r');
			$text = fread($handle, 1000000);
			fclose($handle);
			
			// new dimensions 
			$a .= "<div id='slideshow-text'>\n";
			$a .= $text;
			$a .= "</div>\n";
			
			$a .= "</div>\n";
			
			$this->output['mime'] 	= $rs['media_mime'];
			$this->output['height'] = $height;
			$this->output['output'] = $a;
			return;
		}

		$this->output = '';
	}
}