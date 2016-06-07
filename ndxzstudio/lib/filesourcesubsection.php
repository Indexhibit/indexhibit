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

class FilesourceSubSection
{
	public $rs = array();

	function getExhibitImages($id=0)
	{
		$OBJ =& get_instance();
		global $go, $medias, $default;

		$body = "<ul id='boxes'>\n";

		// the images
		// how do we get the section in here?
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects_prefs, ".PX."objects  
			WHERE media_ref_id = id 
			AND section_id = '" . $this->rs['section_id'] . "'  
			AND section_sub = '" . $this->rs['section_sub'] . "' 
			AND subdir = '0'  
			AND media_mime IN ('" . implode('\', \'', $medias) . "') 
			AND media_order = (SELECT MIN(media_order) FROM ".PX."media WHERE media_ref_id = id) 
			AND section_top != '1' 
			AND status = '1' 
			AND hidden != '1' 
			GROUP BY media_ref_id 
			ORDER BY ord ASC"); 

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
				//$poster = ($poster == true) ? "<div style='background: #fff; position: absolute; z-index: 1; bottom: 1px; right: 0px; height: 9px; border: 1px solid #fff; color: #000; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;'>P</div>" : '';
				
				$poster = '';
				
				$add = (!in_array($img['media_mime'], $medias)) ? "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster" : "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster";
				
				//$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span class='drag-img'><img src='$thumb' title='" . strip_tags($img[media_title]) . "'{$active} /></span>$add<br /><a href='#' onclick=\"deleteImage($img[media_id], '$img[media_file]'); return false;\" style='color: #999;'><img src='asset/img/img-delete.gif' title='".$OBJ->lang->word('delete')."' style='width: 11px; height: 11px;' /></a> <a href='?a=system&amp;q=img&amp;id=$img[media_id]' rel=\"shadowbox;player=iframe;height=400;width=810\" style='color: #999;'><img src='asset/img/img-edit.gif' title='".$OBJ->lang->word('edit')."' style='width: 11px; height: 11px;' /></a></li>\n\n";
				
				$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span style='cursor: default;'><img src='$thumb' title='" . strip_tags($img['title']) . "'{$active} /></span>$add</li>\n\n";
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

	
	function switchInterface()
	{
		$OBJ =& get_instance(); global $default;
		
		$OBJ->lib_class('options');
		$OBJ->vars->format_params = $OBJ->options->getParameters($OBJ->vars->exhibit['format']);
		
		return $OBJ->options->switchInterface();
	}
	
	
	function editOptions()
	{
		$OBJ =& get_instance(); global $default;
		
		$OBJ->lib_class('options');
		$OBJ->vars->format_params = $OBJ->options->getParameters($OBJ->vars->exhibit['format']);
		
		return $OBJ->options->editOptions();
	}
	

	function getDisplayImages()
	{
		$OBJ =& get_instance();
		global $default, $medias;

		$out = array(); $i = 0;
		
		//media_id,media_ref_id,media_mime,media_tags,media_title,media_caption,media_x,media_y,media_thx,media_thy,media_uploaded,media_file,media_path,media_file,media_thumb,media_thumb_path,title,url,new 
		
		// put in a hook to make this the opposite...
		//AND hidden != '1' 
		
		$this->medias = (empty($OBJ->vars->media)) ? $medias : $OBJ->vars->media;
		
		// get images
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects_prefs, ".PX."objects  
			WHERE media_ref_id = id 
			AND section_id = '" . $OBJ->vars->exhibit['section_id'] . "'  
			AND section_sub = '" . $OBJ->vars->exhibit['section_sub'] . "' 
			AND subdir = '0' 
			AND media_mime IN ('" . implode('\', \'', $this->medias) . "') 
			AND media_order = (SELECT MIN(media_order) FROM ".PX."media WHERE media_ref_id = id)  
			AND section_top != '1'   
			AND status = '1' 
			AND hidden != '1' 
			GROUP BY id 
			ORDER BY ord ASC");
			
		//print_r($imgs); exit;

		if ($imgs)
		{
			$IMG =& load_class('media', true, 'lib');
	
			foreach($imgs as $key => $do)
			{
				if (in_array($do['media_mime'], $default['images']))
				{
					$filename = $IMG->autoResize($do, $OBJ->vars->exhibit);
					
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
					$out[$i][$key]['media_dir'] = $do['media_dir'];
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
							$out[$i][$key]['media_dir'] = $do['media_dir'];
						}
					//}
				}

				$i++;
			}
		}

		return $out;
	}
}