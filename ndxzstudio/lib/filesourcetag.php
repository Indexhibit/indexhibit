<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Image source class
*
* This is for bringing in media sources for exhibitions.
* 
* @version 1.0
* @author Vaska 
*/

class FilesourceTag 
{
	public $rs = array();
	
	function getTagged($id=0)
	{
		$OBJ =& get_instance();
		global $go, $default, $medias;

		$body = "<ul id='boxes'>\n";

		// the images
		// how do we get the section in here?
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects_prefs, ".PX."objects, ".PX."tags, ".PX."tagged  
			WHERE tagged_id = '$id' 
			AND tagged_id = tag_id 
			AND tagged_obj_id = media_id
			AND media_ref_id = id 
			AND media_mime IN ('" . implode('\', \'', $medias) . "') 
			AND section_top != '1' 
			GROUP BY media_id 
			ORDER BY media_uploaded DESC");
			
		//print_r($imgs);

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
						$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb'];
						$poster = true;
					}
				}
				else
				{
					//if ($img['media_thumb'] == '')
					//{
						$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_file'];
					//}
					//else
					//{
					//	$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb'];
					//	$poster = true;
					//}
				}
				
				// might need to check this down the line...
				if (!in_array($img['media_mime'], $default['images']))
				{
					// it's not an image but a movie...
					if (!file_exists(DIRNAME . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb']) && $img['media_thumb'] != '')
					{
						$IMG =& load_class('media', true, 'lib');
						$IMG->regenerate($img['media_ref_id'], $img['media_thumb']);
					}
				}
				else
				{
					// final check for images...
					if (!file_exists(DIRNAME . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_file']))
					{
						$IMG =& load_class('media', true, 'lib');
						$IMG->regenerate($img['media_ref_id'], $img['media_file']);
					}
				}
				
				// need to make this a class
				$active = ($img['media_hide'] == 1) ? " style='border: 1px solid #f00;'" : " style='border: 1px solid #fff;'";
				$poster = '';
				
				$add = (!in_array($img['media_mime'], $medias)) ? "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster" : "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster";
				
				$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span style='cursor: default;'><a href='?a=system&amp;q=tagfile&amp;id=$img[media_id]&tag=$img[tag_id]' rel=\"facebox;height=450;width=810\" style='color: #999;'><img src='$thumb' title='" . strip_tags($img['title']) . "'{$active} /></a></span>$add</li>\n\n";
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
	
	
	function getUnTagged($id=0)
	{
		$OBJ =& get_instance();
		global $go, $default, $medias;

		$body = "<ul id='boxes'>\n";

		// the images
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects  
			WHERE NOT EXISTS
				(
					SELECT tagged_obj_id 
					FROM ".PX."tagged 
					WHERE tagged_id = '$id' 
					AND tagged_obj_id = media_id 
				) 
			AND media_ref_id = id 
			AND media_mime IN ('" . implode('\', \'', $medias) . "')
			GROUP BY media_id 
			ORDER BY media_uploaded DESC");

		// set the width of the popup...deals with tags
		$site_vars = unserialize($OBJ->access->settings['site_vars']);
		$width = ($site_vars['tags'] == 1) ? 800 : 450;

		if ($imgs)
		{
			$OBJ->template->add_js('tags.js');
	
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
						$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb'];
						$poster = true;
					}
				}
				else
				{
					if ($img['media_thumb'] == '')
					{
						$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_file'];
					}
					else
					{
						$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb'];
						$poster = true;
					}
				}
				
				// might need to check this down the line...
				if (!in_array($img['media_mime'], $default['images']))
				{
					// it's not an image but a movie...
					if (!file_exists(DIRNAME . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb']) && $img['media_thumb'] != '')
					{
						$IMG =& load_class('media', true, 'lib');
						$IMG->regenerate($img['media_ref_id'], $img['media_thumb']);
					}
				}
				else
				{
					// final check for images...
					if (!file_exists(DIRNAME . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_file']))
					{
						$IMG =& load_class('media', true, 'lib');
						$IMG->regenerate($img['media_ref_id'], $img['media_file']);
					}
				}
				
				// need to make this a class
				$active = ($img['media_hide'] == 1) ? " style='border: 1px solid #f00;'" : " style='border: 1px solid #fff;'";
				$poster = '';
				
				$add = (!in_array($img['media_mime'], $medias)) ? "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster" : "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster";
				
				$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span style='cursor: default;'><a href='#' rel=\"facebox;height=450;width=810\" style='color: #999;' onclick=\"tagme($id, $img[media_id]); return false;\"><img src='$thumb' title='" . strip_tags($img['title']) . "'{$active} /></a></span>$add</li>\n\n";
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
	
	
	function getExhibitImages($id=0)
	{
		$OBJ =& get_instance();
		global $go, $default, $medias, $default;

		$body = "<ul id='boxes'>\n";

		// the images
		// how do we get the section in here?
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects_prefs, ".PX."objects  
			WHERE media_ref_id = id 
			AND section_id = '" . $this->rs['section_id'] . "'  
			AND media_mime IN ('" . implode('\', \'', $medias) . "') 
			AND media_order = (SELECT MIN(media_order) FROM ".PX."media WHERE media_ref_id = id) 
			AND section_top != '1' 
			GROUP BY media_id 
			ORDER BY ord ASC"); 

		// set the width of the popup...deals with tags
		$site_vars = unserialize($OBJ->access->settings['site_vars']);
		$width = ($site_vars['site_tags'] == 1) ? 800 : 450;

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
				
				//$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span class='drag-img'><img src='$thumb' title='" . strip_tags($img[media_title]) . "'{$active} /></span>$add<br /><a href='#' onclick=\"deleteImage($img[media_id], '$img[media_file]'); return false;\" style='color: #999;'><img src='asset/img/img-delete.gif' title='".$OBJ->lang->word('delete')."' style='width: 11px; height: 11px;' /></a> <a href='?a=system&amp;q=img&amp;id=$img[media_id]' rel=\"shadowbox;player=iframe;height=400;width=810\" style='color: #999;'><img src='asset/img/img-edit.gif' title='".$OBJ->lang->word('edit')."' style='width: 11px; height: 11px;' /></a></li>\n\n";
				
				$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span style='cursor: default;'><img src='$thumb' title='" . strip_tags($img[title]) . "'{$active} /></span>$add</li>\n\n";
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
	
	function displaySwitches($switcher=0)
	{
		global $default;
		
		//print_r($this->rs); exit;
		
		// only needed if we have more than one
		if (count($default['filesource']) == 1) return;

		load_helper('html');
		
		$html = '';
		
		// we need page info tell us if we are at a section...etc...
		// is that possible?
		foreach ($default['filesource'] as $key => $switch)
		{
			//if (($key == 1) && ($this->rs['id'] != 1)) continue;
			//if (($key == 2) && ($this->rs['section_top'] != 1)) continue;
			
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
		global $default, $medias;

		$out = array(); $i = 0;
		
		//media_id,media_ref_id,media_mime,media_tags,media_title,media_caption,media_x,media_y,media_thx,media_thy,media_uploaded,media_file,media_path,media_file,media_thumb,media_thumb_path,title,url,new 
		
		// get images
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."tagged, ".PX."tags, ".PX."objects   
			WHERE tagged_id = '" . $OBJ->vars->exhibit['obj_ref_id'] . "'  
			AND media_mime IN ('" . implode('\', \'', $medias) . "') 
			AND tagged_id = tag_id 
			AND tagged_obj_id = media_id 
			AND tagged_object = 'img' 
			AND media_hide != '1' 
			AND media_ref_id = id 
			GROUP BY media_id 
			ORDER BY media_uploaded DESC");

		if ($imgs)
		{
			$IMG =& load_class('media', true, 'lib');
	
			foreach($imgs as $key => $do)
			{
				if (in_array($do['media_mime'], $default['images']))
				{
					$filename = $IMG->autoResize($do, $OBJ->vars->exhibit);
					
					//print_r($do); print_r($OBJ->vars->exhibit); exit;
					
					//$path = ($do['media_dir'] == '') ? GIMGS : "/files/$do[media_dir]";
					$path = "/files/dimgs";
	
					// get info about images
					//$size = @getimagesize(DIRNAME . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file']);
					//$sizeth = @getimagesize(DIRNAME . $path . '/' . $filename);

					$out[$i][$key]['media_id'] = $do['media_id'];
					$out[$i][$key]['media_flickr'] = false;
					$out[$i][$key]['media_ref_id'] = $do['media_ref_id'];
					$out[$i][$key]['media_mime'] = $do['media_mime'];
					$out[$i][$key]['media_tags'] = $do['media_tags']; // should we get tags here?
					$out[$i][$key]['media_title'] = $do['media_title'];
					$out[$i][$key]['media_caption'] = $do['media_caption'];
					//$out[$i][$key]['media_x'] = $size[0];
					//$out[$i][$key]['media_y'] = $size[1];
					//$out[$i][$key]['media_thx'] = $sizeth[0];
					//$out[$i][$key]['media_thy'] = $sizeth[1];
					$out[$i][$key]['media_uploaded'] = $do['media_uploaded'];
					$out[$i][$key]['media_file'] = $do['media_file']; // should not have the id appended to it
					$out[$i][$key]['media_path'] = $OBJ->baseurl . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
					$out[$i][$key]['media_thumb'] = $filename;
					$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . $path . '/' . $filename;
					
					$out[$i][$key]['title'] = $do['title'];
					$out[$i][$key]['url'] = $do['url'];
					$out[$i][$key]['new'] = $do['new'];
					$out[$i][$key]['link'] = $do['link'];
					$out[$i][$key]['target'] = $do['target'];
				}
				else
				{
						if ($do['media_thumb'] != '')
						{
							//echo "$do[media_file] ";
							//$filename = $IMG->autoResize($do, $OBJ->vars->exhibit);
							$filename = $IMG->autoResize($do, $OBJ->vars->exhibit);
							
							$path = "/files/dimgs";

							// get info about images
							//$size = @getimagesize(DIRNAME . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_thumb']);
							//$sizeth = @getimagesize(DIRNAME . GIMGS . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb']);
							
							// get info about images
							//$size = @getimagesize(DIRNAME . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file']);
							//$sizeth = @getimagesize(DIRNAME . $path . '/' . $filename);

							$out[$i][$key]['media_id'] = $do['media_id'];
							$out[$i][$key]['media_flickr'] = false;
							$out[$i][$key]['media_ref_id'] = $do['media_ref_id'];
							$out[$i][$key]['media_mime'] = $do['media_mime'];
							$out[$i][$key]['media_tags'] = $do['media_tags']; // ???
							$out[$i][$key]['media_title'] = $do['media_title'];
							$out[$i][$key]['media_caption'] = $do['media_caption'];
							//$out[$i][$key]['media_x'] = $do['media_x'];
							//$out[$i][$key]['media_y'] = $do['media_y'];
							//$out[$i][$key]['media_thx'] = $sizeth[0];
							//$out[$i][$key]['media_thy'] = $sizeth[1];
							$out[$i][$key]['media_uploaded'] = $do['media_uploaded'];
							$out[$i][$key]['media_file'] = $do['media_file']; // should not have the id appended to it
							//$out[$i][$key]['media_path'] = $OBJ->baseurl . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
							//$out[$i][$key]['media_thumb'] = 'th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
							//$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . GIMGS . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
							$out[$i][$key]['media_path'] = $OBJ->baseurl . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
							$out[$i][$key]['media_thumb'] = $filename;
							$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . $path . '/' . $filename;
							
							//$out[$i][$key]['media_thumb'] = $filename;
							//$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . GIMGS . '/' . $filename;
							
							$out[$i][$key]['title'] = $do['title'];
							$out[$i][$key]['url'] = $do['url'];
							$out[$i][$key]['link'] = $do['link'];
							$out[$i][$key]['target'] = $do['target'];
						}
					//}
				}

				$i++;
			}
		}

		return $out;
	}
}