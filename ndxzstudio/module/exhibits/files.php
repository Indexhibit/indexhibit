<?php if (!defined('SITE')) exit('No direct script access allowed');


function getSection($section='', $name, $attr='')
{
	global $default;
	
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT secid,section,sec_desc,sec_proj FROM ".PX."sections ORDER by sec_ord ASC");
	
	$subs = $OBJ->db->fetchArray("SELECT * FROM ".PX."subsections ORDER by sub_order ASC");
	
	// prepare our subsections for use too
	if ($subs)
	{
		foreach ($subs as $sub)
		{
			$subsection[$sub['sub_sec_id']][] = $sub;
		}
	}

	foreach ($rs as $a) 
	{
		//($section == $a['secid']) ? $sl = "selected ": $sl = "";
		$s .= option($a['secid'], $a['sec_desc'], $a['sec_proj'], 1);
		
		if (isset($subsection[$a['secid']]))
		{
			foreach ($subsection[$a['secid']] as $sub)
			{
				$s .= option($a['secid'] . '.' . $sub['sub_id'], '- ' . $sub['sub_title'], $a['sec_proj'], 1);
			}
		}
	}

	return select($name, attr($attr), $s);
}


function getSections()
{
	global $default;
	
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT secid, sec_desc, sec_proj, sec_ord 
		FROM ".PX."sections ORDER by sec_ord ASC");

	if (!$rs)
	{
		return p('None');
	}
	
	// we need the highest number section
	// sorted by order, so we can array pop
	$quantities = $rs;
	$quantity = array_pop($quantities);

	foreach ($rs as $a) 
	{
		$projects = ($a['sec_proj'] == 1) ? '&nbsp;&nbsp;XX' : '';
		
		$s .= div(href($OBJ->lang->word('edit'), "?a=exhibits&q=section&id=$a[secid]") . '&nbsp;&nbsp;' . $a['sec_desc'] . $projects, "class='section'");
	}
	
	$s .= p(href($OBJ->lang->word('create new section'), '#', "onclick=\"toggle('add-sec'); return false;\""));
	
	$input = label($OBJ->lang->word('section name') . ' ' . span($OBJ->lang->word('required'), "class='small-txt'")) . input('sec_desc', 'text', "maxlength='25'", NULL);
	$input .= label($OBJ->lang->word('folder name') . ' ' . span($OBJ->lang->word('required'), "class='small-txt'")) . input('section', 'text', "maxlength='15'", NULL);
	$input .= input('hsec_ord', 'hidden', NULL, $quantity['sec_ord']);
	$input .= div(input('add_sec', 'submit', NULL, $OBJ->lang->word('add section')), "style='text-align: right;'");
	
	$s .= div($input, "style='display:none; margin-top: 9px;' id='add-sec'");

	return div($s, "class='sections'");
}


function getSectionOrd($section='', $name, $attr='')
{
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT sec_ord,sec_desc FROM ".PX."sections ORDER BY sec_ord ASC");

	foreach ($rs as $a) 
	{
		$s .= option($a['sec_ord'], $a['sec_ord'], $section, $a['sec_ord']);
	}

	return select($name, attr($attr), $s);
}


function getProcessing($state, $name, $attr)
{
	$OBJ =& get_instance();
	
	if ($state == '') $state = 0;
	
	$s = option(1, $OBJ->lang->word('on'), $state, 1);
	$s .= option(0, $OBJ->lang->word('off'), $state, 0);
	
	return select($name, attr($attr), $s);	
}


function getGeneric($state, $name, $attr)
{
	$OBJ =& get_instance();
	
	if ($state == '') $state = 0;
	
	$s = option(1, $OBJ->lang->word('on'), $state, 1);
	$s .= option(0, $OBJ->lang->word('off'), $state, 0);
	
	return select($name, attr($attr), $s);	
}


function getOrganize($state, $name, $attr)
{
	$OBJ =& get_instance();
	
	if ($state == '') $state = 1;
	
	$s = option(1, $OBJ->lang->word('chronological'), $state, 1);
	$s .= option(2, $OBJ->lang->word('sectional'), $state, 2);
	
	return select($name, attr($attr), $s);	
}


