<?php if (!defined('SITE')) exit('No direct script access allowed');


function find_files()
{
	$OBJ =& get_instance();
	global $go;
	
	$s = '';

	$rsa = $OBJ->db->fetchArray("SELECT media_file FROM " . PX . "media 
		ORDER by media_file ASC");
		
	if ($rsa)
	{
		foreach ($rsa as $yes) 
		{
			$temp[] = $yes['media_file'];
		}
	}
	else
	{
		$temp = array(0);
	}
	
	$files = get_unknown_files(DIRNAME . '/files/');

	if ($files)
	{
		foreach ($files as $yepp)
		{
			if (!in_array($yepp, $temp)) $out[] = $yepp;
		}
	}
	else
	{
		$out = '';
		$s = 'None found.';
	}

	if ($out == '')
	{
		$s = 'None found.';
	}
	else
	{
		$s .= p("Click filename to add to database.", "style='margin-bottom: 24px;'");
		
		foreach ($out as $a) 
		{
			$mime = array_pop(explode('.', $a));
			
			$url = "?a=$go[a]&q=find&id=$go[id]&x=" . $a;
			
			$s .= p(span(href($a, $url), "class='p-name'"));
		}
	}

	return $s;
}


function get_file_replacement($state, $name, $attr)
{
	$OBJ =& get_instance();
	global $go;
	
	$s = option('', 'No Selection', 1, 0);
	
	// this gives us known or 'selected' files
	// tough logic...we exclude the current id so we get it back later
	$rsa = $OBJ->db->fetchArray("SELECT media_id, media_ref_id, media_file_replace FROM ".PX."media 
		WHERE media_obj_type = ''  
		AND media_file_replace != '' 
		AND media_id != '$go[id]' 
		ORDER by media_file_replace ASC");
		
	$used = array();
		
	if ($rsa)
	{
		foreach ($rsa as $yes) 
		{
			// we want all files...but we only want the active id to get it's file (as it is selected)
			$used[] = ($yes['media_ref_id'] == $go['id'])? null : $yes['media_file_replace'];
		}
	}
	
	$notused = get_unknown_files(DIRNAME . '/files/');

	if ($notused)
	{
		foreach ($notused as $yepp)
		{
			if (!in_array($yepp, $used)) $out[] = $yepp;
		}
	}

	if (is_array($out))
	{
		foreach ($out as $a) 
		{
			$s .= option($a, $a, $state, $a);
		}
	}

	return select($name, $attr, $s);
	
	
	
	//////////////////////////////////////////////////////////////////
	$OBJ =& get_instance();
	
	if ($state == '') $state = 0;
	
	$s = option(1, $OBJ->lang->word('yes'), $state, 1);
	$s .= option(0, $OBJ->lang->word('no'), $state, 0);
	
	return select($name, $attr, $s);	
}


function get_unknown_files($path)
{
	global $default;

	// let's get the folders and info...
	$modules = array();

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{	
			// not images
			$theFiles = array_merge($default['media'], $default['sound'], $default['files'], $default['flash']);
			
			while (($module = readdir($fp)) !== false) 
			{
				// if module extension in array keep it...
				$ext = explode('.', $module);
				$ext = array_pop($ext);
				
				if (in_array($ext, $theFiles))
				{
					$modules[] = $module;
				}
			} 
		}

		closedir($fp);
	}
 
	sort($modules);
	
	return $modules;
}


function getFiles()
{
	$OBJ =& get_instance();
	global $go, $default;
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT * FROM ".PX."media 
		WHERE media_ref_id = '0' 
		AND media_obj_type = '' 
		ORDER by media_uploaded DESC");

	if (!$rs)
	{
		$s = 'No files yet';
	}
	else
	{
		foreach ($rs as $a) 
		{
			// fake 'mime', actually
			$mime = array_pop(explode('.', $a['media_file']));

			$use = span(filesManagerType($mime, $a['media_file'], $a['media_x'], $a['media_y'], $a['media_caption']),  "class='p-action'");
				
			$edit = span(href("<img src='asset/img/files-edit.gif' />",
				"?a=$go[a]&amp;q=editfile&amp;id=$a[media_id]"), "class='p-action'");
			
			$url = BASEURL . '/files/' . $a['media_file'];
			
			if (in_array($mime, array_merge($default['images'], $default['flash'], $default['media'])))
			{
				$nodims = (($a['media_x'] == 0) || ($a['media_y'] == 0)) ? " style='color: #f00;'" : '';
			}
			else
			{
				$nodims = '';
			}
			
			$s .= div($use . $edit . span(href($a['media_file'], $url, "target='show'$nodims"), "class='p-name'"),
				row_color("class='row-color'"));
		}
	}

	return $s;
}


function get_extensions_list($path)
{
	// let's get the folders and info...
	$files = array();

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($file = readdir($fp)) !== false) 
			{
				if ((preg_match("/^plugin/i", $file)))
				{      
					$files[] = $file;
				}
			} 
		}
		closedir($fp);
	}
 
	sort($files);
	
	return $files;
}


