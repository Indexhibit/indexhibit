<?php

/*
Plugin Name: Site Password
Plugin URI: http://www.indexhibit.org/plugin/site-password/
Description: Password protects site globally
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: site_protect
Function: site_password:protect
Space:
Order: 1
Options Builder: make_option
End
*/

class site_password
{
	var $password;
	var $has_access = false;
	var $notification;
	var $flag = false;
	var $options = array();
	
	function make_option()
	{
		$html = '';

		$password = (isset($this->options['password'])) ? $this->options['password'] : '';
		
		if ($password != '')
		{
			$html .= "<label>Password Hash</label>\n";
			$html .= "<p>$password</p>\n";
		}
		
		$html .= "<label>Password</label>\n";
		$html .= "<p><input name='option[md5_password]' value='' /></p>\n";
		
		return $html;
	}
	
	function protect()
	{
		$OBJ =& get_instance();
		
		// is the hook blank? no password...
		if (!isset($OBJ->hook->options['site_password']['password'])) return;
		if ($OBJ->hook->options['site_password']['password'] == '') return;
		
		// look for cookie
		$this->cookie_check();
		
		if ($this->has_access)
		{
			return;
		}
		else
		{
			// check for $_POST?
			$this->submit_check();
			
			if (!$this->has_access)
			{
				echo $this->template();
				exit;
			}
		}
	}
	
	function display_login()
	{
		if ($this->flag == true) return;

		return "<form name='login' method='post' action=''><p><label>Enter Password</label>
<input id='password' type='password' name='password' value='' /></p>
<p><button type='submit' name='submitter' class='button'>Submit</button></p>
<input type='hidden' name='check' value='' /></form>";
	}
	
	function template()
	{
		return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<title>Password Protected Site</title>
<style type='text/css'>
body { margin: 60px; font: 12px 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 18px; }
body { background: red; color: #fff; }
label { display: block; margin-bottom: 3px; font-weight: bold; }
input[type='password'] { width: 250px; border: none; padding: 4px; font-size: 15px; background: white; }
.button { padding: 5px 10px; display: inline; background: #777; border: none; color: #fff; cursor: pointer;
font-weight: bold; }
.button:hover { background-position: 0 -48px; background: #0c0; }
.button:active { background-position: 0 top; position: relative; top: 1px; padding: 6px 10px 4px; }
</style>
</head>
<body>
<h1>This site is password protected.</h1>
" . $this->display_login($this->flag) . "
<div id='notification'>" . $this->notification . "</div>
</body>
</html>";
	}
	
	
	function cookie_check()
	{
		if (isset($_COOKIE['site_password_count']))
		{
			if ($_COOKIE['site_password_count'] >= 4)
			{
				$this->notification = 'Please contact the owner of this website for help.';
				
				$this->flag = true;
				echo $this->template();
				exit;
			}
		}
		
		if (isset($_COOKIE['site_password']))
		{
			$OBJ =& get_instance();
			
			if ($_COOKIE['site_password'] == $OBJ->hook->options['site_password']['password'])
			{
				$this->has_access = true;
				
				setcookie('site_password_count', 0, time(), '/');
			}
		}
	}
	
	function submit_check()
	{
		if (isset($_POST['password']))
		{
			if (function_exists('sleep')) sleep(2);
			
			// it's supposedly not a bot
			if ($_POST['check'] == '')
			{
				$OBJ =& get_instance();

				// load processor and validate
				$P = load_class('processor', true, 'lib');
				
				$password = $P->process('password', array('alphanum'));
				$password = md5(sha1(md5(sha1($password))));
				
				if ($password == $OBJ->hook->options['site_password']['password'])
				{
					$this->has_access = true;
					
					// register the cookie
					setcookie('site_password', $password, time()+3600*24*2, '/');
					setcookie('site_password_count', 0, time(), '/');
				}
				else
				{	
					// count the attempts
					$attempts = (isset($_COOKIE['site_password_count'])) ?
						$_COOKIE['site_password_count'] : 0;
						
					$attempts = $attempts + 1;
						
					$this->notification = 'Wrong password';
					
					if ($attempts >= 1)
					{
						$this->notification .= ' - you have ' . (5 - $attempts) . ' attempts left.';
					}
					
					setcookie('site_password_count', $attempts, time()+3600*24*2, '/');
				}
			}
		}
	}
}