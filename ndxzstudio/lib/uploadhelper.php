<?php


class Uploadhelper
{
	public $coverart = false;

	public function __construct()
	{
		
	}
	
	public function coverart()
	{
		
	}
	
	public function background($file, $size, $type)
	{
		$OBJ =& get_instance();
		global $go, $default;
		
		load_module_helper('files', $go['a']);

		$IMG =& load_class('media', TRUE, 'lib');
		$dir = DIRNAME . BASEFILES . '/';
		$types = array_merge($default['images']);
		$IMG->path = $dir;

		$id = (int) $go['id'];

		// we need to clean the file name
		$test = explode('.', $file);
		$thetype = array_pop($test);
		$name = $test[0];
		//$name = $file;
		$IMG->type = '.' . $thetype;
		$IMG->filename = $IMG->checkName($name) . '.' . $thetype;

		if (in_array($thetype, $types)) {
			if (move_uploaded_file($type, $IMG->path . '/' . $IMG->filename)) {
				$OBJ->db->updateRecord("UPDATE ".PX."objects SET
					bgimg = '$IMG->filename' WHERE id = '$id'");
			} else {
				// not uploaded
			}
		} else {
			// wrong file type
		}

		// return the new filename
		return $IMG->filename;
	}
	
	public function uploading($file, $size, $type, $coverart=false)
	{
		$OBJ =& get_instance();
		global $go, $default;

		load_module_helper('files', $go['a']);

		$IMG =& load_class('media', true, 'lib');

		// we'll query for all our defaults first...
		$rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape   
			FROM ".PX."objects    
			WHERE id = '$go[id]'");

		// we need to get these from some defaults someplace
		$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
		$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
		$IMG->quality = $default['img_quality'];
		$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
		$IMG->makethumb	= true;

		load_helper('output');
		$URL =& load_class('publish', TRUE, 'lib');

		// +++++++++++++++++++++++++++++++++++++++++++++++++++

		$new_images['name'] = $file;
		$new_images['temp'] = $type;
		$new_images['size'] = $size;

		$test = explode('.', strtolower($new_images['name']));
		$thetype = array_pop($test);

		// need to deal with all the rest..
		if (!in_array($thetype, $default['images']))
		{
			$IMG->path = DIRNAME . BASEFILES . '/';
		}
		else
		{
			$IMG->path = DIRNAME . GIMGS . '/';
		}

		$x = 0;
		$added_x = array();

		//if ($new_images['size'] < $IMG->upload_max_size)
		//{
			//$test = explode('.', strtolower($new_images['name']));
			$test = explode('.', $new_images['name']);
			$thetype = array_pop($test);

			// check for black and white flag
			// another time...
			if (preg_match('/^bw_/', $test[0]))
			{
				$IMG->bw_flag = true;
			}

			$URL->title = implode('_', $test);
			//$new_title = $URL->processTitle();
			$new_title = $URL->title;

			$IMG->type = '.' . strtolower($thetype);
			//$IMG->filename = $IMG->checkName($_POST['id'] . '_' . $new_title) . '.' . $thetype;
			$IMG->filename = $IMG->checkName($new_title) . '.' . strtolower($thetype);
			//$IMG->filename = $new_title . '.' . $thetype;
			$IMG->origname = $IMG->filename;

		// .swf will report dimensions it seems
		//if (in_array($thetype, array_merge($default['images'], $default['media'])))
		//{
			//print_r($new_images);
			//echo $IMG->path . '/' . $IMG->filename;
			// if uploaded we can work with it
			if (move_uploaded_file($new_images['temp'], $IMG->path . '/' . $IMG->filename)) 
			{
				$x++;

				// images
				if (in_array($thetype, $default['images']))
				{
					$IMG->id = $go['id'] . '_';
					$IMG->filename = $IMG->filename;

					$IMG->image = $IMG->path . '/' . $IMG->filename;
					$IMG->uploader();
					//phpinfo(); exit;

					if ($coverart == true)
					{
						$clean['media_thumb'] = $IMG->origname;
						//$clean['media_thumb'] = $IMG->filename;

						$OBJ->db->updateArray(PX.'media', $clean, "media_id='" . $_GET['xid'] . "'");
						//phpinfo(); exit;
					}
					else
					{
						// update the order
						$OBJ->db->updateRecord("UPDATE ".PX."media SET
							media_order = media_order + 1 
							WHERE 
							media_ref_id = '$go[id]'");

						$clean['media_id'] = 'NULL';
						$clean['media_order'] = '1';
						$clean['media_ref_id'] = $go['id'];
						$clean['media_file'] = $IMG->origname;
						//$clean['media_thumb'] = $IMG->filename;
						$clean['media_mime'] = strtolower($thetype);
						$clean['media_obj_type'] = 'exhibits';
						$clean['media_x'] = $IMG->size[0];
						$clean['media_y'] = $IMG->size[1];
						$clean['media_kb'] = $IMG->orig_kb;

						$date = getNow();
						$clean['media_udate'] = $date;
						$clean['media_uploaded'] = $date;

						//$clean['media_dir'] = '';

						$OBJ->db->insertArray(PX.'media', $clean);
					}

					@chmod($IMG->path . '/' . $IMG->filename, 0755);
					
					// return the new filename
					return $IMG->origname;
				}
				else // other files...video
				{
					$IMG->image = $IMG->path . '/' . $IMG->filename;
					
					// videos get an auto thumb
					if (in_array($thetype, array_merge($default['media'], $default['services'])))
					{
						$thumb = $IMG->make_video_image($IMG->filename);
						$clean['media_thumb'] = $thumb;
						// defaults so people can't forget
						$clean['media_x'] = 600;
						$clean['media_y'] = 400;
					}

					$OBJ->db->updateRecord("UPDATE ".PX."media SET
						media_order = media_order + 1 
						WHERE 
						media_ref_id = '$go[id]'");

					$clean['media_id'] = 'NULL';
					$clean['media_order'] = '1';
					$clean['media_ref_id'] = $go['id'];
					$clean['media_file'] = $IMG->filename;
					$clean['media_mime'] = strtolower($thetype);
					$clean['media_obj_type'] = 'exhibits';

					$date = getNow();
					$clean['media_udate'] = $date;
					$clean['media_uploaded'] = $date;

					$clean['media_kb'] = $IMG->file_size; // ???

					$OBJ->db->insertArray(PX.'media', $clean);

					@chmod($IMG->path . '/' . $IMG->filename, 0755);
					
					// return the new filename
					return $IMG->origname;
				}
			//}
		}
	}
	
	public function photoblog($file, $size, $type, $coverart=false)
	{
		$OBJ =& get_instance();
		global $go, $default;

		load_module_helper('files', $go['a']);

		$IMG =& load_class('media', true, 'lib');

		// we'll query for all our defaults first...
		$rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape   
			FROM ".PX."objects    
			WHERE id = '$go[id]'");

		// we need to get these from some defaults someplace
		$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
		$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
		$IMG->quality = $default['img_quality'];
		$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
		$IMG->makethumb	= true;

		load_helper('output');
		$URL =& load_class('publish', TRUE, 'lib');

		// +++++++++++++++++++++++++++++++++++++++++++++++++++

		$new_images['name'] = $file;
		$new_images['temp'] = $type;
		$new_images['size'] = $size;

		$test = explode('.', strtolower($new_images['name']));
		$thetype = array_pop($test);

		// need to deal with all the rest..
		if (!in_array($thetype, $default['images']))
		{
			$IMG->path = DIRNAME . BASEFILES . '/';
		}
		else
		{
			$IMG->path = DIRNAME . GIMGS . '/';
		}

		$x = 0;
		$added_x = array();

		//if ($new_images['size'] < $IMG->upload_max_size)
		//{
			//$test = explode('.', strtolower($new_images['name']));
			$test = explode('.', $new_images['name']);
			$thetype = array_pop($test);

			// check for black and white flag
			// another time...
			if (preg_match('/^bw_/', $test[0]))
			{
				$IMG->bw_flag = true;
			}

			$URL->title = implode('_', $test);
			//$new_title = $URL->processTitle();
			$new_title = $URL->title;

			$IMG->type = '.' . strtolower($thetype);
			//$IMG->filename = $IMG->checkName($_POST['id'] . '_' . $new_title) . '.' . $thetype;
			$IMG->filename = $IMG->checkName($new_title) . '.' . strtolower($thetype);
			//$IMG->filename = $new_title . '.' . $thetype;
			$IMG->origname = $IMG->filename;

		// .swf will report dimensions it seems
		//if (in_array($thetype, array_merge($default['images'], $default['media'])))
		//{
			//print_r($new_images);
			//echo $IMG->path . '/' . $IMG->filename;
			// if uploaded we can work with it
			if (move_uploaded_file($new_images['temp'], $IMG->path . '/' . $IMG->filename)) 
			{
				$x++;

				// images
				if (in_array($thetype, $default['images']))
				{
					$IMG->id = $go['id'] . '_';
					$IMG->filename = $IMG->filename;

					$IMG->image = $IMG->path . '/' . $IMG->filename;
					$IMG->uploader();
					//phpinfo(); exit;

					if ($coverart == true)
					{
						$clean['media_thumb'] = $IMG->origname;
						//$clean['media_thumb'] = $IMG->filename;

						$OBJ->db->updateArray(PX.'media', $clean, "media_id='" . $_GET['xid'] . "'");
						//phpinfo(); exit;
					}
					else
					{
						// update the order
						$OBJ->db->updateRecord("UPDATE ".PX."media SET
							media_order = media_order + 1 
							WHERE 
							media_ref_id = '$go[id]'");

						$clean['media_id'] = 'NULL';
						$clean['media_order'] = '1';
						$clean['media_ref_id'] = $go['id'];
						$clean['media_file'] = $IMG->origname;
						//$clean['media_thumb'] = $IMG->filename;
						$clean['media_mime'] = strtolower($thetype);
						$clean['media_obj_type'] = 'exhibits';
						$clean['media_x'] = $IMG->size[0];
						$clean['media_y'] = $IMG->size[1];
						$clean['media_kb'] = $IMG->orig_kb;

						$date = getNow();
						$clean['media_udate'] = $date;
						$clean['media_uploaded'] = $date;

						//$clean['media_dir'] = '';

						$OBJ->db->insertArray(PX.'media', $clean);
					}

					@chmod($IMG->path . '/' . $IMG->filename, 0755);
					
					// return the new filename
					return $IMG->origname;
				}
				else // other files
				{
					$IMG->image = $IMG->path . '/' . $IMG->filename;

					$OBJ->db->updateRecord("UPDATE ".PX."media SET
						media_order = media_order + 1 
						WHERE 
						media_ref_id = '$go[id]'");

					$clean['media_id'] = 'NULL';
					$clean['media_order'] = '1';
					$clean['media_ref_id'] = $go['id'];
					$clean['media_file'] = $IMG->filename;
					$clean['media_mime'] = strtolower($thetype);
					$clean['media_obj_type'] = 'exhibits';

					$date = getNow();
					$clean['media_udate'] = $date;
					$clean['media_uploaded'] = $date;

					$clean['media_kb'] = $IMG->file_size; // ???

					$OBJ->db->insertArray(PX.'media', $clean);

					@chmod($IMG->path . '/' . $IMG->filename, 0755);
					
					// return the new filename
					return $IMG->origname;
				}
			//}
		}
	}
}