function linksManager()
{
	$OBJ =& get_instance();
	
	$rs = $OBJ->db->fetchArray("SELECT id, title, url, sec_desc  
		FROM ".PX."objects 
		INNER JOIN ".PX."sections ON ".PX."objects.section_id = secid 
		WHERE status = '1' AND url != '' 
		ORDER BY section_id ASC");
		
	// rewrite the array based on section name
	$i = 0;
	$x = '';
	if (is_array($rs))
	{
		foreach ($rs as $ar)
		{
			$newarr[$ar['sec_desc']][$i] = array($ar['url'], strip_tags($ar['title']), $ar['id']);
			$i++;
		}
	}
		
	foreach ($newarr as $key => $out)
	{
		$p = '';
			
		foreach ($out as $go)
		{
			$p .= "<option value=\"<a href='".BASEURL.ndxz_rewriter($go[0])."' alt='' title='".htmlspecialchars($go[1])."'>$go[1]\">$go[1]</option>\n";
		}
			
		$x .= "<optgroup label='" . ucwords($key) . "'>\n$p\n</optgroup>\n";
			
	}
		

	if (is_array($rs)) 
	{	
		$s = select('sysLink', "style='width: 225px; margin-bottom: 6px;'", $x);
	} 
	else
	{		
		$s = p($OBJ->lang->word('none found'));
	}
	
	return $s;
}

function upgrades()
{
	$OBJ =& get_instance();

	global $default;
	
	$upgrade = false;
	
	// we need to record the version into the database now
	if (VERSION > $OBJ->access->settings['version'])
	{
		$rs = get_file_kind(DIRNAME . BASENAME . '/upgrade/', 'php');

		$old_version = ($OBJ->access->settings['version'] == '') ? 
			'' : 
			str_replace('.', '', $OBJ->access->settings['version']);

		foreach ($rs as $a) 
		{
			$new_version = str_replace(array('upgrade_', '.php'), array('', ''), $a);
			
			if ($new_version > $old_version) $upgrade = true;
		}
	}
	
	return $upgrade;
}


function get_yes_no($state, $name, $attr)
{
	$OBJ =& get_instance();
	
	if ($state == '') $state = 0;
	
	$s = option(1, $OBJ->lang->word('yes'), $state, 1);
	$s .= option(0, $OBJ->lang->word('no'), $state, 0);
	
	return select($name, attr($attr), $s);	
}


function get_break($state, $name, $attr)
{
	$OBJ =& get_instance();
	
	if ($state == '') $state = 0;
	
	$s = '';
	
	for ($i=0; $i<11; $i++)
	{
		$s .= option($i, $i, $state, $i);
	}
	
	return select($name, attr($attr), $s);	
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


function filesManagerType($type, $file, $x='', $y='', $desc='')
{
	global $default;
	
	// images
	if (in_array($type, $default['images']))
	{
		return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsImg('". BASEURL . '/files/' . $file ."', '$x', '$y'); return false;\"");
	}

	// mp3, mov, etc...
	elseif (in_array($type, $default['media']))
	{
		switch ($type) {
		case 'mov': 
			//$desc = ($desc)
		   return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsMov('$file', '$x', '$y', '$desc'); return false;\"");
		   break;
		// not in use...
		case 'avi':
		   return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsAVI('$file', '$x', '$y'); return false;\"");
		   break;
		case 'jar':
		   return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsJAR('$file', '$x', '$y'); return false;\"");
		   break;
		case 'flv':
		   return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsFlv('$file', '$x', '$y', '$desc'); return false;\"");
		   break;
		}
	}
	elseif (in_array($type, $default['sound'])) // sound
	{
		switch ($type) {
		case 'mp3':
		   return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsMP3('$file'); return false;\"");
		   break;
		}
	}
	// flash
	elseif (in_array($type, $default['flash']))
	{
		return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsFlash('$file', '$x', '$y'); return false;\"");
	}
	// other files
	else
	{
		
		return href("<img src='asset/img/files-use.gif' />", '#', "onClick=\"parent.ModInsFile('". BASEURL . "/files/$file'); return false;\"");
	}	
}



