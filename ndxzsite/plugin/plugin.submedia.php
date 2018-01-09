<?php

class Submedia
{
	public function __construct()
	{
		
	}
	
	function display()
	{
		$OBJ =& get_instance();
		global $rs, $default, $medias;
		
		//echo $OBJ->image_file; exit;

		// we still need security here...is there another way to do this?
		if ($OBJ->page->protected == true) return $OBJ->page->exhibit['exhibit'];

		$page = $OBJ->db->fetchRecord("SELECT * 
			FROM ".PX."media  
			WHERE media_ref_id = '$rs[id]' 
			AND media_hide = '0' 
			AND media_file = '" . $OBJ->db->escape_str(urldecode($OBJ->image_file)) . "' 
			AND media_mime IN ('" . implode('\', \'', $medias) . "') 
				ORDER BY media_order ASC, media_id ASC");

		if (!in_array($page['media_mime'], $default['images'])) 
		{
			//$OBJ->core->image_file = $page['media_file_replace'];
			$OBJ->image_replace = true;
			//return $this->ndxz_formats($page);
		}

		// here we need to deal with the media types

		if (!$page) return;

		$title = $page['media_file'];

		//$s .= "<p>$page[media_title]</p>\n";
		//$s .= "<p>$page[media_caption]</p>\n";

		$cap = ($page['media_title'] == '') ? '' : $page['media_title'] . ' ';
		$cap .= ($page['media_caption'] == '') ? '' : $page['media_caption'];
		$cap = ($cap == '') ? '' : ' / ' . $cap;
		
		if (!in_array($page['media_mime'], $default['images']))
		{
			$path = ($page['media_dir'] == '') ? GIMGS : "/files/$page[media_dir]";
			//$path = GIMGS;

			$size = @getimagesize(DIRNAME . $path . '/' . $page['media_ref_id'] . '_' . $page['media_file']);

			$s = "<div id='ndxz-media'>\n";
			//$s .= "<h2><a href='" . BASEURL . "$rs[url]'>$rs[title]</a>$cap</h2>\n";
			$return = "<a href='" . BASEURL . ndxz_rewriter($rs['url']) . "'>Back</a>&nbsp;&nbsp;";

			$s .= "<p>$return " . $this->array_neighbor($page['media_id'], $page['media_ref_id'], $page['media_file']) . "</p>\n";
			
			$mime = $page['media_mime'];
			
			$file = ($page['media_dir'] == '') ? $page['media_file'] : $page['media_dir'] . '/' . $page['media_file'];
			
			$s .= "<div>" . $mime($file, $page['media_x'], $page['media_y'], $page['media_thumb']) . "</div>";

		}
		else
		{
			$path = GIMGS;

			$size = @getimagesize(DIRNAME . $path . '/' . $page['media_ref_id'] . '_' . $page['media_file']);

			$s = "<div id='ndxz-media'>\n";
			//$s .= "<h2><a href='" . BASEURL . "$rs[url]'>$rs[title]</a>$cap</h2>\n";
			$return = "<a href='" . BASEURL . ndxz_rewriter($rs['url']) . "'>Back</a>&nbsp;&nbsp;";

			$s .= "<p>$return " . $this->array_neighbor($page['media_id'], $page['media_ref_id'], $page['media_file']) . "</p>\n";
	
			$s .= "<div><img src='" . BASEURL . $path . '/' . $page['media_ref_id'] . '_' . $page['media_file'] . "' width='$size[0]' height='$size[1]' /></div>\n";
		}
		
		// this should become an option
		//$OBJ->page->exhibit['append_menu'][] = "<p id='append_nav'>$return " . $this->array_neighbor($page['media_id'], $page['media_ref_id'], $page['media_file']) . "</p>\n";
		
		// this part could be styled better
		$s .= "<div id='media_info'>\n";
		
		$s .= (($page['media_title'] !=  '') && ($page['media_caption'] !=  '')) ? "<p>" : '';
		$s .= ($page['media_title'] !=  '') ? $page['media_title'] : '';
		$s .= ($page['media_title'] !=  '') ? ": " : '';
		$s .= ($page['media_caption'] !=  '') ? strip_tags($page['media_caption'], "a,i,b") : '';
		$s .= (($page['media_title'] !=  '') && ($page['media_caption'] !=  '')) ? "</p>" : '';
		
		$s .= "</div>\n";
		$s .= "\n";
		//$s .= $this->show_tags($page['media_id']);
		$s .= "\n";
		$s .= "</div>\n";

		return $s;
	}


	function ndxz_formats($arr)
	{
		$OBJ =& get_instance();
		global $rs;

		$paginate = $this->array_neighbor($arr['media_id'], $arr['media_ref_id'], $arr['media_file']);

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
		else { }


		$s .= "<p>$arr[media_title]</p>\n";
		$s .= "<p>$arr[media_caption]</p>\n";
		$s .= "\n";
		//$s .= $this->show_tags($arr['media_id']);
		$s .= "\n";
		$s .= "</div>\n";

		return $s;
	}


	function show_tags($id=0)
	{
		$OBJ =& get_instance();
		global $rs;

		$tags = $OBJ->db->fetchArray("SELECT tag_name, url   
			FROM ".PX."tags, ".PX."tagged, ".PX."objects     
			WHERE tagged_obj_id = '$id'  
			AND tagged_id = tag_id 
			AND object = 'tag' 
			AND obj_ref_id = tag_id 
			ORDER BY tag_name ASC");

		if (!$tags) return;

		//foreach ($tags as $tag) $tagged[] = "<a href='" . BASEURL . "$tag[url]'>" . $tag['tag_name'] . "</a>";
		foreach ($tags as $tag) $tagged[] = "<a href='" . BASEURL . ndxz_rewriter($tag['url']) . "'>" . str_replace('_' , ' ', $tag['tag_name']) . "</a>";

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
			if ($ck[0] == $id) { $c = true; $px = (isset($arr[$key-1][1])) ? $arr[$key-1][1] : ''; $nn = true; }
			if ($c == true) { $c = false; }
			if ($ck[1] == $img) $here = $key + 1;	
		}

		$s = "";

		if ($total > 1)
		{
			$s .= "<span id='where'>" . $here . ' of ' . $total . '</span> &nbsp;&nbsp;';

			if ($total > 1)
			{
				//$s .= ($img != $first) ? " <a href='" . BASEURL . ndxz_rewriter($rs['url']) . $first . "' title='First'>First</a> | " : "<span class='inactive'>First</span> | ";
			}

			if (($px == '') || ($img == $px)) 
			{
				$s .= "<span class='inactive'>Previous</span> | ";
			}
			else
			{
				// if not an image we'll switch the $px...but how?

				$s .= "<a href='" . BASEURL . ndxz_rewriter($rs['url']) . $px . "' title='Previous'>Previous</a> | ";
			}

			// why does '' work and 0 doesn't?
			$s .= (($nx == '') || ($img == $nx)) ? "<span class='inactive'>Next</span> " : " <a href='" . BASEURL . ndxz_rewriter($rs['url']) . $nx . "' title='Next'>Next</a> ";

			//$s .= (($nx == 0) || ($img == $nx)) ? "<span class='inactive'>Next</span> " : " <a href='" . BASEURL . "$rs[url]$nx/' title='Next'>Next</a> ";

			if ($total > 1)
			{
				//$s .= ($img != $final) ? " | <a href='" . BASEURL . ndxz_rewriter($rs['url']) . $final . "' title='Last'>Last</a>" : " | <span class='inactive'>Last</span>";
			}
		}

		$s .= "&nbsp;\n\n";

		return $s;
	}
}