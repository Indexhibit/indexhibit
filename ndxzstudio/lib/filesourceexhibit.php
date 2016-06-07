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

class FilesourceExhibit 
{
	var $rs = array();
	var $medias = array();
	
	public function getExhibitImages($id=0)
	{
		$OBJ =& get_instance();
		global $go, $default, $medias;

		$body = "<ul id='boxes'>\n";

		// the images
		//AND media_obj_type = 'exhibit'
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media 
			WHERE media_ref_id = '$id' 
			ORDER BY media_order ASC, media_id ASC");

		// set the width of the popup...deals with tags
		$site_vars = unserialize($OBJ->access->settings['site_vars']);
		$width = ($site_vars['tags'] == 1) ? 800 : 450;

		if ($imgs)
		{
			foreach ($imgs as $img)
			{
				$path = GIMGS; $cover = false;

				if (!in_array($img['media_mime'], $default['images']))
				{
					if ($img['media_thumb'] == '')
					{
						$thumb = 'asset/img/thumb-default.gif';
					}
					else
					{
						$thumb = BASEURL . $path . '/sys-' . $img['media_ref_id'] . '_' . $img['media_thumb'];
						$cover = true;
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
						$cover = true;
					}
				}
				
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

				$cover = ($cover == true) ? "<div style='background: #000; position: absolute; z-index: 1; bottom: 1px; right: 0px; height: 9px; border: 1px solid #000; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;'>C</div>" : '';
				
				$add = (!in_array($img['media_mime'], $medias)) ? "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$cover" : "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$cover";
				
				$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span class='drag-img'><img src='$thumb' title='" . strip_tags($img['media_title']) . "'{$active} /></span>$add<br /><a href='#' onclick=\"deleteImage($img[media_id], '$img[media_file]'); return false;\" style='color: #999;'><img src='asset/img/img-delete.gif' title='".$OBJ->lang->word('delete')."' style='width: 11px; height: 11px;' /></a> <a href='?a=system&amp;q=img&amp;id=$img[media_id]' rel=\"facebox;height=450;width=810\" style='color: #999;'><img src='asset/img/img-edit.gif' title='".$OBJ->lang->word('edit')."' style='width: 11px; height: 11px;' /></a></li>\n\n";

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

	
	public function switchInterface()
	{
		$OBJ =& get_instance(); global $default;
		
		$OBJ->lib_class('options');
		$OBJ->vars->format_params = $OBJ->options->getParameters($OBJ->vars->exhibit['format']);
		
		return $OBJ->options->switchInterface();
	}
	
	public function editOptions()
	{
		$OBJ =& get_instance(); global $default;
		
		$OBJ->lib_class('options');
		$OBJ->vars->format_params = $OBJ->options->getParameters($OBJ->vars->exhibit['format']);
		
		return $OBJ->options->editOptions();
	}
	

	public function getDisplayImages()
	{
		$OBJ =& get_instance();
		global $default, $medias;
		
		// what about exhibit images, full index, section index, tagged index???

		// add the exhibit to the beginning of the page
		// it will always have precedence
		//if (is_array($OBJ->vars->exhibit['exh_id']))
		//{
		//	array_unshift($OBJ->vars->exhibit['exh_id'], $OBJ->vars->exhibit['id']);
		//}
		//else
		//{
			$OBJ->vars->exhibit['exh_id'][] = $OBJ->vars->exhibit['id'];
		//}

		$out = array(); $i = 0;
		
		// we need to add the ability to add more formats - assuming people
		// have a way to handle them
		$this->medias = (empty($OBJ->vars->media)) ? $medias : $OBJ->vars->media;
		
		foreach ($OBJ->vars->exhibit['exh_id'] as $do)
		{
			$imgs = $OBJ->db->fetchArray("SELECT * 
				FROM ".PX."media, ".PX."objects_prefs, ".PX."objects  
				WHERE media_ref_id = '$do' 
				AND obj_ref_type = media_obj_type 
				AND id = '$do' 
				AND media_hide = '0'  
				AND media_mime IN ('" . implode('\', \'', $this->medias) . "') 
				ORDER BY media_order ASC, media_id ASC");
				
			//print_r($imgs); exit;

			if ($imgs)
			{
				foreach($imgs as $key => $do)
				{
					$path = GIMGS;

					if (in_array($do['media_mime'], $default['images']))
					{
						// get info about images
						$size = @getimagesize(DIRNAME . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file']);
						//$sizeth = @getimagesize(DIRNAME . $path . '/th-' . $do['media_ref_id'] . '_' . $do['media_file']);

						$out[$i][$key]['media_id'] = $do['media_id'];
						$out[$i][$key]['media_flickr'] = false;
						$out[$i][$key]['media_ref_id'] = $do['media_ref_id'];
						$out[$i][$key]['media_mime'] = $do['media_mime'];
						$out[$i][$key]['media_tags'] = $do['media_tags']; // should we get tags here?
						$out[$i][$key]['media_title'] = $do['media_title'];
						$out[$i][$key]['media_caption'] = $do['media_caption'];
						$out[$i][$key]['media_x'] = $size[0];
						$out[$i][$key]['media_y'] = $size[1];
						//$out[$i][$key]['media_thx'] = $sizeth[0];
						//$out[$i][$key]['media_thy'] = $sizeth[1];
						$out[$i][$key]['media_uploaded'] = $do['media_uploaded'];
						$out[$i][$key]['media_file'] = $do['media_file']; // should not have the id appended to it
						$out[$i][$key]['media_path'] = $OBJ->baseurl . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
						
						$out[$i][$key]['media_thumb_source'] = $do['media_thumb'];
						$thumb = ($do['media_thumb'] != '') ? $do['media_thumb'] : $do['media_file'];
						
						//$out[$i][$key]['media_thumb'] = $OBJ->baseurl . $path . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];

						$out[$i][$key]['media_thumb'] = 'th-' . $do['media_ref_id'] . '_' . $thumb;
						$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . $path . '/th-' . $do['media_ref_id'] . '_' . $thumb;
						
						$out[$i][$key]['title'] = $do['title'];
						$out[$i][$key]['url'] = $do['url'];
						$out[$i][$key]['link'] = '';
						$out[$i][$key]['target'] = '';
						$out[$i][$key]['media_dir'] = $do['media_dir'];
					}
					else
					{
						if ($do['media_thumb'] != '')
						{
						$out[$i][$key]['media_id'] = $do['media_id'];
						$out[$i][$key]['media_flickr'] = false;
						$out[$i][$key]['media_ref_id'] = $do['media_ref_id'];
						$out[$i][$key]['media_mime'] = $do['media_mime'];
						$out[$i][$key]['media_tags'] = $do['media_tags']; // ???
						$out[$i][$key]['media_title'] = $do['media_title'];
						$out[$i][$key]['media_caption'] = $do['media_caption'];
						$out[$i][$key]['media_x'] = $do['media_x'];
						$out[$i][$key]['media_y'] = $do['media_y'];
						//$out[$i][$key]['media_thx'] = $sizeth[0];
						//$out[$i][$key]['media_thy'] = $sizeth[1];
						
						
						$out[$i][$key]['media_uploaded'] = $do['media_uploaded'];
						$out[$i][$key]['media_file'] = $do['media_file']; // should not have the id appended to it
						$out[$i][$key]['media_path'] = $OBJ->baseurl . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
						
						// something to think about here
						// the problem here is that we aren't accessing the true media thumb
						// *****************************************************************
						$out[$i][$key]['media_thumb_source'] = $do['media_thumb'];
						$out[$i][$key]['media_thumb'] = 'th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
						$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . $path . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
						
						// something to think about here
						/*
						if ($do['media_thumb'] != '')
						{
						$out[$i][$key]['media_thumb'] = $OBJ->baseurl . $path . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
						}
						else
						{
						$out[$i][$key]['media_thumb'] = $OBJ->baseurl . '/ndxzsite/img/default-video.gif';
						}
						*/
								
						//$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . '/' . $out[$i][$key]['media_thumb'];
						//$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . $path . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
						//$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . '/files/default_video_panel.jpg';

						$out[$i][$key]['title'] = $do['title'];
						$out[$i][$key]['url'] = $do['url'];
						$out[$i][$key]['link'] = '';
						$out[$i][$key]['target'] = '';
						$out[$i][$key]['media_dir'] = $do['media_dir'];
						}
					}
				}

				$i++;
			}
		}

		return $out;
	}
}