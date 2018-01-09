<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Media class
*
* Resizes and thumbnails images
* 
* @version 1.0
* @author Vaska 
*/
class Media
{
	public $image;
	public $path;
	public $filename;
	public $quality;
	public $filemime;
	public $sizelimit;
	public $maxsize;
	public $thumbsize;
	public $sys_thumb	= 100;
	public $size		= array();
	public $new_size	= array();
	public $final_size	= array();
	public $out_size	= array();
	public $uploads	= array();
	public $offset		= array();
	public $sys_size	= array();
	public $type;
	public $input_image;
	public $upload_max_size;
	public $file_size;
	public $id;
	public $origname;
	public $orig_kb;
	public $bw_flag 	= false;
	public $make_sys	= true;
	public $make_image	= true;
	public $makethumb	= true;
	public $makesysthumb = true;
	public $cinematic 	= false;
	public $shape		= 0;
	public $shapes		= array(0 => 'proportional', 1 => 'square', 2 => '4x3', 3 => 'cinematic', 4 => '3x2');
	public $new_filename = false;
	public $filename_override = false;
	public $output_path;
	public $odim = array();
	
	/**
	* Returns allowed uploads (filetypes from config.php) array and max size
	*
	* @param void
	* @return mixed
	*/
	public function __construct()
	{
		global $default;
		$this->uploads = $default;
		$this->upload_max_size();
	}
	
	/**
	* Creates dynamic thumbnails for displays on all, section and tag pages
	* only outputs to the 'dimgs' folder
	*
	* @param array, array
	* @return void
	*/
	public function autoResize($img=array(), $page=array())
	{
		global $go, $default;
		
		//print_r($page); exit;

		//$IMG =& load_class('media2', true, 'lib');
		$this->thumbsize = ($page['thumbs'] != '') ? $page['thumbs'] : 200;
		$this->maxsize = ($page['images'] != '') ? $page['images'] : 9999;
		$this->maxwidth = $this->maxsize;
		$this->quality = $default['img_quality'];
		$this->shape = ($page['thumbs_shape'] != '') ? $page['thumbs_shape'] : 0;
		$this->make_image = false;
		$this->makethumb = true;
		$this->make_sys	= false;
		$this->makesysthumb = false;
		
		//$this->new_size['w']
		//$this->new_size['h']
		
		// source path
		if ($img['media_dir'] == '')
		{
			$this->path = DIRNAME . GIMGS . '/';
		}
		else // we need to find the path to thumb
		{
			// image uploaded via folder
			if (in_array($img['media_mime'], $default['images']))
			{
				// this works?
				if ($img['media_thumb'] != '')
				{
					$this->path = DIRNAME . "/files/gimgs/";
				}
				else
				{
					$this->path = DIRNAME . "/files/$img[media_dir]/";
				}
			}
			else
			{
				$this->path = DIRNAME . GIMGS . '/';
			}
		}
		
		//$this->path = ($img['media_dir'] == '') ? DIRNAME . GIMGS . '/' : DIRNAME . "/files/$img[media_dir]/";
		$this->output_path = DIRNAME . '/files/dimgs/';
	
		$test = ($img['media_thumb'] == '') ? $img['media_file'] : $img['media_thumb'];
			
		$tmp = ($img['media_thumb'] == '') ? explode('.', $img['media_file']) : 
			explode('.', $img['media_thumb']);
			
		//$test = ($img['media_thumb'] == '') ? explode('.', strtolower($img['media_file'])) : 
		//	explode('.', strtolower($img['media_thumb']));

		$thetype = array_pop($tmp);
		//$new_title = implode('_', $test);
		$new_title = $test;

		$this->type = '.' . $thetype;
		$this->filename = $new_title;
		$this->origname = $this->filename;

		$this->id = $go['id'] . '_';
		$this->filename = $this->filename;

		$this->filename_override = 'thumb_' . $page['thumbs_shape'] . 'x' . $this->thumbsize . '_' . $page['section_id'] . '_' . $img['media_ref_id'] . '_' . $img['media_id'] . '.' . $thetype;
		
		// move this elsewhere later
		if (!file_exists($this->output_path . '/' . $this->filename_override))
		{
			$this->image = $this->path . '/' . $this->filename;
			$this->uploader();

			if (function_exists('chmod')) @chmod($this->image, 0777);
		}
		
		return $this->filename_override;
	}
	
