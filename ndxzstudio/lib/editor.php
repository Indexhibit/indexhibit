<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Editor class
* 
* @version 1.0
* @author Vaska 
*/
class Editor
{
	public $html;
	public $css;
	public $content;
	public $content_id;
	public $process;
	public $advanced = false;
	public $canvas = 0;
	
	/**
	* Editor
	*
	* @param void
	* @return array
	*/
	public function __construct()
	{
		
	}
	
	public function output()
	{
		$this->editor_button();
		$this->top_space();
		$this->the_editor();
		return $this->html;
	}
	
	public function admin_editor()
	{
		$OBJ =& get_instance();
		
		$canvas = ($this->canvas != 0) ? 'edCanvas' . $this->canvas : 'edCanvas';
		
		$button['h1'] = array('standard', $canvas, 'h1.gif', 'h1', 'h1');
		$button['h2'] = array('standard', $canvas, 'h2.gif', 'h2', 'h2');
		$button['bold'] = array('standard', $canvas, 'bold.gif', 'bold', 'strong');
		$button['italic'] = array('standard', $canvas, 'italic.gif', 'italic', 'em');
		$button['underline'] = array('standard', $canvas, 'under.gif', 'underline', 'u');
		//$button['l1'] = array('spacer');
		//$button['link'] = array('standard_pop', $canvas, 'link.gif', '?a=system&amp;q=links', 'links manager', "rel=\"shadowbox;player=iframe;height=325;width=350\"");
		//$button['files'] = array('standard_pop', $canvas, 'files.gif', '?a=system&amp;q=ext&amp;x=img', 'files manager', "rel=\"shadowbox;player=iframe;height=325;width=350\"");

		$this->html .= "<div style='margin-top: 3px; margin-left: 0;'>\n";
		$this->html .= $this->output_editor( $button );
		$this->html .= "</div>\n";
		
		$this->the_editor();
		return $this->html;
	}
	
	public function mini_editor()
	{
		$OBJ =& get_instance();
		
		$canvas = 'edCanvas';

		$button['bold'] = array('standard', $canvas, 'bold.gif', 'bold', 'strong');
		$button['italic'] = array('standard', $canvas, 'italic.gif', 'italic', 'em');
		$button['underline'] = array('standard', $canvas, 'under.gif', 'underline', 'u');
		//$button['l1'] = array('spacer');
		//$button['link'] = array('standard_pop', $canvas, 'link.gif', '?a=system&amp;q=links', 'links manager', "rel=\"shadowbox;player=iframe;height=325;width=350\"");

		$this->html .= "<div style='margin-top: 3px; margin-left: 0;'>\n";
		$this->html .= $this->output_editor( $button );
		$this->html .= "</div>\n";
		
		$this->the_editor();
		return $this->html;
	}
	
	public function editor_button()
	{
		$OBJ =& get_instance();
		
		$this->html .= "<div class='col' style='margin-top: 18px; margin-left: 0; width: 48%;'>\n";
		
		// text processing
		//$this->html .= ($OBJ->vars->exhibit['process'] == 1) ? href("<img src='asset/img/process-on.gif' alt='1' />",'#',"id='processing' title='text processing' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" onClick=\"processing(); return false;\" width='20'") : href("<img src='asset/img/process-off.gif' alt='0' />",'#',"id='processing' title='text processing' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" onClick=\"processing(); return false;\" width='20'");
		
		$this->html .= $this->editor_buttons();
		
		if (isset($OBJ->hook->action_table['system_plugin_insert']))
		{
			// experimental - code insert for plugins
			//$this->html .= href("<img src='asset/img/plugin.gif' />", "?a=system&q=pluginsert", "id='pluginsert' title='insert plugin' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\"  width='20' rel=\"facebox;width=800;height=500;modal=true\"");
		}
		
		$this->html .= "</div>\n";
	}
	
	public function top_space()
	{
		$OBJ =& get_instance();

		$this->html .= "<div class='col txt-right' style='margin-top: 18px; float: right; width: 48%;'>\n";
		
		$this->html .= ($OBJ->vars->exhibit['process'] == 1) ? href("<img src='asset/img/process-on.gif' alt='1' />",'#',"id='processing' title='text processing' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" onClick=\"processing(); return false;\" width='20'") . " <img src=\"asset/img/line_spcr.gif\" border=\"0\"> " : href("<img src='asset/img/process-off.gif' alt='0' />",'#',"id='processing' title='text processing' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" onClick=\"processing(); return false;\" width='20'") . " <img src=\"asset/img/line_spcr.gif\" border=\"0\"> ";
		
		$this->html .= "&nbsp;" . $this->base_functions(); // save, delete, etc
		$this->html .= "</div>\n";
		
		$this->html .= "<div class='cl'><!-- --></div>\n";
	}
	
	public function create_standard_button($arr)
	{
		$OBJ =& get_instance();

		return href("<img src='asset/img/" . $arr[1] . "' alt'[]' />",'#',"title='".$OBJ->lang->word($arr[2])."' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" onClick=\"standardTag(" . $arr[0] . ", '" . $arr[3] . "'); return false;\" width='20'");
	}
	
