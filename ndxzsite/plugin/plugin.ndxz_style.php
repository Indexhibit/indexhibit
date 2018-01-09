<?php

/*
Plugin Name: Indexhbit Style
Plugin URI: http://www.indexhibit.org/plugin/indexhibit-style/
Description: Color picker test.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: pre_load
Function: indexhibit_style:picker
Order: 11
Options Builder: make_option
End
*/

class indexhibit_style
{
	var $options = array();

	public function __construct()
	{
		
	}

	public function picker()
	{
		$OBJ =& get_instance();
		
		if (isset($OBJ)) 
		{
			// we can't use these with mobile
			if ($OBJ->vars->default['isMobile'] == false) 
			{
				$OBJ->page->exhibit['dyn_css'][] = "#index { width: " . $OBJ->hook->options['indexhibit_style']['menu_width'] . "px; }";
				$OBJ->page->exhibit['dyn_css'][] = "#exhibit { margin-left: " . $OBJ->hook->options['indexhibit_style']['menu_width'] . "px; }";
				
				$OBJ->page->exhibit['dyn_css'][] = "#index, #exhibit { font-size: " . $OBJ->hook->options['indexhibit_style']['font_size'] . "px; }";
				$OBJ->page->exhibit['dyn_css'][] = "#index, #exhibit { line-height: " . $OBJ->hook->options['indexhibit_style']['line_height'] . "px; }";

				$OBJ->page->exhibit['dyn_css'][] = ".container { padding-top: " . $OBJ->hook->options['indexhibit_style']['padding_top'] . "px; }";
				$OBJ->page->exhibit['dyn_css'][] = ".container { padding-left: " . $OBJ->hook->options['indexhibit_style']['padding_left'] . "px; }";
			}
			
			$OBJ->page->exhibit['dyn_css'][] = "a:link { color: #" . $OBJ->hook->options['indexhibit_style']['bgcolor'] . "; }";
			$OBJ->page->exhibit['dyn_css'][] = "a:visited { color: #" . $OBJ->hook->options['indexhibit_style']['bgcolor3'] . "; }";
			$OBJ->page->exhibit['dyn_css'][] = "a:hover { color: #" . $OBJ->hook->options['indexhibit_style']['bgcolor2'] . "; }";
		
			$OBJ->page->exhibit['dyn_css'][] = "#index, #exhibit { color: #" . $OBJ->hook->options['indexhibit_style']['text_color'] . "; }";
		
			$OBJ->page->exhibit['dyn_css'][] = "#index, #exhibit { font-family: " . $OBJ->hook->options['indexhibit_style']['fonts'] . "; }";
		
		if (($OBJ->hook->options['indexhibit_style']['astyle'] == 'bold') || ($OBJ->hook->options['indexhibit_style']['astyle'] == 'italic'))
		{
			if ($OBJ->hook->options['indexhibit_style']['astyle'] == 'bold') $OBJ->page->exhibit['dyn_css'][] = "li.active a:link, li a.active, li.active a:hover, li a.active, li.active a:active, li a.active, li.active a:visited, li a.active, li span.active, #index ul.section li.active a:link, #index ul.section li.active a:hover, #index ul.section li.active a:active, #index ul.section li.active a:visited { font-weight: bold; }";
			
			if ($OBJ->hook->options['indexhibit_style']['astyle'] == 'italic') $OBJ->page->exhibit['dyn_css'][] = "li.active a:link, li a.active, li.active a:hover, li a.active, li.active a:active, li a.active, li.active a:visited, li a.active, li span.active, #index ul.section li.active a:link, #index ul.section li.active a:hover, #index ul.section li.active a:active, #index ul.section li.active a:visited { font-style: italic; }"; 
		}
		
		if (($OBJ->hook->options['indexhibit_style']['ststyle'] == 'bold') || ($OBJ->hook->options['indexhibit_style']['ststyle'] == 'italic'))
		{
			if ($OBJ->hook->options['indexhibit_style']['ststyle'] == 'bold') $OBJ->page->exhibit['dyn_css'][] = "#index ul.section span.section_title, #index ul.section span.section_title a, #index ul.section span.subsection_title, #index ul.section span.subsection_title a { font-weight: bold; }";
			
			if ($OBJ->hook->options['indexhibit_style']['ststyle'] == 'italic') $OBJ->page->exhibit['dyn_css'][] = "#index ul.section span.section_title, #index ul.section span.section_title a, #index ul.section span.subsection_title, #index ul.section span.subsection_title a { font-style: italic; }"; 
		}
		
		$OBJ->page->exhibit['dyn_css'][] = "#index ul.section span.section_title, #index ul.section span.section_title a, #index ul.section span.subsection_title, #index ul.section span.subsection_title a { font-size: " . $OBJ->hook->options['indexhibit_style']['stsize'] . "px; }";
		}
	}

