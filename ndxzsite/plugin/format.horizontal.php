<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Horizontal
Format URI: http://www.indexhibit.org/format/horizontal/
Description: Horizontal format.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Options Builder: default_settings
Params: format,images,placement,titling,custom('height'),custom('textplacement')
Source: exhibit
Objects: exhibits
*/

/**
* Context
*
* Exhbition format
* 
* @version 1.2
* @author Vaska 
*/

class Exhibit
{
	// PADDING AND TEXT WIDTH ADJUSTMENTS UP HERE!!!
	var $picture_block_padding_right = 0;
	var $text_width = 300;
	var $text_padding_right = 35;
	var $final_img_container = 0; // do not adjust this one
	var $imgs = array();
	var $br = 1;
	var $placement;
	var $titles = true;
	var $bottom_margin = 25;
	var $operand = 0;
	var $source;
	var $text_block_height;
	var $collapse = 1;
	var $center = false;
	var $valign = 0;
	var $settings = array();
	var $force_height = 0;
	var $padding_left;
	
	///////////////
	var $x;
	
	function default_settings()
	{
		$OBJ =& get_instance();

		$margin = (isset($this->settings['margin'])) ? $this->settings['margin'] : 0;
		$text_width = (isset($this->settings['text_width'])) ? $this->settings['text_width'] : 200;
		$text_box_height = (isset($this->settings['text_box_height'])) ? $this->settings['text_box_height'] : 0;
		$padding_left = (isset($this->settings['padding_left'])) ? $this->settings['padding_left'] : 0;
	
		$OBJ->template->add_css('themes/base/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<label id='right_margin_value'>separator <span>$margin</span></label>\n";
		$html .= "<input type='hidden' id='right_margin' name='option[margin]' value='$margin' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $margin, max: 500,  
	stop: function(event, ui) { $('#right_margin').val(ui.value); },
	slide: function(event, ui) { $('label#right_margin_value span').html(ui.value) }
	});";
	
		$html .= "<label id='text_width_value'>text width <span>$text_width</span></label>\n";
		$html .= "<input type='hidden' id='text_width' name='option[text_width]' value='$text_width' />\n";
		$html .= "<div id='slider2' style='margin: 10px 0;'></div>\n\n";
	
		$OBJ->template->onready[] = "$('#slider2').slider({ value: $text_width, max: 600, step: 1, 
	stop: function(event, ui) { $('#text_width').val(ui.value); },
	slide: function(event, ui) { $('label#text_width_value span').html(ui.value) }
	});";
	
		$html .= "<label id='padding_left_value'>text padding <span>$padding_left</span></label>\n";
		$html .= "<input type='hidden' id='padding_left' name='option[padding_left]' value='$padding_left' />\n";
		$html .= "<div id='slider4' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider4').slider({ value: $padding_left, max: 50, 
