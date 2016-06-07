<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Options class
* 
* @version 1.0
* @author Vaska 
*/
class Options
{
	public $custom_output = array();

	public function __construct()
	{

	}
	
	public function format()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (isset($OBJ->vars->format_params['format']))
		{
			$bod = "<label>" . $OBJ->lang->word('exhibition format') . "</label>\n";
			$bod .= getPresent(DIRNAME . '/ndxzsite/plugin/', $OBJ->vars->exhibit['format']);
			$OBJ->template->onready[] = "$('#ajx-present').change( function() { updatePresent(); } );";
		}
		
		return $bod;
	}
	
	public function source()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		// the switch is here...
		// front exhibit/page of site
		if ($OBJ->vars->exhibit['home'] == 1)
		{
			// if it's not a section top we can't allow section
			if ($OBJ->vars->exhibit['section_top'] != 1)
			{
				if (isset($OBJ->vars->format_params['source']['section'])) unset($OBJ->vars->format_params['source']['section']);
			}
			
			if (isset($OBJ->vars->format_params['source']['subsection'])) unset($OBJ->vars->format_params['source']['subsection']);
		}
		// section top exhibit/page
		elseif ($OBJ->vars->exhibit['section_top'] == 1)
		{
			if (isset($OBJ->vars->format_params['source']['all'])) unset($OBJ->vars->format_params['source']['all']);
			if (isset($OBJ->vars->format_params['source']['subsection'])) unset($OBJ->vars->format_params['source']['subsection']);
		}
		// subsection exhibit/page
		elseif ($OBJ->vars->exhibit['subdir'] == 1)
		{
			//if (isset($OBJ->vars->format_params['source']['all'])) phpinfo();
			if (isset($OBJ->vars->format_params['source']['all'])) unset($OBJ->vars->format_params['source']['all']);
			if (isset($OBJ->vars->format_params['source']['section'])) unset($OBJ->vars->format_params['source']['section']);
		}
		// standard exhibit/page
		else
		{
			if (isset($OBJ->vars->format_params['source']['all'])) unset($OBJ->vars->format_params['source']['all']);
			if (isset($OBJ->vars->format_params['source']['section'])) unset($OBJ->vars->format_params['source']['section']);
			if (isset($OBJ->vars->format_params['source']['subsection'])) unset($OBJ->vars->format_params['source']['subsection']);
		}
		
		if (isset($OBJ->vars->format_params['source']))
		{
			$bod .= label($OBJ->lang->word('media source'));
			$bod .= $this->displaySwitches($OBJ->vars->exhibit['media_source'], $OBJ->vars->format_params['source']);
			$OBJ->template->onready[] = "$('#ajx-source').change( function() { updateSource(); } );";
		}
		
		return $bod;
	}
	
	public function images()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (isset($OBJ->vars->format_params['images']))
		{
			$bod .= label($OBJ->lang->word('image max')).br();
			$bod .= getImageSizes($OBJ->vars->exhibit['images'], "class='listed' id='ajx-images'");
			$OBJ->template->onready[] = "$('#ajx-images li').tabpost();";
		}
		
		return $bod;
	}
	
	public function thumbs()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (isset($OBJ->vars->format_params['thumbs']))
		{
			$bod .= label($OBJ->lang->word('thumb max') . showHelp($OBJ->lang->word('thumb max'))).br();
			$bod .= getThumbSize($OBJ->vars->exhibit['thumbs'], "class='listed' id='ajx-thumbs'");
			$OBJ->template->onready[] = "$('#ajx-thumbs li').tabpost();";
		}
		
		return $bod;
	}
	
	public function shape()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (isset($OBJ->vars->format_params['shape']))
		{
			$bod .= label($OBJ->lang->word('thumbs shape') . showHelp($OBJ->lang->word('thumbs shape'))).br();
			$bod .= getImageShape($OBJ->vars->exhibit['thumbs_shape'], "class='listed' id='ajx-shape'");
			$OBJ->template->onready[] = "$('#ajx-shape li').tabpost();";
		}
		
		return $bod;
	}
	
	public function titling()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (isset($OBJ->vars->format_params['titling']))
		{
			$bod .= label($OBJ->lang->word('titling')).br();
			$bod .= getOnOff($OBJ->vars->exhibit['titling'], "class='listed' id='ajx-titling'");
			$OBJ->template->onready[] = "$('#ajx-titling li').tabpost();";
		}
		
		return $bod;
	}
	
	public function placement()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (isset($OBJ->vars->format_params['placement']))
		{
			$bod .= label($OBJ->lang->word('files placement')).br();
			$bod .= getPlacement($OBJ->vars->exhibit['placement'], "class='listed' id='ajx-place'");
			$OBJ->template->onready[] = "$('#ajx-place li').tabpost();";
		}
		
		return $bod;
	}
	
	public function operand()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (!empty($OBJ->vars->format_params['operand']))
		{
			$param = (!empty($OBJ->vars->format_params['operand'])) ? $OBJ->vars->format_params['operand'] : array();
			
			$bod .= label($OBJ->lang->word('onclick') . showHelp($OBJ->lang->word('onclick'))).br();
			$bod .= getOperand($OBJ->vars->exhibit['operand'], "class='listed' id='ajx-operand'", $OBJ->vars->format_params['operand']);
			$OBJ->template->onready[] = "$('#ajx-operand li').tabpost();";
		}
		
		return $bod;
	}
	
	public function counter()
	{
		$OBJ =& get_instance();
		
		$bod = '';
		
		if (isset($OBJ->vars->format_params['break']))
		{
			$bod .= label($OBJ->lang->word('counter')).br();
			$bod .= getBreak($OBJ->vars->exhibit['break']);
		}
		
		return $bod;
	}

	
	public function custom_options()
	{
		$OBJ =& get_instance();
		
		if (isset($OBJ->vars->format_params['custom']))
		{
			// do we load the format here?
			if (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->access->settings['obj_theme'] . '/format.' . $OBJ->vars->exhibit['format'] . '.php'))
			{
				require_once(DIRNAME . '/ndxzsite/' . $OBJ->access->settings['obj_theme'] . '/format.' . $OBJ->vars->exhibit['format'] . '.php');
			}
			else
			{
				require_once(DIRNAME . '/ndxzsite/plugin/format.' . $OBJ->vars->exhibit['format'] . '.php');
			}
			
			$EXH = new Exhibit;
			
			// we should check for defaults
			$OBJ->vars->exhibit['custom_options_flag'] = (method_exists($EXH, 'default_settings')) ? true : false;

			foreach ($OBJ->vars->format_params['custom'] as $do)
			{
				// name the method
				$method = 'custom_option_' . $do;

				// execute the custom options
				if (method_exists($EXH, $method)) $EXH->$method();
			}
		}
	}
	
	public function custom_output($column)
	{
		$output = '';

		if (isset($this->custom_output[$column]))
		{
			foreach ($this->custom_output[$column] as $do)
			{
				$output .= $do;
			}
			
			return $output;
		}
	}
	
	public function switchInterface()
	{
		// call custom first
		$this->custom_options();

		$bod = "<div style='width: 205px; float: left; padding: 0 5px;'>\n";
		
		$bod .= $this->source();
		$bod .= $this->format();
		$bod .= $this->custom_output(1);
		
		$bod .= "</div>\n";
		
		$bod .= "<div style='width: 205px; float: left; padding: 0 5px;' id='img-sizes'>\n";

		$bod .= $this->images();
		$bod .= $this->thumbs();
		$bod .= $this->shape();
		$bod .= $this->custom_output(2);
		
		$bod .= "</div>\n";
		
		$bod .= "<div style='width: 205px; float: left; padding: 0 5px;'>\n";
		
		$bod .= $this->titling();
		$bod .= $this->placement();
		$bod .= $this->operand();
		$bod .= $this->counter();
		$bod .= $this->custom_output(3);
		
		$bod .= "</div>\n";

		$bod .= "<div class='cl'><!-- --></div>\n";
		
		return $bod;
	}
	
	
	public function editOptions()
	{
		// call custom first
		$this->custom_options();

		$bod = $this->images();
		$bod .= $this->thumbs();
		$bod .= $this->shape();
		$bod .= $this->titling();
		$bod .= $this->placement();
		$bod .= $this->operand();
		$bod .= $this->counter();
		$bod .= $this->custom_output(1);
		$bod .= $this->custom_output(2);
		$bod .= $this->custom_output(3);
		
		return $bod;
	}
	
	
	public function getParameters($format='')
	{
		$OBJ =& get_instance();
		
		// we need to check for format in theme first
		$file = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->access->settings['obj_theme'] . '/format.' . $format . '.php')) ?
			DIRNAME . '/ndxzsite/' . $OBJ->access->settings['obj_theme'] . '/format.' . $format . '.php' : 
			DIRNAME . '/ndxzsite/plugin/format.' . $format . '.php';

		$fp = fopen($file, 'r');
		$info = fread($fp, 8192);
		fclose($fp);
		
		$arr = array();
		preg_match ( '|Params:(.*)$|mi', $info, $params );
		preg_match ( '|Operands:(.*)$|mi', $info, $operands );
		preg_match ( '|Source:(.*)$|mi', $info, $source );
		
		if (isset($operands[1]))
		{
			$tmp = explode(',', $operands[1]);
			
			foreach ($tmp as $go)
			{
				$arr['operand'][trim($go)] = trim($go);
			}
		}
		
		if (isset($source[1]))
		{
			$tmp = explode(',', $source[1]);
			
			foreach ($tmp as $go)
			{
				$arr['source'][trim($go)] = trim($go);
			}
		}
		
		if (isset($params[1]))
		{
			$tmp = explode(',', $params[1]);
			$custom = 0;
			
			foreach ($tmp as $go)
			{
				// if it's a custom option
				if (preg_match("/^custom/i", $go))
				{
					// right a better regex
					$name = str_replace("custom('", '', $go);
					$name = str_replace("')", '', $name);

					// this is basically a call to a hook
					// or perhaps the exhibition format directly...
					$arr['custom'][$custom] = $name;
					$custom++;
				}
				else
				{
					$arr[trim($go)] = true;
				}
			}

			return $arr;
		}
		else
		{
			// if it's empty show all options
			return array('format' => true, 'images' => true, 'thumbs' => true, 'shape' => true, 'placement' => true, 'break' => true, 'operands' => false, 'source' => false, 'titling' => false);
		}
	}
	
	
	public function displaySwitches($switcher=0, $source='')
	{
		$OBJ =& get_instance();
		global $default;
		
		//$source = (empty($source)) ? array() : $source;
		
		// only needed if we have more than one
		//if (count($default['filesource']) == 1) return;

		load_helper('html');
		
		$html = ''; $test = 0; $flag = false;

		foreach ($default['filesource'] as $key => $switch)
		{	
			if (in_array($switch, $source))
			{
				if ($test == $switcher) $flag = true;

				$html .= option($key, $switch, $key, $switcher);
			}
			
			// here we set the default
			if ($flag == false)
			{
				// switcher
			}
		}
		
		//if ($html == '') return; // do we do a reset here to 'exhibit'?
		
		return select('media_source', "id='ajx-source' style='width: 150px;'", $html);
	}
}