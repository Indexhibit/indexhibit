<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Format Name: Documenta
Format URI: http://www.indexhibit.org/format/documenta/
Description: Auto columns for your exhibition.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Params: format,images,thumbs,shape,placement,break
Options Builder: default_settings
Source: exhibit
Operands: permalinks,overlay,unlinked
Objects: exhibits
*/

/**
* Columnize Format
*
* Exhbition format
* 
* @version 1.0
* @author Vaska 
*/


class Exhibit
{
	// PADDING AND TEXT WIDTH ADJUSTMENTS UP HERE!!!
	var $margin_right = 25;
	var $imgs = array();
	var $overlay = 'dark';
	var $settings = array();
	var $bottom_margin;
	
	public function __construct()
	{
		
	}
	
	function default_settings()
	{
		$OBJ =& get_instance();
		
		$bottom_margin = (isset($this->settings['bottom_margin'])) ? $this->settings['bottom_margin'] : 0;
		$overlay = (isset($this->settings['overlay'])) ? $this->settings['overlay'] : 0;

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
		
		$html = "<label id='bottom_margin_value'>bottom margin <span>$bottom_margin</span></label>\n";
		$html .= "<input type='hidden' id='bottom_margin' name='option[bottom_margin]' value='$bottom_margin' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $bottom_margin, max: 20, 
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
	
	function createExhibit()
	{
		$OBJ =& get_instance();
		global $rs, $default;
		
		$this->overlay = (isset($OBJ->hook->options['documenta_settings']['overlay'])) ? 
			$OBJ->hook->options['documenta_settings']['overlay'] : 'dark';
			
		$this->bottom_margin = (isset($OBJ->hook->options['documenta_settings']['bottom_margin'])) ? 
			$OBJ->hook->options['documenta_settings']['bottom_margin'] : 0;
		
		$this->center = 'true';
		
		// exhibit only source
		$this->source = $default['filesource'][0];
	
		// get images
		$this->imgs = $OBJ->page->get_imgs();
		
		//$OBJ->page->add_jquery('autocolumn.js', 20);
		$OBJ->page->add_jquery('jquery.columnizer.js', 20);

		$OBJ->page->exhibit['dyn_css'][] = $this->dynamicCSS();
		
		$columns = ($rs['break'] == 0) ? 0 : $rs['break'];
		$width = $rs['thumbs'] + $this->margin_right;
		
		$insert = ($columns == 0) ? "width: $width" : "columns: $columns";
		
		$OBJ->page->exhibit['dyn_js'][] = "var column_width = " . $this->margin_right . ";";
		$OBJ->page->exhibit['dyn_js'][] = "var image_width = " . $rs['thumbs'] . ";";
		
		$OBJ->page->add_jquery_onready("$('.picture_holder').addClass('dontsplit');
$('#img-container').columnize({ $insert, lastNeverTallest: true });", 5);

		// overlay
		if ($OBJ->vars->exhibit['operand'] == 1)
		{
			$OBJ->page->add_jquery('jquery.ndxzbox.js', 21);
			//$OBJ->page->add_jquery('swfobject.js', 22); // better way to do this?
			$OBJ->page->add_jquery_onready("$(window).resize( function(){ resize(); });", 12);
			$OBJ->page->add_jquery_onready("$(window).scroll( function(){ resize(); });", 14);
			$OBJ->page->exhibit['dyn_js'][] = "var theme = '" . $this->overlay . "';";
			$OBJ->page->exhibit['dyn_js'][] = "var center = true;";
			$OBJ->page->exhibit['lib_css'][] = "overlay.css";
		}

		// if no images return our text only
		if (!$this->imgs) 
		{ 
			$s = "\n<div id='wrap-columns'>\n";
			$s .= "\n<div class='thin'>\n";
			$s .= "\n<div id='img-container'>\n";
			$s .= $rs['content'];
			$s .= "<div style='clear: left;'><!-- --></div>";
			$s .= "</div>\n";
			$s .= "</div>\n";
			$s .= "</div>\n";
			
			$OBJ->page->exhibit['exhibit'] = $s; 
			return $OBJ->page->exhibit['exhibit'];
		}
	
		$s = ''; $a = ''; $w = 0;

		foreach ($this->imgs as $do)
		{
			foreach ($do as $go)
			{
				// media check
				if (in_array($go['media_mime'], array_merge($default['media'], $default['services'])))
				{
					$OBJ->page->add_lib_js('jwplayer.js', 21);
				}

				$title = ($go['media_title'] == '') ? '' : "<div class='title'>" . $go['media_title'] . "</div>";
			
				// height and width of thumbnail
				$size = @getimagesize(DIRNAME . GIMGS . '/' . $go['media_thumb']);
		
				$a .= "<div class='picture_holder'>\n";
				$a .= "<div class='picture'>\n";
				$a .= "<div class='inner-picture'>\n";
				
				// default (permalink)
				if ($OBJ->vars->exhibit['operand'] == 0)
				{
					$a .= "<a href='" . BASEURL . ndxz_rewriter($rs['url'])  . $go['media_file'] . "'>";
					//$a .= "<a href='" . BASEURL . $rs['url'] . $go['media_file'] . "'>";
					$a .= "<img src='$go[media_thumb_path]' width='$size[0]' height='$size[1]' alt='$go[media_thumb_path]' />";
					$a .= "</a>\n";
				}
				elseif ($OBJ->vars->exhibit['operand'] == 1) // overlay
				{
					$a .= "<a href='$go[media_path]' id='aaa$go[media_id]' class='group overlay' rel='group$rs[id]'>";
					$a .= "<img src='$go[media_thumb_path]' width='$size[0]' height='$size[1]' alt='$go[media_thumb_path]' />";
					$a .= "</a>\n";
					
					$OBJ->page->exhibit['dyn_js'][] = "var baseurl = '" . BASEURL . "';";
					$OBJ->page->add_jquery('jquery.ndxzbox.js', 21);
					//$OBJ->page->add_jquery('swfobject.js', 21); // better way to do this?
					$OBJ->page->add_jquery_onready("$(window).resize( function(){ resize(); });", 12);
					$OBJ->page->add_jquery_onready("$(window).scroll( function(){ resize(); });", 14);
				}
				else // unlinked
				{
					//$a .= "<a href='" . BASEURL . ndxz_rewriter($rs['url'])  . $go['media_file'] . "'>";
					$a .= "<img src='$go[media_thumb_path]' width='$size[0]' height='$size[1]' alt='$go[media_thumb_path]' />";
					//$a .= "</a>\n";
				}
				
				$a .= "</div>\n";
				$a .= "</div>\n";
				if ($go['media_title'] != '') $a .= "<div class='media_title'>$title</div>\n";
				if ($go['media_caption'] != '') $a .= "<div class='media_caption'>" . $this->reduction($go['media_caption'], "...", 60) . "</div>\n";
				$a .= "</div>\n\n";
			}
		}
		
		$s .= "\n<div id='wrap-columns'>\n";
		$s .= "\n<div class='thin'>\n";
		$s .= "\n<div id='img-container'>\n";
		//if ($rs['content'] != '') $s .= "<div id='text'>" . $rs['content'] . "</div>\n";
		$s .= ($rs['placement'] == 1) ? $a . $rs['content'] : $rs['content'] . $a;
		$s .= "<div style='clear: left;'><!-- --></div>";
		$s .= "</div>\n";
		$s .= "</div>\n";
		$s .= "</div>\n";
		
		$OBJ->page->exhibit['exhibit'] = $s;
		return $OBJ->page->exhibit['exhibit'];
	}
	
	
	function reduction($string='', $repl, $limit=40)
	{
	    if (($string == '') || ($string == null)) return;

	    $limit = @strpos(strip_tags($string), " ", $limit);

	    if ($limit)
	    {
	        return substr_replace(strip_tags($string), $repl, $limit);
	    }
	    else
	    {
	        return $string;
	    }
	}


	function dynamicCSS()
	{
		global $rs;
		
		// having a hard time figuring out how to control column sizes beyond this - seems partially random
		// 50 is an arbitrary number
		$wrap = ($rs['break'] == 0) ? 'auto' : (($rs['break'] * ($rs['thumbs'] + $this->margin_right)) + 50) . 'px';
		
		return "#wrap-columns { width: {$wrap}; }
#exhibit .thin #img-container p,
#exhibit .thin #img-container code,
#exhibit .thin #img-container blockquote 
{ width: auto; margin-right: " . $this->margin_right . "px; }
#exhibit .picture a { background: transparent; }
.thin { clear: both; }
.inner-picture { margin-bottom: " . $this->bottom_margin . "px; }
.picture_holder { margin-bottom: 12px; margin-right: " . $this->margin_right . "px; }";
	}
}