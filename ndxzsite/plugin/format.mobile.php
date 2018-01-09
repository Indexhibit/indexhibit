<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Mobile Format
Format URI: http://www.indexhibit.org/format/mobile/
Description: Default Indexhibit mobile format.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Params: format,images,thumbs,shape,placement,break,titling
Source: exhibit,all,section,subsection
Operands: permalinks
Objects: exhibits
*/

/**
* Mobile
*
* Exhbition format
* 
* @version 1.0
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
	var $uniform_width = 0;
	
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
		
		$this->spacer_height = (isset($OBJ->hook->options['mobile_settings']['spacer_height'])) ? 
			$OBJ->hook->options['mobile_settings']['spacer_height'] : 18;
			
		$this->caption_top = (isset($OBJ->hook->options['mobile_settings']['caption_top'])) ? 
			$OBJ->hook->options['mobile_settings']['caption_top'] : 0;
			
		$this->uniform_width = (isset($OBJ->abstracts->abstract['width'])) ? 
			$OBJ->abstracts->abstract['width'] : 0;
	
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
				$img = $OBJ->vars->media;
			
				// mime type
				if (in_array($img['media_mime'], $default['video']))
				{
					// margin bottom should be a setting or a css thing...
					$a .= "<div>\n";
					$a .= "<div id='over-$i' class='over video-container'>\n";
					
					$mime = $img['media_mime'];

					$a .= "<div class='asset'>\n";
					
					$file = ($img['media_dir'] != '') ? $img['media_dir'].'/'.$img['media_file'] : $img['media_file'];
					
					// we need to scale the new width/height
					// 250 is a temporary value for now (quick solution)
					$a .= $mime($file, 0, 0, $img['media_thumb']);
					
					$a .= "</div>\n";
					$a .= "</div>\n";
				
					// title goes into this space
					if ($OBJ->vars->exhibit['titling'] == 1)
					{
						$txt = '';

						if ($OBJ->vars->exhibit['media_source'] == 0)
						{
							if ($img['media_title'] != '') 		$txt .= "<span class='image-title'>$img[media_title]</span><br />\n";
							if ($img['media_caption'] != '') 	$txt .= "<span class='image-caption'>" . strip_tags($img['media_caption'], 'a');
						}
						else // it's an exhibit
						{
							if ($img['title'] != '') 		$txt .= "<span class='image-title'>$img[title]</span>\n";
						}
						
						if ($txt != '') $a .= "<p class='captioning'>$txt</p>\n";
						
						$a .= "<div class='spacer'></div>\n";
					}
					else
					{
						$a .= "<div class='spacer'></div>\n";
					}
				}
				else // it's an image
				{
					// margin bottom should be a setting or a css thing...
					$a .= "<div id='over-$i' class='over'>\n";
					
					$source = ($img['media_thumb_source'] == '') ? $img['media_ref_id'] . '_' . $img['media_file'] : $img['media_ref_id'] . '_' .$img['media_thumb_source'];

					// here we need to regenerate the thumbnail
					$size = getimagesize(DIRNAME . "/files/gimgs/" . $source);
					
					if ($OBJ->vars->exhibit['media_source'] == 0)
					{
					$a .= "<div class='asset'><img data-src='" . BASEURL . "/files/gimgs/" . $img['media_file'] . "' src='" . BASEURL . "/files/gimgs/" . $img['media_ref_id'] . '_' . $img['media_file'] . "' width='$size[0]' height='$size[1]' alt='' class='lazyload' data-mime='$img[media_mime]' data-src-title=\"$img[media_title]\" data-src-caption=\"" . strip_tags($img['media_caption']) . "\"/></div>\n";
					}
					else // no links or appropriate links
					{
						// we need to link to the dimg images here?
						$a .= "<div class='asset'><a href='" . BASEURL . $img['url'] . "'><img data-src='" . BASEURL . "/files/gimgs/" . $img['media_file'] . "' src='" . BASEURL . "/files/gimgs/" . $img['media_ref_id'] . '_' . $img['media_file'] . "' width='$size[0]' height='$size[1]' alt='' class='lazyload' /></a></div>\n";
					}
				
					// title goes into this space
					if ($OBJ->vars->exhibit['titling'] == 1)
					{
						$txt = '';
						
						if ($OBJ->vars->exhibit['media_source'] == 0)
						{
							if ($img['media_title'] != '') 		$txt .= "<span class='image-title'>$img[media_title]</span><br />\n";
							if ($img['media_caption'] != '') 	$txt .= "<span class='image-caption'>" . strip_tags($img['media_caption'], 'a');
						}
						else // it's an exhibit
						{
							if ($img['title'] != '') 		$txt .= "<span class='image-title'>$img[title]</span>\n";
						}
						
						if ($txt != '') $a .= "<p class='captioning'>$txt</p>\n";
						
						$a .= "<div class='spacer'></div>\n";
					}
					else
					{
						$a .= "<div class='spacer'></div>\n";
					}
				}
				
				$a .= "</div>\n";
				$i++;
			}
		}

		$s = "\n<div id='img-container'>\n";
		$s .= $a;
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['exhibit'] = ($OBJ->vars->exhibit['placement'] == 1) ? 
			$s . $OBJ->vars->exhibit['content'] : 
			$OBJ->vars->exhibit['content'] . $s;

		return $OBJ->page->exhibit['exhibit'];
	}
	
	
	/////////////////// CUSTOM OPTIONS
	function custom_option_width()
	{
		$OBJ =& get_instance();

		$width = (isset($OBJ->abstracts->abstract['width'])) ?
			(int)$OBJ->abstracts->abstract['width'] : 0;
			
		$set = (isset($OBJ->abstracts->abstract['width'])) ? 1 : 0;
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<div style='padding-right: 15px;'><label id='width_value'>uniform width <span>$width</span></label>\n";
		$html .= "<input type='hidden' id='width' name='option[width]' value='$width' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $width, min: 0, max: 1200, step: 25,  
	stop: function(event, ui) { $('#width').val(ui.value); update_abstract(ui.value, 'width', $set); },
	slide: function(event, ui) { $('label#width_value span').html(ui.value); }
	});";
		
		// output column
		$OBJ->options->custom_output[2][0] = $html;
	
		return;
	}


	function defaultCSS()
	{
		$css = "#img-container .spacer { height: " . $this->spacer_height . "px; }
#img-container .captioning { text-align: center; margin-top: 3px; }
#img-container .captioning .title { text-align: center; }
#img-container .captioning .caption { text-align: center; }
#img-container .asset { line-height: 0; }
#img-container .captioning p:last-child { margin-bottom: 0; }
#img-container .spacer { height: 3px; }";
		
		return $css;
	}
}