	public function make_option()
	{
		$OBJ =& get_instance();
		
		$OBJ->template->add_js('jquery.colorpick.js');
		$OBJ->template->add_css('jquery.colorpick.css');
	
		$bgcolor = (isset($this->options['bgcolor'])) ? $this->options['bgcolor'] : 'ffffff';
		$bgcolor2 = (isset($this->options['bgcolor2'])) ? $this->options['bgcolor2'] : 'ffffff';
		$bgcolor3 = (isset($this->options['bgcolor3'])) ? $this->options['bgcolor3'] : 'ffffff';
		$menu_width = (isset($this->options['menu_width'])) ? $this->options['menu_width'] : 215;
		$size = (isset($this->options['font_size'])) ? $this->options['font_size'] : 13;
		$line = (isset($this->options['line_height'])) ? $this->options['line_height'] : 9;
		$ptop = (isset($this->options['padding_top'])) ? $this->options['padding_top'] : 27;
		$pleft = (isset($this->options['padding_left'])) ? $this->options['padding_left'] : 27;
		$tcolor = (isset($this->options['text_color'])) ? $this->options['text_color'] : '000';
		$fonts = (isset($this->options['fonts'])) ? $this->options['fonts'] : 'Arial, Verdana, sans-serif';
		$astyle = (isset($this->options['ststyle'])) ? $this->options['ststyle'] : 'normal';
		$ststyle = (isset($this->options['astyle'])) ? $this->options['astyle'] : 'normal';
		$stsize = (isset($this->options['stsize'])) ? $this->options['stsize'] : 13;
		
		// things to add
		$html = $this->fonts($fonts, 'fonts', 'fonts');
		$html .=  $this->slider_font_size($size);
		$html .=  $this->slider_line_height($line);
		$html .= $this->color_chooser($tcolor, 'text color', 'text_color');
		$html .= $this->slider_widget($ptop, $min=0, $max=100, 'padding top', 'padding_top');
		$html .= $this->slider_widget($pleft, $min=0, $max=100, 'padding left', 'padding_left');
		$html .=  $this->slider_menu_width($menu_width);

		// background color - this is a mess
		$html .= "<div style='margin-bottom: 12px;'>\n";
		$html .= "<label>" . $OBJ->lang->word('link color') . "</label>\n";
		$html .= "<input type='hidden' id='bgcolor_value' name='option[bgcolor]' value='$bgcolor' />";
		$html .= "<div id='bgcolor' style='margin: 3px 0 6px 0;'><div style='background-color: #$bgcolor; width: 15px; height: 15px; border: 1px solid #ccc; cursor: pointer;'></div></div></div>";

		$OBJ->template->onready[] = "$('#bgcolor').ColorPicker({
	color: '$bgcolor',
	onShow: function (colpkr) {
	$(colpkr).show();
	return false;
	},
	onHide: function (colpkr) {
	$(colpkr).hide();
	return false;
	},
	onSubmit: function (hsb, hex, rgb) {
	// update the color here
	$('#bgcolor div').css('backgroundColor', '#' + hex);
	$('#bgcolor_value').val(hex);
	$('.colorpicker').hide();
	getColor(hex);
	return false;
	}
	});";

	// background color - this is a mess
	$html .= "<div style='margin-bottom: 12px;'>\n";
	$html .= "<label>" . $OBJ->lang->word('link color hover') . "</label>\n";
	$html .= "<input type='hidden' id='bgcolor_value2' name='option[bgcolor2]' value='$bgcolor2' />";
	$html .= "<div id='bgcolor2' style='margin: 3px 0 6px 0;'><div style='background-color: #$bgcolor2; width: 15px; height: 15px; border: 1px solid #ccc; cursor: pointer;'></div></div></div>";

	$OBJ->template->onready[] = "$('#bgcolor2').ColorPicker({
	color: '$bgcolor2',
	onShow: function (colpkr) {
	$(colpkr).show();
	return false;
	},
	onHide: function (colpkr) {
	$(colpkr).hide();
	return false;
	},
	onSubmit: function (hsb, hex, rgb) {
	// update the color here
	$('#bgcolor2 div').css('backgroundColor', '#' + hex);
	$('#bgcolor_value2').val(hex);
	$('.colorpicker').hide();
	getColor(hex);
	return false;
	}
	});";

	// background color - this is a mess
	$html .= "<div style='margin-bottom: 12px;'>\n";
	$html .= "<label>" . $OBJ->lang->word('link color visited') . "</label>\n";
	$html .= "<input type='hidden' id='bgcolor_value3' name='option[bgcolor3]' value='$bgcolor3' />";
	$html .= "<div id='bgcolor3' style='margin: 3px 0 6px 0;'><div style='background-color: #$bgcolor3; width: 15px; height: 15px; border: 1px solid #ccc; cursor: pointer;'></div></div></div>";

	$OBJ->template->onready[] = "$('#bgcolor3').ColorPicker({
	color: '$bgcolor3',
	onShow: function (colpkr) {
	$(colpkr).show();
	return false;
	},
	onHide: function (colpkr) {
	$(colpkr).hide();
	return false;
	},
	onSubmit: function (hsb, hex, rgb) {
	// update the color here
	$('#bgcolor3 div').css('backgroundColor', '#' + hex);
	$('#bgcolor_value3').val(hex);
	$('.colorpicker').hide();
	getColor(hex);
	return false;
	}
	});";
	
		// active link style
		$html .= $this->font_style($astyle, 'Active Link Style', 'astyle');
	
		// section title parts
		$html .= $this->font_style($ststyle, 'Section Title Style', 'ststyle');
		$html .= $this->font_size($stsize, 'Section Title Font Size', 'stsize', 9, 21);
		// ?
	
		return $html;
	}
	
	
	public function font_style($value, $title, $id)
	{
		$OBJ =& get_instance();
		
		$styles = array('normal', 'bold', 'italic');
		
		$html = "<p><label>$title</label>\n";
		$html .= "<select name='option[{$id}]'>\n";
		
		foreach ($styles as $style) 
		{
			$selected = ($value == $style) ? " selected='selected'" : '';
			$html .= "<option value='$style'{$selected}>$style</option>\n";
		}	
			
		$html .= "</select></p>\n";
		
		return $html;
	}
	
	
	public function color_chooser($value='fff', $title, $id)
	{
		$OBJ =& get_instance();

		$html = "<div style='margin-bottom: 12px;'>\n";
		$html .= "<label>" . $OBJ->lang->word($title) . "</label>\n";
		$html .= "<input type='hidden' id='{$id}_value' name='option[{$id}]' value='$value' />";
		$html .= "<div id='{$id}' style='margin: 3px 0 6px 0;'><div style='background-color: #$value; width: 15px; height: 15px; border: 1px solid #ccc; cursor: pointer;'></div></div></div>\n\n";

		$OBJ->template->onready[] = "$('#{$id}').ColorPicker({
	color: '$value',
	onShow: function (colpkr) {
	$(colpkr).show();
	return false;
	},
	onHide: function (colpkr) {
	$(colpkr).hide();
	return false;
	},
	onSubmit: function (hsb, hex, rgb) {
	// update the color here
	$('#{$id} div').css('backgroundColor', '#' + hex);
	$('#{$id}_value').val(hex);
	$('.colorpicker').hide();
	getColor(hex);
	return false;
	}
	});";
	
		return $html;
	}
	
	public function fonts($value, $title, $id)
	{
		$OBJ =& get_instance();
		
		$fonts = array('Arial, Verdana, sans-serif', 
			'Times, Times Roman, serif', 
			'Helvetica Neue, Arial, Verdana, sans-serif',
			'Courier New, Courier, monospace, sans-serif',
			'Garamond, Times, Times Roman, serif',
			'sans-serif', 'serif', 'monospace');
		
		$html = "<p><label>$title</label>\n";
		$html .= "<select name='option[{$id}]'>\n";
		
		foreach ($fonts as $font) 
		{
			$selected = ($value == $font) ? " selected='selected'" : '';
			$html .= "<option value='$font'{$selected}>$font</option>\n";
		}	
			
		$html .= "</select></p>\n";
		
		return $html;
	}
	
	public function slider_widget($value=9, $min=0, $max=200, $title, $id)
	{
		$OBJ =& get_instance();

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
		
		$html = "<label id='{$id}_value'>$title <span>$value</span></label>\n";
		$html .= "<input type='hidden' id='{$id}' name='option[{$id}]' value='$value' />\n";
		$html .= "<div id='{$id}_size' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#{$id}_size').slider({ value: $value, min: $min, max: $max,  
	stop: function(event, ui) { $('#{$id}').val(ui.value); },
	slide: function(event, ui) { $('label#{$id}_value span').html(ui.value) }
	});";
	
		return $html;
	}
	
	public function slider_line_height($size=9)
	{
		$OBJ =& get_instance();

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
		
		$html = "<label id='line_height_value'>line height <span>$size</span></label>\n";
		$html .= "<input type='hidden' id='line_height' name='option[line_height]' value='$size' />\n";
		$html .= "<div id='line_height_size' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#line_height_size').slider({ value: $size, min: 9, max: 50,  
	stop: function(event, ui) { $('#line_height').val(ui.value); },
	slide: function(event, ui) { $('label#line_height_value span').html(ui.value) }
	});";
	
		return $html;
	}
	
	
	public function font_size($size=9, $title, $var, $min, $max)
	{
		$OBJ =& get_instance();

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
		
		$html = "<label id='{$var}_size_value'>$title <span>$size</span></label>\n";
		$html .= "<input type='hidden' id='{$var}_size' name='option[$var]' value='$size' />\n";
		$html .= "<div id='{$var}_font_size' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#{$var}_font_size').slider({ value: $size, min: $min, max: $max,  
	stop: function(event, ui) { $('#{$var}_size').val(ui.value); },
	slide: function(event, ui) { $('label#{$var}_size_value span').html(ui.value) }
	});";
	
		return $html;
	}
	
	
	public function slider_font_size($size=9)
	{
		$OBJ =& get_instance();

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
		
		$html = "<label id='font_size_value'>font size <span>$size</span></label>\n";
		$html .= "<input type='hidden' id='font_size' name='option[font_size]' value='$size' />\n";
		$html .= "<div id='slider_font_size' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider_font_size').slider({ value: $size, max: 27,  
	stop: function(event, ui) { $('#font_size').val(ui.value); },
	slide: function(event, ui) { $('label#font_size_value span').html(ui.value) }
	});";
	
		return $html;
	}
	
	public function slider_menu_width($width)
	{
		$OBJ =& get_instance();

		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
		
		$html = "<label id='menu_width_value'>menu_width <span>$width</span></label>\n";
		$html .= "<input type='hidden' id='menu_width' name='option[menu_width]' value='$width' />\n";
		$html .= "<div id='slider_menu' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider_menu').slider({ value: $width, max: 350,  
	stop: function(event, ui) { $('#menu_width').val(ui.value); },
	slide: function(event, ui) { $('label#menu_width_value span').html(ui.value) }
	});";
	
		return $html;
	}
}