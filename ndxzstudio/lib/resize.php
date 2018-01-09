<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Resize class
* Dependent upon Media class
* Mostly for dynamic resize at frontend
* 
* @version 1.0
* @author Vaska 
*/
class Resize
{
	public $resize = array();
	public $make_sys = false;
	public $makethumb = false;
	public $make_image = true;
	public $thumbsize = 200;
	public $shape = 0;
	public $maxsize = 530;
	public $quality = 100;
	public $path;
	public $input_file_path; // ???
	public $media_ref_id;
	public $media_file;
	
	public function __construct()
	{
		$this->resize = load_class('media', TRUE, 'lib');
		
		$this->path = DIRNAME . '/files/dimgs/';
	}
	
	//$R->reformat($new_width, $this->force_height, $size, $go, $OBJ->vars->exhibit['id'], $name);
	public function reformat($width, $height, $size, $img, $id=0, $name, $dir='')
	{
		global $default;
		
		$dir = GIMGS;
		
		// image
		if (in_array($img['media_mime'], $default['images'])) 
		{
			$file = $img['media_file'];
			
			if ($img['media_dir'] != '') $dir = "/files/$img[media_dir]";
		}
		else // video and other
		{
			$file = $img['media_thumb_source'];

			//if ($img['media_dir'] != '') $dir = "/files/$img[media_dir]";
		}
		
		// get the mime
		$tmp = explode('.', $file);
		$mime = array_pop($tmp);
		$mime = strtolower($mime);
		
		//$dir = ($dir == '') ? GIMGS : "/files/$dir";
		//$dir = GIMGS;
		
		$source_image = DIRNAME . $dir . '/' . $file;
		
		///echo $source_image . '  //// ';
		
		// let's go
		switch($mime)
		{
            case 'gif':
				$image = imagecreatefromgif($source_image);
                break;
            case 'jpg':
                $image = imagecreatefromjpeg($source_image);
                break;
			case 'jpeg':
				$image = imagecreatefromjpeg($source_image);
				break;
            case 'png':
                $image = imagecreatefrompng($source_image);
                break;
        }

		$output_image = imagecreatetruecolor($width, $height);

		// resizing
		@imagecopyresampled($output_image,  $image, 0, 0, 0, 0,
			$width, $height, $size[0], $size[1]);
			
		$output_image_path = DIRNAME . '/files/dimgs/' . $name;
			
		switch($mime) {
			case 'gif':
				imagegif($output_image, $output_image_path);
				break;
			case 'jpg':
				imagejpeg($output_image, $output_image_path, 100);
				break;
			case 'jpeg':
				imagejpeg($output_image, $output_image_path, 100);
				break;
			case 'png':
				imagepng($output_image, $output_image_path);
				break;
		}

		imagedestroy($image);
		
		//if (function_exists('chmod')) chmod($out, 0777);
		
		return;
	}
	
	public function setDefaults()
	{
		$this->resize->make_sys 	= $this->make_sys;
		$this->resize->makethumb 	= $this->makethumb;
		$this->resize->make_image 	= $this->make_image;
		$this->resize->thumbsize 	= $this->thumbsize;
		$this->resize->shape 		= $this->shape;
		$this->resize->maxsize 		= $this->maxsize;
		$this->resize->quality 		= $this->quality;
		$this->resize->path 		= $this->path;
	}
	
	public function doResize()
	{
		$test 						= explode('.', strtolower($this->media_file));
		$thetype 					= array_pop($test);

		$this->resize->type 		= '.' . $thetype;
		$this->resize->filename 	= $test[0] . '.' . $thetype;
		$this->resize->origname 	= $this->media_file;

		$this->resize->id 			= $this->media_ref_id . '_';
		$this->resize->filename 	= $this->resize->filename;

		$this->resize->image 		= DIRNAME . GIMGS . '/' . $this->resize->filename;
		$this->resize->uploader();
	}
	
