<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Visual Index
Format URI: http://www.indexhibit.org/format/visual-index/
Description: Default Indexhibit format.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Params: format,images,thumbs,shape,placement,break,titling
Options Builder: default_settings
Source: exhibit,all,section,subsection
Operands: grow,permalinks,overlay,unlinked
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
	var $text_width = 250;
	var $text_padding_right = 35;
	var $final_img_container = 0; // do not adjust this one
	var $imgs = array();
	var $br = 1;
	var $grid = 0;
	var $titles = true;
	var $bottom_margin = 25;
	var $operand = 0;
	var $source;
	var $text_block_height;
	var $collapse = 1;
	var $overlay = 'dark';
	var $settings = array();
	var $medias = array();
	var $center = false;
	var $size = array();
	var $file = array();
	var $title = array();
	var $align = 'center';
	
	///////////////
	var $x;

	public function default_settings()
	{
		$OBJ =& get_instance();

		$margin = (isset($this->settings['margin'])) ? $this->settings['margin'] : 0;
		//$margin = (isset($OBJ->abstracts->abstract['right_margin'])) ? $OBJ->abstracts->abstract['right_margin'] : 0;
		$bottom_margin = (isset($this->settings['bottom_margin'])) ? $this->settings['bottom_margin'] : 0;
		$text_box_height = (isset($this->settings['text_box_height'])) ? $this->settings['text_box_height'] : 0;
		$grid = (isset($this->settings['grid'])) ? $this->settings['grid'] : '';
		$collapse = (isset($this->settings['collapse'])) ? $this->settings['collapse'] : '';
		$overlay = (isset($this->settings['overlay'])) ? $this->settings['overlay'] : 0;

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');

		$html = "<label id='right_margin_value'>right margin <span>$margin</span></label>\n";
		$html .= "<input type='hidden' id='right_margin' name='option[margin]' value='$margin' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $margin, max: 100,  
stop: function(event, ui) { $('#right_margin').val(ui.value); },
slide: function(event, ui) { $('label#right_margin_value span').html(ui.value) }
});";

		$html .= "<label id='bottom_margin_value'>bottom margin <span>$bottom_margin</span></label>\n";
		$html .= "<input type='hidden' id='bottom_margin' name='option[bottom_margin]' value='$bottom_margin' />\n";
		$html .= "<div id='slider2' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider2').slider({ value: $bottom_margin, max: 100, 
stop: function(event, ui) { $('#bottom_margin').val(ui.value); },
slide: function(event, ui) { $('label#bottom_margin_value span').html(ui.value) }
});";

		$html .= "<label id='text_box_height_value'>text box height <span>$text_box_height</span></label>\n";
		$html .= "<input type='hidden' id='text_box_height' name='option[text_box_height]' value='$text_box_height' />\n";
		$html .= "<div id='slider3' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider3').slider({ value: $text_box_height, max: 50, 
