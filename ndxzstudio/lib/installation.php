<?php if (!defined('SITE')) exit('No direct script access allowed');

// we follow the directions of the extending files
// but what if we are skipping many version?
// don't make them classes?
// the should simply be protected instructions...

class Installation
{
	public $indexhibit_version;
	public $php_version;
	public $mysqli_version;
	public $template;
	public $ouptput;

	public function __construct()
	{
		require_once '../ndxzsite/config/options.php';
		require_once 'common.php';
		require_once './helper/entrance.php';
		require_once './helper/html.php';
		require_once './helper/time.php';
		require_once './lang/index.php';

		// the basic things
		//$this->mysqli_ver();
	}
	
	public function test()
	{
		$this->output = 'Yes!';
	}
	
	// common parts
	public function load_common()
	{
		require_once 'defaults.php';
		require_once 'common.php';
		require_once './helper/entrance.php';
		require_once './helper/html.php';
		require_once './helper/time.php';
		require_once './lang/index.php';

		$page = getURI('page', 0, 'alnum', 1);

		// set cookie
		if (isset($_POST['submitLang']) && ($_POST['user_lang'] != ''))
		{
			setcookie('install', $_POST['user_lang'], time()+3600);
			
			// couldn't i simply make a template and send the info?
			header("location:install.php?page=1");
		}

		// look for the cookie here
		$picked = (isset($_COOKIE['install'])) ? $_COOKIE['install'] : 'en-us';

		$lang = new Lang;
		$lang->setlang($picked);
	}
	
	public function new_installation()
	{
		// read the contents of 'new_install.php'
	}
	
	public function php_version()
	{
		
	}
	
	public function mysqli_ver($link)
	{
		$ver = mysqli_get_client_info($link);
		$num = explode('.', $ver);
		$this->mysqli_version = $num[0];
	}
	
	public function upgrade()
	{
		echo 'what?'; exit;
	}
	
	public function template()
	{
		return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<title>Install : Indexhibit</title>
<link type=\"text/css\" rel='stylesheet'  href=\"asset/css/style.css\" />
<style type='text/css'>
body { font-family: Arial, Helvetica, Verdana, sans-serif; font-size: 10px; }
h1, h2 { margin: 6px 0 0 3px; }
h2 { margin-bottom: 6px; }
p { margin: 0 0 6px 3px; font-size: 12px; width: 300px; }
p.red { color: #c00; }
code { margin: 18px 0; font-size: 12px; }
.ok { color: #0c0; padding-right: 9px; }
.ok-not { color: #f00; padding-right: 9px; }
#footer { border-top: none; }
#log-form { margin-left: 3px; }
</style>
</head>
<body>
<div id='all'>
<h1>Indexhibit</h1>
<div id='main'>
<form action='' method='post'>
<!-- the important things go here -->
" . $this->output . "
</form>
<div class='cl'><!-- --></div>
</div>
<div id='footer' class='c2'>
<div class='col'><a href='" . BASEURL . BASENAME . "/license.txt'>License</a></div>
<div class='cl'><!-- --></div>
</div>
</div>
</body>
</html>";
	}
}