<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Access class
*
* User authentications
* 
* @version 1.0
* @author Vaska 
*/
class Access 
{
	public $settings		= array();
	public $prefs			= array();
	public $user_settings	= array();
	public $salt			= 'widewhitespace';
	
	public function __construct()
	{
		
	}
	
	/**
	* User logout 
	*
	* @param void
	* @return mixed
	*/
	public function logout()
	{
		setcookie('ndxz_hash', '', time()+3600*24*2);
		setcookie('ndxz_access', '', time()+3600*24*2);
		setcookie('ndxz_hash', '', time()+3600*24*2, '/');
		setcookie('ndxz_access', '', time()+3600*24*2, '/');
		
		$self = (dirname($_SERVER['PHP_SELF']) == '/') ? '' : dirname($_SERVER['PHP_SELF']);
		header('Location: http://' . $_SERVER['HTTP_HOST'] . $self . '/');
	}

	/**
	* Returns settings array or error
	*
	* @param void
	* @return mixed
	*/
	public function settings()
	{
		$OBJ =& get_instance();
		
		$adm['adm_id'] = 1;
		
		$this->settings = $OBJ->db->selectArray(PX.'settings', $adm, 'record');
		$OBJ->vars->site = unserialize($this->settings['site_vars']);
		$OBJ->vars->settings = $this->settings;
			
		if (!$this->settings) show_error('error finding settings');
	}
	
