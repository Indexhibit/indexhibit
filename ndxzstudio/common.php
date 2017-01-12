<?php if (!defined('SITE')) exit('No direct script access allowed');

if (function_exists('date_default_timezone_set')) date_default_timezone_set('GMT');

// this is a little redundant
function load_path($type)
{
	switch ($type) {
		case 'lib':
			return LIBPATH;
			break;
		case 'db':
			return DBPATH;
			break;
		case 'help':
			return HELPATH;
			break;
		case 'mod':
			return MODPATH;
			break;
		case 'lang':
			return LANGPATH;
			break;	
	}
}

// can't return direct references (weird bug)
function &get_instance()
{
	global $OBJ;
	
	$reference = $OBJ;
	return $reference;
}


// concept grabbed mostly from CodeIgniter (and Zend) - sorry it's been dumbed down so much. ;)
// this is a little ugly but it works
function &load_class($class, $instantiate = TRUE, $type, $internal = FALSE)
{
	global $indx;
	
	static $objects = array();
	
	$path = load_path($type);
	$file = $class;
	
	// exceptions
	if ($type == 'db') $file = 'db.' . $indx['sql'];
	if ($type == 'lang') $file = 'index';
	
	// we really need to clean this file up
	if ($type == 'collect')
	{
		if (file_exists(DIRNAME . '/ndxzstudio/module/collect/' . $file . '.php'))
		{
			// our extended modules
			require_once(DIRNAME . '/ndxzstudio/module/collect/' . $file . '.php');
		}
		else
		{
			show_error($file . ' class not found');
		}
		

		if ($instantiate == TRUE)
		{
			$objects[$class] = new $class();
		}
		else
		{
			$objects[$class] = TRUE;
		}
		
		return $objects[$class];
	}
	
	// we really need to clean this file up
	// extend files should be loaded with the call
	if ($type == 'extend')
	{
		/*
		if (file_exists(DIRNAME . '/ndxzstudio/lib/' . $file . '.php'))
		{
			// our extended modules
			require_once(DIRNAME . '/ndxzstudio/lib/' . $file . '.php');
		}
		else
		{
			show_error($file . ' class not found');
		}
		*/
		
		if ($instantiate == TRUE)
		{
			$objects[$class] = new $class();
		}
		else
		{
			$objects[$class] = TRUE;
		}
		
		return $objects[$class];
	}

	// REVIEW 'LOCAL'
	// loaded via plugin
	if ($type == 'local')
	{
		if (!isset($objects[$class]))
		{
			if ($instantiate == TRUE)
			{
				$objects[$class] = new $class();
			}
			else
			{
				$objects[$class] = TRUE;
			}
		}

		return $objects[$class];
	}
	
	
	// extending the 'module'
	if ($type == 'template')
	{
		global $go;
		
		// we'll need to add the loader for extend as well
		if (file_exists(DIRNAME . BASENAME . '/module/' . $go['a'] . '/' . $file . '.php'))
		{
			require_once(DIRNAME . BASENAME . '/module/' . $go['a'] . '/' . $file . '.php');
		}
		else
		{
			show_error($file . ' class not found');
		}

		if ($instantiate == TRUE)
		{
			$objects[$class] = new $class();
		}
		else
		{
			$objects[$class] = TRUE;
		}
		
		return $objects[$class];
	}

	
	if (!isset($objects[$class]))
	{
		if ($internal == FALSE)
		{
			if (file_exists(DIRNAME . BASENAME . '/' . $path . '/' . $file . '.php'))
			{
				require_once(DIRNAME . BASENAME . '/' . $path . '/' . $file . '.php');
			}
			else
			{
				show_error($file . ' class not found');
			}
	
			if ($instantiate == TRUE)
			{
				$objects[$class] = new $class();
			}
			else
			{
				$objects[$class] = TRUE;
			}
		}
		else // TRUE
		{
			if (file_exists(DIRNAME . '/ndxzsite/extend/module/' . $file . '/index.php'))
			{
				// our extended modules
				require_once(DIRNAME . '/ndxzsite/extend/module/' . $file . '/index.php');
			}
			elseif (file_exists(DIRNAME . BASENAME . '/' . $path . '/' . $file . '/index.php'))
			{
				// our regular modules
				require_once(DIRNAME . BASENAME . '/' . $path . '/' . $file . '/index.php');
			}
			else
			{
				show_error($file . ' class not found');
			}
			

			if ($instantiate == TRUE)
			{
				$objects[$class] = new $class();
			}
			else
			{
				$objects[$class] = TRUE;
			}
		}
	}
	
	return $objects[$class];
}


