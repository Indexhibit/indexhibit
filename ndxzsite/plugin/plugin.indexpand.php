<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Plugin Name: Indexpand
Plugin URI: http://www.indexhibit.org/plugin/indexpand/
Description: Enables Indexpand.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: pre_load
Options Builder: default_settings

Function: indexpand:setup
End
*/

class Indexpand
{
	public function __construct()
	{
		
	}
	
	function default_settings()
	{
		$OBJ =& get_instance();
		
		// if it's not set we use the default value
		$single = (isset($this->options['single'])) ? $this->options['single'] : 'true';
		$exclude = (isset($this->options['exclude'])) ? $this->options['exclude'] : array();
		$mouse = (isset($this->options['mouse'])) ? $this->options['mouse'] : 'click';
		$speed = (isset($this->options['speed'])) ? $this->options['speed'] : 100;
		
		$html = "<label>Auto collapse section</label>\n";
		$html .= "<select name='option[single]'>\n";
		$html .= "<option value='true'" . $this->selected($single, "true") . ">Yes</option>\n";
		$html .= "<option value='false'" . $this->selected($single, "false") . ">No</option>\n";
		$html .= "</select>\n";
		
		$html .= "<label>Mouse behavior</label>\n";
		$html .= "<select name='option[mouse]'>\n";
		$html .= "<option value='click'" . $this->selected($mouse, "click") . ">click</option>\n";
		$html .= "<option value='over'" . $this->selected($mouse, "over") . ">hover</option>\n";
		$html .= "</select>\n";
		
		$OBJ->template->add_css('themes/ui-lightness/jquery.ui.all.css');
		$OBJ->template->add_js('ui/jquery.ui.core.js');
		$OBJ->template->add_js('ui/jquery.ui.widget.js');
		$OBJ->template->add_js('ui/jquery.ui.mouse.js');
		$OBJ->template->add_js('ui/jquery.ui.slider.js');

		$html .= "<label id='speed_value'>transition speed (milliseconds) <span>$speed</span></label>\n";
		$html .= "<input type='hidden' id='speed' name='option[speed]' value='$speed' />\n";
		$html .= "<div id='slider' style='margin: 10px 0;'></div>\n\n";

		$OBJ->template->onready[] = "$('#slider').slider({ value: $speed, step: 100, min: 0, max: 1000,  
stop: function(event, ui) { $('#speed').val(ui.value); },
slide: function(event, ui) { $('label#speed_value span').html(ui.value) }
});";

		// we need to query for sections and output accordingly
		$sections = $OBJ->db->fetchArray("SELECT secid,sec_desc FROM ".PX."sections ORDER BY sec_ord");
		
		if (is_array($sections))
		{
			$html .= "<div style='margin: 18px 0;'><label>Exclude from expanding</label><br /><br />\n";

			foreach ($sections as $key => $section)
			{
				$checked = (in_array("section_$section[secid]", $exclude)) ? " checked='checked'" : '';
				$html .= "<input type='checkbox' name='option[exclude][$key]' value='section_$section[secid]' $checked/> $section[sec_desc]<br />";
			}
		
			$html .= "</div>";
		}
	
		return $html;
	}
	
	
	function selected($var='', $check='')
	{
		return ($var == $check) ? " selected='selected'" : '';
	}
	
	
	function setup()
	{
		$OBJ =& get_instance();
		
		// indexpand doesn't work for mobile
		if ($OBJ->vars->default['isMobile'] == true) { return; }
		
		$single = (isset($OBJ->hook->options['indexpand']['single'])) ? 
			$OBJ->hook->options['indexpand']['single'] : 'true';
		$mouse = (isset($OBJ->hook->options['indexpand']['mouse'])) ? 
			$OBJ->hook->options['indexpand']['mouse'] : 'click';
		$speed = (isset($OBJ->hook->options['indexpand']['speed'])) ? 
			$OBJ->hook->options['indexpand']['speed'] : 100;
			
		$options[] = ($single == 'true') ? '' : "single:false";
		$options[] = ($mouse == 'click') ? '' : "mouse:'over'";
		$options[] = ($speed == 100) ? '' : "speed:$speed";
		
		if (isset($OBJ->hook->options['indexpand']['exclude']))
		{
			if (is_array($OBJ->hook->options['indexpand']['exclude']))
			{
				if (isset($OBJ->hook->options['indexpand']['exclude']))
				{
					$options[] = "exclude:['" . implode("','", $OBJ->hook->options['indexpand']['exclude']) . "']";
				} 
			}
		}
		
		foreach ($options as $option) { if ($option != '') { $tmp_options[] = $option; } }
		
		// delete empty elements
		$options = (!empty($tmp_options)) ? '{' . implode(',', $tmp_options) . '}' : '';
		
		// do we need to do a file check for this?
		$OBJ->page->add_jquery('jquery.indexpand.js', 25);	
		$OBJ->page->add_jquery_onready("$('ul.section').indexpand($options);", 8);
	}
}