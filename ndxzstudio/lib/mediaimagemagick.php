<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
 * Media class
 *
 * Resizes and thumbnails images using image magick library.
 * It requires at least IM 6.3.8-3 and is developed using IM 6.6.8-4.
 *
 * @version 1.0
 * @author Martijn
 * @author Lukasz Mordawski <lukasz.mordawski@gmail.com>
 */
class MediaImageMagick
{

    # --- --- --- --- ---
    # Image Magick
    # --- --- --- --- --- --- --- --- --- ---
    // TODO: Add __construct() that sets this variable from the config.

    public $im = '/usr/bin/convert';

    # --- --- --- --- ---
    # Variables
    # --- --- --- --- --- --- --- --- --- ---
    // TODO: Add descriptions.
    // NOTE: Should all these variables be public?
    //       Looks like some can be removed, such as offset.

    public $image;
    public $path;
    public $filename;
    public $quality;
    public $filemime;
    public $sizelimit;
    public $maxsize;
    public $thumbsize;
    public $sys_thumb = 100;
    public $size = array();
    public $new_size = array();
    public $final_size = array();
    public $out_size = array();
    public $uploads = array();
    public $offset = array();
    public $sys_size = array();
    public $type;
    public $input_image;
    public $upload_max_size;
    public $file_size;
    public $id;
    public $origname;
    public $orig_kb;
    public $bw_flag = false;
    public $make_sys = true;
    public $make_image = true;
    public $makethumb = true;
    public $makesysthumb = true;
    public $cinematic = false;
    public $shape = 0;
    public $shapes = array(0 => 'proportional', 1 => 'square', 2 => '4x3', 3 => 'cinematic', 4 => '3x2');
    public $new_filename = false;
    public $filename_override = false;
    public $output_path;
    public $odim = array();

    # --- --- --- --- ---
    # From the old file
    # --- --- --- --- --- --- --- --- --- ---

    public function __construct($im)
    {
        // TODO: Remove usage of global? Is there a $OBJ available to get defaults?
        global $default;
        $this->im = $im;
        $this->uploads = $default;
        $this->upload_max_size();
    }

    /**
     * Returns allowed uploads (filetypes from config.php) array and max size
     *
     * @param void
     * @return mixed
     */
    public function Media($im)
    {
        self::__construct($im);
    }