function createFileBox($num)
{
	$OBJ =& get_instance();
	
	$s = label($OBJ->lang->word('title') . span(' ' . $OBJ->lang->word('optional')));
	
	for ($i = 0; $i <= $num; $i++)
	{
		($i > 0) ? $style = " style='display:none'" : $style = '';
		
		$s .= div(input("media_title[$i]",'text',"size='20' maxlength='35'",NULL).'&nbsp;'.
			input("filename[$i]",'file',"size='20'",NULL),
			"class='attachFiles' id='fileInput$i'$style");
	}	
	
	$s .= p(href($OBJ->lang->word('attach more'),'javascript:AddFileInput()')
			,"class='attachMore' id='attachMoreLink'");

	return $s;
}



function getTimeOffset($default='', $name, $attr='')
{
	$s = '';
	$default = ($default == '') ? 0 : $default;
	$timestamp = getNow();

	$offset = array(13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0,
		-1, -2, -3, -4, -5, -6, -7, -8, -9, -10, -11, -12);
	
	$timestamp = str_replace(array('-', ':', ' '), array('', '', ''), $timestamp);
	
	$time[0] = substr($timestamp, 8, 2); // hours
	$time[1] = substr($timestamp, 10, 2); // min
	$time[2] = substr($timestamp, 12, 2); // seconds
	$time[3] = substr($timestamp, 6, 2); // day
	$time[4] = substr($timestamp, 4, 2); // month
	$time[5] = substr($timestamp, 0, 4); // year

	foreach ($offset as $a) 
	{
		$hello = date('Y-m-d H:i:s', mktime($time[0]+$a, $time[1], $time[2], $time[4], $time[3], $time[5]));
		
		$newdate = date("G:i", strtotime($hello));
		
		($default == $a) ? $sl = "selected ": $sl = "";
		$s .= option($a, $newdate, $default, $a);
	}

	return select($name, attr($attr), $s);
}


function getTimeFormat($default='', $name, $attr='')
{
	$s = '';
	$default = ($default == '') ? '%Y-%m-%d %T' : $default;
	$timestamp = getNow();

	$formats = array('%d %B %Y', '%A, %H:%M %p', '%Y-%m-%d %T');
	
	$timestamp = str_replace(array('-', ':', ' '), array('', '', ''), $timestamp);
	
	$time[0] = substr($timestamp, 8, 2); // hours
	$time[1] = substr($timestamp, 10, 2); // min
	$time[2] = substr($timestamp, 12, 2); // seconds
	$time[3] = substr($timestamp, 6, 2); // day
	$time[4] = substr($timestamp, 4, 2); // month
	$time[5] = substr($timestamp, 0, 4); // year

	foreach ($formats as $format) 
	{
		$hello = date('Y-m-d H:i:s', mktime($time[0], $time[1], $time[2], $time[4], $time[3], $time[5]));
		
		$newdate = strftime($format, strtotime($hello));
		
		($default == $format) ? $sl = "selected ": $sl = "";
		$s .= option($format, $newdate, $default, $format);
	}

	return select($name, attr($attr), $s);
}


function getLanguage($default='', $name, $attr='')
{
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->lang->lang_options();

	if ($default == '')
	{
		$s .= option('', $OBJ->lang->word('make selection'), 0, 0);
	}

	foreach ($rs as $key => $a) 
	{
		$language = array_pop($a);
		
		// check to see if the lang folder exists
		if (is_dir(DIRNAME . BASENAME . '/' . LANGPATH . '/' . $key))
		{
			($default == $a) ? $sl = "selected ": $sl = "";
			$s .= option($key, $OBJ->lang->word($language), $default, $key);
		}
	}
	clearstatcache();

	return select($name, $attr, $s);
}