	public function create_custom_button($arr)
	{
		$OBJ =& get_instance();
		
		$OBJ->template->ex_js[] = "var customButton = new Array();";
		
		// we need to register an array for this
		// split the before and after tag here
		$tmp = explode('><', $arr[3]);
		
		// this is a bit hackish - sheesh
		$OBJ->template->ex_js[] = "customButton['" . $arr[2] . "'] = new Array(\"" . $tmp[0] . ">\", \"<" . $tmp[1] . "\");";

		return href("<img src='asset/img/" . $arr[1] . "' alt'[]' />",'#',"title='".$OBJ->lang->word($arr[2])."' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" onClick=\"customTag(" . $arr[0] . ", '" . $arr[2] . "'); return false;\" width='20'");
	}
	
	public function create_standard_pop_button($arr)
	{
		$OBJ =& get_instance();
		
		return href("<img src='asset/img/" . $arr[1] . "' alt'[]' />", $arr[2] ,"title='".$OBJ->lang->word($arr[3])."' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\"  width='20' $arr[4]");
	}
	
	public function default_editor()
	{
		$button['bold'] = array('standard', 'edCanvas', 'bold.gif', 'bold', 'strong');
		$button['italic'] = array('standard', 'edCanvas', 'italic.gif', 'italic', 'em');
		$button['underline'] = array('standard', 'edCanvas', 'under.gif', 'underline', 'u');
		$button['blockquote'] = array('edCanvas', 'quote.gif', 'blockquote', 'blockquote');
		$button['small'] = array('standard', 'edCanvas', 'small.gif', 'small', 'small');
		$button['highlight'] = array('custom', 'edCanvas', 'highlight.gif', 'highlight', '<span class=\'highlight\'></span>');
		$button['l2'] = array('spacer');
		$button['link'] = array('standard_pop', 'edCanvas', 'link.gif', '?a=system&amp;q=links', 'links manager', "rel=\"facebox;width=400;height=400\"");
		
		//$button['l1'] = array('spacer');
		//$button['code'] = array('standard', 'edCanvas', 'code.gif', 'code', 'code');
		//$button['h2'] = array('standard', 'edCanvas', 'h2.gif', 'h2', 'h2');
		//$button['h3'] = array('standard', 'edCanvas', 'h3.gif', 'h3', 'h3');
		//$button['h4'] = array('standard', 'edCanvas', 'h4.gif', 'h4', 'h4');
		
		//$button['files'] = array('standard_pop', 'edCanvas', 'files.gif', '?a=system&amp;q=utilities&amp;x=img', 'files manager', "rel=\"shadowbox;player=iframe;height=325;width=350\"");
		
		return $button;
	}
	
	public function output_editor($button)
	{
		$s = '';

		foreach ($button as $key => $b)
		{
			// need the cases here
			switch ($b[0]) 
			{
			    case 'standard':
					array_shift($b);
			        $s .= $this->create_standard_button($b);
			        break;
			    case 'standard_pop':
					array_shift($b);
					$s .= $this->create_standard_pop_button($b);
					break;
				case 'custom':
					array_shift($b);
					$s .= $this->create_custom_button($b);
					break;
				case 'custom_pop':
					array_shift($b);
					$s .= $this->create_custom_pop_button($b);
					break;
				case 'spacer':
					$s .= $this->create_spacer();
					break;
			}
		}
		
		return $s;
	}
	
	public function create_spacer()
	{
		return "<img src=\"asset/img/line_spcr.gif\" border=\"0\">\n";
	}
	
	public function editor_buttons()
	{
		return $this->output_editor( $this->default_editor() );
	}
	
	public function base_functions()
	{
		$OBJ =& get_instance();

		global $go, $rs;
		
		$s = '';
		
		if ($go['id'] != 1)
		{	
			if ($rs['section_top'] != 1)
			{
				//$s .= "<input name='delete' type='image' src='asset/img/delete.gif' title='".$OBJ->lang->word('delete')."' onClick=\"javascript:return confirm('".$OBJ->lang->word('are you sure')."');return false;\" class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' />\n";

				//$s .= "<img src=\"asset/img/line_spcr.gif\" border=\"0\">\n";
			}
		}

		//$s .= "<input name='preview' type='image' src='asset/img/f-prev.gif' title='Preview (without saving)' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' onclick=\"previewText($go[id]); return false;\" />\n";

		// save things
		$s .= "<input name='save' type='image' src='asset/img/save.gif' title='".$OBJ->lang->word('save')."'  class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' onclick=\"updateText($go[id]); return false;\" />\n";
		
		return $s;
	}
	