	// special function for...
	// this is a resize function but does not work with the other functions
	public function resize_images($id=0, $size=9999, $type='image')
	{
		$OBJ =& get_instance();
		global $default;
		
		// query for all images
		$images = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects 
			WHERE media_ref_id = '$id' 
			AND media_ref_id = id 
			AND media_obj_type = 'exhibit' 
			ORDER BY media_order ASC");
			
		if ($images[0]['media_source'] >= 1) exit;
			
		// let's delete first
		if ($images)
		{
			// yeah, we should consolidate our files better
			// this should go into a 'files' helper or something
			load_helper('files');

			foreach ($images as $image)
			{
				// check the mime
				if (in_array($image['media_mime'], $default['images']))
				{
					$file = $image['media_ref_id'] . '_' . $image['media_file'];
				}
				else
				{
					$file = ($image['media_thumb'] != '') ? $image['media_ref_id'] . '_' . $image['media_thumb'] : '';
				}
				
				($type == 'image') ? 
					delete_image(DIRNAME . GIMGS . '/' . $file) : 
					delete_image(DIRNAME . GIMGS . '/th-' . $file);
			}
			
			//load_module_helper('files', $go['a']);
			$IMG =& load_class('media', TRUE, 'lib');
			
			if ($type == 'image')
			{
				$IMG->make_sys = false;
				$IMG->makethumb = false;
				$IMG->make_image = true;
			}
			else
			{
				$IMG->make_sys = false;
				$IMG->makethumb = true;
				$IMG->make_image = false;
			}

			// we'll query for all our defaults first...
			$rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape  
				FROM ".PX."objects    
				WHERE id = '$id'");

			// we need to get these from some defaults someplace
			$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
			$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
			$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
			$IMG->quality = $default['img_quality'];
			$IMG->path = DIRNAME . GIMGS . '/';

			//load_helper('output');
			$URL =& load_class('publish', TRUE, 'lib');
			
			// do the resizery
			foreach ($images as $image)
			{
				if (in_array($image['media_mime'], $default['images']))
				{
					$test = explode('.', strtolower($image['media_file']));
					$thetype = array_pop($test);

					$IMG->type = '.' . $thetype;
					$IMG->filename = $test[0] . '.' . $thetype;
					$IMG->origname = $IMG->filename;
				
					$IMG->id = $id . '_';
					$IMG->filename = $IMG->filename;
				
					$IMG->image = $IMG->path . '/' . $IMG->filename;
					
					// check for black and white flag
					// another time...
					if (preg_match('/^bw/', $IMG->filename))
					{
						$IMG->bw_flag = true;
						//$test[0] = str_replace('bw_', '', $test[0]);
					}

					$IMG->uploader();
				}
				else
				{
					if ($image['media_thumb'] != '')
					{
						$test = explode('.', strtolower($image['media_thumb']));
						$thetype = array_pop($test);

						$IMG->type = '.' . $thetype;
						$IMG->filename = $test[0] . '.' . $thetype;
						$IMG->origname = $IMG->filename;
				
						$IMG->id = $go['id'] . '_';
						$IMG->filename = $IMG->filename;
				
						$IMG->image = $IMG->path . '/' . $IMG->filename;
						
						// check for black and white flag
						// another time...
						if (preg_match('/^bw/', $IMG->filename))
						{
							$IMG->bw_flag = true;
							//$test[0] = str_replace('bw_', '', $test[0]);
						}

						$IMG->uploader();
					}
				}
			
				//@chmod($IMG->path . '/' . $IMG->filename, 0755);
			}
		}

		return;
	}
	
	
	// special function for...
	// this is a resize function but does not work with the other functions
	public function folder_load_images($images=array(), $id=0, $size=9999, $type='image', $dir)
	{
		$OBJ =& get_instance();
		global $default;
		
		// we should check to make sure the files are of the allowed type/mime
		foreach ($images as $img)
		{
			$mime = array_pop( explode('.', $img) );
			
			$size = getimagesize(DIRNAME . '/files/' . $dir . '/' . $img);

			$clean['media_ref_id'] = $id;
			$clean['media_order'] = 999;
			$clean['media_obj_type'] = 'exhibits';
			$clean['media_file'] = $img;
			$clean['media_mime'] = $mime;
			$clean['media_dir'] = $dir;
			$clean['media_x'] = $size[0];
			$clean['media_y'] = $size[1];
			$clean['media_kb'] = @filesize(DIRNAME . '/files/' . $dir . '/' . $img);
			$clean['media_udate'] = getNow();
			$clean['media_uploaded'] = getNow();
			
			$OBJ->db->insertArray(PX.'media', $clean);
		}
		
		//echo 'YES! '; exit;
		
		// query for all images
		$images = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects 
			WHERE media_ref_id = '$id' 
			AND media_ref_id = id 
			AND media_obj_type = 'exhibit' 
			AND media_dir != '' 
			ORDER BY media_order ASC");
			
		if ($images[0]['media_source'] >= 1) exit;
			
		// let's delete first
		if ($images)
		{
			// yeah, we should consolidate our files better
			// this should go into a 'files' helper or something
			load_helper('files');
			
			//load_module_helper('files', $go['a']);
			$IMG =& load_class('media', TRUE, 'lib');
			
			$IMG->make_sys = true;
			$IMG->makethumb = true;
			$IMG->make_image = true;

			// we'll query for all our defaults first...
			$rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape  
				FROM ".PX."objects    
				WHERE id = '$id'");

			// we need to get these from some defaults someplace
			$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
			$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
			$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
			$IMG->quality = $default['img_quality'];
			$IMG->path = DIRNAME . '/files/' . $dir . '/';

			//load_helper('output');
			$URL =& load_class('publish', TRUE, 'lib');
			
			// do the resizery
			foreach ($images as $image)
			{
				if (in_array($image['media_mime'], $default['images']))
				{
					$test = explode('.', strtolower($image['media_file']));
					$thetype = array_pop($test);

					$IMG->type = '.' . $thetype;
					$IMG->filename = $test[0] . '.' . $thetype;
					$IMG->origname = $IMG->filename;
				
					$IMG->id = $id . '_';
					$IMG->filename = $IMG->filename;
				
					$IMG->image = $IMG->path . '/' . $IMG->filename;
					
					// check for black and white flag
					// another time...
					if (preg_match('/^bw/', $IMG->filename))
					{
						$IMG->bw_flag = true;
						//$test[0] = str_replace('bw_', '', $test[0]);
					}
					
					$IMG->output_path = DIRNAME . GIMGS . '/';
					$IMG->uploader();
				}
				else
				{
					if ($image['media_thumb'] != '')
					{
						$test = explode('.', strtolower($image['media_thumb']));
						$thetype = array_pop($test);

						$IMG->type = '.' . $thetype;
						$IMG->filename = $test[0] . '.' . $thetype;
						$IMG->origname = $IMG->filename;
				
						$IMG->id = $go['id'] . '_';
						$IMG->filename = $IMG->filename;
				
						$IMG->image = $IMG->path . '/' . $IMG->filename;
						
						// check for black and white flag
						// another time...
						if (preg_match('/^bw/', $IMG->filename))
						{
							$IMG->bw_flag = true;
							//$test[0] = str_replace('bw_', '', $test[0]);
						}
						
						$IMG->output_path = DIRNAME . GIMGS . '/';
						$IMG->uploader();
					}
				}
			
				//@chmod($IMG->path . '/' . $IMG->filename, 0755);
			}
		}
		
		// can we get to the reload here?
		$OBJ->template->onload[] = "parent.updateImages();";

		//return;
	}
	