// we'll need to break these out later...
function get_section_type($state, $name, $attr)
{
	$OBJ =& get_instance();
	global $default;
	
	if ($state == '') $state = 0;

	$s = option(0, $OBJ->lang->word('default'), $state, 0);
	$s .= option(1, $OBJ->lang->word('chronological'), $state, 1);
	$s .= option(3, $OBJ->lang->word('tags'), $state, 3);
	
	return select($name, $attr, $s);	
}


function get_section_type_name($input)
{
	$OBJ =& get_instance();

	$temp = array(0 => 'default', 1 => 'chronological', 3 => 'tags');
	
	foreach ($temp as $key => $do)
	{
		if ($input == $key)
		{
			return $OBJ->lang->word($do);
		}
	}
}


function getGeneric($state, $name, $attr)
{
	$OBJ =& get_instance();
	
	if ($state == '') $state = 0;
	
	$s = option(1, $OBJ->lang->word('on'), $state, 1);
	$s .= option(0, $OBJ->lang->word('off'), $state, 0);
	
	return select($name, attr($attr), $s);	
}


function selector($i='')
{
	$services = array('youtube', 'vimeo');
	
	$a = '';
	
	foreach ($services as $svc)
	{
		//$selected = ($svc == $default) ? " selected='selected'" : '';
		$a .= "<option value='$svc'{selected}>$svc</option>\n";
	}
	
	return "<select name='svc_type[$i]' style='width: 75px;'>\n" . $a . "</select>\n";
}