// frontend helpers
function load_plugin($file)
{
	if ($file == '') return;
	
	if (file_exists(DIRNAME . '/ndxzsite/plugin/plugin.' . $file . '.php'))
	{
		require_once(DIRNAME . '/ndxzsite/plugin/plugin.' . $file . '.php');
	}
}


// from the helper folder
function load_helper($file)
{
	if ($file == '') return;
	
	if (file_exists(DIRNAME . BASENAME . '/' . HELPATH . '/' . $file . '.php'))
	{
		require_once(DIRNAME . BASENAME . '/' . HELPATH . '/' . $file . '.php');
	}
}


// load multiple helpers
function load_helpers($files)
{
	if (!is_array($files)) return;
	
	foreach ($files as $file) load_helper($file);
}


// loading helpers for a module (located in module folder)
function load_module_helper($file, $section)
{
	if ($file == '') return;
	
	$OBJ =& get_instance();
	
	if ($OBJ->template->extended == true)
	{
		if (file_exists(DIRNAME . '/ndxzsite/extend/module/' . $section . '/' . $file . '.php'))
			require_once(DIRNAME . '/ndxzsite/extend/module/' . $section . '/' . $file . '.php');
		return;
	}
	
	if (file_exists(DIRNAME . BASENAME . '/' . MODPATH . '/' . $section . '/' . $file . '.php'))
		require_once(DIRNAME . BASENAME . '/' . MODPATH . '/' . $section . '/' . $file . '.php');
}


function front_error($message = '', $code = 404)
{
	$OBJ =& get_instance();
	
	$codes = array(
		302	=> array('Moved Temporarily', 'Temporarily disposed.', 'Status: 302 Moved Temporarily'),
		403 => array('Forbidden', 'Access to a protected or private password protected folder was attempted.', 'Status: 403 Forbidden'),
		404 => array('Not Found', 'The requested file was not found.', 'Status: 404 Not Found'),
		503 => array('Service Unavailable', 'Service Unavailable.', 'Status: 503 Service Unavailable')
		);

	header('Content-type: text/html; charset=utf-8');
	header($codes[$code][0]);
	
	$rs = $OBJ->vars->exhibit;
	$rs['error_message'] = $message;
	
	// 302 is voluntary hibernate mode
	if ($code != 302)
	{
		$rs['obj_name'] = $codes[$code][0];
		$rs['error_message'] = $codes[$code][1];
	}

	ob_start();
	include_once DIRNAME . '/ndxzsite/errors.php';
	$buffer = ob_get_contents();
	ob_end_clean();
	echo $buffer;
}


function show_error($message='')
{
	// we'll use the default language for this
	$lang =& load_class('lang', TRUE, 'lib');
	$lang->setlang(); // get the default strings
	
	$message = $lang->word($message);
	
	$error =& load_class('errors', TRUE, 'lib');
	header('Status: 503 Service Unavailable'); // change to right error note
	echo $error->show_error($message);
	exit;
}


// could use refinement - rethink
function show_login($message='', $showreset=true, $no_load_login=false)
{
	$tooMany = false;

	// let's track failed login attempts
 	if (isset($_COOKIE['ndxz_accessed']))
	{
		// show reset form
		if ((int) $_COOKIE['ndxz_accessed'] > 3)
		{
			$tooMany = true;

			if ($showreset == false)
			{
				$tooMany = false;
			}
		}
		else
		{
			$tooMany = false;
		}
	}

	// we'll use the default language for this
	$lang =& load_class('lang', TRUE, 'lib');
	$lang->setlang(); // get the default strings
	
	if ($tooMany == true)
	{
		// form to reset the password and send info
		$login = "<form method='post' action=''>
		<h1>Indexhibit</h1>
		<br />
		<p>You have used up all of your login attempts. Please enter your email address to<br />retrieve your login and a new password or contact the site admin for help.</p>
		<p><strong>".$lang->word('email address').":</strong> 
			<input name='email' type='text' maxlength='50' /></p>
		<p><input name='retrievePassword' type='submit' value='".$lang->word('retrieve')."' class='login-button' /></p>
		<p>".$lang->word($message)."&nbsp;</p>
		</form>";
	}
	else
	{	
		if ($no_load_login == false)
		{
			$login = "<form method='post' action=''>
<h1>Indexhibit</h1>
<br />
<p><strong>".$lang->word('login').":</strong> 
<input name='uid' type='text' maxlength='100' /></p>
<p><strong>".$lang->word('password').":</strong> 
<input name='pwd' type='password' maxlength='32' /></p>
<p><input name='submitLogin' type='submit' value='".$lang->word('login')."' class='login-button' /></p>
<p>".$lang->word($message)."&nbsp;</p>
</form>";
		}
		else // do not show any login
		{
			$login = "<form>
<h1>Indexhibit</h1>
<br />
<p>".$lang->word($message)."&nbsp;</p>
</form>";
		}
	}
	
	$error =& load_class('errors', TRUE, 'lib');
	echo $error->show_login($login);
	exit;
}


