<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Image source class
*
* This is for bringing in media sources for exhibitions.
*
* 0 = default
* 1 = gallery builder (not yet)
* 2 = flickr
* 3 = ???
* 
* @version 1.0
* @author Vaska 
*/

class FilesourceFolder implements Filesource 
{
	var $rs = array();
	
	function getExhibitImages($id=0)
	{
		$OBJ =& get_instance();
		global $go, $default, $medias;
		
		// we need defaults
		$rs = $OBJ->db->fetchRecord("SELECT * FROM ".PX."objects WHERE id='$id'");
		
		//print_r($rs); exit;
		
		// the images
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media 
			WHERE media_ref_id = '$id'
			AND media_obj_type = 'exhibit' 
			AND media_dir = '$rs[media_info]'
			ORDER BY media_order ASC, media_id ASC");
			
		if ($imgs)
		{
			// need to rewrite part of the array for other use
			foreach ($imgs as $thing)
			{
				$tmp[] = $thing['media_file'];
			}
		}
		
		//print_r($tmp); exit;
		
		// we need to traverse the folder for files
		load_helper('files');
		
		$x = getFiles(DIRNAME . '/files/' . $rs['media_info'] . '/', null);
		
		// now what do we do with the files?
		// insert...unless it already exists with the specified folder...
		// we do this check everytime
		// and delete ones that are no longer existant?
		
		foreach ($x as $key => $do)
		{
			if (!in_array($do, $tmp))
			{
			// this is adequate for the moment
			$clean['media_obj_type'] = 'exhibit';
			$clean['media_ref_id'] = $id;
			$clean['media_file'] = $do;
			$clean['media_mime'] = array_pop( explode('.', $do) );
			$clean['media_order'] = $key + 1;
			$clean['media_dir'] = $rs['media_info'];
			
			$OBJ->db->insertArray(PX.'media', $clean);
			
			// and we need to make a whole heck of alot of thumbnails...
			if (in_array($clean['media_mime'], $default['images']))
			{
				// make all the thumbs...
				$IMG =& load_class('media', true, 'lib');
				
				// we need to get these from some defaults someplace
				$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
				$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
				$IMG->quality = $default['img_quality'];
				$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
				$IMG->makethumb	= true;

				load_helper('output');
				$URL =& load_class('publish', TRUE, 'lib');

				// +++++++++++++++++++++++++++++++++++++++++++++++++++

				$new_images['name'] = $clean['media_file'];

				$test = explode('.', strtolower($new_images['name']));
				$thetype = array_pop($test);
				
				$IMG->path = DIRNAME . '/files/' . $rs['media_info'] . '/';
				$IMG->type = '.' . $thetype;
				$IMG->origname = $IMG->filename;
				
				$IMG->id = $id . '_';
				$IMG->filename = $clean['media_file'];
					
				$IMG->image = $IMG->path . '/' . $IMG->filename;
				$IMG->uploader();
			}
			}
		}

		$body = "<ul id='boxes'>\n";

		// the images
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media 
			WHERE media_ref_id = '$id'
			AND media_obj_type = 'exhibit'
			ORDER BY media_order ASC, media_id ASC");

		// set the width of the popup...deals with tags
		$site_vars = unserialize($OBJ->access->settings['site_vars']);
		$width = ($site_vars['site_tags'] == 1) ? 800 : 450;

		if ($imgs)
		{
			foreach ($imgs as $img)
			{
				$add = (!in_array($img['media_mime'], $medias)) ? "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>" : "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>";

				if (!in_array($img['media_mime'], $default['images']))
				{
					$thumb = ($img['media_thumb'] == '') ? 'asset/img/thumb-default.gif' : BASEURL . '/files/' . $rs['media_info'] . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb'];
				}
				else
				{
					$thumb = BASEURL . '/files/' . $rs['media_info'] . '/sys-' . $img['media_ref_id'] . '_' . $img['media_file'];
				}

				//$view = "<a href='?a=system&amp;q=view&amp;id=$img[media_id]' rel=\"shadowbox;player=iframe;height=400;width=800\" style='color: #999;'>".$OBJ->lang->word('view')."</a> ";

				// need to make this a class
				$active = ($img['media_hide'] == 1) ? " style='border: 1px solid #f00;'" : " style='border: 1px solid #fff;'";

				$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span class='drag-img'><img src='$thumb' title='" . strip_tags($img[media_title]) . "'{$active} /></span>$add<br /><a href='#' onclick=\"deleteImage($img[media_id], '$img[media_file]'); return false;\" style='color: #999;'><img src='asset/img/img-delete.gif' title='".$OBJ->lang->word('delete')."' style='width: 11px; height: 11px;' /></a> <a href='?a=system&amp;q=img&amp;id=$img[media_id]' rel=\"shadowbox;player=iframe;height=400;width=$width\" style='color: #999;'><img src='asset/img/img-edit.gif' title='".$OBJ->lang->word('edit')."' style='width: 11px; height: 11px;' /></a></li>\n\n";
			}
		}
		else
		{
			$body .= "<li>".$OBJ->lang->word('no images')."</li>\n";
		}

		$body .= "</ul>\n";

		$body .= "<div class='cl'><!-- --></div>\n";

		//$body .= "<div><span style='color: #0c0; font-size: 18px;'>&bull;</span> Active &nbsp;&nbsp;<span style='background: #000;'>&nbsp;&nbsp;&nbsp;</span> Inactive</div>";

		return $body;
	}
	