	/**
	* Returns server settings for max upload size
	*
	* @param void
	* @return integer
	*/
	public function upload_max_size()
	{
		$upload_max_filesize = ini_get('upload_max_filesize');
		$upload_max_filesize = preg_replace('/M/', '000000', $upload_max_filesize);
		
		$post_max_size = ini_get('post_max_size');
		$post_max_size = preg_replace('/M/', '000000', $post_max_size);
		
		$this->upload_max_size = ($post_max_size >= $upload_max_filesize) ? $upload_max_filesize : $post_max_size;
	}

	/**
	* Returns filetype by file extension
	*
	* @param void
	* @return string
	*/
	public function getFileType()
	{
		$type = explode('.', $this->filename);
		$this->filemime = array_pop($type);
	}
	
	/**
	* Returns array of image filetypes
	*
	* @param void
	* @return array
	*/
	public function allowThumbs()
	{
		return $this->uploads['images'];
	}
	
	
	public function regenerate($id=0, $file='')
	{
		$OBJ =& get_instance();
		global $default;

		// but we need our inputs...
		// we'll query for all our defaults first...
		$rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape    
			FROM ".PX."objects    
			WHERE id = '$id'");

		// we need to get these from some defaults someplace
		$this->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
		$this->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
		$this->quality = $default['img_quality'];
		$this->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
		$this->makethumb = true;
		
		load_helper('output');
		$URL =& load_class('publish', TRUE, 'lib');
		
		$this->path = DIRNAME . GIMGS . '/';
		
		$test = explode('.', $file);
		$thetype = array_pop($test);
		
		$URL->title = implode('_', $test);
		//$new_title = $URL->processTitle();
		$new_title = $URL->title;
	
		$this->type = '.' . strtolower($thetype);
		//$IMG->filename = $IMG->checkName($_POST['id'] . '_' . $new_title) . '.' . $thetype;
		$this->filename = $new_title . '.' . $thetype;
		$this->origname = $this->filename;
		
		$this->id = $id . '_';
		$this->filename = $this->filename;
			
		$this->image = $this->path . '/' . $this->filename;
		// end inputs

		$this->getFileType();
		$this->get_input();
		
		// original sizes of things
		$this->size = getimagesize($this->image);
		$this->orig_kb = str_replace('.', '', @filesize($this->image));
		
		if ($this->make_image == true) $this->make_image();
		
		if ($this->makethumb == true) 
		{
			// this is where we deal with shape of thumbnail
			$this->shape = ($this->shape == '') ? '0' : $this->shape;
			$thumbed = 'make_thumbnail_' . $this->shapes[$this->shape];
			$this->$thumbed();
		}

		if (($this->makethumb == true) && ($this->makesysthumb == true)) $this->make_systhumb();
		
		if ($this->make_sys == true) $this->make_system();
		
		imagedestroy($this->input_image);
	}
	

