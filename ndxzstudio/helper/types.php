<?php if (!defined('SITE')) exit('No direct script access allowed');

// to be updated as new formats are added

function plugin_insert($arr)
{
	global $default;
	
	$file = $arr['media_file'];
	$x = $arr['media_x'];
	$y = $arr['media_y'];
	$desc = $arr['media_title'];
	
	$vids = array_merge($default['media'], $default['services']);
	
	// images
	if (in_array($arr['media_mime'], $default['images']))
	{
		echo 'here';
		return "onClick=\"parent.ModInsImg('". BASEURL . '/files/' . $file ."', '$x', '$y'); return false;\"";
	}

	// mp3, mov, etc...
	elseif (in_array($arr['media_mime'], $vids))
	{
		$file = ($arr['media_dir'] == '') ? $file : $arr['media_dir'] . '/' . $file;

		switch ($arr['media_mime']) {
		case 'mov':
			$desc = ($arr['media_thumb'] == '') ? '' : $arr['media_ref_id'] . '_' . $arr['media_thumb'];
		   	return "onClick=\"parent.ModInsMov('$file', '$x', '$y', '$desc'); return false;\"";
		   	break;
		// not in use...
		case 'avi':
		   	return "onClick=\"parent.ModInsAVI('$file', '$x', '$y'); return false;\"";
		   	break;
		case 'jar':
		   	return "onClick=\"parent.ModInsJAR('$file', '$x', '$y'); return false;\"";
		   	break;
		case 'flv':
			$desc = ($arr['media_thumb'] == '') ? '' : $arr['media_ref_id'] . '_' . $arr['media_thumb'];
		   	return "onClick=\"parent.ModInsFlv('$file', '$x', '$y', '$desc'); return false;\"";
		   	break;
		case 'youtube':
			$desc = ($arr['media_thumb'] == '') ? '' : $arr['media_ref_id'] . '_' . $arr['media_thumb'];
			return "onClick=\"parent.ModInsYoutube('$file', '$x', '$y'); return false;\"";
			break;
		case 'vimeo':
			$desc = ($arr['media_thumb'] == '') ? '' : $arr['media_ref_id'] . '_' . $arr['media_thumb'];
			return "onClick=\"parent.ModInsVimeo('$file', '$x', '$y'); return false;\"";
			break;
		}
	}
	elseif (in_array($arr['media_mime'], $default['sound'])) // sound
	{
		$file = ($arr['media_dir'] == '') ? $file : $arr['media_dir'] . '/' . $file;

		switch ($arr['media_mime']) {
		case 'mp3':
		   	return "onClick=\"parent.ModInsMP3('$file', '$desc'); return false;\"";
		   	break;
		}
	}
	// flash
	elseif (in_array($arr['media_mime'], $default['flash']))
	{
		$file = ($arr['media_dir'] == '') ? $file : $arr['media_dir'] . '/' . $file;

		return "onClick=\"parent.ModInsFlash('$file', '$x', '$y'); return false;\"";
	}
	// other files
	else
	{
		$file = ($arr['media_dir'] == '') ? $file : $arr['media_dir'] . '/' . $file;

		return "onClick=\"parent.ModInsFile('". BASEURL . "/files/$file', '$desc', 1); return false;\"";
	}	
}