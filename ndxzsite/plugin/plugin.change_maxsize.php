<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Plugin Name: Change maxsize
Plugin URI: http://www.indexhibit.org/plugin/image-max-size/
Description: Change image upload maximum size. Use with caution.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: update_defaults
Function: change_maxsize:change
Order: 11
Options Builder: make_option
End
*/

class Change_maxsize
{
	public function __construct()
	{
		
	}
	
	function change()
	{
		$OBJ =& get_instance();
		
		if (isset($OBJ->hook->options['change_maxsize']['maxsize']))
		{
			$OBJ->vars->default['maxsize'] = $OBJ->hook->options['change_maxsize']['maxsize'];
		}
		
		return false;
	}
	
	function make_option()
	{
		$OBJ =& get_instance();

		$maxsize = (isset($this->options['maxsize'])) ? $this->options['maxsize'] : 0;
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');
	
		$html = "<div style='padding-right: 15px;'><label id='maxsize_value'>maximum image upload size <span>$maxsize</span></label>\n";
		$html .= "<input type='hidden' id='maxsize' name='option[maxsize]' value='$maxsize' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $maxsize, min: 100, max: 1400, step: 50,   
stop: function(event, ui) { $('#maxsize').val(ui.value); },
slide: function(event, ui) { $('label#maxsize_value span').html(ui.value); }
});";
	
		return $html;
	}
}