    /**
     * Creates dynamic thumbnails for displays on all, section and tag pages
     * only outputs to the 'dimgs' folder
     *
     * @param array
     * @param array
     * @return void
     */
    public function autoResize($img = array(), $page = array())
    {
        // TODO: Rewrite? Clean-up?
        global $go, $default;
        $this->thumbsize = ($page['thumbs'] != '') ? $page['thumbs'] : 200;
        $this->maxsize = ($page['images'] != '') ? $page['images'] : 9999;
        $this->maxwidth = $this->maxsize;
        $this->quality = $default['img_quality'];
        $this->shape = ($page['thumbs_shape'] != '') ? $page['thumbs_shape'] : 0;
        $this->make_image = false;
        $this->makethumb = true;
        $this->make_sys = false;
        $this->makesysthumb = false;
        // source path
        if ($img['media_dir'] == '') {
            $this->path = DIRNAME . GIMGS . '/';
        } else { // we need to find the path to thumb
            // image uploaded via folder
            if (in_array($img['media_mime'], $default['images'])) {
                // this works?
                if ($img['media_thumb'] != '') {
                    $this->path = DIRNAME . "/files/gimgs/";
                } else {
                    $this->path = DIRNAME . "/files/$img[media_dir]/";
                }
            } else {
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
        if (!file_exists($this->output_path . '/' . $this->filename_override)) {
            $this->image = $this->path . '/' . $this->filename;
            $this->uploader();

            if (function_exists('chmod')) chmod($this->image, 0777);
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

    /**
     * ???
     *
     * @param ???
     * @param ???
     * @return ???
     */
    public function regenerate($id = 0, $file = '')
    {
        $OBJ =& get_instance();
        global $default;

        // but we need our inputs...
        // we'll query for all our defaults first...
        $rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape    
			FROM " . PX . "objects    
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

        // original sizes of things
        $this->size = getimagesize($this->image);
        $this->orig_kb = str_replace('.', '', @filesize($this->image));

        if ($this->make_image == true) $this->make_image();

        if ($this->makethumb == true) {
            // this is where we deal with shape of thumbnail
            $this->shape = ($this->shape == '') ? '0' : $this->shape;
            $thumbed = 'make_thumbnail_' . $this->shapes[$this->shape];
            $this->$thumbed();
        }

        if (($this->makethumb == true) && ($this->makesysthumb == true)) $this->make_systhumb();

        if ($this->make_sys == true) $this->make_system();
    }

    /**
     * ???
     *
     * @param ???
     * @param ???
     * @return ???
     */
    public function user_image()
    {
        $this->getFileType();
        $this->size = getimagesize($this->image);
        $this->orig_kb = str_replace('.', '', @filesize($this->image));
        $this->make_user_thumbnail();
    }

    /**
     * ???
     *
     * @param ???
     * @param ???
     * @return ???
     */
    public function cover_image()
    {
        // TODO: Work around the @.
        $this->getFileType();
        $this->size = getimagesize($this->image);
        $this->orig_kb = str_replace('.', '', @filesize($this->image));
        $this->make_thumbnail_cinematic();
    }

    /**
     * Returns file size
     *
     * @param void
     * @return integer
     */
    public function file_size()
    {
        // TODO: Work around the @.
        $size = str_replace('.', '', @filesize($this->image));
        $this->file_size = ($size == 0) ? 0 : $size;
    }

    # --- --- --- --- ---
    # Video functions
    # --- --- --- --- --- --- --- --- --- ---
    // TODO: Redo these!

    /**
     * ???
     *
     * @param ???
     * @param ???
     * @return ???
     */
    public function make_video_image($source_name)
    {
        $test = explode('.', $source_name);
        $thetype = array_pop($test);
        $new_name = str_replace($thetype, 'gif', $source_name);

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

    /**
     * ???
     *
     * @param ???
     * @param ???
     * @return ???
     */
    public function video_thumbnailer()
    {
        // make a blank neutral image for the videos

        $this->getFileType();
        $this->get_input();

        // original sizes of things
        $this->size = getimagesize($this->image);
        $this->orig_kb = str_replace('.', '', @filesize($this->image));

        if ($this->make_image == true) $this->make_image();

        if ($this->makethumb == true) {
            // this is where we deal with shape of thumbnail
            $this->shape = ($this->shape == '') ? '0' : $this->shape;
            $thumbed = 'make_thumbnail_' . $this->shapes[$this->shape];
            $this->$thumbed();
        }

        if (($this->makethumb == true) && ($this->makesysthumb == true)) $this->make_systhumb();

        if ($this->make_sys == true) $this->make_system();

        imagedestroy($this->input_image);
    }

    # --- --- --- --- ---
    # Logic
    # --- --- --- --- --- --- --- --- --- ---

    public function checkName($filename)
    {
        $v = 1;
        while (file_exists($this->path . '/' . $filename . $this->type)) {
            $filename = preg_replace('/_v[0-9]+$/i', '', $filename);
            $filename .= '_v' . (++$v);
        }
        return $filename;
    }

    public function uploader()
    {
        // NOTE: should this be $this->type?
        $type = explode('.', $this->filename);
        $this->filemime = array_pop($type);
        $this->size = getimagesize($this->image);
        $this->orig_kb = str_replace('.', '', @filesize($this->image));
        if ($this->make_image == true) $this->make_image();
        if ($this->makethumb == true) {
            $this->shape = ($this->shape == '') ? '0' : $this->shape;
            $thumbed = 'make_thumbnail_' . $this->shapes[$this->shape];
            $this->$thumbed();
        }
        if (($this->makethumb == true) && ($this->makesysthumb == true)) $this->make_systhumb();
        if ($this->make_sys == true) $this->make_system();
    }

    # --- --- --- --- ---
    # Images
    # --- --- --- --- --- --- --- --- --- ---

    public function make_image()
    {
        $out = ($this->output_path == '') ? realpath($this->path) . '/' . $this->id . $this->filename : realpath($this->output_path) . '/' . $this->id . $this->filename;
        if ($this->maxsize != 9999) { // Why 9999 ?
            exec($this->im . ' -resize ' . intval($this->maxsize) . 'x' . intval($this->maxsize) . '\> ' . $this->quality() . realpath($this->image) . ' ' . $out);
        } else {
            exec($this->im . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        }
        if (function_exists('chmod')) chmod($out, 0777);
    }

    # --- --- --- --- ---
    # Thumbnail functions
    # All of these need a value for @thumbsize. Except _systhumb and _system, those use internal settings.
    # --- --- --- --- --- --- --- --- --- ---

    public function make_systhumb()
    {
        // TODO: Stop using global default;?
        global $default;
        $out = $this->output_path('systh-');
        exec($this->im . ' -resize ' . intval($default['systhumb']) . 'x' . intval($default['systhumb']) . '\> ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_system()
    {
        $out = $this->output_path('sys-');
        exec($this->im . ' -resize ' . intval($this->sys_thumb) . 'x' . intval($this->sys_thumb) . '^ -gravity center -extent ' . intval($this->sys_thumb) . 'x' . intval($this->sys_thumb) . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_thumbnail_proportional()
    {
        $out = $this->output_path();
        exec($this->im . ' -resize ' . intval($this->thumbsize) . 'x' . intval($this->thumbsize) . '\> ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_thumbnail_cinematic()
    {
        $out = $this->output_path();
        $width = $this->thumbsize;
        $height = round($this->thumbsize / 16 * 9);
        exec($this->im . ' -resize ' . intval($width) . 'x' . intval($height) . '\^ -gravity center -extent ' . intval($width) . 'x' . intval($height) . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_thumbnail_4x3()
    {
        $out = $this->output_path();
        $width = $this->thumbsize;
        $height = round($this->thumbsize / 4 * 3);
        exec($this->im . ' -resize ' . intval($width) . 'x' . intval($height) . '\^ -gravity center -extent ' . intval($width) . 'x' . intval($height) . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_thumbnail_3x2()
    {
        $out = $this->output_path();
        $width = $this->thumbsize;
        $height = round($this->thumbsize / 3 * 2);
        exec($this->im . ' -resize ' . intval($width) . 'x' . intval($height) . '\^ -gravity center -extent ' . intval($width) . 'x' . intval($height) . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_thumbnail_3x4()
    {
        $out = $this->output_path();
        $width = $this->thumbsize;
        $height = round($this->thumbsize / 3 * 4);
        exec($this->im . ' -resize ' . intval($width) . 'x' . intval($height) . '\^ -gravity center -extent ' . intval($width) . 'x' . intval($height) . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_thumbnail_square()
    {
        $out = $this->output_path();
        $size = $this->thumbsize;
        exec($this->im . ' -resize ' . intval($size) . 'x' . intval($size) . '^ -gravity center -extent ' . intval($size) . 'x' . intval($size) . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    public function make_user_thumbnail()
    {
        $out = realpath(DIRNAME . '/files/') . '/' . $this->filename;
        $size = $this->thumbsize;
        exec($this->im . ' -resize ' . intval($size) . 'x' . intval($size) . '^ -gravity center -extent ' . intval($size) . 'x' . intval($size) . ' ' . $this->quality() . realpath($this->image) . ' ' . $out);
        if (function_exists('chmod')) chmod($out, 0777);
    }

    # --- --- --- --- ---
    # Helper functions
    # --- --- --- --- --- --- --- --- --- ---

    /**
     * Returns the absolute (real) path to save a new image to.
     * Combines @output_path, @path, @filename_override, and @filename.
     * Default filenames are build up from a prefix ($prefix),
     * the @id and the @filename values.
     *
     * @param string $prefix
     * @return string
     */
    private function output_path($prefix = 'th-')
    {
        if (is_string($this->filename_override) && strlen($this->filename_override) > 0) {
            $out = realpath(DIRNAME . '/files/dimgs/') . '/' . $this->filename_override;
        } else {
            if (is_string($this->output_path) && strlen($this->output_path) > 0 && $path = realpath($this->output_path)) $out = $path;
            else $out = realpath($this->path);
            $out .= '/' . $prefix . $this->id . $this->filename;
        }
        return $out;
    }

    /**
     * Returns quality flag for JPEG images, based on @filemime.
     *
     * @param void
     * @return string
     */
    private function quality()
    {
        // TODO: remove scope breaking global;
        global $default;
        $q = $default['img_quality'];
        if ($this->filemime === 'jpg' || $this->filemime === 'jpeg') {
            $q = is_object($q) ? 1 : intval($q);
            if ($q < 1) $q = 1;
            else if ($q > 100) $q = 100;
            return '-quality ' . $q . ' ';
        }
        return '';
    }

    /**
     * Returns array of sizes:
     *  1: width to scale to,
     *  2: height to scale to,
     *  3: width to cut to,
     *  4: height to cut to.
     *
     * Originally used for @make_thumbnail_cinematic():
     *    $resizers = $this->resizer($this->thumbsize, $this->size[1], $this->size[0], 16/9);
     *    exec($this->im.' -resize '.$resizers[0].'x'.$resizers[1].'\! -gravity center -extent '.$resizers[2].'x'.$resizers[3].' '.realpath($this->image).' '.$name);
     *
     * Not used anymore, but should possibly be ported to the GD library?
     *
     * @param int wanted width, eg 200
     * @param int original height, eg 400
     * @param int original width, eg 600
     * @param float ratio of new bounding box, eg 16/9
     * @return array
     */
    private function resizer($wantedWidth, $oHeight, $oWidth, $ratio)
    {
        $newWidth = $wantedWidth; // Assume we can scale to wanted width.
        $newHeight = $oHeight * ($wantedWidth / $oWidth); // Calculate scaled height.
        $wantedHeight = $wantedWidth / $ratio; // The height we want with current width.
        if ($newHeight < $wantedHeight) { // The scaled height does not cut it.
            $newHeight = $wantedHeight; // Go with the wanted height instead.
            $newWidth = $oWidth * ($newHeight / $oHeight); // Scale with according to wanted height.
        }
        return array(round($newWidth), round($newHeight), round($wantedWidth), round($wantedHeight));
    }

}