/* system_redirect("?a=$go[a]&q=note&id=$last"); */
function system_redirect($params='')
{
	// do we need to put some validators on this?
	// don't want the extra slash
	$self = (dirname($_SERVER['PHP_SELF']) == '/') ? '' : dirname($_SERVER['PHP_SELF']);
	
	header('Location: http://' . $_SERVER['HTTP_HOST'] . $self . '/' .  $params);
	return;
}


// revise this later...
function entry_uri($uri='', $server_uri)
{
	$url = $server_uri;

	// remove any illegal chars first ' " $ * @
	// remove non alpha chars (a-zA-Z0-9-_/?# only)
	// all urls are lowercase
	$url = preg_replace(
		array("/[^a-zA-Z0-9?=#-_\/]/", '/\/+/'),
		array('', '/'), $url);
		
	$url = strtolower($url);

	// we need to remove the references (they can be found when necessary)
	$url = preg_replace("/\?(.*)$/", '', $url);

	$url = explode('/', $url);
	if (is_array($url)) array_shift($url);

	// if we aren't in the root we need to deal with it
	// allows our site to be more portable
	$uri = preg_replace(array("/^\//", "/\/$/"), array('', ''), $uri);

	if ($uri != '')
	{
		$delete_dir = explode('/', $uri);

		// we need to pop off the default root if it's set
		if (is_array($delete_dir))
		{
			foreach ($delete_dir as $dir)
			{
				if ($url[0] == $dir)
				{
					array_shift($url);
				}
			}
		}
	}

	// always must have / at the beginning for the db
	$url = '/' . implode('/', $url);

	// ouch, needs thought
	// trailing slash
	if ((substr($url,-1) != '/') && 
		(substr($url,-3) != 'php'))
		$url = $url . '/';
	
	// better?
	if (MODREWRITE == false) $url = str_replace('/index.php', '', $url);

	return $url;
}

// fore dealing with mod_rewrite issues
function ndxz_rewriter($url='')
{
	if (MODREWRITE == false)
	{
		if ($url == '/')
		{
			return '/';
		}
		else
		{
			return '/index.php' . $url;
		}
	}
	else
	{
		return $url;
	}
}


function shutDownCheck()
{
	global $indx;
	
	if (!isset($indx['db'])) { echo "Database is not installed."; exit; }
}


function media_check($uri)
{
	$OBJ =& get_instance();
	global $default;
	
	// preserve the original for caching
	//$OBJ->temp_uri = $uri;
	
	// remove final slash
	$temp_uri = preg_replace("/\/$/", '', $uri);
	
	// let's look for an extension
	//$test = substr($temp_uri, -4);
	
	$test = explode('.', $temp_uri);
	
	if (!is_array($test))
	{
		return $uri;
	}
	else
	{
		$test = array_pop($test);
	}

	
	$allowed = array_merge($default['images'], $default['media'], $default['sound'], $default['flash'], $default['services']);
	
	// use in array here?
	if (in_array($test, $allowed))
	{
		$tmp = explode('/', $temp_uri);
		$temp = array_pop($tmp);
		
		// rewrite the uri
		$uri = str_replace("$temp/", '', $uri);
		
		$OBJ->image_ext = $test;
		$OBJ->image_file = $temp;
		$OBJ->template_override = true;
		
		return $uri;
	}
	
	return $uri;
}


function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}