<?php if (!defined('SITE')) exit('No direct script access allowed');


// a small set of validators specifically for dealing with
// get, post, cookies upon entry to system.
// not the same as our 'processor' class
function directions()
{
	global $default;
	
	$go['a'] 	= getURI('a', $default['module'], 'alpha', 15);
	$go['q']	= getURI('q', 'index', 'alpha', 15);
	$go['id']	= getURI('id', 0, 'digit', 5);
	$go['oid']	= getURI('oid', 0, 'digit', 5); // for collections
	$go['x']	= getURI('x', '', 'alpha', 15);
	
	return $GLOBALS['go'] = $go;
}


// we aren't using all of these...
// simple validation, returns a default if it's not right
function check_chars($default, $str='', $arr, $length) 
{
	$password 	= "/^[a-zA-Z0-9]+$/"; // login and password
	$digit 		= "/^[0-9]+$/"; // numbers only
	$alpha 		= "/^[a-z]+$/"; // lwr case letters only (roman chars)
	$alphaall 	= "/^[a-z]+$/i"; // upr & lwr letters only (roman chars)
	$alnum 		= "/^[a-z0-9]+$/"; // letters and numbers only
	$iso 		= "/^[a-z-_]+$/i"; // upr & lwr letters plus _-
	$email		= "/^[a-zA-Z0-9._-@]+$/i"; // email chars
	$connect	= "//"; // used at installer

	// temporary
	$none			= "//"; // not in use?

	// not working yet
	$special1 	= "/[a-zA-Z0-9]+$/"; // for mainurl info - not in use?
	
	// check string length
	if (strlen($str) <= $length) 
	{
		return (preg_match($$arr,$str)) ? $str : $default;
	}

	return $default;
}


// check out $_GET vars
function getURI($var, $default, $validate, $length, $upper=false)
{
	$uri = (isset($_GET[$var])) ?
		check_chars($default, $_GET[$var], $validate, $length) :
		$default;

	return ($upper == false) ? strtolower($uri) : $uri;
}


// check out $_POST vars
function getPOST($var, $default, $validate, $length)
{
	return (isset($_POST[$var])) ?
		check_chars($default, $_POST[$var], $validate, $length) :
		$default;
}


// check out $_COOKIE vars
function getCOOKIE($var, $default, $validate, $length)
{
	return ($var) ?
		check_chars($default, $var, $validate, $length) :
		$default;
}