	public function uploader()
	{
		$this->getFileType();
		$this->get_input();
		
		// original sizes of things
		$this->size = getimagesize($this->image);
		$this->orig_kb = str_replace('.', '', @filesize($this->image));
		
		if ($this->make_image == true) $this->make_image();
		
		if ($this->makethumb == true) 
		{
			// this is where we deal with shape of thumbnail
			$this->shape = ($this->shape == '') ? '0' : $this->shape;
			$thumbed = 'make_thumbnail_' . $this->shapes[$this->shape];
			$this->$thumbed();
		}

		if (($this->makethumb == true) && ($this->makesysthumb == true)) $this->make_systhumb();
		
		if ($this->make_sys == true) $this->make_system();
		
		imagedestroy($this->input_image);
	}
	
	
	public function make_video_image($source_name)
	{
		$test 		= explode('.', $source_name);
		$thetype 	= array_pop($test);
		$new_name 	= str_replace($thetype, 'gif', $source_name);

		// Create a new image instance
		$im = imagecreatetruecolor(800, 600);

		// a neutral gray - we'll add a hook here later
		imagefilledrectangle($im, 0, 0, 800, 600, 0x999999);

		// Output the files/gimgs folder
		$this->filemime = 'gif';
		$this->do_output($im, DIRNAME . '/files/gimgs/' . $new_name);

		imagedestroy($im);
		
		return $new_name;
	}
	
	
	public function video_thumbnailer()
	{
		// make a blank neutral image for the videos

		$this->getFileType();
		$this->get_input();
		
		// original sizes of things
		$this->size = getimagesize($this->image);
		$this->orig_kb = str_replace('.', '', @filesize($this->image));
		
		if ($this->make_image == true) $this->make_image();
		
		if ($this->makethumb == true) 
		{
			// this is where we deal with shape of thumbnail
			$this->shape = ($this->shape == '') ? '0' : $this->shape;
			$thumbed = 'make_thumbnail_' . $this->shapes[$this->shape];
			$this->$thumbed();
		}

		if (($this->makethumb == true) && ($this->makesysthumb == true)) $this->make_systhumb();
		
		if ($this->make_sys == true) $this->make_system();
		
		imagedestroy($this->input_image);
	}
	
	
	public function user_image()
	{
		$this->getFileType();
		$this->get_input();
		
		// original sizes of things
		$this->size = getimagesize($this->image);
		$this->orig_kb = str_replace('.', '', @filesize($this->image));
		
		//if ($this->make_image == true) $this->make_image();
		$this->make_user_thumbnail();
		
		imagedestroy($this->input_image);
	}
	
	
	public function cover_image()
	{
		$this->getFileType();
		$this->get_input();
		
		// original sizes of things
		$this->size = getimagesize($this->image);
		$this->orig_kb = str_replace('.', '', @filesize($this->image));
		
		//if ($this->make_image == true) $this->make_image();
		$this->make_thumbnail_cinematic();
		
		imagedestroy($this->input_image);
	}
	
	
	public function make_image()
	{
		if ($this->maxsize != 9999)
		{
			// get the new sizes
			$this->resizing($this->maxsize);
		
			$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);

			// resizing
			@imagecopyresampled($output_image,  $this->input_image, 0, 0, 0, 0,
				$this->new_size['w'], $this->new_size['h'], $this->size[0], $this->size[1]);
		}
		else
		{
			// copy the image and output it at the same size
			$output_image = imagecreatetruecolor($this->size[0], $this->size[1]);
				
			@imagecopy($output_image, $this->input_image, 0, 0, 0, 0, $this->size[0], $this->size[1]);
		}
		
		//$this->image =  $this->path . $this->id . $this->filename;
		
		//echo $this->path . $this->id . $this->filename; exit;
		
		$out = ($this->output_path == '') ? $this->path . $this->id . $this->filename : 
			$this->output_path . $this->id . $this->filename;
	
		$this->do_output($output_image, $out);
		imagedestroy($output_image);
		
