<?php if (!defined('SITE')) exit('No direct script access allowed');

// returns a list of folders inside the path
function getFiles($path, $default)
{
	global $default;

	// let's get the folders and info...
	$modules = array();
	
	$thumbclude = array('systh-', 'sys-', 'th-');

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($module = readdir($fp)) !== false) 
			{
				// need to get the extension
				$ext = array_pop( explode('.', $module) );
					
				// and does not begin with...thumbnails...
					
				if (in_array($ext, $default['images'])) 
				{
					if ((!preg_match("/^sys-/i", $module)) && (!preg_match("/^systh-/i", $module)) && (!preg_match("/^th-/i", $module)) && (!preg_match("/^16_/i", $module)))
					{
						$modules[] = $module;
					}
				}
			} 
		}
		closedir($fp);
	}
 
	sort($modules);
	
	return $modules;
}


// returns a list of folders inside the path
function getFolders($path, $default, $exclude=array())
{
	// let's get the folders and info...
	$modules = array();

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($module = readdir($fp)) !== false) 
			{
				if (is_dir($path . '/' . $module) && (!preg_match("/.DS_Store/i", $module)) && (!preg_match("/\./", $module)) && (!preg_match("/^_/", $module)) && (!preg_match("/^CVS$/", $module)))
				{
					if (!in_array($module, $exclude)) $modules[] = $module;
				}
			} 
		}
		closedir($fp);
	}
 
	sort($modules);
	
	// we need this so only one option can be selected...
	$s = option('', 'Select', '', '');
	
	foreach ($modules as $module)
	{
		$s .= option($module, ucwords($module), $module, $default);
	}
	
	return select('media_info', "id='ajx-folder' style='width: 150px;'", $s);
}



// returns a list of folders inside the path
function get_the_folders($path, $default, $exclude=array())
{
	// let's get the folders and info...
	$folders = array();

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($module = readdir($fp)) !== false) 
			{
				if (is_dir($path . '/' . $module) && (!preg_match("/.DS_Store/i", $module)) && (!preg_match("/\./", $module)) && (!preg_match("/^_/", $module)) && (!preg_match("/^CVS$/", $module)))
				{
					if (!in_array($module, $exclude)) $folders[] = $path . '/' . $module;
				}
			} 
		}
		closedir($fp);
	}
 
	return $folders;
}


function show_the_folders_files($folders=null)
{
	$OBJ =& get_instance();

	global $go;

	if ($folders == null) return;
	
	// get a list of all files in the system - duplicates can not be added to exhibit
	$loaded = $OBJ->db->fetchArray("SELECT media_file FROM ".PX."media 
		WHERE media_ref_id = '" . $go['id'] . "'");
		
	if ($loaded)
	{
		foreach ($loaded as $load) $new[] = $load['media_file'];
		$loaded = $new;
	}
	else
	{
		$loaded = array();
	}
	
	load_helper('html');
	$i = 0; $j = 0;
	
	$html = "<ul id='folderfiles'>\n";
	
	foreach ($folders as $key => $folder)
	{
		if (is_writable(DIRNAME . '/files/' . $key . '/'))
		{
			$html .= "<li class='folder_name'><a href='#' onclick=\"$('ul#j$j').toggle(); return false;\">$key</a></li>\n";
			$html .= "<ul id='j$j' style='margin-bottom: 9px; display: none;'>\n";
		
			foreach ($folder as $file)
			{
				// this doesn't really seem to work
				if (is_writable(DIRNAME . '/files/' . $key . '/'))
				{
					$html .= (!in_array($file, $loaded)) ?
						li(href($file, '#', "onclick=\"file_add_single($go[id], $i, '$file', '$key'); return false;\""), "id='file-$i' style='margin: 5px 0;'") :
						li($file, "id='file-$i' style='margin: 5px 0; color: red;'");
				}
				else
				{
					//if (is_writable(DIRNAME . '/files/' . $key . '/' . $file))
					//{
						$html .= li($file, "id='file-$i' style='margin: 5px 0; color: red;'");
					//}
				}
				
				$i++;
			}
		
			$html .= "</ul>\n";
		
			$j++;
		}
	}
	
	$html .= "</ul>\n";
	
	return $html;
}


function get_the_files_from_folders($path, $default, $exclude=array())
{
	// let's get the folders and info...
	$files = array();
	
	if (is_array($path))
	{
		foreach ($path as $dir)
		{
			if (is_dir($dir))
			{
				// create the folder name here
				$folder_name = explode(DIRECTORY_SEPARATOR, $dir);
				$folder_name = array_pop($folder_name);
				
				if ($fp = opendir($dir)) 
				{
					while (($module = readdir($fp)) !== false) 
					{
						if ((!preg_match("/.DS_Store/i", $module)) && (!preg_match("/^\./", $module)) && (!preg_match("/^_/i", $module)) && (!preg_match("/^CVS$/i", $module)) && (!preg_match("/^systh-/i", $module)) && (!preg_match("/^sys-/i", $module)) && (!preg_match("/^th-/i", $module)))
						{
							if (!in_array($module, $exclude)) $files[$folder_name][] = $module;
						}
					} 
				}
				closedir($fp);
			}
		}
	}
	else
	{
		if (is_dir($path))
		{
			if ($fp = opendir($path)) 
			{
				while (($module = readdir($fp)) !== false) 
				{
					if ((!preg_match("/.DS_Store/i", $module)) && (!preg_match("/^\./", $module)) && (!preg_match("/^_/i", $module)) && (!preg_match("/^CVS$/i", $module)) && (!preg_match("/^systh-/i", $module)) && (!preg_match("/^sys-/i", $module)) && (!preg_match("/^th-/i", $module)))
					{
						if (!in_array($module, $exclude)) $files[] = $module;
					}
				} 
			}
			closedir($fp);
		}
	}
 
	//sort($modules);
	
	//return $modules;
	
	return $files;	
}


function getTheFiles($path, $default, $exclude=array())
{
	// let's get the folders and info...
	$modules = array();

	if (is_dir($path))
	{
		if ($fp = opendir($path)) 
		{
			while (($module = readdir($fp)) !== false) 
			{
				if ((!preg_match("/.DS_Store/i", $module)) && (!preg_match("/^\./", $module)) && (!preg_match("/^_/i", $module)) && (!preg_match("/^CVS$/i", $module)) && (!preg_match("/^systh-/i", $module)) && (!preg_match("/^sys-/i", $module)) && (!preg_match("/^th-/i", $module)))
				{
					if (!in_array($module, $exclude)) $modules[] = $module;
				}
			} 
		}
		closedir($fp);
	}
 
	sort($modules);
	
	return $modules;	
}


// need to put the path with the file name
function delete_image($file='')
{
	if ($file)
	{
		if (file_exists($file))
		{
			@unlink($file);
		}
	}
}