<?php define('SITE', 'Bonjour!');

// the basics
if (file_exists('../../ndxzsite/config/config.php')) require_once '../../ndxzsite/config/config.php';
require_once '../../ndxzsite/config/options.php';
require_once '../../ndxzstudio/defaults.php';
require_once '../../ndxzstudio/common.php';

// load helpers
load_helpers(array('entrance', 'time'));

// core and hooks
$OBJ =& load_class('core', true, 'lib');
$OBJ->assign_core_front();
$OBJ->vars->default = $default;
$OBJ->hook->load_hooks_front();

// we need to make an url adjustment here
$OBJ->baseurl = str_replace('/ndxzsite/plugin', '', BASEURL);
$OBJ->vars->exhibit['baseurl'] = str_replace('/ndxzsite/plugin', '', BASEURL);

// where are we coming from?
$OBJ->vars->exhibit['ajax'] = true;
$OBJ->vars->exhibit['cms'] = false;

// let's get our page and function
$function = 'jxs_' . getPOST('jxs', null, 'alnum', 35);
$class = str_replace('jxs_', '', $function);

load_plugin($function);

// what about class?
if (class_exists($function)) 
{
    $classy = new $function();
	$output = $classy->output();
}
else
{
	if (function_exists($function))
	{
		$output = $function();
	}
}

// backup - we'll remove this in the future
$rs = array();

// we still need the plugins main helper files
require_once('index.php');

// we parse this so we can do anything
$OBJ->parse_class($OBJ->vars->default['parse']);
$OBJ->parse->vars = $OBJ->vars->exhibit;
$OBJ->parse->code = $output;
$output = $OBJ->parse->parsing();

header('Content-Type: text/html; charset=utf-8');
echo $output;
exit;