function getThemes($path, $default)
{
	// let's get the folders and info...
	$modules = array();

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($module = readdir($fp)) !== false) 
			{
				if ((!preg_match("/^_/i",$module)) && (!preg_match("/^CVS$/i",$module)) && (!preg_match("/.php$/i",$module)) && (!preg_match("/.html$/i",$module)) && (!preg_match("/.DS_Store/i",$module)) && (!preg_match("/\./i",$module)) && (!preg_match("/plugin/i", $module)) && (!preg_match("/css/i", $module)) && (!preg_match("/js/i", $module)) && (!preg_match("/img/i", $module))  && (!preg_match("/mobile/i", $module)))
				{      
					$modules[] = $module;
				}
			} 
		}
		closedir($fp);
	}
 
	sort($modules);
	
	$s = '';
	
	foreach ($modules as $module)
	{
		$s .= option($module, ucwords($module), $module, $default);
	}
	
	return select('obj_theme', NULL, $s);
}


function get_templates($template='', $name, $attr='')
{
	$OBJ =& get_instance();

	$s = '';

	$rs = get_file_kind(DIRNAME . '/ndxzsite/' . $OBJ->access->settings['obj_theme'] . '/', 'php');

	foreach ($rs as $a) 
	{
		// need to filter out formats and plugins here
		if (!preg_match("/^(format.|plugin.)/i", $a, $match))
		{
			$s .= option($a, $a, $template, $a);
		}
	}

	return select($name, $attr, $s);
}


function get_file_kind($path, $kind)
{
	// let's get the folders and info...
	$files = array();
	$kind = $kind . '$';

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($file = readdir($fp)) !== false) 
			{
				if (preg_match("/$kind/i", $file)) $files[] = $file;
			} 
		}

		closedir($fp);
	}
 	
	return $files;
}


function getPresent($path, $default)
{
	$OBJ =& get_instance();
	
	$modules = array();

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($module = readdir($fp)) !== false) 
			{
				if (preg_match("/^format/i", $module))
				{
					// can we run a quick check on the 'object' of the format?
					$object_check = $OBJ->hook->get_format_header_single(DIRNAME . '/ndxzsite/plugin/', $module);
					
					if (isset($object_check['objects']))
					{
						// need to accomodate collections
						if (preg_match('/^collect/', $OBJ->vars->exhibit['object'], $match))
						{
							$object = str_replace('collect_', '', $OBJ->vars->exhibit['object']);
							$tmp_object = explode('_', $object);
							$object = $tmp_object[0];
						}
						else
						{
							$object = $OBJ->vars->exhibit['object'];
						}
						
						// if it's '' it's good for any condition
						if (($object_check['objects'] == $object) || 
							($object_check['objects'] == ''))
						{
							$modules[] = $module;
						}
					}
				}
			} 
		}
		closedir($fp);
	}
 
	sort($modules);
		
	$s = '';
	
	foreach ($modules as $module)
	{
		if ($module != "format.mobile.php")
		{
			$search = array('format.','.php');
			$replace = array('','');
			$module = str_replace($search, $replace, $module);
			$name = str_replace('_', ' ', $module);

			$s .= option($module, ucwords($name), $module, $default);
		}
	}
	
	return select('obj_present', "id='ajx-present' style='width: 150px;'", $s);
}


function createFileBox($num)
{
	$OBJ =& get_instance();
	
	$s = label($OBJ->lang->word('image title') . span(' ' . $OBJ->lang->word('optional')));
	
	for ($i = 0; $i <= $num; $i++)
	{
		($i > 0) ? $style = " style='display:none'" : $style = '';
		
		$s .= div(input("media_title[$i]", 'text', NULL, NULL).'&nbsp;'.
			input("filename[]", 'file', NULL, NULL),
			"class='attachFiles' id='fileInput$i'$style");
	}	
	
	$s .= p(href($OBJ->lang->word('attach more files'), 'javascript:AddFileInput()'),
			"class='attachMore' id='attachMoreLink'");

	return $s;
}