	function displaySwitches($switcher=0)
	{
		global $default;
		
		// only needed if we have more than one
		//if (count($default['filesource']) == 1) return;

		load_helper('html');
		
		$html = '';

		foreach ($default['filesource'] as $key => $switch)
		{
			$html .= option($key, $switch, $key, $switcher);
		}
		
		return select('media_source', "id='ajx-source' style='width: 150px;'", $html);
	}
	
	function getParameters($format='')
	{
		$file = DIRNAME . '/ndxzsite/plugin/exhibit.' . $format . '.php';
		$fp = fopen($file, 'r');
		$info = fread($fp, 8192);
		fclose($fp);
		
		$arr = array();
		preg_match ( '|Params:(.*)$|mi', $info, $params );
		preg_match ( '|Operands:(.*)$|mi', $info, $operands );
		
		if (isset($operands[1]))
		{
			$tmp = explode(',', $operands[1]);
			
			foreach ($tmp as $go)
			{
				$arr['operand'][trim($go)] = trim($go);
			}
		}
		
		if (isset($params[1]))
		{
			$tmp = explode(',', $params[1]);
			
			foreach ($tmp as $go)
			{
				$arr[trim($go)] = true;
			}

			return $arr;
		}
		else
		{
			// if it's empty show all options
			return array('format' => true, 'images' => true, 'thumbs' => true, 'shape' => true, 'placement' => true, 'break' => true, 'operands' => false);
		}
	}
	
	function switchInterface()
	{
		$OBJ =& get_instance(); global $default;
		
		$params = $this->getParameters($this->rs['format']);
		
		$bod = "<div style='width: 185px; float: left; padding: 0 5px;'>\n";
		
		// don't need it if we only have the default
		if (count($default['filesource']) > 1)
		{
			$bod .= label($OBJ->lang->word('media source'));
			$bod .= $this->displaySwitches($this->rs['media_source']);
			$OBJ->template->onready[] = "$('#ajx-source').change( function() { updateSource(); } );";
		}
		
		// here we'll get a list of available folders in 'files'
		load_helper('files');

		//getFolders(DIRNAME . '/files/', '');
		$bod .= "<label>" . $OBJ->lang->word('folders') . "</label>\n";
		$bod .= getFolders(DIRNAME . '/files/', $this->rs['media_source_detail'], $exclude = array('gimgs', 'dimgs'));
		$OBJ->template->onready[] = "$('#ajx-folder').change( function() { updateFolder(); } );";
		
		if (isset($params['format']))
		{
			$bod .= "<label>" . $OBJ->lang->word('exhibition format') . "</label>\n";
			$bod .= getPresent(DIRNAME . '/ndxzsite/plugin/', $this->rs['format']);
			$OBJ->template->onready[] = "$('#ajx-present').change( function() { updatePresent(); } );";
		}
		
		$bod .= "</div>\n";
		
		$bod .= "<div style='width: 185px; float: left; padding: 0 5px;' id='img-sizes'>\n";
		if (isset($params['images']))
		{
			$bod .= label($OBJ->lang->word('image max')).br();
			$bod .= getImageSizes($this->rs['images'], "class='listed' id='ajx-images'");
			$OBJ->template->onready[] = "$('#ajx-images li').tabpost();";
		}
		
		if (isset($params['thumbs']))
		{
			$bod .= label($OBJ->lang->word('thumb max') . showHelp($OBJ->lang->word('thumb max'))).br();
			$bod .= getThumbSize($this->rs['thumbs'], "class='listed' id='ajx-thumbs'");
			$OBJ->template->onready[] = "$('#ajx-thumbs li').tabpost();";
		}
		
		if (isset($params['shape']))
		{
			$bod .= label($OBJ->lang->word('thumbs shape') . showHelp($OBJ->lang->word('thumbs shape'))).br();
			$bod .= getImageShape($this->rs['thumbs_shape'], "class='listed' id='ajx-shape'");
			$OBJ->template->onready[] = "$('#ajx-shape li').tabpost();";
		}
		$bod .= "</div>\n";
		
		$bod .= "<div style='width: 185px; float: left; padding: 0 5px;'>\n";
		
		if (isset($params['titling']))
		{
			$bod .= label($OBJ->lang->word('titling')).br();
			$bod .= getOnOff($this->rs['titling'], "class='listed' id='ajx-titling'");
			$OBJ->template->onready[] = "$('#ajx-titling li').tabpost();";
		}

		if (isset($params['placement']))
		{
			$bod .= label($OBJ->lang->word('placement')).br();
			$bod .= getPlacement($this->rs['placement'], "class='listed' id='ajx-place'");
			$OBJ->template->onready[] = "$('#ajx-place li').tabpost();";
		}
		
		if (!empty($params['operand']))
		{
			$param = (!empty($params['operand'])) ? $params['operand'] : array();
			
			$bod .= label($OBJ->lang->word('operand') . showHelp($OBJ->lang->word('operand'))).br();
			$bod .= getOperand($this->rs['operand'], "class='listed' id='ajx-operand'", $params['operand']);
			$OBJ->template->onready[] = "$('#ajx-operand li').tabpost();";
		}
		
		if (isset($params['break']))
		{
			$bod .= label($OBJ->lang->word('image break')).br();
			$bod .= getBreak($this->rs['break']);
		}
		$bod .= "</div>\n";

		$bod .= "<div class='cl'><!-- --></div>\n";
		
		return $bod;
	}
	