		if (function_exists('chmod')) chmod($out, 0777);
	}


	public function make_thumbnail_cinematic()
	{
		// output dimensions
		$the_width = round($this->thumbsize);
		$the_height = round((9 * $this->thumbsize) / 16);
		
		$this->odim['x'] = $the_width;
		$this->odim['y'] = $the_height;
		
		// get the new sizes
		$this->cinematic($this->thumbsize);
		
		$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);
		$imT = imagecreatetruecolor($the_width, $the_height);
		
		// resizing
		@imagecopyresampled($output_image,  $this->input_image, 0, 0, 
			0, 0,
			$this->new_size['w'], $this->new_size['h'], 
			$this->size[0], $this->size[1]);
		
		// cropping
		// we need to run another check here too
		if ($this->new_size['h'] > $the_height)
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				0, (($this->new_size['h'] - $the_height) / 2),
				$this->new_size['w'], $this->new_size['h'], 
				$this->new_size['w'], $this->new_size['h']);
		}
		else
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				(($this->new_size['w'] - $the_width) / 2), 0,
				$this->new_size['w'], $the_height, 
				$this->new_size['w'], $this->new_size['h']);
		}
		
		/*
		// resizing
		@imagecopyresampled($output_image,  $this->input_image, 0, 0, 
			0, 0,
			$this->new_size['w'], $this->new_size['h'], 
			$this->size[0], $this->size[1]);
			
		@imagecopyresampled($imT, $output_image, 0, 0, 
			0, (($this->new_size['h'] - $the_height) / 2),
			$the_width, $this->new_size['h'], 
			$this->new_size['w'], $this->new_size['h']);
			
		// this makes the background white on thumbs
		//$bgcolor = imagecolorallocate($imT, 255, 255, 255);
		//imagefill($imT, 0, 0, $bgcolor);
		
		// how do we flag when we are working on thumbs>
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			//imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/
		
		//$name = ($this->filename_override == false) ? $this->path . 'th-' . $this->id . $this->filename : 
		//	$this->path . $this->filename_override;
		
		//$this->do_output($imT, $name);
		//$this->do_output($imT, $this->path . 'th-' . $this->id . $this->filename);
		//$this->do_output($imT, $this->path . '/' . 'th-' . $this->filename);
		
		$out = ($this->output_path == '') ? $this->path . 'th-' . $this->id . $this->filename : 
			$this->output_path . 'th-' . $this->id . $this->filename;
			
		$name = ($this->filename_override == false) ? $out : 
			DIRNAME . '/files/dimgs/' . $this->filename_override;
		
		$this->do_output($imT, $name);
		
		imagedestroy($output_image);
		imagedestroy($imT);
		
		if (function_exists('chmod')) chmod($name, 0777);
	}
	
	
	public function make_thumbnail_4x3()
	{
		// output dimensions
		$the_width = round($this->thumbsize);
		$the_height = round((3 * $this->thumbsize) / 4);
		
		$this->odim['x'] = $the_width;
		$this->odim['y'] = $the_height;
		
		// get the new sizes
		// this does a double check on things
		$this->size4x3($this->thumbsize);
		
		$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);
		$imT = imagecreatetruecolor($the_width, $the_height);
		
		// resizing
		@imagecopyresampled($output_image,  $this->input_image, 0, 0, 
			0, 0,
			$this->new_size['w'], $this->new_size['h'], 
			$this->size[0], $this->size[1]);
		
		// cropping
		// we need to run another check here too
		if ($this->new_size['h'] > $the_height)
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				0, (($this->new_size['h'] - $the_height) / 2),
				$this->new_size['w'], $this->new_size['h'], 
				$this->new_size['w'], $this->new_size['h']);
		}
		else
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				(($this->new_size['w'] - $the_width) / 2), 0,
				$this->new_size['w'], $the_height, 
				$this->new_size['w'], $this->new_size['h']);
		}
		
		// how do we flag when we are working on thumbs
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			//imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/
		
		//$name = ($this->filename_override == false) ? $this->path . 'th-' . $this->id . $this->filename : 
		//	$this->path . $this->filename_override;
			
		$out = ($this->output_path == '') ? $this->path . 'th-' . $this->id . $this->filename : 
			$this->output_path . 'th-' . $this->id . $this->filename;
			
		$name = ($this->filename_override == false) ? $out : 
			DIRNAME . '/files/dimgs/' . $this->filename_override;
		
		$this->do_output($imT, $name);
		//$this->do_output($imT, $this->path . 'th-' . $this->id . $this->filename);
		//$this->do_output($imT, $this->path . '/' . 'th-' . $this->filename);
		imagedestroy($output_image);
		imagedestroy($imT);
		
		if (function_exists('chmod')) chmod($name, 0777);
	}
	
	
	public function make_thumbnail_3x2()
	{
		// output dimensions
		$the_width = round($this->thumbsize);
		$the_height = round((2 * $this->thumbsize) / 3);
		
		$this->odim['x'] = $the_width;
		$this->odim['y'] = $the_height;
		
		// get the new sizes
		// this does a double check on things
		$this->size3x2($this->thumbsize);
		
		$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);
		$imT = imagecreatetruecolor($the_width, $the_height);
		
		// resizing
		@imagecopyresampled($output_image,  $this->input_image, 0, 0, 
			0, 0,
			$this->new_size['w'], $this->new_size['h'], 
			$this->size[0], $this->size[1]);
		
		// cropping
		// we need to run another check here too
		if ($this->new_size['h'] > $the_height)
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				0, (($this->new_size['h'] - $the_height) / 2),
				$this->new_size['w'], $this->new_size['h'], 
				$this->new_size['w'], $this->new_size['h']);
		}
		else
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				(($this->new_size['w'] - $the_width) / 2), 0,
				$this->new_size['w'], $the_height, 
				$this->new_size['w'], $this->new_size['h']);
		}
		
		// how do we flag when we are working on thumbs
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			//imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/
		
		//$name = ($this->filename_override == false) ? $this->path . 'th-' . $this->id . $this->filename : 
		//	$this->path . $this->filename_override;
			
		$out = ($this->output_path == '') ? $this->path . 'th-' . $this->id . $this->filename : 
			$this->output_path . 'th-' . $this->id . $this->filename;
			
		$name = ($this->filename_override == false) ? $out : 
			DIRNAME . '/files/dimgs/' . $this->filename_override;
		
		$this->do_output($imT, $name);
		//$this->do_output($imT, $this->path . 'th-' . $this->id . $this->filename);
		//$this->do_output($imT, $this->path . '/' . 'th-' . $this->filename);
		imagedestroy($output_image);
		imagedestroy($imT);
		
		if (function_exists('chmod')) chmod($name, 0777);
	}
	
	
	public function make_thumbnail_3x4()
	{
		// output dimensions
		$the_width = round($this->thumbsize);
		$the_height = round((4 * $this->thumbsize) / 3);
		
		$this->odim['x'] = $the_width;
		$this->odim['y'] = $the_height;
		
		// get the new sizes
		// this does a double check on things
		$this->size3x4($this->thumbsize);
		
		$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);
		$imT = imagecreatetruecolor($the_width, $the_height);
		
		// resizing
		@imagecopyresampled($output_image,  $this->input_image, 0, 0, 
			0, 0,
			$this->new_size['w'], $this->new_size['h'], 
			$this->size[0], $this->size[1]);
		
		// cropping
		// we need to run another check here too
		if ($this->new_size['h'] > $the_height)
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				0, (($this->new_size['h'] - $the_height) / 2),
				$this->new_size['w'], $this->new_size['h'], 
				$this->new_size['w'], $this->new_size['h']);
		}
		else
		{
			@imagecopyresampled($imT, $output_image, 0, 0, 
				(($this->new_size['w'] - $the_width) / 2), 0,
				$this->new_size['w'], $the_height, 
				$this->new_size['w'], $this->new_size['h']);
		}
		
		// how do we flag when we are working on thumbs
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			//imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/
		
		//$name = ($this->filename_override == false) ? $this->path . 'th-' . $this->id . $this->filename : 
		//	$this->path . $this->filename_override;
			
		$out = ($this->output_path == '') ? $this->path . 'th-' . $this->id . $this->filename : 
			$this->output_path . 'th-' . $this->id . $this->filename;
			
		$name = ($this->filename_override == false) ? $out : 
			DIRNAME . '/files/dimgs/' . $this->filename_override;
		
		$this->do_output($imT, $name);
		//$this->do_output($imT, $this->path . 'th-' . $this->id . $this->filename);
		//$this->do_output($imT, $this->path . '/' . 'th-' . $this->filename);
		imagedestroy($output_image);
		imagedestroy($imT);
		
		if (function_exists('chmod')) chmod($name, 0777);
	}
	

	public function make_thumbnail_cinematic2()
	{
		// get the new sizes
		$this->cinematic($this->thumbsize);
		
		$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);
		$imT = imagecreatetruecolor(200, 113);

		// resizing
		@imagecopyresampled($output_image,  $this->input_image, 0, 0, 
			0, 0,
			$this->new_size['w'], $this->new_size['h'], 
			$this->size[0], $this->size[1]);
			
		@imagecopyresampled($imT, $output_image, 0, 0, 
			0, (($this->new_size['h'] - 113) / 2),
			200, $this->new_size['h'], 
			$this->new_size['w'], $this->new_size['h']);
		
		// how do we flag when we are working on thumbs>
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			//imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			//imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/
		
		$out = ($this->output_path == '') ? $this->path . 'th-' . $this->id . $this->filename : 
			$this->output_path . 'th-' . $this->id . $this->filename;
		
		$this->do_output($imT, $out);
		
		imagedestroy($output_image);
		imagedestroy($imT);
		
		if (function_exists('chmod')) chmod($out, 0777);
	}
	
	
	public function make_thumbnail_proportional()
	{
		// get the new sizes
		$this->resizing($this->thumbsize);
		
		$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);

		// resizing
		@imagecopyresampled($output_image, $this->input_image, 0, 0, 0, 0,
			$this->new_size['w'], $this->new_size['h'], $this->size[0], $this->size[1]);
		
		// how do we flag when we are working on thumbs>
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			//imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			//imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
			
			//imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			//imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			//imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
			
			//imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			//imagefilter($output_image, IMG_FILTER_CONTRAST, 255);
			//imagefilter($output_image, IMG_FILTER_NEGATE);
			//imagefilter($output_image, IMG_FILTER_COLORIZE, 2, 118, 219);
			//imagefilter($output_image, IMG_FILTER_COLORIZE, 234, 199, 64);
			//imagefilter($output_image, IMG_FILTER_NEGATE);
		}
		*/
			
		$out = ($this->output_path == '') ? $this->path . 'th-' . $this->id . $this->filename : 
			$this->output_path . 'th-' . $this->id . $this->filename;
			
		$name = ($this->filename_override == false) ? $out : 
			DIRNAME . '/files/dimgs/' . $this->filename_override;
	
		$this->do_output($output_image, $name);
		imagedestroy($output_image);
		
		if (function_exists('chmod')) chmod($name, 0777);
	}
	
	
	public function make_thumbnail_square()
	{
		$this->square_resize();

		$output_image = imagecreatetruecolor($this->thumbsize, $this->thumbsize);
			
		@imagecopyresampled($output_image, $this->input_image, 0, 0, 
			$this->offset['w'], $this->offset['h'],
			$this->thumbsize, $this->thumbsize, 
			$this->sys_size['w'], $this->sys_size['h']);
		
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			//imagefilter($output_image, IMG_FILTER_CONTRAST, -5);
			//imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/
		
		//$name = ($this->filename_override == false) ? $this->path . 'th-' . $this->id . $this->filename : 
		//	$this->path . $this->filename_override;
		
		$out = ($this->output_path == '') ? $this->path . 'th-' . $this->id . $this->filename : 
			$this->output_path . 'th-' . $this->id . $this->filename;
			
		$out = ($this->filename_override == false) ? $out : $this->path . $this->filename_override;
		
		$name = ($this->filename_override == false) ? $out : 
			DIRNAME . '/files/dimgs/' . $this->filename_override;
		
		$this->do_output($output_image, $name);

		//$this->do_output($output_image, $this->path . 'th-' .  $this->id . $this->filename);
		imagedestroy($output_image);
		
		if (function_exists('chmod')) chmod($name, 0777);
	}
	
	
	public function make_systhumb()
	{
		global $default;

		// get the new sizes
		$this->resizing($default['systhumb']);
		
		$output_image = imagecreatetruecolor($this->new_size['w'], $this->new_size['h']);

		// resizing
		@imagecopyresampled($output_image,  $this->input_image, 0, 0, 0, 0,
			$this->new_size['w'], $this->new_size['h'], $this->size[0], $this->size[1]);
		
		// how do we flag when we are working on thumbs>
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			imagefilter($output_image, IMG_FILTER_CONTRAST, -5);
			imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/
		
		$out = ($this->output_path == '') ? $this->path . 'systh-' . $this->id . $this->filename : 
			$this->output_path . 'systh-' . $this->id . $this->filename;
	
		$this->do_output($output_image, $out);
		imagedestroy($output_image);
		
		if (function_exists('chmod')) chmod($out, 0777);
	}
	
	public function make_system()
	{
		$this->sys_resize();

		$output_image = imagecreatetruecolor($this->sys_thumb, $this->sys_thumb);
		
		@imagecopyresampled($output_image, $this->input_image, 0, 0, 
			$this->offset['w'], $this->offset['h'],
			$this->sys_thumb, $this->sys_thumb, 
			$this->sys_size['w'], $this->sys_size['h']);
		
		/*
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			//imagefilter($output_image, IMG_FILTER_CONTRAST, -5);
			//imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
		}
		*/

		// for sys- naming convention
		//$image =  $this->path . 'sys-' .  $this->id . $this->filename;
		
		$out = ($this->output_path == '') ? $this->path . 'sys-' . $this->id . $this->filename : 
			$this->output_path . 'sys-' . $this->id . $this->filename;

		$this->do_output($output_image, $out);
		imagedestroy($output_image);
		
		if (function_exists('chmod')) chmod($out, 0777);
	}
	
	
	public function make_user_thumbnail()
	{
		$this->square_resize();

		$output_image = imagecreatetruecolor($this->thumbsize, $this->thumbsize);
			
		@imagecopyresampled($output_image, $this->input_image, 0, 0, 
			$this->offset['w'], $this->offset['h'],
			$this->thumbsize, $this->thumbsize, 
			$this->sys_size['w'], $this->sys_size['h']);
		
		$name = DIRNAME . '/files/' . $this->filename;
		
		$this->do_output($output_image, $name);

		//$this->do_output($output_image, $this->path . 'th-' .  $this->id . $this->filename);
		imagedestroy($output_image);
		
		if (function_exists('chmod')) chmod($name, 0777);
	}

	
	/**
	* Returns file size
	*
	* @param void
	* @return integer
	*/
	public function file_size()
	{
		$size = str_replace('.', '', @filesize($this->image));
		$this->file_size = ($size == 0) ? 0 : $size;
	}
	
	
	/**
	* Returns input image according to type
	*
	* @param void
	* @return variable
	*/
	public function get_input()
	{
		switch($this->filemime)
		{
            case 'gif':

				// is it animated?
				//exec("ls *gif", $this->image);
				//$this->animated = ($this->is_ani($this->image)) ? true : false;

				$this->input_image = imagecreatefromgif($this->image);
                break;
            case 'jpg':
                $this->input_image = imagecreatefromjpeg($this->image);
                break;
			case 'jpeg':
				$this->input_image = imagecreatefromjpeg($this->image);
				break;
            case 'png':
                $this->input_image = imagecreatefrompng($this->image);
                break;
        }
	}

	
	/**
	* Returns output image according to type
	*
	* @param string $output_image
	* @param string $image
	* @return string
	*/
	public function do_output($output_image, $image)
	{
		switch($this->filemime) {
            case 'gif':
                imagegif($output_image, $image);
                break;
            case 'jpg':
                imagejpeg($output_image, $image, $this->quality);
                break;
			case 'jpeg':
				imagejpeg($output_image, $image, $this->quality);
				break;
            case 'png':
                imagepng($output_image, $image);
                break;
        }
	}

	/**
	* Returns array of file size
	* (natural dimensions)
	*
	* @param integer $maxwidth
	* @return array
	*/
	public function resizing($maxwidth)
	{
		$width_percentage = $maxwidth / $this->size[0];
		$height_percentage = $maxwidth / $this->size[1];

		if (($this->size[0] > $maxwidth) || ($this->size[1] > $maxwidth))
		{
			if ($width_percentage <= $height_percentage)
			{
				$this->new_size['w'] = round($width_percentage * $this->size[0]);
				$this->new_size['h'] = round($width_percentage * $this->size[1]);
			} 
			else
			{
				$this->new_size['w'] = round($height_percentage * $this->size[0]);
				$this->new_size['h'] = round($height_percentage * $this->size[1]);
			}	
		}
		else
		{  // square images ?
			$this->new_size['w'] = $this->size[0];
			$this->new_size['h'] = $this->size[1];
		}
	}
	
	
	// ((9 * 200) / 16)
	// cinematic 16 x 9
	public function cinematic($maxwidth)
	{
		// width greater than height
		if ($this->size[0] > $this->size[1])
		{
			//phpinfo();
			$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = round($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = round($this->odim['y']);
				
				// if not...do it again...inflate both
			}
			
			//echo $this->new_size['w'] . ' : ' . $this->new_size['h']; exit;
		}
		
		return;
		//$this->new_size['w'] = round($maxwidth);
		//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
		
		// width greater than height
		if ($this->size[0] > $this->size[1])
		{
			//phpinfo();
			$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = round($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
		}
	}


	public function size4x3($maxwidth)
	{
		// width greater than height
		if ($this->size[0] > $this->size[1])
		{
			//phpinfo();
			$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = round($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = round($this->odim['y']);
				
				// if not...do it again...inflate both
			}
			
			//echo $this->new_size['w'] . ' : ' . $this->new_size['h']; exit;
		}
	}
	
	
	
	public function size3x2($maxwidth)
	{
		// width greater than height
		if ($this->size[0] > $this->size[1])
		{
			//phpinfo();
			$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = round($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = round($this->odim['y']);
				
				// if not...do it again...inflate both
			}
			
			//echo $this->new_size['w'] . ' : ' . $this->new_size['h']; exit;
		}
	}
	
	
	public function size3x4($maxwidth)
	{
		// width greater than height
		if ($this->size[0] > $this->size[1])
		{
			//phpinfo();
			$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = round($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = round($maxwidth);
			$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = round(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = round(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = round($this->odim['y']);
				
				// if not...do it again...inflate both
			}
			
			//echo $this->new_size['w'] . ' : ' . $this->new_size['h']; exit;
		}
	}
	
	
	/**
	* Returns array of file size
	* (square thumbnails)
	*
	* @param void
	* @return array
	*/
	public function square_resize()
	{
		$this->sys_size['w'] = $this->size[0];
		$this->sys_size['h'] = $this->size[1];
		
		if ($this->sys_size['w'] > $this->sys_size['h']) 
		{
		   $this->offset['w'] = ($this->sys_size['w'] - $this->sys_size['h'])/2;
		   $this->offset['h'] = 0;
		   $this->sys_size['w'] = $this->sys_size['h'];
		} 
		elseif ($this->sys_size['h'] > $this->sys_size['w']) 
		{
		   $this->offset['w'] = 0;
		   $this->offset['h'] = ($this->sys_size['h'] - $this->sys_size['w'])/2;
		   $this->sys_size['h'] = $this->sys_size['w'];
		} 
		else
		{
			$this->offset['w'] = 0;
			$this->offset['h'] = 0;
			$this->sys_size['w'] = $this->sys_size['h'];
		}
	}
	
	
	
	/**
	* Returns array of file size
	* (square thumbnails)
	*
	* @param void
	* @return array
	*/
	public function sys_resize()
	{
		$this->sys_size['w'] = $this->size[0];
		$this->sys_size['h'] = $this->size[1];
		
		if ($this->sys_size['w'] > $this->sys_size['h']) 
		{
		   $this->offset['w'] = ($this->sys_size['w'] - $this->sys_size['h'])/2;
		   $this->offset['h'] = 0;
		   $this->sys_size['w'] = $this->sys_size['h'];
		} 
		elseif ($this->sys_size['h'] > $this->sys_size['w']) 
		{
		   $this->offset['w'] = 0;
		   $this->offset['h'] = ($this->sys_size['h'] - $this->sys_size['w'])/2;
		   $this->sys_size['h'] = $this->sys_size['w'];
		} 
		else
		{
			$this->offset['w'] = 0;
			$this->offset['h'] = 0;
			$this->sys_size['w'] = $this->sys_size['h'];
		}
	}
	

	/**
	* Returns new file name based upon exiting files to prevent name collisions
	*
	* @param string $filename
	* @return string
	*/
	public function checkName($filename)
	{
		static $v = 1;
		
		if (file_exists($this->path . '/' . $filename . $this->type))
		{
			// remove the previous version number
			$filename = preg_replace("/_v[0-9]{1,3}$/i", '', $filename);
			$v++;
			$filename = $filename . '_v' . $v;
			$filename = $this->checkName($filename);
		}
		else
		{
			$v = 1;
			return $filename;
		}
		
		return $filename;
	}
	
	
	/// experimental
	public function is_ani($filename)
	{
	        $filecontents=file_get_contents($filename);

	        $str_loc=0;
	        $count=0;
	        while ($count < 2) # There is no point in continuing after we find a 2nd frame
	        {

	                $where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
	                if ($where1 === FALSE)
	                {
	                        break;
	                }
	                else
	                {
	                        $str_loc=$where1+1;
	                        $where2=strpos($filecontents,"\x00\x2C",$str_loc);
	                        if ($where2 === FALSE)
	                        {
	                                break;
	                        }
	                        else
	                        {
	                                if ($where1+8 == $where2)
	                                {
	                                        $count++;
	                                }
	                                $str_loc=$where2+1;
	                        }
	                }
	        }

	        if ($count > 1)
	        {
	                return(true);

	        }
	        else
	        {
	                return(false);
	        }
	}
}