	/**
	*/
	public function hash_equals_tmp($a, $b) 
	{
		$ret = strlen($a) ^ strlen($b);
        $ret |= array_sum(unpack("C*", $a^$b));
        return !$ret;
	}
	
	
	/**
	* Returns user preferences array or error
	*
	* @param void
	* @return mixed
	*/
	public function checkLogin()
	{
		$OBJ =& get_instance();
	
		// if logging out
		if (isset($_POST['logout'])) $this->logout();
		
		// looking for password reset query string
		if (isset($_GET['key']) && isset($_GET['id']))
		{	
			// validate inputs
			$clean['ID'] 	= getURI('id', 0, 'connect', 2);
			$clean['hash'] 	= getURI('key', 'none', 'connect', 32, true);
			
			// get user id record
			$rs = $OBJ->db->fetchRecord("SELECT ID, email, userid, password 
				FROM ".PX."users 
				WHERE ID = '" . $clean['ID'] . "'");
				
			if ($rs)
			{
				if(!function_exists('hash_equals')) 
				{
    				if ($this->hash_equals_tmp($clean['hash'], md5($rs['email'] . $rs['password']))) 
					{
   						// make new password, update database, send to user, notify
						$new_password = $this->createRandomPassword();
						$password['password'] = md5($new_password);
				
						// update
						$OBJ->db->updateArray(PX.'users', $password, "ID = '$rs[ID]'");
					} else {
						show_login('something is wrong');
					}
					
				} else {
					if (hash_equals($clean['hash'], md5($rs['email'] . $rs['password']))) 
					{	
						// make new password, update database, send to user, notify
						$new_password = $this->createRandomPassword();
						$password['password'] = md5($new_password);
				
						// update
						$OBJ->db->updateArray(PX.'users', $password, "ID = '$rs[ID]'");
					} else {
						show_login('something is wrong');
					}
				}
				
				// send email info
				#produce message in txt format 
				$body = "Hi again! Did you miss us already?<br /><br />";
				$body .= "Enclosed are your login details - you may change these after you login (Preferences).<br /><br />";
				$body .= "Login: [your_email_address]<br />";
				$body .= "Password: $new_password<br /><br />";
				$body .= "URL: " . BASEURL . "/ndxzstudio/ <br /><br />";
				$body .= "Indexhibit probably loves you. ;)";

				// load mail class
				$mail =& load_class('mail', true, 'lib');
				
				$mail->setTo($rs['email'], 'From your website');
				$mail->setSubject('Indexhibit Password Reset');
				$mail->setMessage($body);
				$mail->addMailHeader('Reply-To', 'noreply@indexhibit.org', 'indexhibit.org');
				$mail->addGenericHeader('X-Mailer', 'PHP/' . phpversion());
				$mail->addGenericHeader('Content-Type', 'text/html; charset="utf-8"');
				$mail->setWrap(100);
					
				$mail->send();
				
			} else {
				show_login('email address not found');
			}
			
			show_login('almost there, check your email', true, true);
		}
		
		// retreieving
		if (isset($_POST['retrievePassword'])) 
		{
			$clean['email'] 	= md5(getPOST('email', 'x', 'connect', 50));
			
			// search for info
			$rs = $OBJ->db->fetchRecord("SELECT ID, email, userid, user_name, user_surname, password 
				FROM ".PX."users 
				WHERE MD5(email) = '$clean[email]' 
				AND user_active = '1'");
			
			if ($rs)
			{
				#produce message in txt format 
				$body = "Hello from your Indexhibit website!<br /><br />";
				$body .= "Somebody requested a new password - was that you?<br /><br />";
				$body .= "If this is a mistake do nothing - your password has not been reset yet.<br /><br />";
				$body .= "Otherwise, follow the link below to continue reset:<br /><br />";
				$body .= "URL: " . BASEURL . "/ndxzstudio/?id=" . $rs['ID'] . "&key=" . md5($rs['email'] . $rs['password']) . "<br /><br />";
				$body .= "Byeeeeeeee... ;)";
				
				// reset these
				setcookie('ndxz_accessed', '', time());
				setcookie('ndxz_accessed', '', time(), '/');
				
				// load mail class
				$mail =& load_class('mail', true, 'lib');
				
				$mail->setTo($rs['email'], 'From your website');
				$mail->setSubject('Indexhibit Password Reset');
				$mail->setMessage($body);
				$mail->addMailHeader('Reply-To', 'noreply@indexhibit.org', 'indexhibit.org');
				$mail->addGenericHeader('X-Mailer', 'PHP/' . phpversion());
				$mail->addGenericHeader('Content-Type', 'text/html; charset="utf-8"');
				$mail->setWrap(100);
					
				$mail->send();
								
				show_login('check your email for information', false);
			}
			else
			{
				// notify that it's incorrect
				show_login('email address not found');
			}
		}
		
		// if logging in
		if (isset($_POST['submitLogin'])) 
		{	
			$clean['login'] 	= md5(getPOST('uid', null, 'connect', 100));
			$clean['password'] 	= md5(getPOST('pwd', null, 'connect', 32));
			$clean['user_active'] = 1;
			
			$this->prefs = $OBJ->db->fetchRecord("SELECT * FROM ".PX."users 
				WHERE (MD5(userid) = '$clean[login]' OR MD5(email) = '$clean[login]') 
				AND password = '$clean[password]' 
				AND user_active = '1'");
				
			if ($this->prefs)
			{
				// create a new user hash upon login
				$temp['user_hash'] = md5(time() . $clean['password'] . 'secret');

				$OBJ->db->updateArray(PX.'users', $temp, "ID='".$this->prefs['ID']."'");
				
				setcookie('ndxz_hash', $temp['user_hash'], time()+3600*24*2, '/');
				setcookie('ndxz_access', $clean['password'], time()+3600*24*2, '/');
				
				// if it was set
				if (isset($_COOKIE['ndxz_accessed']))
				{
					setcookie('ndxz_accessed', '', time());
					setcookie('ndxz_accessed', '', time(), '/');
				}

				$this->settings();
				return;
			}
			else
			{
				// let's track failed login attempts
				$attempt = (isset($_COOKIE['ndxz_accessed'])) ? (((int) $_COOKIE['ndxz_accessed']) + 1) : 1;
				
				setcookie('ndxz_accessed', $attempt, time()+3600, '/');
				
				show_login('login err');
			}
		}

		// return access
		if (isset($_COOKIE['ndxz_access']) && isset($_COOKIE['ndxz_hash'])) 
		{
			$clean['user_hash'] = getCOOKIE($_COOKIE['ndxz_hash'], null, 'connect', 32);
			$clean['password'] 	= getCOOKIE($_COOKIE['ndxz_access'], null, 'connect', 32);
			$clean['user_active'] = 1;

			$this->prefs = $OBJ->db->selectArray(PX.'users', $clean, 'record');
				
			if ($this->prefs)
			{	
				// we'll update each time so no more weird logouts
				setcookie('ndxz_hash', $clean['user_hash'], time()+3600*24*2, '/');
				setcookie('ndxz_access', $clean['password'], time()+3600*24*2, '/');
				
				$this->settings();
				return;
			}
		}

		show_login();
	}
	
	
	/**
	* Returns user preferences array or error
	*
	* @param void
	* @return mixed
	*/
	public function remoteLogin()
	{
		$OBJ =& get_instance();
		
		$p =& load_class('processor', TRUE, 'lib');
	
		$clean['user_hash'] = $p->process('hash', array('alpha'));
		$clean['password'] = $p->process('access', array('alpha'));
		$clean['user_active'] = 1;

		$this->prefs = $OBJ->db->selectArray(PX.'users', $clean, 'record');
				
		if ($this->prefs)
		{	
			$this->settings();
		}
	}


	public function is_admin()
	{
		return ($this->prefs['user_admin'] == 1) ? true : false;
	}
	
	
	public function createRandomPassword($limit=12)
	{
		$chars = "abcdefghijkmnopqrstuvwxyz0123456789";

	    srand((double)microtime()*1000000);
	    $i = 0;
	    $pass = '';

	    while ($i <= $limit) 
		{
	        $num = rand() % 33;
	        $tmp = substr($chars, $num, 1);
	        $pass = $pass . $tmp;
	        $i++;
	    }

	    return $pass;
	}
}