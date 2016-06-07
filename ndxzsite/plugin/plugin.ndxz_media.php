<?php

function ndxz_media()
{
	$OBJ =& get_instance();
	global $rs, $default, $medias;
	
	// we still need security here...is there another way to do this?
	if ($OBJ->front->protected == true) return $OBJ->front->exhibit['exhibit'];
	
	$page = $OBJ->db->fetchRecord("SELECT * 
		FROM ".PX."media  
		WHERE media_ref_id = '$rs[id]' 
		AND media_hide = '0' 
		AND media_file = '" . $OBJ->db->escape_str($OBJ->core->image_file) . "' 
		AND media_mime IN ('" . implode('\', \'', $medias) . "') 
			ORDER BY media_order ASC, media_id ASC");
		
	if (!in_array($page['media_mime'], $default['images'])) 
	{
		//$OBJ->core->image_file = $page['media_file_replace'];
		$OBJ->core->image_replace = true;
		return ndxz_formats($page);
	}

	// here we need to deal with the media types

	if (!$page) return;
	
	$title = $page['media_file'];
	
	//$s .= "<p>$page[media_title]</p>\n";
	//$s .= "<p>$page[media_caption]</p>\n";
	
	$cap = ($page['media_title'] == '') ? '' : $page['media_title'] . ' ';
	$cap .= ($page['media_caption'] == '') ? '' : $page['media_caption'];
	$cap = ($cap == '') ? '' : ' / ' . $cap;
	
	//$path = ($page['media_dir'] == '') ? GIMGS : "/files/$page[media_dir]";
	$path = GIMGS;
	
	$size = @getimagesize(DIRNAME . $path . '/' . $page['media_ref_id'] . '_' . $page['media_file']);
	
	$s = "<div id='ndxz-media'>\n";
	//$s .= "<h2><a href='" . BASEURL . "$rs[url]'>$rs[title]</a>$cap</h2>\n";
	$return = "<a href='" . BASEURL . ndxz_rewriter($rs['url']) . "'>Thumbnails</a>&nbsp;&nbsp;";
	$s .= "<p>$return " . array_neighbor($page['media_id'], $page['media_ref_id'], $page['media_file']) . "</p>\n";
	$s .= "<p><img src='" . BASEURL . $path . '/' . $page['media_ref_id'] . '_' . $page['media_file'] . "' width='$size[0]' height='$size[1]' /></p>\n";
	$s .= "<div id='media_info'>\n";
	if ($page['media_title'] !=  '') $s .= "<h2>" . $page['media_title'] . "</h2>";
	$s .= $page['media_caption'];
	$s .= "</div>\n";
	//$s .= "<p>$page[media_caption]</p>\n";
	$s .= "\n";
	//$s .= show_tags($page['media_id']);
	$s .= "\n";
	$s .= "</div>\n";
	//$s .= "<p class='copyrighted'>These works are copyrighted to the owner of this website unless otherwise stated.</p>\n";
		
	return $s;
}


function ndxz_formats($arr)
{
	$OBJ =& get_instance();
	global $rs;
	
	$paginate = array_neighbor($arr['media_id'], $arr['media_ref_id'], $arr['media_file']);

	//if (!$page) return;
	$cap = ($arr['media_title'] == '') ? $arr['media_file'] : $arr['media_title'];

	$s = "<div id='ndxz-media'>\n";
	$s .= "<h2><a href='" . BASEURL . "$rs[url]'>$rs[title]</a> / $cap</h2>\n";
	$s .= "<p>" . $paginate . "</p>\n";

	$mime = $arr['media_mime'];
	
	if ($mime == 'flv')
	{
		$s .= flv($arr['media_file'], $arr['media_x'], $arr['media_y'], $arr['media_ref_id'] . '_' . $arr['media_thumb'], null, true, false, '000000');
	}
	elseif ($mime == 'swf')
	{
		$s .= swf($arr['media_file'], $arr['media_x'], $arr['media_y'], null, null, true, false, '000000');
	}
	elseif ($mime == 'mov')
	{
		$s .= mov($arr['media_file'], $arr['media_x'], $arr['media_y'], null, null, true, false, '000000');
	}
	elseif ($mime == 'mp4')
	{
		$s .= mp4($arr['media_file'], $arr['media_x'], $arr['media_y'], null, null, true, false, '000000');
	}
	//$page['media_mime'] == 'mp3'
	elseif (true == 'mp3')
	{
		$s .= mp3($arr['media_file']);
	}
	elseif ($mime == 'youtube')
	{
		$s .= mov($arr['media_file'], $arr['media_x'], $arr['media_y'], null);
	}
	else { }
	
	
	$s .= "<p>$arr[media_title]</p>\n";
	$s .= "<p>$arr[media_caption]</p>\n";
	$s .= "\n";
	$s .= show_tags($arr['media_id']);
	$s .= "\n";
	$s .= "</div>\n";
	$s .= "<p class='copyrighted'>These works are copyrighted to the owner of this website unless otherwise stated.</p>\n";
		
	return $s;
}


function show_tags($id=0)
{
	$OBJ =& get_instance();
	global $rs;
	
	$tags = $OBJ->db->fetchArray("SELECT tag_name  
		FROM ".PX."tags, ".PX."tagged     
		WHERE tagged_obj_id = '$id'  
		AND tagged_id = tag_id 
		ORDER BY tag_name ASC");
		
	if (!$tags) return;
	
	//foreach ($tags as $tag) $tagged[] = "<a href='" . BASEURL . "$tag[url]'>" . $tag['tag_name'] . "</a>";
	foreach ($tags as $tag) $tagged[] = str_replace('_' , ' ', $tag['tag_name']);
	
	return "<p class='tagged'>Tagged: " . implode(', ', $tagged) . ".</p>\n";
}


function array_neighbor($id, $current, $img)
{
	$OBJ =& get_instance();
	global $rs, $medias;
	
	$rsa = $OBJ->db->fetchArray("SELECT media_id, media_file     
		FROM ".PX."media 
		WHERE media_ref_id = '$current' 
		AND media_mime IN ('" . implode('\', \'', $medias) . "') 
		AND media_hide = '0' 
		ORDER BY media_order ASC");
		
		//print_r($rsa);
		
	if (!$rsa) return;
			
	foreach ($rsa as $rw) $arr[] = array($rw['media_id'], $rw['media_file']);
		
	$nx = 0; $px = 0; $last = 0;
	$n = false; $p = false; $c = false; $nn = false;
	
	$total = count($arr);
		
	foreach ($arr as $key => $ck)
	{
		if ($key == 0) { $first = $ck[1]; }
		if ($key == ($total-1)) { $final = $ck[1]; }
		if ($nn == true) { $nx = $ck[1]; $nn = false; }
		if ($ck[0] == $id) { $c = true; $px = $arr[$key-1][1]; $nn = true; }
		if ($c == true) { $c = false; }
		if ($ck[1] == $img) $here = $key + 1;	
	}

	$s = "";
	
	if ($total > 1)
	{
		$s .= "<span id='where'>" . $here . ' of ' . $total . '</span> | ';
			
		if ($total > 1)
		{
			$s .= ($img != $first) ? " <a href='" . BASEURL . "$rs[url]$first' title='First'>First</a> | " : "<span class='inactive'>First</span> | ";
		}
		
		if (($px == '') || ($img == $px)) 
		{
			$s .= "<span class='inactive'>Previous</span> | ";
		}
		else
		{
			// if not an image we'll switch the $px...but how?
			
			$s .= "<a href='" . BASEURL . "$rs[url]$px' title='Previous'>Previous</a> | ";
		}
		
		// why does '' work and 0 doesn't?
		$s .= (($nx == '') || ($img == $nx)) ? "<span class='inactive'>Next</span> " : " <a href='" . BASEURL . "$rs[url]$nx' title='Next'>Next</a> ";
		
		//$s .= (($nx == 0) || ($img == $nx)) ? "<span class='inactive'>Next</span> " : " <a href='" . BASEURL . "$rs[url]$nx/' title='Next'>Next</a> ";

		if ($total > 1)
		{
			$s .= ($img != $final) ? " | <a href='" . BASEURL . "$rs[url]$final' title='Last'>Last</a>" : " | <span class='inactive'>Last</span>";
		}
	}
	
	$s .= "&nbsp;\n\n";

	return $s;
}


?>