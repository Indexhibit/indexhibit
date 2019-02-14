<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Thickbox
Format URI: http://www.indexhibit.org/format/thickbox/
Description: Indexhibit format.
Version: 1.0
Author: Vaska
Author URI: https://vaska.com
Params: format,images,thumbs,shape,placement
Options Builder: default_settings
Source: exhibit
Operands: overlay,permalinks,unlinked
Objects: exhibits
*/

/**
* Thickbox
*
* Exhbition format
* 
* @version 1.0
* @author Vaska 
*/

class Exhibit
{
	// PADDING AND TEXT WIDTH ADJUSTMENTS UP HERE!!!
	var $imgs = array();
	var $bottom_margin = 21;
	var $picture_block_padding_right = 21;
	var $operand = 0;
	var $source;
	var $overlay = 'dark';
	var $settings = array();
	var $medias = array();
	var $size = array();
	var $file = array();
    var $find_smallest_height = array();
    var $smallest_height = 0;
	
	///////////////
	var $x;

	public function default_settings()
	{
		$OBJ =& get_instance();

		$margin = (isset($this->settings['margin'])) ? $this->settings['margin'] : 0;
		$bottom_margin = (isset($this->settings['bottom_margin'])) ? $this->settings['bottom_margin'] : 0;
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
		$this->picture_block_padding_right = (isset($OBJ->hook->options['thickbox_settings']['margin'])) ? 
			$OBJ->hook->options['thickbox_settings']['margin'] : 25;
		$this->bottom_margin = (isset($OBJ->hook->options['thickbox_settings']['bottom_margin'])) ? 
			$OBJ->hook->options['thickbox_settings']['bottom_margin'] : 25;	
		$this->overlay = (isset($OBJ->hook->options['thickbox_settings']['overlay'])) ? 
			$OBJ->hook->options['thickbox_settings']['overlay'] : 'dark';
	}
	
	
	function grid()
	{
		$OBJ =& get_instance();
				
		$a = "<div class='picture_holder' id='node" . $this->file['media_id'] . "'>\n";
		$a .= "<div class='picture'>\n";
				
		$a .= "<div>\n";
		
		$a .= $this->makeLink($this->file, $OBJ->vars->exhibit, $this->title);
		$a .= "<img src='" . $this->file['media_thumb_path'] . "' height='" . $this->smallest_height . "' alt='" . $this->file['media_thumb_path'] . "' />";
		if ($OBJ->vars->exhibit['operand'] != 2) $a .= "</a>\n";
		
		$a .= "</div>\n";
		$a .= "</div>\n";
		$a .= "</div>\n\n";
		
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
		
		$this->source = $default['filesource'][$OBJ->vars->exhibit['media_source']];
		
		// get images
		$this->imgs = $OBJ->page->get_imgs();
		
		// INTEGRATE THIS INTO THE MIX LATER!
		$OBJ->vars->images = $this->imgs;

		// if no images return our text only
		if (!$this->imgs) { $OBJ->page->exhibit['exhibit'] = $OBJ->vars->exhibit['content']; return $OBJ->page->exhibit['exhibit']; }
	
		$s = ''; $a = '';
        
        // we need to find the smallest height
        foreach ($this->imgs[0] as $height)
        {
            $heights = getimagesize(DIRNAME . GIMGS . "/th-" . $height[media_ref_id] . '_' . $height[media_file]);
            $this->find_smallest_height[] = $heights[1];
        }

        sort($this->find_smallest_height, SORT_NUMERIC);
        rsort($this->find_smallest_height);
        $this->smallest_height = array_pop($this->find_smallest_height);
        // end smallest height

		foreach ($this->imgs as $do)
		{
			foreach ($do as $go)
			{	
				$this->file = $go;
				
				$a .= $this->grid();
			}
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

	
	function getAssets()
	{
		$OBJ =& get_instance();
		
		$OBJ->page->exhibit['dyn_js'][] = "var baseurl = '" . BASEURL . "';";
		$OBJ->page->exhibit['dyn_css'][] = $this->defaultCSS();
		
		// overlay
		if ($OBJ->vars->exhibit['operand'] == 0)
		{
			$OBJ->page->add_jquery('jquery.ndxzbox.js', 21);
			$OBJ->page->exhibit['dyn_js'][] = "var theme = '" . $this->overlay . "';";
			$OBJ->page->add_jquery_onready("$(window).resize( function(){ resize(); });", 12);
			$OBJ->page->add_jquery_onready("$(window).scroll( function(){ resize(); });", 14);
			$OBJ->page->exhibit['lib_css'][] = "overlay.css";
		}
		// linked
		else if ($OBJ->vars->exhibit['operand'] == 1)
		{

		}
		else // not linked
		{
			
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

		// overlay
		if ($OBJ->vars->exhibit['operand'] == 0)
		{
			$link = ($OBJ->vars->exhibit['media_source'] == 0) ? $img['media_path'] : BASEURL . ndxz_rewriter($img['url']);
			$overlay = ($OBJ->vars->exhibit['media_source'] == 0) ? " class='group overlay'" : '';

			//return "<a href='$link' id='aaa$img[media_id]'{$overlay} rel='group$exhibit[id]'>";
			
			$theexhibit = $OBJ->vars->exhibit['id'];
			return "<a href='$link' id='aaa$img[media_id]'{$overlay} rel='group$theexhibit'>";
		}
		// linked
		else if ($OBJ->vars->exhibit['operand'] == 1)
		{
			$link = ($OBJ->vars->exhibit['media_source'] == 0) ? BASEURL . ndxz_rewriter($img['url']) . "$img[media_file]" : 
				BASEURL . ndxz_rewriter($img['url']);

			return "<a href='$link' class='link'>";
		}
		// unlinked
		else if ($OBJ->vars->exhibit['operand'] == 2)
		{
			return "";
		}
		// nothing
		else
		{

		}
	}

	function defaultCSS()
	{
		$OBJ =& get_instance();

		return "#img-container { width: auto; }
#img-container .picture_holder { float: left; line-height: 0px; padding-right: " . $this->picture_block_padding_right . "px !important; padding-bottom: " . $this->bottom_margin  . "px !important; }
#img-container .picture { position: relative; }
#img-container .picture {  }
#img-container .picture_holder { padding: 0 0 0 0; }";
	}
}