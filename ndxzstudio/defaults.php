<?php if (!defined('SITE')) exit('No direct script access allowed');

// darn quotes
ini_set("magic_quotes_runtime", 0);

// version
define('VERSION', '2.1.6');
	
// Paths/definitions of things (relative to index file)
define('LIBPATH', 'lib');
define('HELPATH', 'helper');
define('MODPATH', 'module');
define('DBPATH', 'db');
define('LANGPATH', 'lang');
define('EXTPATH', 'ext');

// paths to internal parts
define('ASSET', 'asset/');
define('CSS', ASSET . 'css/');
define('JS', ASSET . 'js/');
define('IMG', ASSET . 'img/');
define('TPLPATH', ASSET . 'tpl/');

// improve this later
$adjust = @realpath(dirname(__FILE__));
$adjust = str_replace(DIRECTORY_SEPARATOR, '/', $adjust);
define('BASENAME', '/ndxzstudio');
define('DIRNAME', str_replace(BASENAME, '', $adjust));

// let's make our root path
$self = '';
$protocol   = (empty($_SERVER['HTTPS'])) ? 'http' : 'https';
$servername = $_SERVER['SERVER_NAME'];
$serverport = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':' . $_SERVER['SERVER_PORT'];

$path = dirname($_SERVER["SCRIPT_NAME"]);
$path = str_replace('/ndxzstudio', '', $path);
$base = $protocol . '://' . preg_replace('/\/+/', '/', $servername . $serverport . $path);
$base = str_replace(':443', '', $base);
define('BASEURL', preg_replace("/\/$/i", '', $base)); // no trailing slashes

// Add default types for files, images and movies upload
// if we add new 'media' we need to update modedit.js
$default['images'] 	= array('jpg', 'gif', 'png');
$default['media']	= array('mov', 'mp4');
$default['sound']	= array('mp3');
$default['files']	= array('txt', 'pdf', 'doc', 'xls', 'eps', 'dwg', 'zip');
$default['flash'] 	= array('swf'); // separate because we can get dims from it
$default['link']	= array('url');

// EXPERIMENTAL - video basically
$default['services'] = array('youtube', 'vimeo');
$default['video'] = array_merge($default['media'], $default['services']);

// these are all the formats we can display
$medias = array_merge($default['images'], $default['media'], $default['flash'], $default['services']);
$default['medias'] = array_merge($default['images'], $default['media'], $default['flash'], $default['services']);

// kinds of sections
$default['section_types'] = array(0 => 'default', 1 => 'chronological', 3 => 'tags');

// this drives the tabs in system/admin
$default['system_admin'] = array('theme', 'formats', 'plugins', 'spacer', 'settings', 'sections', 'assets', 'tag', 'spacer', 'statistics', 'spacer', 'users');

// files sources
$default['filesource'] = array('exhibit', 'all', 'section', 'subsection', 'tag', 'sections');

$default['operands'] = array(0 => 'default', 1 => 'permalinked', 2 => 'overlay', 3 => 'unlinked');

$default['parse'] = 'parse_default';
