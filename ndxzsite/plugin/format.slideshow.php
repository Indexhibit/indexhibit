<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Slideshow
Format URI: http://www.indexhibit.org/format/slideshow/
Description: Slideshow format with ajax.
Version: 1.2
Author: Indexhibit
Author URI: http://indexhibit.org/
Options Builder: default_settings
Params: format,custom('textplacement'),custom('nav_type'),custom('navigate'),custom('height'),custom('thumb_height')
Options Builder: default_settings
Source: exhibit
Objects: exhibits
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
	var $placement = false;
	var $titles = true;
	var $bottom_margin = 25;
	var $operand = 0;
	var $source;
	var $text_block_height;
	var $collapse = 1;
	var $center = false;
	var $settings = array();
	var $text_placement = 0;
	var $effect;
	var $navigate;
	var $nav_type;
	var $thumb_height;
	var $height;
	
	///////////////
	var $x;

	public function __construct()
	{
		$OBJ =& get_instance();

		$this->effect = (isset($OBJ->hook->options['slideshow_settings']['effect'])) ? 
			$OBJ->hook->options['slideshow_settings']['effect'] : 1;
		
		$this->text_placement = (isset($OBJ->abstracts->abstract['text-placement'])) ? 
			$OBJ->abstracts->abstract['text-placement'] : 0;
			
		$this->navigate = (isset($OBJ->abstracts->abstract['navigate'])) ? 
			$OBJ->abstracts->abstract['navigate'] : 0;
			
		$this->nav_type = (isset($OBJ->abstracts->abstract['nav-type'])) ? 
			$OBJ->abstracts->abstract['nav-type'] : 0;
			
		$this->thumb_height = (isset($OBJ->abstracts->abstract['thumb_height'])) ? 
			$OBJ->abstracts->abstract['thumb_height'] : 75;
			
		$this->height = (isset($OBJ->abstracts->abstract['height'])) ? 
				$OBJ->abstracts->abstract['height'] : 575;
	}
	
	public function createExhibit()
	{
		$OBJ =& get_instance();
		global $uploads, $default;
		
		// we need to customize the allowed formats here
		// adding txt for customization
		$OBJ->vars->media = array_merge($default['images'], $default['media'], $default['services'], array('txt'));
		
		// exhibit only source
		$this->source = $default['filesource'][0];
	
		// get images
		$OBJ->vars->images = $OBJ->page->get_imgs();

		// if no images return our text only
		if (!$OBJ->vars->images[0]) { $OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content']; return; }
		
		$OBJ->page->exhibit['lib_css'][] = "slideshow.css";
	
		$s = ''; $a = ''; $i = 0;
		
		$total = count($OBJ->vars->images[0]);
		
		if ($total > 1)
		{
			// make the javascript array
			foreach ($OBJ->vars->images[0] as $img)
			{
				$arr[] = $img['media_id'];
				$i++;
			}
		
			$tmp = implode(', ', $arr);
			
			$OBJ->page->exhibit['dyn_js'][] = ($this->effect == 1) ? "var fade = true;" : "var fade = false;";
			$OBJ->page->exhibit['dyn_js'][] = "var baseurl = '" . $OBJ->baseurl . "';";
			$OBJ->page->exhibit['dyn_js'][] = "var count = 0;";
			$OBJ->page->exhibit['dyn_js'][] = "var total = $total;";
			$OBJ->page->exhibit['dyn_js'][] = "var img = new Array(" . $tmp . ");";
			$OBJ->page->exhibit['dyn_js'][] = ($this->text_placement == 0) ? "var placement = true;" : "var placement = false;";
			//$OBJ->page->add_jquery('jquery.easing.js', 19);
			$OBJ->page->add_jquery('jquery.slideshow.js', 21);
		}
		
		// we need to see if there are any videos and set the js
		global $uploads; $preload = array();
		
		// this version of slideshow only works with dynamic images
		foreach ($OBJ->vars->images as $tests)
		{
			foreach ($tests as $test)
			{
				if (in_array($test['media_mime'], array_merge($default['video'], $default['services'])))
				{
					$OBJ->page->add_jquery('jquery.jwplayer.js', 22);
				}
				
				// let's make an array of images for preloading
				if (in_array($test['media_mime'], array_merge($default['images'])))
				{
					$name = 'rsz_h' . $this->height . '_' . $OBJ->vars->exhibit['id'] . '_' . $test['media_id'] . '.' . $test['media_mime'];
					//$preload[] = $test['media_ref_id'] . '_' . $test['media_file'];
					$preload[] = $name;
				}
			}
		}
		
		// first image array
		$image = $OBJ->vars->images[0][0];
		
		// if it's an image
		if (in_array($image['media_mime'], $default['images']))
		{
			$size = getimagesize(DIRNAME . '/files/gimgs/' . $image['media_file']);
		
			$a = "<div id='slideshow-wrapper'>\n";
			$a .= "<div id='slideshow' style='position: relative;'>\n";
			$a .= '<div id="slide1000" class="picture" style="z-index: 1000; position: absolute;">';
			$a .= '<a href="#" onclick="next(); return false;" alt="">';
			
			// we need to resize this image based on $this->height
			// new dimensions 
			$new_height = $this->height;
			$new_width = round(($size[0] * $new_height) / $size[1]);

			// force width
			$R = load_class('resize', true, 'lib');

			$name = 'rsz_h' . $new_height . '_' . $OBJ->vars->exhibit['id'] . '_' . $image['media_id'] . '.' . $image['media_mime'];

			// we're going to resize and output
			$R->reformat($new_width, $new_height, $size, $image, $OBJ->vars->exhibit['id'], $name, $image['media_dir']);
	
			$a .= '<img src="' . BASEURL . '/files/dimgs/' . $name . '" width="' . $new_width . '" height="' . $this->height . '" />';		
			$a .= '</a>';
		
			if (($image['media_title'] == '') && ($image['media_caption'] == ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= (($image['media_title'] !=  '') && ($image['media_caption'] !=  '')) ? "<p>" : '';
				$a .= ($image['media_title'] !=  '') ? $image['media_title'] : '';
				$a .= ($image['media_title'] !=  '') ? " " : '';
				$a .= ($image['media_caption'] !=  '') ? strip_tags($image['media_caption'], "a,i,b") : '';
				$a .= (($image['media_title'] !=  '') && ($image['media_caption'] !=  '')) ? "</p>" : '';

				$a .= "</div>\n";
			}
			
			$a .= '</div>';
			$a .= '</div>';
		
			$a .= "</div>\n";
		}
		else if (in_array($image['media_mime'], array_merge($default['media'], $default['services']))) // it's a video
		{
			$mime = $image['media_mime'];
			
			$a = "<div id='slideshow-wrapper'>\n";
			$a .= "<div id='slideshow' style='position: relative;'>\n";
			$a .= '<div  id="slide1000" class="picture videoslide" style="z-index: 1000; position: absolute;">';		
			$a .= "<a href='#' onclick=\"next(); return false;\"><span class='nextlink'></span></a>";
			
			// we need to resize this image based on $this->height
			// new dimensions 
			$new_height = $this->height;
			$new_width = round(($image['media_x'] * $new_height) / $image['media_y']);
			
			$a .= $mime($image['media_file'], $new_width, $new_height, $image['media_thumb']);
			
			$a .= '</div>';
			
			//$a .= '<a href="#" onclick="next(); return false;" alt="">';
		
			if (($image['media_title'] != '') && ($image['media_caption'] != ''))
			{
				// do nothing
			}
			else
			{
				$a .= "<div class='captioning'>\n";

				$a .= (($image['media_title'] !=  '') && ($image['media_caption'] !=  '')) ? "<p>" : '';
				$a .= ($image['media_title'] !=  '') ? $image['media_title'] : '';
				$a .= ($image['media_title'] !=  '') ? " " : '';
				$a .= ($image['media_caption'] !=  '') ? strip_tags($image['media_caption'], "a,i,b") : '';
				$a .= (($image['media_title'] !=  '') && ($image['media_caption'] !=  '')) ? "</p>" : '';

				$a .= "</div>\n";
			}
			
			$a .= '</div>';
			$a .= "</div>\n";
		}
		else // it's text only
		{
			// only if media_mime = txt
			$a = "<div id='slideshow-wrapper'>\n";
			$a .= "<div id='slideshow' style='position: relative; height: " . $this->height . "px;'>\n";
			$a .= '<div  id="slide1000" class="picture" style="z-index: 1000; position: absolute; height: ' . $this->height . 'px;">';
			
			// we need to get the text from the file
			$handle = fopen(DIRNAME . '/files/' . $image['media_file'], 'r');
			$text = fread($handle, 1000000);
			fclose($handle);
			
			// new dimensions 
			$a .= "<div id='slideshow-text'>\n";
			$a .= $text;
			$a .= "</div>\n";
			
			$a .= '</div>';
			$a .= "</div>\n";
		}
		
		// the nav
		if ($total > 1)
		{
			$nav_balance = ($this->navigate == 0) ? 'nav-above' : 'nav-below';

			$nav = "\n\n<div id='slideshow-nav' class='$nav_balance'>\n";
			$nav .= '<span id="total"><em>1</em> of ' . $total . '</span>&nbsp;&nbsp;';
			$nav .= '<span id="previous"><a href="#" onclick="previous(); return false;">Previous</a></span> | ';
			$nav .= '<span id="next"><a href="#" onclick="next(); return false;">Next</a></span>';
			$nav .= "</div>\n\n";

			// this will become an option
			//$OBJ->page->exhibit['append_menu'][] = $nav;
			//$nav = '';
		}
		else
		{
			$nav = '';
		}
		
			
		// a bit messy - organize this better
		if ($this->nav_type == 1)
		{
			// already set previously in script
			//$images = $nav . $a;
		}
		elseif ($this->nav_type == 2)
		{
			// make thumbnails here
			$i = 0;

			// what if we used thumbnails here - no nav? or, a different nav?
			// what about thumbnail interface?
			foreach ($OBJ->vars->images as $thumbs)
			{
				foreach ($thumbs as $key => $thumb)
				{
					// if it's an image
					if (in_array($thumb['media_mime'], $default['images']))
					{
						$source = ($thumb['media_thumb_source'] == '') ? $thumb['media_file'] : $thumb['media_thumb_source'];

						// here we need to regenerate the thumbnail
						$size = getimagesize(DIRNAME . "/files/gimgs/" . $source);

						// new dimensions based on the height
						// but we are still recalculating a new width
						$new_width = round(($size[0] * $this->thumb_height) / $size[1]);

						$name = 'rsz_h' . $this->thumb_height . '_' . $OBJ->vars->exhibit['id'] . '_' . $thumb['media_id'] . '.' . $thumb['media_mime'];

						$R = load_class('resize', true, 'lib');

						// we're going to resize and output
						$R->reformat($new_width, $this->thumb_height, $size, $thumb, $OBJ->vars->exhibit['id'], $name, $thumb['media_dir']);

						$thumbnails .= "<a href='#' style='' onclick=\"show(" . $thumb['media_id'] . ", $i); return false;\"><img src='" . BASEURL . "/files/dimgs/$name' /></a> \n";

						$i++;
					}
					else // a movie or other displayable formats
					{
						$source = $thumb['media_thumb_source'];

						// here we need to regenerate the thumbnail
						$size = getimagesize(DIRNAME . "/files/gimgs/" . $source);

						// new dimensions based on the height
						// but we are still recalculating a new width
						$new_width = round(($size[0] * $this->thumb_height) / $size[1]);

						$name = 'rsz_h' . $this->thumb_height . '_' . $OBJ->vars->exhibit['id'] . '_' . $thumb['media_id'] . '.' . $thumb['media_mime'];

						$R = load_class('resize', true, 'lib');

						// we're going to resize and output
						$R->reformat($new_width, $this->thumb_height, $size, $thumb, $OBJ->vars->exhibit['id'], $name, $thumb['media_dir']);

						$thumbnails .= "<a href='#' style='' onclick=\"show(" . $thumb['media_id'] . ", $i); return false;\"><img src='" . BASEURL . "/files/dimgs/$name' /></a> \n";

						$i++;
					}
				}
			}
			
			// we need to determine above or below for the css
			$balance = ($this->navigate == 0) ? 'thumbnails-above' : 'thumbnails-below';
			$nav = ($thumbnails == '') ? '' : "<div id='thumbnails' class='$balance'>$thumbnails</div>\n\n";
			//$images = $a . $nav;
		}
		else
		{
			$nav = '';
			//$images = $a;
		}
		
		// preload array
		if (count($preload) >= 1)
		{
			$OBJ->page->exhibit['dyn_js'][] = "$(function() { $(['" . implode("', '", $preload) . "']).preload(); });";
		}
		
		// composition space - text placement
		// 0 = top
		// 1 = bottom
		$text_css = ($this->text_placement == 0) ? 'placement-top' : 'placement-bottom';
		$content = "<div id='textspace' class='$text_css'>\n" . $OBJ->vars->exhibit['content'] . "</div>\n";
			
		// 0 = above (navigate)
		// 1 = below (navigate)
		
		$images = ($this->navigate == 0) ? $nav . $a : $a . $nav;
		
			
		// 0 = top (text placement)
		// 1 = bottom (text placement)
		$layout = ($this->text_placement == 0) ? $content . $images : $images . $content;

		
		$s .= "<div id='img-container'>\n";
		$s .= $layout;
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['exhibit'] = $s;
			
		$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();
		
		return $OBJ->page->exhibit['exhibit'];
	}


	public function defaultCSS()
	{
		$OBJ =& get_instance();

		return "";
	}
	
	
	///////////////// SETTINGS
	public function default_settings()
	{
		$OBJ =& get_instance();

		$effect = (isset($this->settings['effect'])) ? $this->settings['effect'] : 1;
	
		$html = "<label>transition effect</label>\n";
		$html .= "<p><select name='option[effect]'>\n";
		$html .= "<option value='1'" . $this->selected($effect, 1) . ">fade</option>\n";
		$html .= "<option value='0'" . $this->selected($effect, 0) . ">none</option>\n";
		$html .= "</select></p>\n";
	
		return $html;
	}

	public function selected($var='', $check='')
	{
		return ($var == $check) ? " selected='selected'" : '';
	}
	
	public function custom_option_navigate()
	{
		$OBJ =& get_instance();

		$navigate = (isset($OBJ->abstracts->abstract['navigate'])) ?
			$OBJ->abstracts->abstract['navigate'] : 0;

		// ++++++++++++
		$onoff = array('above', 'below');

		$li = '';
		$input = ($navigate == '') ? 0 : $navigate;
		
		$html = label($OBJ->lang->word('navigate')) . br();

		foreach ($onoff as $key => $val)
		{
			$active = ($input == $key) ? "class='active'" : '';
			$extra = ($key == 0) ? "id='after'" : '';
			$li .= li($OBJ->lang->word($val), "$active title='$key' $extra");
		}
		
		$html .= ul($li, "class='listed' id='navigate'");
		
		$OBJ->template->onready[] = "$('#navigate li').option_list_post('navigate', 'exhibits');";
		
		// output column
		$OBJ->options->custom_output[3][1] = $html;
		
		return;
	}
	
	public function custom_option_textplacement()
	{
		$OBJ =& get_instance();

		$placement = (isset($OBJ->abstracts->abstract['text-placement'])) ?
			$OBJ->abstracts->abstract['text-placement'] : 0;

		// ++++++++++++
		$onoff = array('top', 'bottom');

		$li = '';
		$input = ($placement == '') ? 0 : $placement;
		
		$html = label($OBJ->lang->word('text placement')) . br();

		foreach ($onoff as $key => $val)
		{
			$active = ($input == $key) ? "class='active'" : '';
			$extra = ($key == 0) ? "id='after'" : '';
			$li .= li($OBJ->lang->word($val), "$active title='$key' $extra");
		}
		
		$html .= ul($li, "class='listed' id='text-placement'");
		
		$OBJ->template->onready[] = "$('#text-placement li').option_list_post('text-placement', 'exhibits');";
		
		// output column
		$OBJ->options->custom_output[3][0] = $html;
		
		return;
	}
	
	public function custom_option_nav_type()
	{
		$OBJ =& get_instance();

		$placement = (isset($OBJ->abstracts->abstract['nav-type'])) ?
			$OBJ->abstracts->abstract['nav-type'] : 0;

		// ++++++++++++
		$onoff = array('none', 'links', 'thumbnails');

		$li = '';
		$input = ($placement == '') ? 0 : $placement;
		
		$html = label($OBJ->lang->word('navigation type')) . br();

		foreach ($onoff as $key => $val)
		{
			$active = ($input == $key) ? "class='active'" : '';
			$extra = ($key == 0) ? "id='after'" : '';
			$li .= li($OBJ->lang->word($val), "$active title='$key' $extra");
		}
		
		$html .= ul($li, "class='listed' id='nav-type'");
		
		$OBJ->template->onready[] = "$('#nav-type li').option_list_post('nav-type', 'exhibits');";
		
		// output column
		$OBJ->options->custom_output[3][2] = $html;
		
		return;
	}
	
	
	public function custom_option_height()
	{
		$OBJ =& get_instance();

		$height = (isset($OBJ->abstracts->abstract['height'])) ?
			(int)$OBJ->abstracts->abstract['height'] : 575;
			
		$set = (isset($OBJ->abstracts->abstract['height'])) ? 1 : 0;
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<div style='padding-right: 15px;'><label id='height_value'>uniform height <span>$height</span></label>\n";
		$html .= "<input type='hidden' id='height' name='option[height]' value='$height' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $height, min: 320, max: 1000, step: 10,  
	stop: function(event, ui) { $('#height').val(ui.value); update_abstract(ui.value, 'height', $set); },
	slide: function(event, ui) { $('label#height_value span').html(ui.value); }
	});";
		
		// output column
		$OBJ->options->custom_output[2][1] = $html;
	
		return;
	}
	
	
	public function custom_option_thumb_height()
	{
		$OBJ =& get_instance();

		$height = (isset($OBJ->abstracts->abstract['thumb_height'])) ?
			(int)$OBJ->abstracts->abstract['thumb_height'] : 0;
			
		//$placement = (isset($OBJ->abstracts->abstract['placement'])) ?
		//	(int)$OBJ->abstracts->abstract['placement'] : 0;
			
		$set = (isset($OBJ->abstracts->abstract['thumb_height'])) ? 1 : 0;
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<div style='padding-right: 15px;'><label id='thumb_height_value'>thumbnail_height <span>$height</span></label>\n";
		$html .= "<input type='hidden' id='thumb_height' name='option[thumb_height]' value='$height' />\n";
		$html .= "<div id='slider2' style='margin: 10px 0;'></div></div>\n\n";
		
		////////////////////////////////////
		// $set - DOES NOT WORK!
		////////////////////////////////////

		$OBJ->template->onready[] = "$('#slider2').slider({ value: $height, min: 15, max: 200, step: 5,  
	stop: function(event, ui) { $('#thumb_height').val(ui.value); update_abstract(ui.value, 'thumb_height', $set); },
	slide: function(event, ui) { $('label#thumb_height_value span').html(ui.value); }
	});";
		
		// output column
		$OBJ->options->custom_output[2][2] = $html;
	
		return;
	}
}