	public function the_editor()
	{
		$OBJ =& get_instance();

		$OBJ->template->add_js('alexking.quicktags.js');
		$OBJ->template->add_js('modEdit.js');
		//$OBJ->template->ex_js[] = "var edCanvas = document.getElementById('jxcontent');";
		
		$canvas = ($this->canvas != 0) ? 'edCanvas' . $this->canvas : 'edCanvas';

		$this->html .= "<textarea name='" . $this->content_id . "' class='" . $this->content_id . "' id='jx" . $this->content_id . "' $this->css>" . $this->stripForForm($this->content, $this->process) . "</textarea>\n";
		$this->html .= "<script type='text/javascript'>var $canvas = document.getElementById('jx" . $this->content_id . "');</script>\n";
	}
	
	
	public function stripForForm($text='', $process='')
	{
		// we need helper output with this
		if (($process == 0) || ($process == '')) 
		{
			// have we checked this yet
			if (function_exists('mb_decode_numericentity'))
			{
				return mb_decode_numericentity($text, UTF8EntConvert('1'), 'utf-8');
			}
			else
			{
				$text = htmlspecialchars($text);
				return str_replace(array("&gt;","&lt;"), array(">","<"), $text);
			}
		}

		if ($text) 
		{
			$out = str_replace("<p>", "", $text);
			$out = str_replace(array("<br />","<br>"),array("",""), $out);
			$out = str_replace("</p>", "", $out);

			if (function_exists('mb_decode_numericentity'))
			{
				$out = mb_decode_numericentity($out, UTF8EntConvert('1'), 'utf-8');
			}
			else
			{
				$out = htmlspecialchars($out);
				$out = str_replace(array("&gt;","&lt;"), array(">","<"), $out);
			}

			return $out;
		} 
		else 
		{
			return '';
		}
	}
	
	
	// text processing functions
	
	// this file contains functions for text processing and

	// ---------------------------------------------------
	//  All the best parts of this are remnants from TextPattern
	//  Dean Allen <http://www.textpattern.com/
	//  & <http://www.textdrive.com/
	// ---------------------------------------------------

	public function textProcess($text='', $override)
	{
		if ($text != '')
		{
			return ($override == 1) ? $this->block($text) : $text;
		}	
		else
		{
			return;
		}
	}


	public function cleanWhiteSpace($text)
	{
		$out = str_replace(array("\r\n", "\t"), array("\n", ''), $text);
		$out = preg_replace("/(\n){2,}/", "\n\n", $out);
		$out = preg_replace("/\n *\n/", "\n\n", $out);
		return preg_replace('/"$/', "\" ", $out);
	}


	public function cleaningOutput($text)
	{
		$out = str_replace("<br>", "<br />", $text);
		$out = str_replace("<br />", "\n", $out);

		if (preg_match("/<(code.*|pre.*|table.*|ol.*|ul.*)>/i",$out)) 
		{
			return preg_replace("/(\n)/", "\n", $out);
		} 
		else 
		{
			return preg_replace("/(\n)/", "<br />\n", $out);
		}
	}


	public function block($text)
	{
		// REVIEW THIS APPROACH
		$text = preg_replace("/&(?![#a-z0-9]+;)/i", "x%x%", $text);
		$text = str_replace("x%x%", "&#38;", $text);
		$text = $this->cleanWhiteSpace($text);

		$that = array();
		$that = explode("\n\n",$text);

		foreach ($that as $key => $value) 
		{
			$value = $this->cleaningOutput($value);
			$value = $this->glyphs($value);

			// to make it work with 'plug:'
			//if (preg_match("/<(h[12345]|code.*|h[12345].*|p.*|plug.*|style.*|div.*|\/div)>/i", $value)) 
			if (preg_match("/<(h[12345]|code.*|h[12345].*|blockquote.*|plug.*|object.*|p.*|style.*|!--.*|div.*|\/div|\/p)>/i", $value)) 
			{
				$s[] = $value;
			} 
			else 
			{			
				$s[] = '<p>'.$value.'</p>';
			}
		}

		$out = implode("\n\n",$s);

		return $out;
	}


	public function glyphs($text) 
	{
		$text = preg_replace('/"\z/', "\" ", $text);
		$codepre = false;

		if (!preg_match("/<.*>/", $text)) 
		{
			return $text;
		} 
		else 
		{
			$text = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

			foreach($text as $line) 
			{
				$offtags = ('code|pre');

				if (preg_match('/<(' . $offtags . ')>/i', $line)) $codepre = true;
				if (preg_match('/<\/(' . $offtags . ')>/i', $line)) $codepre = false;

				if ($codepre == true) 
				{
					$line = htmlspecialchars($line, ENT_NOQUOTES, "UTF-8");
					$line = preg_replace('/&lt;(\/?' . $offtags . ')&gt;/', "<$1>", $line);
				}

				$glyph_out[] = $line;
			}

			return join('', $glyph_out);
		}
	}


	// text_reduction($go['content'], "&nbsp;", '<p><br>' 50);
	public function text_reduction($string='', $repl, $allow='', $limit=40)
	{
	    if (($string == '') || ($string == null)) return;

	    $limit = @strpos(strip_tags($string, $allow), " ", $limit);

	    if ($limit)
	    {
	        return substr_replace(strip_tags($string, $allow), $repl, $limit);
	    }
	    else
	    {
	        return $string;
	    }
	}
	
	// end text processing functions
}