<?php if (!defined('SITE')) exit('No direct script access allowed');

// mod_rewrite is being used?
// in case the server does not have mod_rewrite
define('MODREWRITE', false);

// image quality
$default['img_quality'] = 90;
$default['systhumb'] = 150;

// images max size kilobytes
// be careful with shared hosting
$default['maxsize'] = 200;

// things you don't want stats to track
$default['ignore_ip'] = array();

// language default in case of error
define('LANGUAGE', 'en-us');

// for paths to files/images
define('BASEFILES', '/files');
define('GIMGS', BASEFILES . '/gimgs');

// use an editor, i guess...
$default['tinymce'] = false; // not yet

$default['parsing'] = false;
	
// cache time
$default['caching'] = false;
$default['cache_time'] = 15; // minutes

// first year
$default['first_year'] = 2004;
	
// define the default encoding
$default['encoding'] = 'UTF-8';

// basic sizes for images and thumbnails uploading
$default['thumbsize'] = array(150 => 150, 200 => 200, 225 => 225, 300 => 300);
$default['imagesize'] = array(400 => 400, 600 => 600, 700 => 700, 'full' => 9999);

// max exhibit images upload
$default['exhibit_imgs'] = 6;

// subsections within urls /more/more/more...
// not sure if this is 100% yet
$default['subdir'] = false;

$default['timer'] = false;

// media players defaults
$default['screencolor'] = 'f3f3f3';
$default['autoplay'] = false;

// default module
$default['module'] = 'exhibits';