	// special function for...
	// this is a resize function but does not work with the other functions
	public function single_load_image($img='', $id=0, $size=9999, $type='image', $dir)
	{
		$OBJ =& get_instance();
		global $default;
		
		//echo 'The id: ' . $id; exit;
		
		// we should check to make sure the files are of the allowed type/mime
		//foreach ($images as $img)
		//{
			$mime = explode('.', $img);
			$mime = array_pop($mime);
			
			$size = getimagesize(DIRNAME . '/files/' . $dir . '/' . $img);

			$clean['media_ref_id'] = $id;
			$clean['media_order'] = 999;
			$clean['media_obj_type'] = 'exhibits';
			$clean['media_file'] = $img;
			$clean['media_mime'] = $mime;
			$clean['media_dir'] = $dir;
			$clean['media_x'] = $size[0];
			$clean['media_y'] = $size[1];
			$clean['media_kb'] = @filesize(DIRNAME . '/files/' . $dir . '/' . $img);
			$clean['media_udate'] = getNow();
			$clean['media_uploaded'] = getNow();
			
			$last = $OBJ->db->insertArray(PX.'media', $clean);
			
			//echo $dir . ' / ' . $last;
		//}
		
		//echo 'YES! '; exit;
		
		// query for all images
		$images = $OBJ->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects 
			WHERE media_ref_id = id 
			AND media_id = '$last' 
			ORDER BY media_order ASC");
			
		if ($images[0]['media_source'] >= 1) exit;
			
		// let's delete first
		if ($images)
		{
			// yeah, we should consolidate our files better
			// this should go into a 'files' helper or something
			load_helper('files');
			
			//load_module_helper('files', $go['a']);
			$IMG =& load_class('media', TRUE, 'lib');
			
			$IMG->make_sys = true;
			$IMG->makethumb = true;
			$IMG->make_image = true;

			// we'll query for all our defaults first...
			$rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape  
				FROM ".PX."objects    
				WHERE id = '$id'");

			// we need to get these from some defaults someplace
			$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
			$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
			$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
			$IMG->quality = $default['img_quality'];
			$IMG->path = DIRNAME . '/files/' . $dir . '/';

			//load_helper('output');
			$URL =& load_class('publish', TRUE, 'lib');
			
			// do the resizery
			foreach ($images as $image)
			{
				if (in_array($image['media_mime'], $default['images']))
				{
					$test = explode('.', $image['media_file']);
					$thetype = array_pop($test);

					$IMG->type = '.' . $thetype;
					$IMG->filename = $image['media_file'];
					$IMG->origname = $IMG->filename;
				
					$IMG->id = $id . '_';
					$IMG->filename = $IMG->filename;
				
					$IMG->image = $IMG->path . '/' . $IMG->filename;
					
					// check for black and white flag
					// another time...
					if (preg_match('/^bw/', $IMG->filename))
					{
						$IMG->bw_flag = true;
						//$test[0] = str_replace('bw_', '', $test[0]);
					}
					
					@chmod($IMG->path . '/' . $IMG->filename, 0755);
					$IMG->output_path = DIRNAME . GIMGS . '/';
					$IMG->uploader();
				}
				else
				{
					if ($image['media_thumb'] != '')
					{
						$test = explode('.', strtolower($image['media_thumb']));
						$thetype = array_pop($test);

						$IMG->type = '.' . $thetype;
						$IMG->filename = $test[0] . '.' . $thetype;
						$IMG->origname = $IMG->filename;
				
						$IMG->id = $go['id'] . '_';
						$IMG->filename = $IMG->filename;
				
						$IMG->image = $IMG->path . '/' . $IMG->filename;
						
						// check for black and white flag
						// another time...
						if (preg_match('/^bw/', $IMG->filename))
						{
							$IMG->bw_flag = true;
							//$test[0] = str_replace('bw_', '', $test[0]);
						}
						
						@chmod($IMG->path . '/' . $IMG->filename, 0755);
						$IMG->output_path = DIRNAME . GIMGS . '/';
						$IMG->uploader();
					}
				}
			
				//@chmod($IMG->path . '/' . $IMG->filename, 0755);
			}
		}
		else
		{
			// all the other images here...
		}
		
		// can we get to the reload here?
		$OBJ->template->onload[] = "parent.updateImages();";

		//return;
	}
}