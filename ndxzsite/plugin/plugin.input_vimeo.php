<?php if (!defined('SITE')) exit('No direct script access allowed');
/*
Plugin Name: Vimeo Input
Plugin URI: http://www.indexhibit.org/plugin/asset-edtior/
Description: Adds ability to easily insert Youtubes.
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: global
Hook: system_extension_vimeo
Function: input_vimeo:input_interface
End

Plugin Name: Vimeo Input Link
Type: global
Hook: system_uploader_link
Function: input_vimeo:input_link
End
*/

class input_vimeo
{
	public function __construct()
	{
		
	}
	
	function input_link()
	{
		$OBJ =& get_instance();
		global $go;
		
		return " <a href='?a=system&q=extend&x=vimeo&id=$go[id]' rel=\"facebox;height=300;width=400;modal=true\"><img src='asset/img/vimeo.gif' title='Vimeo' /></a>";
	}

	function input_interface()
	{
		$OBJ =& get_instance();
		global $go;
		
		if (isset($_POST['svc_id'])) $this->sbmt_add_vids();
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		$OBJ->template->pop_location = $OBJ->lang->word('Vimeo');
		
		$OBJ->template->pop_links[] = array($OBJ->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		$body = p("https://player.vimeo.com/video/<span style='color: red;'>6669394</span>");
		
		$body .= "<ul class='ext-files-list'>\n";
		$body .= "<li class='ext-files-list-1' style='width: 200px;'>File</li>\n";
		$body .= "<li class='ext-files-list-2' style='width: 35px;'>Width</li>\n";
		$body .= "<li class='ext-files-list-3' style='width: 35px;'>Height</li>\n";
		$body .= "</ul>\n";
		$body .= "<div style='clear: left;'><!-- --></div>\n";
		
		for ($i=0; $i<3; $i++)
		{
			$body .= "<ul class='ext-files-list'>\n";
			$body .= "<li class='ext-files-list-1'><input type='text' name='svc_id[$i]' value='' style='width: 200px;' /></li>\n";
			$body .= "<li class='ext-files-list-2'><input type='text' name='svc_w[$i]' value='' style='width: 35px;' /></li>\n";
			$body .= "<li class='ext-files-list-3'><input type='text' name='svc_h[$i]' value='' style='width: 35px;' /></li>\n";
			//$body .= "<li class='ext-files-list-4'>" . selector($i) . "</li>\n";
			$body .= "</ul>\n";
			$body .= "<div style='clear: left;'><!-- --></div>\n";
		}
		
		$body .= p("<input type='submit' value='submit' name='add_vids' />");
		
		$OBJ->template->body = $body;
		
		$OBJ->template->output('popup');
		exit;
	}
	
	function sbmt_add_vids()
	{
		global $go;

		$OBJ =& get_instance();
		
		// we need to get the order of things
		$order = $OBJ->db->fetchRecord("SELECT media_order FROM ".PX."media 
			WHERE media_ref_id = '$go[id]' 
			ORDER BY media_order DESC");

		$order = (int) $order['media_order'];

		if (isset($_POST))
		{
			foreach ($_POST['svc_id'] as $key => $input)
			{
				if ($input != '')
				{
					$order++;
					
					// we should do a preg_match here for the url parts
					$tmp = $this->check_vid_url($input);

					// clean tmp value
					$tmp = str_replace('video/', '', $tmp);

					// automake the thumbnail
					//$test 				= explode('.', $tmp . '.vimeo');
					//$thetype 				= array_pop($test);
					$clean['media_thumb'] 	= $tmp . '.jpg';

					// let's hope vimeo doesn't shut this route down
					$data = json_decode( file_get_contents( 'http://vimeo.com/api/oembed.json?url=https://player.vimeo.com/video/' . $tmp ), true );

					$ch = curl_init ($data['thumbnail_url']);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
					$rawdata = curl_exec ($ch);
					curl_close ($ch);
					$fp = fopen(DIRNAME . "/files/gimgs/" . $clean['media_thumb'], 'w');
					fwrite($fp, $rawdata);
					fclose($fp);

					$clean['media_file'] = $tmp . '.vimeo';
					$clean['media_ref_id'] = $go['id'];
					$clean['media_mime'] = 'vimeo';
					$clean['media_x'] = ($_POST['svc_w'][$key] != '') ? $_POST['svc_w'][$key] : 400;
					$clean['media_y'] = ($_POST['svc_h'][$key] != '') ? $_POST['svc_h'][$key] : 300;
					$clean['media_order'] = 999;
					$clean['media_obj_type'] = 'exhibits';
					$clean['media_udate'] = getNow();
					$clean['media_uploaded'] = getNow();
					$clean['media_order'] = $order;
					
					$OBJ->db->insertArray(PX.'media', $clean);
				}
			}
			
			// can we get to the reload here?
			$OBJ->template->onload[] = "parent.updateImages();";
		}
	}

	
	function check_vid_url($url='')
	{
		if (preg_match('/http/i', $url))
		{
			if (preg_match('/youtube/i', $url))
			{
				$out = parse_url($url);
				if (!isset($out['host'])) return '';
				parse_str($out['query'], $q);
				return $q['v'];	
			}
			else if (preg_match('/vimeo/i', $url))
			{
				// probably need to review this one
				$out = explode('com/', $url);
				return $out[1];
			}
			else
			{
				return '';
			}
		}
		else
		{
			return $url;
		}
	}
}