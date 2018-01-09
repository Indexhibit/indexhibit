<?php define('SITE', 'Bonjour!');

// -----------------------------------------------------------
// 	WELCOME TO INDEXHIBIT
// -----------------------------------------------------------

// hide errors for the live site
error_reporting(0);

// because we are backwards compatible
ini_set('display_errors', 'Off');

// the basics
if (file_exists('../ndxzsite/config/config.php')) require_once '../ndxzsite/config/config.php';

require_once '../ndxzsite/config/options.php';
require_once 'defaults.php';
require_once 'common.php';

// timer
$default['timer'] = microtime_float();

// make sure we have our connection array
shutDownCheck();
	
// preloading helpers
load_helpers(array('html', 'entrance', 'time', 'server'));

// general tools for loading things
$OBJ =& load_class('core', FALSE, 'lib');	
$OBJ =& load_class('router', TRUE, 'lib');

// we need to get our abstracts
$OBJ->abstracts->get_system_abstracts();

// set defaults
$OBJ->vars->default = $default;

// load hooks
$OBJ->hook->load_hooks();

// make a hook to change defaults? later

// are we logged in?
$OBJ->access->checkLogin();

// get user preferences
$OBJ->lang->setlang($OBJ->access->prefs['user_lang']);

// loading our module object
$OBJ->goto_module($OBJ->go['a']);

// load collections add-on if it exists
$OBJ->load_collector($OBJ->go['a'], $OBJ->go['oid']);

// we load this before anything happens so we can override
// you can only have one at a time...
if ($OBJ->hook->registered_hook('update_module')) $OBJ->extend_module($OBJ->hook->action_table['update_module']);

// pretunnel hooks

// goto the module method. submits happen here too.
$OBJ->tunnel($OBJ->go['a'], $OBJ->go['a'], $OBJ->go['q']);

// output
header('Content-Type: text/html; charset=utf-8'); 
$OBJ->template->output('index');
exit;