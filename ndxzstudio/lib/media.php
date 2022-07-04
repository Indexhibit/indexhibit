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
		$OBJ =& get_instance();
		global $go, $default;

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

		$this->id = $OBJ->vars->exhibit['id'] . '_';
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
		$new_title = $URL->title;
	
		$this->type = '.' . $thetype;
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
		$the_width = ceil($this->thumbsize);
		$the_height = ceil((9 * $this->thumbsize) / 16);
		
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
		$the_width = ceil($this->thumbsize);
		$the_height = ceil((3 * $this->thumbsize) / 4);
		
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
		$the_width = ceil($this->thumbsize);
		$the_height = ceil((2 * $this->thumbsize) / 3);
		
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
		$the_width = ceil($this->thumbsize);
		$the_height = ceil((4 * $this->thumbsize) / 3);
		
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
		
		if ((phpversion() >= '5') && ($this->bw_flag)) 
		{
			imagefilter($output_image, IMG_FILTER_GRAYSCALE);
			imagefilter($output_image, IMG_FILTER_CONTRAST, 0);
			imagefilter($output_image, IMG_FILTER_BRIGHTNESS, 10);
			
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
		switch(strtolower($this->filemime))
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
		switch(strtolower($this->filemime)) {
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
				$this->new_size['w'] = ceil($width_percentage * $this->size[0]);
				$this->new_size['h'] = ceil($width_percentage * $this->size[1]);
			} 
			else
			{
				$this->new_size['w'] = ceil($height_percentage * $this->size[0]);
				$this->new_size['h'] = ceil($height_percentage * $this->size[1]);
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
			$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = ceil($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = ceil($this->odim['y']);
				
				// if not...do it again...inflate both
			}
			
			//echo $this->new_size['w'] . ' : ' . $this->new_size['h']; exit;
		}
		
		return;
		//$this->new_size['w'] = ceil($maxwidth);
		//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
		
		// width greater than height
		if ($this->size[0] > $this->size[1])
		{
			//phpinfo();
			$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = ceil($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
		}
	}


	public function size4x3($maxwidth)
	{
		// width greater than height
		if ($this->size[0] > $this->size[1])
		{
			//phpinfo();
			$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = ceil($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = ceil($this->odim['y']);
				
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
			$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = ceil($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = ceil($this->odim['y']);
				
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
			$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
			//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			$this->new_size['h'] = ceil($this->odim['y']);
			
			//$this->odim['x']
		}
		else
		{
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
		}
		
		//echo $this->new_size['w'] . ' < ' . $this->odim['x']; exit;
		
		// we need to double check dimensions here and reset accordingly
		// meaning the proportions aren't working out well enough
		if ($this->new_size['w'] < $this->odim['x'])
		{
			// reset according to width (not height)
			$this->new_size['w'] = ceil($maxwidth);
			$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
			
			if ($this->new_size['h'] < $this->odim['y'])
			{
				// how do we set this one?
				// reset according to width (not height)
				$this->new_size['w'] = ceil(($this->size[0] * $this->odim['y']) / $this->size[1]);
				//$this->new_size['h'] = ceil(($this->size[1] * $maxwidth) / $this->size[0]);
				$this->new_size['h'] = ceil($this->odim['y']);
				
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

		$filename = $this->removeUnwanted($filename); // exchange accents
		$filename = preg_replace('/\s+/', '_', $filename); // remove blanks
		$filename = preg_replace("/[^a-zA-Z0-9_\-.\s]/", "", $filename); // remove non-chars
		
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


	public function removeUnwanted($input='')
	{
		$unwanted_array = array( '&amp;' => 'and', '&' => 'and', '@' => 'at', '©' => 'c', '®' => 'r', 
'̊'=>'','̧'=>'','̨'=>'','̄'=>'','̱'=>'',
'Á'=>'a','á'=>'a','À'=>'a','à'=>'a','Ă'=>'a','ă'=>'a','ắ'=>'a','Ắ'=>'A','Ằ'=>'A',
'ằ'=>'a','ẵ'=>'a','Ẵ'=>'A','ẳ'=>'a','Ẳ'=>'A','Â'=>'a','â'=>'a','ấ'=>'a','Ấ'=>'A',
'ầ'=>'a','Ầ'=>'a','ẩ'=>'a','Ẩ'=>'A','Ǎ'=>'a','ǎ'=>'a','Å'=>'a','å'=>'a','Ǻ'=>'a',
'ǻ'=>'a','Ä'=>'a','ä'=>'a','ã'=>'a','Ã'=>'A','Ą'=>'a','ą'=>'a','Ā'=>'a','ā'=>'a',
'ả'=>'a','Ả'=>'a','Ạ'=>'A','ạ'=>'a','ặ'=>'a','Ặ'=>'A','ậ'=>'a','Ậ'=>'A','Æ'=>'ae',
'æ'=>'ae','Ǽ'=>'ae','ǽ'=>'ae','ẫ'=>'a','Ẫ'=>'A',
'Ć'=>'c','ć'=>'c','Ĉ'=>'c','ĉ'=>'c','Č'=>'c','č'=>'c','Ċ'=>'c','ċ'=>'c','Ç'=>'c','ç'=>'c',
'Ď'=>'d','ď'=>'d','Ḑ'=>'D','ḑ'=>'d','Đ'=>'d','đ'=>'d','Ḍ'=>'D','ḍ'=>'d','Ḏ'=>'D','ḏ'=>'d','ð'=>'d','Ð'=>'D',
'É'=>'e','é'=>'e','È'=>'e','è'=>'e','Ĕ'=>'e','ĕ'=>'e','ê'=>'e','ế'=>'e','Ế'=>'E','ề'=>'e',
'Ề'=>'E','Ě'=>'e','ě'=>'e','Ë'=>'e','ë'=>'e','Ė'=>'e','ė'=>'e','Ę'=>'e','ę'=>'e','Ē'=>'e',
'ē'=>'e','ệ'=>'e','Ệ'=>'E','Ə'=>'e','ə'=>'e','ẽ'=>'e','Ẽ'=>'E','ễ'=>'e',
'Ễ'=>'E','ể'=>'e','Ể'=>'E','ẻ'=>'e','Ẻ'=>'E','ẹ'=>'e','Ẹ'=>'E',
'ƒ'=>'f',
'Ğ'=>'g','ğ'=>'g','Ĝ'=>'g','ĝ'=>'g','Ǧ'=>'G','ǧ'=>'g','Ġ'=>'g','ġ'=>'g','Ģ'=>'g','ģ'=>'g',
'H̲'=>'H','h̲'=>'h','Ĥ'=>'h','ĥ'=>'h','Ȟ'=>'H','ȟ'=>'h','Ḩ'=>'H','ḩ'=>'h','Ħ'=>'h','ħ'=>'h','Ḥ'=>'H','ḥ'=>'h',
'Ỉ'=>'I','Í'=>'i','í'=>'i','Ì'=>'i','ì'=>'i','Ĭ'=>'i','ĭ'=>'i','Î'=>'i','î'=>'i','Ǐ'=>'i','ǐ'=>'i',
'Ï'=>'i','ï'=>'i','Ḯ'=>'I','ḯ'=>'i','Ĩ'=>'i','ĩ'=>'i','İ'=>'i','Į'=>'i','į'=>'i','Ī'=>'i','ī'=>'i',
'ỉ'=>'I','Ị'=>'I','ị'=>'i','Ĳ'=>'ij','ĳ'=>'ij','ı'=>'i',
'Ĵ'=>'j','ĵ'=>'j',
'Ķ'=>'k','ķ'=>'k','Ḵ'=>'K','ḵ'=>'k',
'Ĺ'=>'l','ĺ'=>'l','Ľ'=>'l','ľ'=>'l','Ļ'=>'l','ļ'=>'l','Ł'=>'l','ł'=>'l','Ŀ'=>'l','ŀ'=>'l',
'Ń'=>'n','ń'=>'n','Ň'=>'n','ň'=>'n','Ñ'=>'N','ñ'=>'n','Ņ'=>'n','ņ'=>'n','Ṇ'=>'N','ṇ'=>'n','Ŋ'=>'n','ŋ'=>'n',
'Ó'=>'o','ó'=>'o','Ò'=>'o','ò'=>'o','Ŏ'=>'o','ŏ'=>'o','Ô'=>'o','ô'=>'o','ố'=>'o','Ố'=>'O','ồ'=>'o',
'Ồ'=>'O','ổ'=>'o','Ổ'=>'O','Ǒ'=>'o','ǒ'=>'o','Ö'=>'o','ö'=>'o','Ő'=>'o','ő'=>'o','Õ'=>'o','õ'=>'o',
'Ø'=>'o','ø'=>'o','Ǿ'=>'o','ǿ'=>'o','Ǫ'=>'O','ǫ'=>'o','Ǭ'=>'O','ǭ'=>'o','Ō'=>'o','ō'=>'o','ỏ'=>'o',
'Ỏ'=>'O','Ơ'=>'o','ơ'=>'o','ớ'=>'o','Ớ'=>'O','ờ'=>'o','Ờ'=>'O','ở'=>'o','Ở'=>'O','ợ'=>'o','Ợ'=>'O',
'ọ'=>'o','Ọ'=>'O','ọ'=>'o','Ọ'=>'O','ộ'=>'o','Ộ'=>'O','ỗ'=>'o','Ỗ'=>'O','ỡ'=>'o','Ỡ'=>'O',
'Œ'=>'oe','œ'=>'oe',
'ĸ'=>'k',
'Ŕ'=>'r','ŕ'=>'r','Ř'=>'r','ř'=>'r','ṙ'=>'r','Ŗ'=>'r','ŗ'=>'r','Ṛ'=>'R','ṛ'=>'r','Ṟ'=>'R','ṟ'=>'r',
'S̲'=>'S','s̲'=>'s','Ś'=>'s','ś'=>'s','Ŝ'=>'s','ŝ'=>'s','Š'=>'s','š'=>'s','Ş'=>'s','ş'=>'s',
'Ṣ'=>'S','ṣ'=>'s','Ș'=>'S','ș'=>'s',
'ſ'=>'z','ß'=>'ss','Ť'=>'t','ť'=>'t','Ţ'=>'t','ţ'=>'t','Ṭ'=>'T','ṭ'=>'t','Ț'=>'T',
'ț'=>'t','Ṯ'=>'T','ṯ'=>'t','™'=>'tm','Ŧ'=>'t','ŧ'=>'t',
'Ú'=>'u','ú'=>'u','Ù'=>'u','ù'=>'u','Ŭ'=>'u','ŭ'=>'u','Û'=>'u','û'=>'u','Ǔ'=>'u','ǔ'=>'u','Ů'=>'u','ů'=>'u',
'Ü'=>'u','ü'=>'u','Ǘ'=>'u','ǘ'=>'u','Ǜ'=>'u','ǜ'=>'u','Ǚ'=>'u','ǚ'=>'u','Ǖ'=>'u','ǖ'=>'u','Ű'=>'u','ű'=>'u',
'Ũ'=>'u','ũ'=>'u','Ų'=>'u','ų'=>'u','Ū'=>'u','ū'=>'u','Ư'=>'u','ư'=>'u','ứ'=>'u','Ứ'=>'U','ừ'=>'u','Ừ'=>'U',
'ử'=>'u','Ử'=>'U','ự'=>'u','Ự'=>'U','ụ'=>'u','Ụ'=>'U','ủ'=>'u','Ủ'=>'U','ữ'=>'u','Ữ'=>'U',
'Ŵ'=>'w','ŵ'=>'w',
'Ý'=>'y','ý'=>'y','ỳ'=>'y','Ỳ'=>'Y','Ŷ'=>'y','ŷ'=>'y','ÿ'=>'y','Ÿ'=>'y','ỹ'=>'y','Ỹ'=>'Y','ỷ'=>'y','Ỷ'=>'Y',
'Z̲'=>'Z','z̲'=>'z','Ź'=>'z','ź'=>'z','Ž'=>'z','ž'=>'z','Ż'=>'z','ż'=>'z','Ẕ'=>'Z','ẕ'=>'z',
'þ'=>'p','ŉ'=>'n','А'=>'a','а'=>'a','Б'=>'b','б'=>'b','В'=>'v','в'=>'v','Г'=>'g','г'=>'g','Ґ'=>'g','ґ'=>'g',
'Д'=>'d','д'=>'d','Е'=>'e','е'=>'e','Ё'=>'jo','ё'=>'jo','Є'=>'e','є'=>'e','Ж'=>'zh','ж'=>'zh','З'=>'z','з'=>'z',
'И'=>'i','и'=>'i','І'=>'i','і'=>'i','Ї'=>'i','ї'=>'i','Й'=>'j','й'=>'j','К'=>'k','к'=>'k','Л'=>'l','л'=>'l',
'М'=>'m','м'=>'m','Н'=>'n','н'=>'n','О'=>'o','о'=>'o','П'=>'p','п'=>'p','Р'=>'r','р'=>'r','С'=>'s','с'=>'s',
'Т'=>'t','т'=>'t','У'=>'u','у'=>'u','Ф'=>'f','ф'=>'f','Х'=>'h','х'=>'h','Ц'=>'c','ц'=>'c','Ч'=>'ch','ч'=>'ch',
'Ш'=>'sh','ш'=>'sh','Щ'=>'sch','щ'=>'sch','Ъ'=>'-',
'ъ'=>'-','Ы'=>'y','ы'=>'y','Ь'=>'-','ь'=>'-',
'Э'=>'je','э'=>'je','Ю'=>'ju','ю'=>'ju','Я'=>'ja','я'=>'ja','א'=>'a','ב'=>'b','ג'=>'g','ד'=>'d','ה'=>'h','ו'=>'v',
'ז'=>'z','ח'=>'h','ט'=>'t','י'=>'i','ך'=>'k','כ'=>'k','ל'=>'l','ם'=>'m','מ'=>'m','ן'=>'n','נ'=>'n','ס'=>'s','ע'=>'e',
'ף'=>'p','פ'=>'p','ץ'=>'C','צ'=>'c','ק'=>'q','ר'=>'r','ש'=>'w','ת'=>'t');

		return strtr($input, $unwanted_array);
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