	function getDisplayImages()
	{
		$OBJ =& get_instance();
		global $rs, $default, $medias;

		$out = array(); $i = 0;

		// get images
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."sections, ".PX."objects  
			WHERE media_ref_id = id  
			AND media_hide = '0' 
			AND media_mime IN ('" . implode('\', \'', $medias) . "') 
			AND media_order = '1' 
			AND section_top != '1' 
			AND section_id = secid 
			AND object = 'exhibit' 
			GROUP BY media_id 
			ORDER BY sec_ord, ord ASC");

		if ($imgs)
		{
			$IMG =& load_class('media', true, 'lib');
	
			foreach($imgs as $key => $do)
			{
				if (in_array($do['media_mime'], $default['images']))
				{
					$filename = $IMG->autoResize($do, $OBJ->core->rs);

					// get info about images
					$size = @getimagesize(DIRNAME . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_file']);
					$sizeth = @getimagesize(DIRNAME . GIMGS . '/' . $filename);

					$out[$i][$key]['media_id'] = $do['media_id'];
					$out[$i][$key]['media_flickr'] = false;
					$out[$i][$key]['media_ref_id'] = $do['media_ref_id'];
					$out[$i][$key]['media_mime'] = $do['media_mime'];
					$out[$i][$key]['media_tags'] = $do['media_tags']; // should we get tags here?
					$out[$i][$key]['media_title'] = $do['media_title'];
					$out[$i][$key]['media_caption'] = $do['media_caption'];
					$out[$i][$key]['media_x'] = $size[0];
					$out[$i][$key]['media_y'] = $size[1];
					$out[$i][$key]['media_thx'] = $sizeth[0];
					$out[$i][$key]['media_thy'] = $sizeth[1];
					$out[$i][$key]['media_uploaded'] = $do['media_uploaded'];
					$out[$i][$key]['media_file'] = $do['media_file']; // should not have the id appended to it
					$out[$i][$key]['media_path'] = $OBJ->baseurl . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
					$out[$i][$key]['media_thumb'] = $filename;
					$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . GIMGS . '/' . $filename;
					$out[$i][$key]['title'] = $do['title'];
					$out[$i][$key]['url'] = $do['url'];
				}
				else
				{
						if ($do['media_thumb'] != '')
						{
							// get info about images
							$size = @getimagesize(DIRNAME . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_thumb']);
							$sizeth = @getimagesize(DIRNAME . GIMGS . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb']);

							$out[$i][$key]['media_id'] = $do['media_id'];
							$out[$i][$key]['media_flickr'] = false;
							$out[$i][$key]['media_ref_id'] = $do['media_ref_id'];
							$out[$i][$key]['media_mime'] = $do['media_mime'];
							$out[$i][$key]['media_tags'] = $do['media_tags']; // ???
							$out[$i][$key]['media_title'] = $do['media_title'];
							$out[$i][$key]['media_caption'] = $do['media_caption'];
							$out[$i][$key]['media_x'] = $do['media_x'];
							$out[$i][$key]['media_y'] = $do['media_y'];
							$out[$i][$key]['media_thx'] = $sizeth[0];
							$out[$i][$key]['media_thy'] = $sizeth[1];
							$out[$i][$key]['media_uploaded'] = $do['media_uploaded'];
							$out[$i][$key]['media_file'] = $do['media_file']; // should not have the id appended to it
							$out[$i][$key]['media_path'] = $OBJ->baseurl . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
							$out[$i][$key]['media_thumb'] = 'th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
							$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . GIMGS . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
							$out[$i][$key]['title'] = $do['title'];
							$out[$i][$key]['url'] = $do['url'];
						}
					//}
				}

				$i++;
			}
		}

		return $out;
	}
}