function getSections()
{
	global $go, $default;
	
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT secid, sec_desc, sec_proj, sec_ord, sec_disp, sec_path, sec_hide, sec_pwd     
		FROM ".PX."sections 
		ORDER by sec_ord ASC");
		
	$subs = $OBJ->db->fetchArray("SELECT *     
		FROM ".PX."subsections 
		ORDER by sub_sec_id ASC, sub_order ASC");
		
	// rewrite the subs array
	if ($subs)
	{
		foreach ($subs as $sub)
		{
			$subsections[$sub['sub_sec_id']][] = $sub;
		}
	}
	else
	{
		$subsections = array();
	}

	if (!$rs)
	{
		return p('None');
	}
	
	$s .= "<ul id='sizes' class='sec_info' style='width: 870px;'>\n";
	
	if ($rs)
	{
		$num = count($rs);
		
		// THIS IS REALLY WONKY...REVIEW IT LATER...
		$path = strong($OBJ->lang->word('section path'), "class='sec_path'");
		$hidd = strong($OBJ->lang->word('section hidden'), "class='sec_hidden'");
		$orgz = strong($OBJ->lang->word('section org'), "class='sec_orgz'");
		$secid = strong($OBJ->lang->word('id'), "class='sec_id'");
		
		$s .= "<li>" . strong($OBJ->lang->word('section')) . " $path $hidd $orgz $secid</li>\n";
		
		$i = 1;
		
		// loop out the stuff
		foreach ($rs as $size)
		{
			// path, hidden, type
			$path = em($size['sec_path'], "class='sec_path'");
			$hidd = em($OBJ->lang->word(($size['sec_hide'] == 1) ? 'yes' : 'no'), "class='sec_hidden'");
			$orgz = em(get_section_type_name($size['sec_proj']), "class='sec_orgz'");
			$secid = em($size['secid'], "class='sec_id'");
			
			$root = ($size['secid'] == 1) ? " grn-text" : '';
			
			$pwd = ($size['sec_pwd'] != '') ? ' sec_locked' : '';
			
			// are there subdirs?
			$tmp = '';
			if (isset($subsections[$size['secid']]))
			{
				foreach ($subsections[$size['secid']] as $sub)
				{
					$pre = ($size['secid'] == 1) ? '' : $size['sec_path'];
					$tmp .= $pre . '/' . $sub['sub_folder'] . '<br />';
				}
					
				$tmp = em($tmp, "class='sec_subs'");
			}
			
			$s .= "<li id='sz$i' class='sec_sort{$root}'><a href='?a=$go[a]&q=sections&id=$size[secid]'>" . $OBJ->lang->word('edit') . "</a>&nbsp;&nbsp; <span id='ss$size[secid]' class='drag-title{$pwd}'>$size[sec_desc]</span> $path $tmp $hidd $orgz $secid</li>\n";
			
			$i++;
		}
	}
	
	$s .= "</ul>\n";
	$s .= p($OBJ->lang->word('drag section title'), "style='padding: 3px;'");
	
	$s .= p(href($OBJ->lang->word('create new section'), '#', "onclick=\"toggle('add-sec'); return false;\""), "style='margin-top: 18px;'");
	
	$input = label($OBJ->lang->word('section name') . ' ' . span($OBJ->lang->word('required'), "class='small-txt'")) . input('sec_desc', 'text', "maxlength='35'", NULL);
	$input .= label($OBJ->lang->word('folder name') . ' ' . span($OBJ->lang->word('required'), "class='small-txt'")) . input('section', 'text', "maxlength='25'", NULL);

	//$input .= ($default['subdir'] == true) ? getSectionPrepend(null, 'sec_prepend', null) : 
	//	input('sec_prepend', 'hidden', NULL, '/');

	//$input .= input('hsec_ord', 'hidden', NULL, $num);
	//$input .= div(input('add_sec', 'submit', NULL, $OBJ->lang->word('add section')), "style='text-align: right;'");
	
	$input .= "<div class='buttons'>";
	
	$input .= input('hsec_ord', 'hidden', NULL, $num);
	$input .= button('add_sec', 'submit', "class='general_submit'", $OBJ->lang->word('update'));
			
	$input .= "</div>\n";
	
	$s .= div($input, "style='display:none; margin-top: 9px;' id='add-sec'");

	return div($s, "class='sections'");
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
				if ((!preg_match("/^_/i",$module)) && (!preg_match("/^CVS$/i",$module)) && (!preg_match("/.php$/i",$module)) && (!preg_match("/.html$/i",$module)) && (!preg_match("/.DS_Store/i",$module)) && (!preg_match("/\./i",$module)) && (!preg_match("/plugin/i", $module)) && (!preg_match("/css/i", $module)) && (!preg_match("/js/i", $module)) && (!preg_match("/img/i", $module)) && (!preg_match("/cache/i", $module)) && (!preg_match("/config/i", $module)) && (!preg_match("/mobile/i", $module)))
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


function getOrganize($state, $name, $attr)
{
	$OBJ =& get_instance();
	
	if ($state == '') $state = 1;
	
	$s = option(1, $OBJ->lang->word('chronological'), $state, 1);
	$s .= option(2, $OBJ->lang->word('sectional'), $state, 2);
	
	return select($name, attr($attr), $s);	
}


function get_section_object($object='', $name, $attr='')
{
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT obj_ref_type FROM ".PX."objects_prefs ORDER BY obj_id ASC");

	//$s .= option('', 'None', 1, 0);

	foreach ($rs as $a) 
	{
		$s .= option($a['obj_ref_type'], $a['obj_ref_type'], $object, $a['obj_ref_type']);
	}

	return select($name, $attr, $s);
}


function get_replacement($section='', $name, $attr='')
{
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT media_file FROM ".PX."media WHERE media_obj_type = '' ORDER BY media_file ASC");

	$s .= option('', 'None', 1, 0);

	foreach ($rs as $a) 
	{
		$s .= option($a['media_file'], $a['media_file'], $section, $a['media_file']);
	}

	return select($name, $attr, $s);
}


function getSectionPrepend($section='', $name, $attr='')
{
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT sec_path,sec_desc,sec_ord FROM ".PX."sections ORDER BY sec_path ASC");
	
	// if there is a section we need to reduce the path one level down...or else /...
	// seems to work just fine
	$new_section = explode('/', $section);
	array_pop($new_section);
	$new_section = preg_replace("/\/\//", '/', '/' . implode('/', $new_section));
	
	// if we are in section 1
	if ($section == '/')
	{
		$s .= option('/', '/', $new_section, '/');
	}

	foreach ($rs as $a) 
	{
		if ($section != $a['sec_path'])
		{
		// we need to deal with double //
		$s .= option(preg_replace("/\/\//", '/', $a['sec_path']), 
			preg_replace("/\/\//", '/', $a['sec_path'] . '/'), 
			$new_section, $a['sec_path']);
		}
	}

	return select($name, $attr, $s);
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


function the_templates($path, $format='', $default)
{
	$OBJ =& get_instance();

	$modules = array();
	
	$format = ($format == '') ? '' : $format;

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($module = readdir($fp)) !== false) 
			{
				if ((preg_match("/.$format$/i", $module)) && (!preg_match("/.DS_Store/i", $module)) && (!preg_match("/^_/i", $module)) && (!preg_match("/^CVS$/i", $module)) && (!preg_match("/^\./i", $module)))
				{
					$modules[] = $module;
				}
			} 
		}
		closedir($fp);
	}
 
	sort($modules);
		
	$s = [];
	foreach ($modules as $module)
	{
		//$search = array('.php');
		//$replace = array('');
		//$module = str_replace($search, $replace, $module);

		$s[] = $module;
	}
	
	return $s;
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



function getTemplate($template='', $name, $attr='')
{
	$OBJ =& get_instance();

	$s = '';

	$rs = get_file_kind(DIRNAME . '/ndxzsite/' . $OBJ->access->settings['obj_theme'] . '/', 'php');

	foreach ($rs as $a) 
	{
		$s .= option($a, $a, $template, $a);
	}

	return select($name, $attr, $s);
}



function getSection($section='', $name, $attr='')
{
	global $default;
	
	$OBJ =& get_instance();
	
	$s = '';

	$rs = $OBJ->db->fetchArray("SELECT secid,section,sec_desc,sec_proj FROM ".PX."sections 
		WHERE secid != '1' ORDER by sec_ord ASC");

	foreach ($rs as $a) 
	{
		//($section == $a['secid']) ? $sl = "selected ": $sl = "";
		$s .= option($a['secid'], $a['sec_desc'], $a['secid'], $section);
	}

	return select($name, attr($attr), $s);
}


//////////////////
function getThumbSize($input='', $attr='')
{
	$OBJ =& get_instance();
	global $default;
	
	$s = '';
	$input = ($input == '') ? 100 : $input;
	
	foreach($default['thumbsize'] as $key => $size)
	{
		$s .= option($key, $size, $size, $input);
	}

	return select('tag[thumbs]', null, $s);
}


function getImageShape($input=0, $attr='')
{
	$OBJ =& get_instance();
	global $default;
	
	$shapes = array(0 => 'natural', 1 => 'square', 2 => '4x3', 3 => 'cinematic');
	
	$s = '';
	//$input = ($input == '') ? 0 : $input;
	
	foreach($shapes as $key => $size)
	{
		$s .= option($key, $size, $key, $input);
	}

	return select('tag[thumbs_shape]', null, $s);
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
		$search = array('format.','.php');
		$replace = array('','');
		$module = str_replace($search, $replace, $module);
		$name = str_replace('_', ' ', $module);

		$s .= option($module, ucwords($name), $module, $default);
	}
	
	return select('tag[format]', null, $s);
}


function getTagPresent($path, $default)
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
					
					if ($object_check['objects'] == 'tag')
					{
						$modules[] = $module;
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
		$search = array('format.','.php');
		$replace = array('','');
		$module = str_replace($search, $replace, $module);
		$name = str_replace('_', ' ', $module);

		$s .= option($module, ucwords($name), $module, $default);
	}
	
	return select('tag[format]', "style='margin-bottom: 0px;'", $s);
}


// these help showing the template code
function tpl_process_code($code)
{
	if (!$code) return;
	
	$out = '';
	$new = explode("\n", $code);
	
	foreach ($new as $line)
	{
		$new_line = htmlentities($line);
		$out .= tpl_tabs($new_line);
	}
		
	return ol($out,NULL);
}


function tpl_tabs($line)
{
	if ($line == '') return li(code('&nbsp;',NULL),"class='tabo'");

	preg_match_all("/\t/",$line,$out);
	$num = count($out[0]);
	if ($num > 5) $num = 5;
	
	// we'll need to strip out things and determine if the link is empty
	
	$search = array("/\t/","/\n/","/\r/","/\r\n/");
	$replace = array('','','','');
	
	$line = preg_replace($search,$replace,$line);
	
	if ($line == '') $line = "&nbsp;";

	return li(code($line,NULL),"class='tab$num'");
}