stop: function(event, ui) { $('#text_box_height').val(ui.value); },
slide: function(event, ui) { $('label#text_box_height_value span').html(ui.value) }
});";

		$html .= "<label>grid</label>\n";
		$html .= "<p><select name='option[grid]'>\n";
		$html .= "<option value='0'" . $this->selected($grid, 0) . ">center/center</option>\n";
		$html .= "<option value='1'" . $this->selected($grid, 1) . ">center/top</option>\n";
		$html .= "<option value='2'" . $this->selected($grid, 2) . ">center/bottom</option>\n";
		$html .= "<option value='3'" . $this->selected($grid, 3) . ">left/top</option>\n";
		$html .= "<option value='4'" . $this->selected($grid, 4) . ">left/bottom</option>\n";
		$html .= "<option value='5'" . $this->selected($grid, 5) . ">left/soft</option>\n";
		$html .= "</select></p>\n";

		$html .= "<label>auto collapse</label>\n";
		$html .= "<p><select name='option[collapse]'>\n";
		$html .= "<option value='1'" . $this->selected($collapse, 1) . ">Yes</option>\n";
		$html .= "<option value='0'" . $this->selected($collapse, 0) . ">No</option>\n";
		$html .= "</select></p>\n";
		
		$html .= "<label>overlay</label>\n";
		$html .= "<p><select name='option[overlay]'>\n";
		$html .= "<option value='light'" . $this->selected($overlay, 'light') . ">Light</option>\n";
		$html .= "<option value='dark'" . $this->selected($overlay, 'dark') . ">Dark</option>\n";
		$html .= "</select></p>\n";

		return $html;
	}
	
	function selected($var='', $check='')
	{
		return ($var == $check) ? " selected='selected'" : '';
	}	

	public function __construct()
	{
		$OBJ =& get_instance();

		// PADDING AND TEXT WIDTH ADJUSTMENTS UP HERE!!!
		$this->picture_block_padding_right = (isset($OBJ->hook->options['visual_index_settings']['margin'])) ? 
			$OBJ->hook->options['visual_index_settings']['margin'] : 25;
		$this->bottom_margin = (isset($OBJ->hook->options['visual_index_settings']['bottom_margin'])) ? 
			$OBJ->hook->options['visual_index_settings']['bottom_margin'] : 25;
		$this->text_block_height = (isset($OBJ->hook->options['visual_index_settings']['text_box_height'])) ? 
			$OBJ->hook->options['visual_index_settings']['text_box_height'] : 18;
		$this->text_width = 250;
		$this->text_padding_right = 35;
		$this->final_img_container = 0; // do not adjust this one
		
		// thumbs shape == 0 and grid = true...
		$this->grid = (isset($OBJ->hook->options['visual_index_settings']['grid'])) ? 
			$OBJ->hook->options['visual_index_settings']['grid'] : 0;
		
		$this->overlay = (isset($OBJ->hook->options['visual_index_settings']['overlay'])) ? 
			$OBJ->hook->options['visual_index_settings']['overlay'] : 'dark';
		
		$this->center = 'true';
			
		$this->collapse = (isset($OBJ->hook->options['visual_index_settings']['collapse'])) ? 
			$OBJ->hook->options['visual_index_settings']['collapse'] : 1;
	}
	
	// rough example of how to do the resets
	function reset()
	{
		$OBJ =& get_instance();
		global $default;
		
		// check to see if we even need the reset first from $OBJ->vars->exhibit vars

		// need to be able to reset the 'defaults' as needed
		// update array
		$OBJ->db->updateArray(PX.'objects', array('images' => 300), "id='" . $OBJ->vars->exhibit['id'] . "'");

		// and reoutput thumbs and images as needed
		// system module - resize_images($size=9999, $type='image') - need $go;
		$R = load_class('resize', true, 'lib');
		$R->resize_images($OBJ->vars->exhibit['id'], 300, 'image');
		$R->resize_images($OBJ->vars->exhibit['id'], 100, 'thumb');
	}
	
	
	function grid()
	{
		$OBJ =& get_instance();

		switch ($this->grid)
		{
			// center/center
			case 0:
			
				$temp_x = $this->size[0] + $this->picture_block_padding_right;
				$temp_y = $OBJ->vars->exhibit['thumbs'];
				$top_padding = $OBJ->vars->exhibit['thumbs'] - $this->size[1];

				$temp_x = ($this->grid == 0) ? 
					$OBJ->vars->exhibit['thumbs'] + $this->picture_block_padding_right : 
					$this->size[0] + $this->picture_block_padding_right;
			
				$temp_px = ($this->grid == 0) ? 
					$OBJ->vars->exhibit['thumbs'] : 
					$this->size[0];
			
				$temp_tx = ($this->grid == 0) ? 
					$OBJ->vars->exhibit['thumbs'] : 
					$this->size[0];
				
				$temp_y = $OBJ->vars->exhibit['thumbs'] + $this->bottom_margin;
				
				$pwidth = " style='width: " . $OBJ->vars->exhibit['thumbs'] ."px;'";
				
				if ($OBJ->vars->exhibit['titling'] == 1)
				{
					$yadjust = $this->thumbShapeHeight() + $this->text_block_height + $this->bottom_margin;
				}
				else
				{
					$yadjust = $this->thumbShapeHeight() + $this->bottom_margin;
				}
				
				$a = "<div class='picture_holder' id='node" . $this->file['media_id'] . "' style='width: {$temp_x}px; height: {$yadjust}px;'>\n";
				$a .= "<div class='picture'{$pwidth}>\n";
				
				$top_pad = (($OBJ->vars->exhibit['thumbs_shape'] == 0) || ($this->grid == 0)) ? "{$top_padding}px" : 0;
				//$top_pad = round($top_pad / 2) . 'px';
				$top_pad = ($OBJ->vars->exhibit['thumbs_shape'] == 0) ?
					round($top_pad / 2) . 'px' : 0;
				
				$a .= "<div style='padding-top: {$top_pad};'>\n";
		
				$a .= $this->makeLink($this->file, $OBJ->vars->exhibit, $this->title);
				$a .= "<img src='" . $this->file['media_thumb_path'] . "' width='" . $this->size[0] . "' height='" . $this->size[1] . "' alt='" . $this->file['media_thumb_path'] . "' />";
				if ($OBJ->vars->exhibit['operand'] != 3) $a .= "</a>\n";
		
				$a .= "</div>\n";
				$a .= "</div>\n";
				$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning'>$this->title</div>\n" : '';
				$a .= "</div>\n\n";
			
			break;
			
			// center/top
			case 1:

				$top_padding = $OBJ->vars->exhibit['thumbs'] - $this->size[1];
				$temp_x = $OBJ->vars->exhibit['thumbs'] + $this->picture_block_padding_right;
				$temp_y = $OBJ->vars->exhibit['thumbs'] + $this->bottom_margin;
			
				$pwidth = " style='width: " . $OBJ->vars->exhibit['thumbs'] ."px;'";
			
				if ($OBJ->vars->exhibit['titling'] == 1)
				{
					$yadjust = $this->thumbShapeHeight() + $this->text_block_height + $this->bottom_margin;
				}
				else
				{
					$yadjust = $this->thumbShapeHeight() + $this->bottom_margin;
				}
			
				$a = "<div class='picture_holder' id='node" . $this->file['media_id'] . "' style='width: {$temp_x}px; height: {$yadjust}px;'>\n";
				$a .= "<div class='picture'{$pwidth}>\n";
			
				$top_pad = 0;
				//$top_pad = round($top_pad / 2) . 'px';
				$top_pad = ($OBJ->vars->exhibit['thumbs_shape'] == 0) ?
					round($top_pad / 2) . 'px' : 0;
			
				$a .= "<div style='padding-top: {$top_pad};'>\n";
	
				$a .= $this->makeLink($this->file, $OBJ->vars->exhibit, $this->title);
				$a .= "<img src='" . $this->file['media_thumb_path'] . "' width='" . $this->size[0] . "' height='" . $this->size[1] . "' alt='" . $this->file['media_thumb_path'] . "' />";
				if ($OBJ->vars->exhibit['operand'] != 3) $a .= "</a>\n";
	
				$a .= "</div>\n";
				$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning'>$this->title</div>\n" : '';
				$a .= "</div>\n";
				//$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning'>$this->title</div>\n" : '';
				$a .= "</div>\n\n";
			
			break;
			
			case 2:
				
				$top_padding = $OBJ->vars->exhibit['thumbs'] - $this->size[1];
				$temp_x = $OBJ->vars->exhibit['thumbs'] + $this->picture_block_padding_right;
				$temp_y = $OBJ->vars->exhibit['thumbs'] + $this->bottom_margin;
				
				$pwidth = " style='width: " . $OBJ->vars->exhibit['thumbs'] ."px;'";
				
				if ($OBJ->vars->exhibit['titling'] == 1)
				{
					$yadjust = $this->thumbShapeHeight() + $this->text_block_height + $this->bottom_margin;
				}
				else
				{
					$yadjust = $this->thumbShapeHeight() + $this->bottom_margin;
				}
				
				$a = "<div class='picture_holder' id='node" . $this->file['media_id'] . "' style='width: {$temp_x}px; height: {$yadjust}px;'>\n";
				$a .= "<div class='picture'{$pwidth}>\n";
				
				$top_pad = ($OBJ->vars->exhibit['thumbs_shape'] == 0) ?
					($OBJ->vars->exhibit['thumbs'] - $this->size[1]) . 'px' : 0;
				
				$a .= "<div style='padding-top: {$top_pad};'>\n";
		
				$a .= $this->makeLink($this->file, $OBJ->vars->exhibit, $this->title);
				$a .= "<img src='" . $this->file['media_thumb_path'] . "' width='" . $this->size[0] . "' height='" . $this->size[1] . "' alt='" . $this->file['media_thumb_path'] . "' />";
				if ($OBJ->vars->exhibit['operand'] != 3) $a .= "</a>\n";
		
				$a .= "</div>\n";
				$a .= "</div>\n";
				$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning'>$this->title</div>\n" : '';
				$a .= "</div>\n\n";
				
			break;
			
			// left/top
			case 3:
			
				$top_padding = $OBJ->vars->exhibit['thumbs'] - $this->size[1];
				$temp_x = $OBJ->vars->exhibit['thumbs'] + $this->picture_block_padding_right;
				$temp_y = $OBJ->vars->exhibit['thumbs'] + $this->bottom_margin;
				$this->align = 'left';
			
				$pwidth = " style='width: " . $OBJ->vars->exhibit['thumbs'] ."px;'";
			
				if ($OBJ->vars->exhibit['titling'] == 1)
				{
					$yadjust = $this->thumbShapeHeight() + $this->text_block_height + $this->bottom_margin;
				}
				else
				{
					$yadjust = $this->thumbShapeHeight() + $this->bottom_margin;
				}
			
				$a = "<div class='picture_holder' id='node" . $this->file['media_id'] . "' style='width: {$temp_x}px; height: {$yadjust}px;'>\n";
				$a .= "<div class='picture'{$pwidth}>\n";
			
				$top_pad = 0;
			
				$a .= "<div style='padding-top: {$top_pad};'>\n";
	
				$a .= $this->makeLink($this->file, $OBJ->vars->exhibit, $this->title);
				$a .= "<img src='" . $this->file['media_thumb_path'] . "' width='" . $this->size[0] . "' height='" . $this->size[1] . "' alt='" . $this->file['media_thumb_path'] . "' />";
				if ($OBJ->vars->exhibit['operand'] != 3) $a .= "</a>\n";
	
				$a .= "</div>\n";
				$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning'>$this->title</div>\n" : '';
				$a .= "</div>\n";
				//$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning'>$this->title</div>\n" : '';
				$a .= "</div>\n\n";
			
			break;
			
			// left/bottom
			case 4:
			
				$top_padding = $OBJ->vars->exhibit['thumbs'] - $this->size[1];
				$temp_x = $OBJ->vars->exhibit['thumbs'] + $this->picture_block_padding_right;
				$temp_y = $OBJ->vars->exhibit['thumbs'] + $this->bottom_margin;
				$this->align = 'left';
			
				$pwidth = " style='width: " . $OBJ->vars->exhibit['thumbs'] ."px;'";
			
				if ($OBJ->vars->exhibit['titling'] == 1)
				{
					$yadjust = $this->thumbShapeHeight() + $this->text_block_height + $this->bottom_margin;
				}
				else
				{
					$yadjust = $this->thumbShapeHeight() + $this->bottom_margin;
				}
			
				$a = "<div class='picture_holder' id='node" . $this->file['media_id'] . "' style='width: {$temp_x}px; height: {$yadjust}px;'>\n";
				$a .= "<div class='picture'{$pwidth}>\n";
			
				$top_pad = ($OBJ->vars->exhibit['thumbs_shape'] == 0) ?
					($OBJ->vars->exhibit['thumbs'] - $this->size[1]) . 'px' : 0;
			
				$a .= "<div style='padding-top: {$top_pad};'>\n";
	
				$a .= $this->makeLink($this->file, $OBJ->vars->exhibit, $this->title);
				$a .= "<img src='" . $this->file['media_thumb_path'] . "' width='" . $this->size[0] . "' height='" . $this->size[1] . "' alt='" . $this->file['media_thumb_path'] . "' />";
				if ($OBJ->vars->exhibit['operand'] != 3) $a .= "</a>\n";
	
				$a .= "</div>\n";
				$a .= "</div>\n";
				$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning'>$this->title</div>\n" : '';
				$a .= "</div>\n\n";
			
			break;
			
			case 5:
			
				$top_padding = $OBJ->vars->exhibit['thumbs'] - $this->size[1];
				$temp_y = $OBJ->vars->exhibit['thumbs'] + $this->bottom_margin;
				$temp_x = $this->size[0] + $this->picture_block_padding_right;
				$this->align = 'left';
				
				// cinematic has a different height
				//if ($OBJ->vars->exhibit['thumbs_shape'] != 0)
				//{ 
				//	$temp_y = $this->size[0] + $this->bottom_margin;
				//}
			
				$pwidth = " style='width: " . $OBJ->vars->exhibit['thumbs'] ."px;'";
			
				if ($OBJ->vars->exhibit['titling'] == 1)
				{
					$yadjust = $this->thumbShapeHeight() + $this->text_block_height + $this->bottom_margin;
				}
				else
				{
					$yadjust = $this->thumbShapeHeight() + $this->bottom_margin;
				}
			
				$a = "<div class='picture_holder' id='node" . $this->file['media_id'] . "' style='width: {$temp_x}px; height: {$yadjust}px;'>\n";
				$a .= "<div class='picture'{$pwidth}>\n";
			
				$top_pad = ($OBJ->vars->exhibit['thumbs_shape'] == 0) ?
					($OBJ->vars->exhibit['thumbs'] - $this->size[1]) . 'px' : 0;
			
				$a .= "<div style='padding-top: {$top_pad};'>\n";
	
				$a .= $this->makeLink($this->file, $OBJ->vars->exhibit, $this->title);
				$a .= "<img src='" . $this->file['media_thumb_path'] . "' width='" . $this->size[0] . "' height='" . $this->size[1] . "' alt='" . $this->file['media_thumb_path'] . "' />";
				if ($OBJ->vars->exhibit['operand'] != 3) $a .= "</a>\n";
	
				$a .= "</div>\n";
				$a .= "</div>\n";
				$a .= ($OBJ->vars->exhibit['titling'] == 1) ? "<div class='captioning' style='width: " . $this->size[0] . "px;'>$this->title</div>\n" : '';
				$a .= "</div>\n\n";
			
			break;
		}
		
		return $a;
	}
	
	
	function createExhibit()
	{
		$OBJ =& get_instance();
		global $default, $medias;

		// if it's a link page do the iframed
		if ($OBJ->vars->exhibit['link'] != '')
		{
			$OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content'];
			return $OBJ->page->exhibit['exhibit'];
		}
		
		// is there a break? then we need the grid by default
		//if ($OBJ->vars->exhibit['break'] >= 1) $this->grid = true;
		
		$this->source = $default['filesource'][$OBJ->vars->exhibit['media_source']];
		
		// get images
		$this->imgs = $OBJ->page->get_imgs();
		
		// INTEGRATE THIS INTO THE MIX LATER!
		$OBJ->vars->images = $this->imgs;
		
		//print_r($this->imgs); exit;

		// if no images return our text only
		if (!$this->imgs) { $OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content']; return $OBJ->page->exhibit['exhibit']; }
	
		$s = ''; $a = ''; $w = 0; $i = 0;
		$this->final_img_container = ($OBJ->vars->exhibit['content'] != '') ? ($this->text_padding_right + $this->text_width) : 0;
		
		///////////////////
		$this->x = $OBJ->vars->exhibit['thumbs'];

		foreach ($this->imgs as $do)
		{
			foreach ($do as $go)
			{
				// media check
				if (in_array($go['media_mime'], array_merge($default['media'], $default['services'])))
				{
					//$OBJ->page->add_lib_js('swfobject.js', 21);
					$OBJ->page->add_jquery('jwplayer.js', 22);
				}
				
				$title = ($OBJ->vars->exhibit['titling'] == 1) ? 
					($OBJ->vars->exhibit['media_source'] == 0) ? $go['media_title'] : $go['title'] : '';
					
				$title = ($title != '') ? "<div class='title'>" . $title  . "</div>" : '';
			
				// height and width of thumbnail
				$this->size = ($OBJ->vars->exhibit['media_source'] == 0) ? 
					getimagesize(DIRNAME . '/files/gimgs/' . $go['media_thumb']) : 
					getimagesize(DIRNAME . '/files/dimgs/' . $go['media_thumb']);
				
				$this->file = $go;
				$this->title = $title;
				
				$a .= $this->grid();
			
				$this->br++;
			}
			
			$i++;
		}
		
		// call up the javascript files
		$this->getAssets();

		$s .= "\n<div id='img-container'>\n";
		$s .= $a;
		$s .= "<div style='clear: left;'><!-- --></div>";
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['exhibit'] = ($OBJ->vars->exhibit['placement'] == 1) ? 
			$s . $OBJ->vars->exhibit['content'] : 
			$OBJ->vars->exhibit['content'] . $s;

		return $OBJ->page->exhibit['exhibit'];
	}
	
	
	function thumbShapeHeight()
	{
		$OBJ =& get_instance();
		
		// do some math
		// square
		if ($OBJ->vars->exhibit['thumbs_shape'] == 1)
		{
			return $OBJ->vars->exhibit['thumbs'];
		}
		// 4x3
		elseif ($OBJ->vars->exhibit['thumbs_shape'] == 2)
		{
			return round(0.75 * $OBJ->vars->exhibit['thumbs']);
		}
		// 16x9 - ((9 * 200) / 16)
		elseif ($OBJ->vars->exhibit['thumbs_shape'] == 3)
		{
			return round((9 * $OBJ->vars->exhibit['thumbs']) / 16);
		}
		// 3x2
		elseif ($OBJ->vars->exhibit['thumbs_shape'] == 4)
		{
			return round(0.66 * $OBJ->vars->exhibit['thumbs']);
		}
		// natural
		else
		{
			return $OBJ->vars->exhibit['thumbs'];
		}
	}
	
	
	function doTitling($image_title='', $page_title='')
	{
		$OBJ =& get_instance();
		
		$title = '';
		
		if ($OBJ->vars->exhibit['titling'] == 1)
		{
			// it's an exhibit
			if ($OBJ->vars->exhibit['media_source'] == 0)
			{
				return $image_title;
			}
			else // use the page title
			{
				return $page_title;
			}
		}
		else
		{
			//
		}
		
		return $title;
	}
	
	function getAssets()
	{
		$OBJ =& get_instance();
		
		$OBJ->page->exhibit['dyn_js'][] = "var baseurl = '" . BASEURL . "';";
		
		// grow
		if ($OBJ->vars->exhibit['operand'] == 0)
		{
			/*
				opt={'br':'3'}
			*/
			
			$opts = '';
			$opts .= ($this->collapse != 1) ? "opt={'single':'false'}" : '';
			
			$OBJ->page->add_jquery('jquery.ndxz_grow.js', 20);
			//$OBJ->page->add_jquery('swfobject.js', 21); // RESEARCH THIS!
			$OBJ->page->add_jquery_onready("$('.picture_holder').ndxz_grow($opts);", 5);
			
			$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();
			
			$OBJ->page->exhibit['dyn_css'][] = "#img-container .picture .loading { position: absolute; top: 0; left: 0; z-index: 1; 
text-align: center; width: 24px; height: 24px;
background-position: center top; background-repeat: no-repeat; }
.loading { position: absolute; top: 18px; left: 0; z-index: 1; 
text-align: center; width: 24px; height: 24px;  
background-position: center top; background-repeat: no-repeat; }";
			
			// deal with the 'breaks'
			if ($OBJ->vars->exhibit['break'] >= 1)
			{
				$br = (($OBJ->vars->exhibit['break'] * $OBJ->vars->exhibit['thumbs']) + ($OBJ->vars->exhibit['break'] * $this->picture_block_padding_right));
				$OBJ->page->exhibit['dyn_css'][] = "#img-container { width: {$br}px; }";
			}
			
		}
		// linked
		else if ($OBJ->vars->exhibit['operand'] == 1)
		{
			$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();
			
			$br = (($OBJ->vars->exhibit['break'] * $OBJ->vars->exhibit['thumbs']) + ($OBJ->vars->exhibit['break'] * $this->picture_block_padding_right));
			//$OBJ->page->exhibit['dyn_css'][] = "#img-container { width: {$br}px; }";
		}
		// overlay
		else
		{
			$OBJ->page->add_jquery('jquery.ndxzbox.js', 21);
			//$OBJ->page->add_jquery('swfobject.js', 21); // better way to do this?
			$OBJ->page->exhibit['dyn_js'][] = "var theme = '" . $this->overlay . "';";
			$OBJ->page->add_jquery_onready("$(window).resize( function(){ resize(); });", 12);
			$OBJ->page->add_jquery_onready("$(window).scroll( function(){ resize(); });", 14);
			
			$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();
			$OBJ->page->exhibit['lib_css'][] = "overlay.css";
			
			$br = (($OBJ->vars->exhibit['break'] * $OBJ->vars->exhibit['thumbs']) + ($OBJ->vars->exhibit['break'] * $this->picture_block_padding_right));
			$OBJ->page->exhibit['dyn_js'][] = "var center = " . $this->center . ";";
			//$OBJ->page->exhibit['dyn_css'][] = "#img-container { width: {$br}px; }";
		}
	}
	
	// we need to account for the various sources as well
	function makeLink($img, $title='')
	{
		$OBJ =& get_instance();
		
		// external link
		//if ($OBJ->vars->exhibit['media_source'] != 0)
		//{
			if ($img['link'] != '')
			{
				if ($img['target'] == 1)
				{
					$link = $img['link'];
					//$target = ($OBJ->vars->exhibit['target'] == 1) ? " target='_new'" : '';

					return "<a href='$link' class='link' target='_new'>";
				}
				else
				{
					$link = $img['url'];
				
					return "<a href='$link' class='link'>";
				}
			}
		//}
		
		// if the asset is a link
		if (preg_match("/^http/i", $img['media_file']))
		{
			return "<a href='" . $img['media_file'] . "' class='link' target='_blank'>";
		}

		// grow
		if ($OBJ->vars->exhibit['operand'] == 0)
		{
			$link = ($OBJ->vars->exhibit['media_source'] == 0) ? '#' : BASEURL . ndxz_rewriter($img['url']);
			$onclick = ($OBJ->vars->exhibit['media_source'] == 0) ? 
				' onclick="$.fn.ndxz_grow.grower(this, true); return false;"' :
				'';

			return "<a href='$link' class='link' id='a$img[media_id]' $onclick>";
		}
		// linked
		else if ($OBJ->vars->exhibit['operand'] == 1)
		{
			$link = ($OBJ->vars->exhibit['media_source'] == 0) ? BASEURL . ndxz_rewriter($img['url']) . "$img[media_file]" : 
				BASEURL . ndxz_rewriter($img['url']);

			return "<a href='$link' class='link'>";
		}
		// unlinked
		else if ($OBJ->vars->exhibit['operand'] == 3)
		{
			return "";
		}
		// overlay
		else
		{
			$link = ($OBJ->vars->exhibit['media_source'] == 0) ? $img['media_path'] : BASEURL . ndxz_rewriter($img['url']);
			$overlay = ($OBJ->vars->exhibit['media_source'] == 0) ? " class='group overlay'" : '';

			//return "<a href='$link' id='aaa$img[media_id]'{$overlay} rel='group$exhibit[id]'>";
			
			$theexhibit = $OBJ->vars->exhibit['id'];
			return "<a href='$link' id='aaa$img[media_id]'{$overlay} rel='group$theexhibit'>";
		}
	}
	
	
	function do_break($limit=0, $counter, $html)
	{
		if ($limit != 0)
		{
			if ($limit == $counter) 
			{
				$this->br = 0;
				return $html;
			}
		}
	}


	function defaultCSS()
	{
		$OBJ =& get_instance();

		$align = " text-align: " . $this->align . ";";	
		$talign = $this->align;
		
		$grid = ($OBJ->vars->exhibit['break'] >= 1) ? 'width: ' . (($OBJ->vars->exhibit['break'] * ($this->picture_block_padding_right + $OBJ->vars->exhibit['thumbs'])) + 1) . 'px' : '';
		
		$title_block = ($OBJ->vars->exhibit['titling'] == 1) ? $this->text_block_height : 0;
		
		$picture_height = ($OBJ->vars->exhibit['thumbs_shape'] == 0) ? "height: " . $this->x . "px;" : '';
		$picture_width = ($this->grid == (0 || 1 || 2 || 3)) ? "width: " . $this->x . "px; " : '';
		
		$caption_width = ($this->grid == 5) ? 0 : 'width: ' . $OBJ->vars->exhibit['thumbs'] . 'px;';
		
		return "#img-container { {$grid} }
#img-container #text { float: left; width: " . ($this->text_width + $this->text_padding_right) . "px; }
#img-container #text p { width: " . $this->text_width . "px; }
#img-container .picture_holder { float: left; }
#img-container .picture { position: relative;{$align} }
#img-container .picture { {$picture_width}{$picture_height} }
#img-container .picture_holder { padding: 0 0 0 0; }
#img-container .captioning { height: {$title_block}px; text-align: center;{$caption_width} }
#img-container .captioning .title { text-align: {$talign}; }";
	}
}