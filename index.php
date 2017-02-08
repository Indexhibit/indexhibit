<?php define('SITE', 'Bonjour!');

// hide errors for the live site
error_reporting(0);

// because we are backwards compatible
ini_set('display_errors', 'Off');

// the basics
if (file_exists('ndxzsite/config/config.php')) require_once 'ndxzsite/config/config.php';

require_once 'ndxzsite/config/options.php';
require_once 'ndxzstudio/defaults.php';
require_once 'ndxzstudio/common.php';

$time_start = microtime_float();

// make sure we have our connection array
shutDownCheck();

// messy, but seems to work
$_REAL_SCRIPT_DIR = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
$_REAL_BASE_DIR = realpath(dirname(__FILE__));
$_MY_PATH_PART = substr($_REAL_SCRIPT_DIR, strlen($_REAL_BASE_DIR));
$uri = $_MY_PATH_PART
	? substr(dirname($_SERVER['SCRIPT_NAME']), 0, -strlen($_MY_PATH_PART))
	: dirname($_SERVER['SCRIPT_NAME']);

$uri = entry_uri($uri, $_SERVER['REQUEST_URI']);
$uri = str_replace($self, '', $uri);

// load the core
$OBJ =& load_class('core', true, 'lib');

// need to load up hooks
$OBJ->assign_core_front();

// set defaults
$OBJ->vars->default = $default;

// temporary for production
$OBJ->vars->default['isMobile'] = false;

$OBJ->hook->load_hooks_front();

// make a hook to change defaults
$OBJ->hook->do_action_array('update_defaults');

// check for cached
load_helpers(array('time'));
$CACHE =& load_class('cache', true, 'lib');
$CACHE->check_cached($uri); // let's check for post values to turn off caching

// if the cached page exists
if ($CACHE->cached == true)
{
	$CACHE->show_cached();
}
else
{
	// check for a preloading hook
	if ($OBJ->hook->registered_hook('site_protect')) $OBJ->hook->do_action('site_protect');

	// make a hook to change defaults?

	// media checks
	$uri = media_check($uri);

	// 'home' feature
	// if $uri == '/' switch query to search via 'home'
	if ($uri == '/')
	{
		$q['qry'] = "AND home = '1' ";
		$q['flag'] = true;
	}
	else
	{
		$q['qry'] = "AND url =  " . $OBJ->db->escape($uri) ;
		$q['flag'] = false;
	}

	// page query
	$OBJ->vars->exhibit = $OBJ->db->fetchRecord("SELECT *
		FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections
		INNER JOIN ".PX."settings ON adm_id = '1'
		WHERE status = '1'
		$q[qry]
		AND section_id = secid
		AND object = obj_ref_type");

	// second try - enforces / as default
	if ((!$OBJ->vars->exhibit) && ($q['flag'] == true))
	{
		$OBJ->vars->exhibit = $OBJ->db->fetchRecord("SELECT *
			FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections
			INNER JOIN ".PX."settings ON adm_id = '1'
			WHERE status = '1'
			AND url = " . $OBJ->db->escape($uri) . "
			AND section_id = secid
			AND object = obj_ref_type");
	}

	if (!$OBJ->vars->exhibit)
	{
		// try again with site root - the 'home' page
		$OBJ->vars->exhibit = $OBJ->db->fetchRecord("SELECT *
			FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections
			INNER JOIN ".PX."settings ON adm_id = '1'
			WHERE home = '1'
			AND status = '1'
			AND section_id = secid
			AND object = obj_ref_type");

		// we don't cache these
		$CACHE->defaults['caching'] = false;

		header("HTTP/1.1 404 Not Found");

		// we need a formal error page
		if (!$OBJ->vars->exhibit)
		{
			// we don't search engines indexing this
			front_error('File Not Found', 404);
			exit;
		}
	}

	// we need to let the system know whether it's front or back end...
	$OBJ->vars->exhibit['cms'] = false;

	// create the abstracts for exhibits or even images
	$OBJ->abstracts->front_abstracts();

	// autoload 'plugins' folder
	include_once DIRNAME . '/ndxzsite/plugin/index.php';

	// additional variables
	// perhaps we should port these differently?
	$OBJ->baseurl = BASEURL;
	$OBJ->vars->exhibit['baseurl'] = BASEURL;
	$OBJ->vars->exhibit['basename'] = BASENAME;
	$OBJ->vars->exhibit['basefiles'] = BASEFILES;
	$OBJ->vars->exhibit['gimgs'] = GIMGS;
	$OBJ->vars->exhibit['ajax'] = false;
	$OBJ->vars->exhibit['cms'] = false;

	// look into this later
	$GLOBALS['rs'] = $OBJ->vars->exhibit;

	// setup front end helper class
	$OBJ->lib_class('page', true, 'lib');

	// if device is mobile then use the mobile theme and format
	// enable this via plugin
	if ($OBJ->vars->default['isMobile'] == true)
	{ 		
		$OBJ->vars->exhibit['obj_theme'] = 'mobile';
		$OBJ->vars->exhibit['format'] = 'mobile';
	}
	
	if ($OBJ->hook->registered_hook('pre_load')) $OBJ->hook->do_action('pre_load');

	// loading the exhibit class
	$OBJ->page->loadExhibit();
	$OBJ->page->init_page();

	// is it special media?
	$OBJ->vars->exhibit['template'] = ($OBJ->template_override == true) ? 'media.php' : $OBJ->vars->exhibit['template'];

	$OBJ->parse_class($OBJ->vars->default['parse']);
	$OBJ->parse->vars = $OBJ->vars->exhibit;

	if ($OBJ->vars->default['isMobile'] == true)
	{ 		
		// if device is mobile then use the mobile theme and format
        $filename = DIRNAME . '/ndxzsite/mobile/index.php';
   	}
   	else
   	{
   		$template = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/' . $OBJ->vars->exhibit['template'])) ? $OBJ->vars->exhibit['template'] : 'index.php';
        $filename = DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/' . $template; 
	}

	$fp = @fopen($filename, 'r');
	$contents = fread($fp, filesize($filename));
	fclose($fp);

	$OBJ->parse->code = $contents;
	$output = $OBJ->parse->parsing();

	header('Content-Type: text/html; charset=utf-8');
	echo $output;

	// caching - if enabled and possible
	// we want media pages to cache as well
	$uri = ($OBJ->template_override == true) ? $OBJ->temp_uri : $uri;

	// no cache if a password page
	if ($OBJ->vars->exhibit['caching'] == true) $CACHE->makeCache($uri, $output);
	
	// statistics work better here now (adblocking)
	// enable via plugin
	$OBJ->hook->do_action('enable_statistics');
}
exit;