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

class FilesourceAll 
{
	public $rs = array();
	
	public function getExhibitImages($id=0)
	{
		$OBJ =& get_instance();
		global $go, $default, $medias;

		$body = "<ul id='boxes'>\n";

		// the images
		// we need the section id here
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."sections, ".PX."objects  
			WHERE media_ref_id = id    
			AND media_mime IN ('" . implode('\', \'', $medias) . "') 
			AND media_order = (SELECT MIN(media_order) FROM ".PX."media WHERE media_ref_id = id) 
			AND section_id = secid 
			AND object = 'exhibits' 
			AND status = '1' 
			AND media_source = '0' 
			GROUP BY id 
			ORDER BY sec_ord ASC, ord ASC");
			
		if ($imgs)
		{
			// get subsections order
			$subs = $OBJ->db->fetchArray("SELECT sub_id, sec_ord, sub_order, secid 
				FROM ".PX."sections, ".PX."subsections 
				WHERE sub_sec_id = secid 
				GROUP BY sub_id 
				ORDER BY sec_ord ASC, sub_order ASC");
				
			// rewrite the subs order
			if ($subs)
			{
				foreach ($subs as $sub)
				{
					$new_subs[$sub['sub_id']] = $sub;
				}
			}
			else
			{
				$new_subs = array();
			}
			
			foreach ($imgs as $rw)
			{
				// order by section sub and ord
				if ($rw['section_sub'] != '')
				{
					$new[$rw['sec_ord']][$new_subs[$rw['section_sub']]['sub_order']][$rw['ord']] = $rw;
				}
				else // standard
				{
					$new[$rw['sec_ord']][0][] = $rw;
				}
			}
		}
		
		$this->deep_ksort($new);
		$imgs = $this->reformat($new);

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
				$poster = '';
				
				$add = (!in_array($img['media_mime'], $medias)) ? "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster" : "<div style='position: absolute; z-index: 1; top: 1px; right: 0px; height: 9px; border-bottom: 1px solid #fff; border-left: 1px solid #fff; color: #fff; padding: 0 1px; font-weight: bold; text-transform: uppercase; font-size: 8px;' class='file-$img[media_mime]'>$img[media_mime]</div>$poster";
				
				$body .= "<li class='box' id='box$img[media_id]' style='position: relative;' title='$img[media_file]'><span style='cursor: default;'><img src='$thumb' title='" . strip_tags($img['title']) . "'{$active} /></span>$add</li>\n\n";
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
	
	public function displaySwitches2($switcher=0)
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
	
	
	public function deep_ksort(&$arr) 
	{ 
		$a =  array();
		
		if (!empty($arr))
		{
	   	ksort($arr); 
		    foreach ($arr as &$a) { 
		        if (is_array($a) && !empty($a)) { 
		            $this->deep_ksort($a); 
		        } 
		    }
		}
	
		return $a;
	}
	
	
	public function reformat($arr)
	{
		$x = array();
		
		if (!empty($arr))
		{
			foreach ($arr as $a)
			{
				//ksort($a);
			
				foreach ($a as $b)
				{
					//ksort($b);
				
					foreach ($b as $c)
					{
						$x[] = $c;
					}
				}
			}
		}
		
		return $x;
	}
	

	public function getDisplayImages()
	{
		$OBJ =& get_instance();
		global $rs, $default, $medias;

		$out = array(); $i = 0;

		// get images
		$this->medias = (empty($OBJ->vars->media)) ? $medias : $OBJ->vars->media;
			
		$imgs = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."sections, ".PX."objects  
			WHERE media_ref_id = id    
			AND media_mime IN ('" . implode('\', \'', $this->medias) . "') 
			AND media_order = (SELECT MIN(media_order) FROM ".PX."media WHERE media_ref_id = id) 
			AND section_id = secid 
			AND object = 'exhibits' 
			AND status = '1'  
			AND media_source = '0' 
			GROUP BY id 
			ORDER BY sec_ord ASC, ord ASC");
			
		$new = array();
	
		if ($imgs)
		{
			// get subsections order
			$subs = $OBJ->db->fetchArray("SELECT sub_id, sec_ord, sub_order, secid 
				FROM ".PX."sections, ".PX."subsections 
				WHERE sub_sec_id = secid 
				GROUP BY sub_id 
				ORDER BY sec_ord ASC, sub_order ASC");
				
			// rewrite the subs order
			if ($subs)
			{
				foreach ($subs as $sub)
				{
					$new_subs[$sub['sub_id']] = $sub;
				}
			}
			else
			{
				$new_subs = array();
			}
			
			foreach ($imgs as $rw)
			{
				// order by section sub and ord
				if ($rw['section_sub'] != '')
				{
					$top = ($rw['subdir'] == 1) ? 0 : $rw['ord'];
					$new[$rw['sec_ord']][$rw['section_sub']][$top] = $rw;
				}
				else // standard
				{
					$new[$rw['sec_ord']][0][] = $rw;
				}
			}
		}
		
		if (!empty($new)) $this->deep_ksort($new);
		$imgs = $this->reformat($new);

		if (!empty($imgs))
		{
			$IMG =& load_class('media', true, 'lib');
	
			foreach($imgs as $key => $do)
			{
				if (in_array($do['media_mime'], $default['images']))
				{
					$filename = $IMG->autoResize($do, $OBJ->vars->exhibit);
					
					$path = "/files/dimgs";

					// get info about images
					//$size = @getimagesize(DIRNAME . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_file']);
					//$sizeth = @getimagesize(DIRNAME . GIMGS . '/' . $filename);

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
					$out[$i][$key]['media_thumb_source'] = $do['media_thumb'];
					
					//$out[$i][$key]['media_thumb'] = $filename;
					//$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . GIMGS . '/' . $filename;
					
					$out[$i][$key]['title'] = $do['title'];
					$out[$i][$key]['url'] = $do['url'];
					$out[$i][$key]['link'] = $do['link'];
					$out[$i][$key]['target'] = $do['target'];
					$out[$i][$key]['media_dir'] = $do['media_dir'];
				}
				else
				{
						if ($do['media_thumb'] != '')
						{
							// get info about images
							//$size = @getimagesize(DIRNAME . GIMGS . '/' . $do['media_ref_id'] . '_' . $do['media_thumb']);
							//$sizeth = @getimagesize(DIRNAME . GIMGS . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb']);
							$filename = $IMG->autoResize($do, $OBJ->vars->exhibit);
							
							$path = "/files/dimgs";

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
							$out[$i][$key]['media_path'] = $OBJ->baseurl . $path . '/' . $do['media_ref_id'] . '_' . $do['media_file'];
							$out[$i][$key]['media_thumb'] = $filename;
							$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . $path . '/' . $filename;
							$out[$i][$key]['media_thumb_source'] = $do['media_thumb'];
							
							//$out[$i][$key]['media_thumb'] = 'th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
							//$out[$i][$key]['media_thumb_path'] = $OBJ->baseurl . GIMGS . '/th-' . $do['media_ref_id'] . '_' . $do['media_thumb'];
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