function getExhibitImages($id)
{
	$OBJ =& get_instance();
	global $go, $default, $medias;
	
	$body = "<ul id='boxes'>\n";
	
	// the images
	$imgs = $OBJ->db->fetchArray("SELECT * 
		FROM ".PX."media 
		WHERE media_ref_id = '$id'
		AND media_obj_type = 'exhibits'
		ORDER BY media_order ASC, media_id ASC");
		
	// set the width of the popup...deals with tags
	$site_vars = unserialize($OBJ->access->settings['site_vars']);
	$width = ($site_vars['tags'] == 1) ? 800 : 450;
	
	if ($imgs)
	{
		foreach ($imgs as $img)
		{	
			$path = GIMGS; $poster = false;

			if (!in_array($img['media_mime'], $default['images']))
			{
				if ($img['media_thumb'] == '')
				{
					$thumb = 'asset/img/thumb-default.gif';
				}
				else
				{
					$thumb = BASEURL . $path . '/sys-' . $img['media_thumb'];
					$poster = true;
				}
			}
			else
			{
				//$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_file'];
				if ($img['media_thumb'] == '')
				{
					$thumb = BASEURL . $path . '/sys-' . $img['media_file'];
				}
				else
				{
					$thumb = BASEURL . $path . '/sys-' . $img['media_thumb'];
					$poster = true;
				}
			}
			
			// need to make this a class
			$active = ($img['media_hide'] == 1) ? " style='border: 1px solid #f00;'" : " style='border: 1px solid #fff;'";
			$poster = ($poster == true) ? "<div style='background: #fff; position: absolute; z-index: 1; bottom: 1px; right: 0px; height: 9px; border: 1px solid #fff; color: #000; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;'>P</div>" : '';
			
			$add = (!in_array($img['media_mime'], $medias)) ? "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster" : "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster";
			
			$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span class='drag-img'><img src='$thumb' title='" . strip_tags($img['media_title']) . "'{$active} /></span>$add<br /><a href='#' onclick=\"deleteImage($img[media_id], '$img[media_file]'); return false;\" style='color: #999;'><img src='asset/img/img-delete.gif' title='".$OBJ->lang->word('delete')."' style='width: 11px; height: 11px;' /></a> <a href='?a=system&amp;q=img&amp;id=$img[media_id]' rel=\"facebox;height=450;width=810\" style='color: #999;'><img src='asset/img/img-edit.gif' title='".$OBJ->lang->word('edit')."' style='width: 11px; height: 11px;' /></a></li>\n\n";
		}
	}
	else
	{
		$body .= "<li>".$OBJ->lang->word('no images')."</li>\n";
	}
	
	$body .= "</ul>\n";
	
	$body .= "<div class='cl'><!-- --></div>\n";
	
	return $body;
}


function getOnOff($input='', $attr='')
{
	$OBJ =& get_instance();
	$onoff = array('on' => 1, 'off' => 0);
	
	$li = '';
	$input = ($input == '') ? 'off' : $input;
	
	foreach($onoff as $key => $val)
	{
		$active = ($input == $val) ? "class='active'" : '';
		$extra = ($val == 0) ? "id='off'" : '';
		$li .= li($OBJ->lang->word($key), "$active title='$val' $extra");
	}
	
	return ul($li, $attr);
}


function getPlacement($input='', $attr='')
{
	$OBJ =& get_instance();
	$onoff = array('before' => 1, 'after' => 0);
	
	$li = '';
	$input = ($input == '') ? 'after' : $input;
	
	foreach($onoff as $key => $val)
	{
		$active = ($input == $val) ? "class='active'" : '';
		$extra = ($val == 0) ? "id='after'" : '';
		$li .= li($OBJ->lang->word($key), "$active title='$val' $extra");
	}
	
	return ul($li, $attr);
}


function getPermalinked($input='', $attr='')
{
	$OBJ =& get_instance();
	$onoff = array('on' => 1, 'off' => 0);
	
	$li = '';
	$input = ($input == '') ? 'off' : $input;
	
	foreach($onoff as $key => $val)
	{
		$active = ($input == $val) ? "class='active'" : '';
		$extra = ($val == 0) ? "id='off'" : '';
		$li .= li($OBJ->lang->word($key), "$active title='$val' $extra");
	}
	
	return ul($li, $attr);
}


function getThumbSize($input='', $attr='')
{
	$OBJ =& get_instance();
	global $default;
	
	$li = '';
	$input = ($input == '') ? 100 : $input;
	
	foreach($default['thumbsize'] as $key => $size)
	{
		$active = ($input == $size) ? "class='active'" : '';
		$li .= li($OBJ->lang->word($key) . 'px', "$active title='$size'");
	}
	
	return ul($li, $attr);
}

function getImageSizes($input='', $attr='')
{
	$OBJ =& get_instance();
	global $default;
	
	$li = '';
	$input = ($input == '') ? 300 : $input;
	
	foreach($default['imagesize'] as $key => $size)
	{
		$title = $key . 'px';
		
		$active = ($input == $size) ? "class='active'" : '';
		$li .= li($title, "$active title='$size'");
	}
	
	return ul($li, $attr);
}


function getImageShape($input='', $attr='')
{
	$OBJ =& get_instance();
	global $default;
	
	$shapes = array(0 => 'natural', 1 => 'square', 2 => '4x3', 3 => 'cinematic');
	//$shapes = array(0 => 'natural', 1 => 'square', 2 => '4x3', 3 => 'cinematic', 4 => '3x2');
	
	$li = '';
	$input = ($input == '') ? 0 : $input;
	
	foreach($shapes as $key => $size)
	{
		$title = $size;
		
		$active = ($input == $key) ? "class='active'" : '';
		$li .= li($title, "$active title='$key'");
	}
	
	return ul($li, $attr);
}

function getOperand($input='', $attr='', $param=array())
{
	$OBJ =& get_instance();
	global $default;
	
	$li = ''; $i = 0;
	$input = ($input == '') ? 0 : $input;
	
	foreach($param as $key => $operand)
	{
		$title = $operand;

		$active = ($input == $i) ? "class='active'" : '';
		$li .= li($title, "$active title='$i'");

		$i++;
	}
	
	if ($li == '') return;
	
	return ul($li, $attr);
}

// this should be moved to a helper file
// for use with javascripts encodeURIComponent()
function utf8Urldecode($value)
{
	if (is_array($value))
	{
		foreach ($key as $val) { $value[$key] = utf8Urldecode($val); }
    }
	else
	{
		$value = urldecode($value);
    }

    return $value;
}


function deleteImage($file, $ext='')
{
	if ($file)
	{
		$file = ($ext == '') ? $file : $ext .'-' . $file;

		if (file_exists(DIRNAME . GIMGS . '/' . $file))
		{
			@unlink(DIRNAME . GIMGS . '/' . $file);
		}
	}
}


function getBreak($default)
{
	$OBJ =& get_instance();
	$s = '';
	
	$OBJ->template->onready[] = "$('#ajx-break').change( function() { updateBreak(); } );";
	
	for ($i = 0; $i <= 10; $i++)
	{
		$s .= option($i, $i, $i, $default);
	}
	
	return select('break', "id='ajx-break' style='width: 50px;'", $s);
}


function getYear($init)
{
	global $default;
	$s = '';
	
	$this_year = date('Y');
	
	if ($init == '') $init = $this_year; // default is this year
	
	$current = $this_year + 1; // we want to add one year in the future
	
	for ($i = $current; $i >= $default['first_year']; $i--)
	{
		$s .= option($i, $i, $i, $init);
	}
	
	return select('year', "id='ajx-year'", $s);	
	
}


function getColorPicker($bgcolor)
{
	return "<div style='margin: 3px 0 5px 0;' onclick=\"toggle('plugin'); return false;\">
		<span id='plugID' style='background: #$bgcolor; cursor: pointer;'>&nbsp;</span> 
		<span id='colorTest2'>#$bgcolor</span>
	</div>
	
	<div id='plugin' onmousedown=\"HSVslide('drag','plugin',event);\" style='display: none;'>
		<div id='SV' onmousedown=\"HSVslide('SVslide','plugin',event);\" title='Saturation + Value'>
			<div id='SVslide' style='TOP: -4px; LEFT: -4px;'><br /></div>
		</div>
		
		<div id='H' onmousedown=\"HSVslide('Hslide','plugin',event);\" title='Hue'>
			<div id='Hslide' style='TOP: -7px; LEFT: -8px;'><br /></div>
			<div id='Hmodel'></div>
		</div>
	</div>
	
	<input id='colorTest' type='text' name='color' value='ffffff' style='display:none;' />\n\n";
}


?>