stop: function(event, ui) { $('#padding_left').val(ui.value); },
slide: function(event, ui) { $('label#padding_left_value span').html(ui.value) }
});";	
	
		$html .= "<label id='text_box_height_value'>text box height <span>$text_box_height</span></label>\n";
		$html .= "<input type='hidden' id='text_box_height' name='option[text_box_height]' value='$text_box_height' />\n";
		$html .= "<div id='slider3' style='margin: 10px 0;'></div>\n\n";
	
		$OBJ->template->onready[] = "$('#slider3').slider({ value: $text_box_height, max: 150, 
	stop: function(event, ui) { $('#text_box_height').val(ui.value); },
	slide: function(event, ui) { $('label#text_box_height_value span').html(ui.value) }
	});";
	
		return $html;
	}

	function selected($var='', $check='')
	{
		return ($var == $check) ? " selected='selected'" : '';
	}

	function __construct()
	{
		$OBJ =& get_instance();

		// PADDING AND TEXT WIDTH ADJUSTMENTS UP HERE!!!
		$this->picture_block_padding_right = (isset($OBJ->hook->options['horizontal_settings']['margin'])) ? 
			$OBJ->hook->options['horizontal_settings']['margin'] : 25;
		$this->text_width = (isset($OBJ->hook->options['horizontal_settings']['text_width'])) ? 
			$OBJ->hook->options['horizontal_settings']['text_width'] : 200;
		$this->text_block_height = (isset($OBJ->hook->options['horizontal_settings']['text_box_height'])) ? 
			$OBJ->hook->options['horizontal_settings']['text_box_height'] : 18;
		$this->text_padding_right = 35;
		$this->final_img_container = 0; // do not adjust this one
		$this->padding_left = (isset($OBJ->hook->options['horizontal_settings']['padding_left'])) ? 
			$OBJ->hook->options['horizontal_settings']['padding_left'] : 10;		
		$this->valign = (isset($OBJ->hook->options['horizontal_settings']['valign'])) ? 
			$OBJ->hook->options['horizontal_settings']['valign'] : 0;		
		$this->force_height = (isset($OBJ->abstracts->abstract['height'])) ? 
			$OBJ->abstracts->abstract['height'] : 0;
		
		// thumbs shape == 0 and grid = true...
		if (isset($OBJ->hook->options['horizontal_settings']['placement']))
		{
			$this->placement = ($OBJ->hook->options['horizontal_settings']['placement'] == 1) ? true : false;
		}
		
		$this->title_placement = (isset($OBJ->abstracts->abstract['title-placement'])) ? 
			$OBJ->abstracts->abstract['title-placement'] : 0;
	}
	
	function createExhibit()
	{
		$OBJ =& get_instance();
		global $default;
		
		$this->placement = (isset($OBJ->abstracts->abstract['title-placement'])) ? 
			$OBJ->abstracts->abstract['title-placement'] : 0;
		
		// exhibit only source
		$this->source = $default['filesource'][0];
	
		// get images
		$this->imgs = $OBJ->page->get_imgs();
		
		// INTEGRATE THIS INTO THE MIX LATER!
		$OBJ->vars->images = $this->imgs;

		// if no images return our text only
		if (!$this->imgs) { $OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content']; return; }
	
		$s = ''; $a = ''; $w = 0; $i = 0;
		
		///////////////////
		$this->x = $OBJ->vars->exhibit['thumbs'];
		
		$R = load_class('resize', true, 'lib');

		foreach ($this->imgs as $do)
		{
			$total = count($do); $t = 1;

			foreach ($do as $go)
			{
				// we should do the resize exercises here...
				if ($this->force_height != 0)
				{
					$name = 'rsz_h' . $this->force_height . '_' . $OBJ->vars->exhibit['id'] . '_' . $go['media_id'] . '.' . $go['media_mime'];
					
					if (!file_exists(DIRNAME . '/files/dimgs/' . $name))
					{
						// get dimensions and resize
						// need to set this up for folder source as well - later
						$path = ($go['media_dir'] != '') ? $go['media_dir'] : 'gimgs';
						$source = ($go['media_thumb_source'] == '') ? $go['media_file'] : $go['media_thumb_source'];
						$size = getimagesize(DIRNAME . "/files/$path/" . $source);

						// new dimensions based on the height
						$new_width = (($size[0] * $this->force_height) / $size[1]);
						
						// we're going to resize and output
						$R->reformat($new_width, $this->force_height, $size, $go, $OBJ->vars->exhibit['id'], $name, $go['media_dir']);
						
						$the_file = '/files/dimgs/' . $name;
					}
					else
					{
						$the_file = '/files/dimgs/' . $name;
					}
				}
				else
				{
					$the_file = GIMGS . '/' . $go['media_ref_id'] . '_' . $go['media_file'];
				}
				
				//if (file_exists())

				// space between images/videos
				// width of text space
				// margin
				$margin = $this->padding_left;
				$text_block = $this->text_block_height;
				$separator = $this->picture_block_padding_right;
				
				if ($OBJ->vars->exhibit['titling'] == 1)
				{
					$text = $go['media_title'] . $go['media_caption'];
				}
				else
				{
					$text = '';
				}
				
				$b = '';
				
				// top and bottom titles
				if (($this->placement == 0) || ($this->placement == 2))
				{	
					if (in_array($go['media_mime'], $default['video']))
					{
						$mime = $go['media_mime'];
						
						$size[0] = $go['media_x'];
						$size[1] = $go['media_y'];
						$width_adjust = $size[0];
						
						// need to recalculate proportions if the force height feature is in use
						if ($this->force_height != 0)
						{
							$ratio = $this->force_height / $go['media_y'];
							
							$tmp_y = round($go['media_y'] * $ratio);
							$tmp_x = round($go['media_x'] * $ratio);
							$width_adjust = $tmp_x;
							$size[0] = $tmp_x;
						}
						else
						{
							$tmp_y = $go['media_y'];
							$tmp_x = $go['media_x'];
						}
						
						
						$txt = ($text != '') ? "\n<div class='captioning text2' style='width: {$width_adjust}px; height: {$text_block}px;'><div style='padding: 0 {$margin}px 0 0;'>$text</div></div>\n" :
						"\n<div class='captioning text2' style='width: {$width_adjust}px; height: {$text_block}px;'><div style='padding: 0 {$margin}px 0 0;'>&nbsp;</div></div>\n";
						
						$b .= "<div id='node$go[media_id]' class='picture' style='width: {$width_adjust}px;'>\n";
						
						if ($this->placement == 0) { $b .= $txt; }
						
						$b .= "<div " . $this->valign($size[1]) . ">\n";
						
						$file = ($go['media_dir'] != '') ? $go['media_dir'] . '/' . $go['media_file'] : $go['media_file'];

						$b .= $mime($go['media_file'], $tmp_x, $tmp_y, $go['media_thumb_source']);
						
						$b .= "</div>\n";
					}
					else
					{
						// height and width of thumbnail
						$size = getimagesize(DIRNAME . $the_file);
						$width_adjust = $size[0];
						
						$txt = ($text != '') ? "\n<div class='captioning text2' style='width: {$width_adjust}px; height: {$text_block}px;'><div style='padding: 0 {$margin}px 0 0;'>$text</div></div>\n" :
						"\n<div class='captioning text2' style='width: {$width_adjust}px; height: {$text_block}px;'><div style='padding: 0 {$margin}px 0 0;'>&nbsp;</div></div>\n";
						
						$b .= "<div id='node$go[media_id]' class='picture' style='width: {$width_adjust}px;'>\n";
						
						if ($this->placement == 0) { $b .= $txt; }
						
						$b .= "<div " . $this->valign($size[1]) . ">\n";
						
						$b .= "<img src='" . BASEURL . $the_file . "' width='$size[0]' height='$size[1]' alt='$go[media_thumb_path]' />";
						
						$b .= "</div>\n";
					}
					
					if ($this->placement == 2) $b .= $txt;

					$b .= "</div>\n";
				
					$se = "<div class='separator' style='float: left; width: {$separator}px;'>&nbsp;</div>";
				
					$texty = (($OBJ->vars->exhibit['titling'] == 1) && ($txt != '')) ? $this->text_width : 0;
				
					$this->final_img_container = $this->final_img_container + ($size[0] + $separator);
				
					$a .= ($this->placement == 0) ? $b . $se : $b . $se;
				}
				// titles are are right (1) and left (3)
				else
				{					
					$txt = ($text != '') ? "<div class='captioning text' style='width: {$this->text_width}px;'><div style='padding: 0 {$margin}px;'>$text</div></div>\n" : '';
					
					if (in_array($go['media_mime'], $default['video']))
					{
						$mime = $go['media_mime'];
						
						$size[0] = $go['media_x'];
						$size[1] = $go['media_y'];
						$width_adjust = $size[0];
						
						// need to recalculate proportions if the force height feature is in use
						if ($this->force_height != 0)
						{
							$ratio = $this->force_height / $go['media_y'];
							
							$tmp_y = round($go['media_y'] * $ratio);
							$tmp_x = round($go['media_x'] * $ratio);
							$width_adjust = $tmp_x;
							$size[0] = $tmp_x;
						}
						else
						{
							$tmp_y = $go['media_y'];
							$tmp_x = $go['media_x'];
						}
						
						$b = "<div id='node$go[media_id]' class='picture' style='width: {$width_adjust}px;'>\n";
						
						$b .= "<div " . $this->valign($size[1]) . ">\n";

						$b .= $mime($go['media_file'], $tmp_x, $tmp_y, $go['media_thumb']);
						
						$b .= "</div>\n";
					}
					else
					{
						// height and width of thumbnail
						$size = getimagesize(DIRNAME . $the_file);
						$width_adjust = $size[0];
						
						$b = "<div id='node$go[media_id]' class='picture' style='width: {$width_adjust}px;'>\n";
						
						$b .= "<div " . $this->valign($size[1]) . ">\n";
						
						$b .= "<img src='" . BASEURL . $the_file . "' width='$size[0]' height='$size[1]' alt='$go[media_thumb_path]' />";
						
						$b .= "</div>\n";
					}
					
					$b .= "</div>\n";

					$se = "<div class='separator' style='float: left; width: {$separator}px;'>&nbsp;</div>";
				
					$texty = (($OBJ->vars->exhibit['titling'] == 1) && ($txt != '')) ? $this->text_width : 0;
					
					//echo $texty . ' / ';
				
					$this->final_img_container = $this->final_img_container + ($size[0] + $texty + $separator);
				
					$a .= ($this->placement == 1) ? $b . $txt . $se : $txt . $b . $se;
				}
				
				$t++;
			}
			
			// we need to deal with the first paragraph...if any exists...
			if ($OBJ->vars->exhibit['content'] != '')
			{
				$content = "<div class='textor' style='float: left; width: " . $this->text_width . "px;'>\n";
				$content .= "<div style='margin-right: {$margin}px;'>\n";
				$content .= $OBJ->vars->exhibit['content'];
				$content .= "</div>\n";
				$content .= "</div>\n";
				$content .= "<div class='separator' style='float: left; width: {$separator}px;'>&nbsp;</div>\n";
				
				// add the content and separator to things...
				$this->final_img_container = ($this->final_img_container + $this->text_width + $separator);
			}
			
			$i++;
		}
		
		$s .= "\n<div id='img-container'>\n";
		$s .= ($OBJ->vars->exhibit['placement'] == 1) ? $a . $content : $content . $a;
		$s .= "<div style='clear: left;'><!-- --></div>";
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['exhibit'] = $s;
			
		$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();

		return $OBJ->page->exhibit['exhibit'];
	}
	
	
	function valign($img_height=100)
	{
		$OBJ =& get_instance();
		
		// centered
		if ($this->valign == 1)
		{
			$h = round(($OBJ->vars->exhibit['images'] - $img_height) / 2);
			$style = " style='padding-top: {$h}px;'";
		}
		// bottom
		elseif ($this->valign == 2)
		{
			$h = round(($OBJ->vars->exhibit['images'] - $img_height));
			$style = " style='padding-top: {$h}px;'";
		}
		else
		{
			// nothing
			$style = '';
		}
		
		return $style;
	}


	function defaultCSS()
	{
		$OBJ =& get_instance();
		
		//$title_block = ($OBJ->vars->exhibit['titling'] == 1) ? $this->text_block_height : 0;
		
		//$picture_height = ($OBJ->vars->exhibit['thumbs_shape'] == 0) ? "height: " . $this->x . "px;" : '';
		
		return "#img-container { width: " . $this->final_img_container . "px; }
#img-container .text { float: left; width: " . ($this->text_width + $this->text_padding_right) . "px; }
#img-container .text p, #img-container .textor p { width: auto; }
#img-container .text2 p { width: auto; }
#img-container .picture { float: left; }
#img-container .captioning { height: 50px; text-align: left; overflow: visible; }
#img-container .captioning .title { margin-top: 3px; text-align: left; }";
	}


	/////////////////// CUSTOM OPTIONS
	function custom_option_height()
	{
		$OBJ =& get_instance();

		$height = (isset($OBJ->abstracts->abstract['height'])) ?
			(int)$OBJ->abstracts->abstract['height'] : 0;
			
		$placement = (isset($OBJ->abstracts->abstract['placement'])) ?
			(int)$OBJ->abstracts->abstract['placement'] : 0;
			
		$set = (isset($OBJ->abstracts->abstract['height'])) ? 1 : 0;
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<div style='padding-right: 15px;'><label id='height_value'>height <span>$height</span></label>\n";
		$html .= "<input type='hidden' id='height' name='option[height]' value='$height' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div></div>\n\n";
		
		////////////////////////////////////
		// $set - DOES NOT WORK!
		////////////////////////////////////

		$OBJ->template->onready[] = "$('#slider').slider({ value: $height, max: 900,  
	stop: function(event, ui) { $('#height').val(ui.value); update_abstract(ui.value, 'height', $set); },
	slide: function(event, ui) { $('label#height_value span').html(ui.value); }
	});";
		
		// output column
		$OBJ->options->custom_output[2][0] = $html;
	
		return;
	}
	
	function custom_option_textplacement()
	{
		$OBJ =& get_instance();

		$placement = (isset($OBJ->abstracts->abstract['title-placement'])) ?
			$OBJ->abstracts->abstract['title-placement'] : 0;

		// ++++++++++++
		$onoff = array('top', 'right', 'bottom', 'left');

		$li = '';
		$input = ($placement == '') ? 0 : $placement;
		
		$html = label($OBJ->lang->word('titles placement')) . br();

		foreach ($onoff as $key => $val)
		{
			$active = ($input == $key) ? "class='active'" : '';
			$extra = ($key == 0) ? "id='after'" : '';
			$li .= li($OBJ->lang->word($val), "$active title='$key' $extra");
		}
		
		$html .= ul($li, "class='listed' id='title-placement'");
		
		$OBJ->template->onready[] = "$('#title-placement li').option_list_post('title-placement', 'exhibits');";
		
		// output column
		$OBJ->options->custom_output[3][0] = $html;
		
		return;
	}
}