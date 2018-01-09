<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Over and Over
Format URI: http://www.indexhibit.org/format/over_and_over/
Description: 'Over and Over' format.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Params: format,images,placement,titling
Options Builder: default_settings
Source: exhibit
Objects: exhibits
*/

/**
* Over and Over
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
	var $text_width = 250;
	var $text_padding_right = 35;
	var $final_img_container = 0; // do not adjust this one
	var $imgs = array();
	var $br = 1;
	var $grid = false;
	var $titles = true;
	var $bottom_margin = 0;
	var $operand = 0;
	var $source;
	var $text_block_height;
	var $collapse = 1;
	var $center = false;
	var $settings = array();

	public function __construct()
	{
		
	}

	function default_settings()
	{
		$OBJ =& get_instance();

		$spacer_height = (isset($this->settings['spacer_height'])) ? $this->settings['spacer_height'] : 18;
		$caption_top = (isset($this->settings['caption_top'])) ? $this->settings['caption_top'] : 0;

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');

		$html = "<label id='spacer_height_value'>spacer <span>$spacer_height</span></label>\n";
		$html .= "<input type='hidden' id='spacer_height' name='option[spacer_height]' value='$spacer_height' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $spacer_height, max: 120, 
stop: function(event, ui) { $('#spacer_height').val(ui.value); },
slide: function(event, ui) { $('label#spacer_height_value span').html(ui.value) }
});";

		$html .= "<label id='caption_top_value'>caption_top <span>$caption_top</span></label>\n";
		$html .= "<input type='hidden' id='caption_top' name='option[caption_top]' value='$caption_top' />\n";
		$html .= "<div id='slider2' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider2').slider({ value: $caption_top, max: 20, 
stop: function(event, ui) { $('#caption_top').val(ui.value); },
slide: function(event, ui) { $('label#caption_top_value span').html(ui.value) }
});";

		return $html;
	}
	
	
	function selected($var='', $check='')
	{
		return ($var == $check) ? " selected='selected'" : '';
	}
	
	
	function createExhibit()
	{
		$OBJ =& get_instance();
		global $default;
		
		$this->spacer_height = (isset($OBJ->hook->options['over_and_over_settings']['spacer_height'])) ? 
			$OBJ->hook->options['over_and_over_settings']['spacer_height'] : 18;
			
		$this->caption_top = (isset($OBJ->hook->options['over_and_over_settings']['caption_top'])) ? 
			$OBJ->hook->options['over_and_over_settings']['caption_top'] : 0;
	
		// get images
		$OBJ->vars->images = $OBJ->page->get_imgs();

		// if no images return our text only
		if (!$OBJ->vars->images) 
		{ 
			$OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content'];
			return $OBJ->page->exhibit['exhibit']; 
		}
	
		$s = ''; $a = ''; $w = 0; $i = 0;

		foreach ($OBJ->vars->images as $imgs)
		{
			foreach ($imgs as $OBJ->vars->media)
			{	
				// experimental
				//$OBJ->vars->media = $img;
				
				$img = $OBJ->vars->media;

				// margin bottom should be a setting or a css thing...
				$a .= "<div class='over'>\n";
			
				// mime type
				if (in_array($img['media_mime'], $default['video']))
				{
					$mime = $img['media_mime'];

					$a .= "<div class='asset'>\n";
					
					$file = ($img['media_dir'] != '') ? $img['media_dir'].'/'.$img['media_file'] : $img['media_file'];
					
					$a .= $mime($file, $img['media_x'], $img['media_y'], $img['media_thumb']);
					
					$a .= "</div>\n";
				
					// title goes into this space
					if ($OBJ->vars->exhibit['titling'] == 1)
					{
						$txt = '';

						if ($img['media_title'] != '') $txt .= "<div class='title'>$img[media_title]</div>\n";
						if ($img['media_caption'] != '') $txt .= "<div class='caption'>$img[media_caption]</div>\n";
							
						$a .= "<div class='spacer'><div class='captioning'>$txt</div></div>\n";
					}
					else
					{
						$a .= "<div class='spacer'>&nbsp;</div>\n";
					}
				}
				else
				{
					// height and width of thumbnail
					$size = getimagesize(DIRNAME . '/files/gimgs/' . $img['media_ref_id'] . '_' . $img['media_file']);
				
					//$a .= "<div class='over-and-over' style='margin-bottom: 100px;'>\n";
					$a .= "<div class='asset'>\n<img src='" . BASEURL . "/files/gimgs/" . $img['media_ref_id'] . '_' . $img['media_file'] . "' width='$size[0]' height='$size[1]' alt='' class='lazyload' />\n</div>\n";
				
					// title goes into this space
					if ($OBJ->vars->exhibit['titling'] == 1)
					{
						$txt = '';

						if ($img['media_title'] != '') $txt .= "<div class='title'>$img[media_title]</div>\n";
						if ($img['media_caption'] != '') $txt .= "<div class='caption'>$img[media_caption]</div>\n";
							
						$a .= "<div class='spacer'><div class='captioning'>$txt</div></div>\n";
					}
					else
					{
						$a .= "<div class='spacer'>&nbsp;</div>\n";
					}
				}
				
				$a .= "</div>\n";
			}
			
			$i++;
		}

		$s = "\n<div id='img-container'>\n";
		$s .= $a;
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();
		
		$OBJ->page->exhibit['exhibit'] = ($OBJ->vars->exhibit['placement'] == 1) ? 
			$s . "<div class='textspace'>" . $OBJ->vars->exhibit['content'] . "</div>" : 
			"<div class='textspace'>" . $OBJ->vars->exhibit['content'] . "</div>" . $s;

		return $OBJ->page->exhibit['exhibit'];
	}


	function defaultCSS()
	{
		return "#img-container .spacer { height: " . $this->spacer_height . "px; }
#img-container .captioning { margin-top: " . $this->caption_top . "px; }";
	}
}