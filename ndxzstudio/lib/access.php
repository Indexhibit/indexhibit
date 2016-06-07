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
		
		// retreieving
		if (isset($_POST['retrievePassword'])) 
		{
			sleep(3);

			// FIX THIS VALIDATION LATER!!!!!!!!!!
			$clean['email'] 	= md5(getPOST('email', null, 'connect', 50));
			
			// search for info
			$rs = $OBJ->db->fetchRecord("SELECT ID, email, userid, user_name, user_surname FROM ".PX."users 
				WHERE MD5(email) = '$clean[email]' AND user_active = '1'");
			
			if ($rs)
			{
				// make new password, update database, send to user, notify
				$new_password = $this->createRandomPassword();
				$password['password'] = md5($new_password);
				
				// update
				$OBJ->db->updateArray(PX.'users', $password, "ID = '$rs[ID]'");
				
				// transmit
				$MAIL =& load_class('mail', true, 'lib');

				$MAIL->setRecipients($rs['user_name'] . ' ' . $rs['user_surname'], $rs['email'], 
					'Password' . ' ' . 'Update', 'noreply@indexhibit.org');

				#produce message in html format 
				$body = "Your password was updated.\n\n";
				$body .= "Enclosed are your login details - you may change these after you login.\n\n";
				$body .= "Login: $rs[userid]\n";
				$body .= "Password: $new_password\n\n";
				$body .= "URL: " . BASEURL . "/ndxzstudio/";

				#build the message with the message title and message content 
				$MAIL->buildMessage('Password Update', $body);
				
				// reset these
				setcookie('ndxz_accessed', '', time());
				setcookie('ndxz_accessed', '', time(), '/');

				#build and send the email 
				if ($MAIL->sendmail()) 
				{ 
				    show_login('check email for new information');
				} 
				else 
				{ 
				    show_login('there was an error');
				}
			}
			else
			{
				// notify that it's incorrect
				show_login('email address not found');
			}
			
			return;
		}
		
		// if logging in
		if (isset($_POST['submitLogin'])) 
		{
			sleep(3); // obscure prevention of absuse
			
			$clean['userid'] 	= getPOST('uid', null, 'password', 12);
			$clean['password'] 	= md5(getPOST('pwd', null, 'password', 12));
			$clean['user_active'] = 1;

			$this->prefs = $OBJ->db->selectArray(PX.'users', $clean, 'record');
				
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
				$attempt = (isset($_COOKIE['ndxz_accessed'])) ? 
					(((int) $_COOKIE['ndxz_accessed']) + 1) : 1;
				
				setcookie('ndxz_accessed', $attempt, time()+3600, '/');

				//echo $attempt;
				
				show_login('login err');
			}
		}


		// return access
		if (isset($_COOKIE['ndxz_access']) && isset($_COOKIE['ndxz_hash'])) 
		{
			$clean['user_hash'] = getCOOKIE($_COOKIE['ndxz_hash'], null, 'password', 32);
			$clean['password'] 	= getCOOKIE($_COOKIE['ndxz_access'], null, 'password', 32);
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
	
	
	public function createRandomPassword($limit=9)
	{
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";

	    srand((double)microtime()*1000000);
	    $i = 0;
	    $pass = '' ;

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

?>