<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* REST class
*
* REST class for communicating with indexhibit.org
* (requires more work in the future)
* 
* @version 1.0
* @author Vaska 
*/
class Rest
{
	public $target		= 'http://api.indexhibit.org/';
	public $apikey;
	public $method;
	public $id;
	public $url;
	public $title;
	public $email;
	
	/**
	* Return 'success' boolean back from $target
	*
	* @param void
	* @return boolean
	*/
	public function report_to_indexhibit()
	{
		if (($this->apikey == '') || ($this->url == '')) return;
	
		$params = array();
		$params['api_key']		= $this->apikey;
		$params['method']		= 'post_exhibit';
		$params['email']		= $this->email;
		$params['id']			= $this->id;
		$params['url']			= $this->url;
		$params['title']		= $this->title;

		$encoded_params = array();

		foreach ($params as $k => $v)
		{
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}

		$rest = $this->target . '?' . implode('&', $encoded_params);
	
		// we'll need to deal with errors here eventually
		$rsp = array();
		$rsp = @file_get_contents($rest);
		
		// some systems do not support file_get_contents via url
		// we need another way to report
		if ($rsp == '') return FALSE;
		
		$rsp_obj = unserialize($rsp);
		
		return ($rsp_obj['success'] == TRUE) ? TRUE : FALSE;
	}
	
	/**
	* Return list of indexhibit users - we'll cache it so we don't kill our server
	*
	* @param void
	* @return string
	*/
	public function indexhibit_user_list()
	{
		$params['method']		= 'users';

		$encoded_params = array();

		foreach ($params as $k => $v)
		{
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}

		$rest = $this->target . 'users/index.php' . '?' . implode('&', $encoded_params);
		
		$path = DIRNAME . '/files';
		$filename = $path . '/ndxz.users.php';
		
		$filed = '00000000';
		$today = date('Ymd', time());
		
		// file date
		if (file_exists($filename)) $filed = date('Ymd', filemtime($filename));
		
		if ($today > $filed)
		{
			if (is_writable($path)) 
			{
				$users = @file_get_contents($rest);
				
				if ($users == '') return $this->no_user_list();
				
				$handle = @fopen($filename, 'w');
				@fwrite($handle, $users);
				@fclose($handle);
				return $users;
			}
			else
			{
				// get contents of cached file
				if (file_exists($filename)) {
					$users = @file_get_contents($filename);
					
					if ($users == '') return $this->no_user_list();
					
					return $users;
				}
			}
		}
		else
		{
			// get contents of cached file
			if (file_exists($filename)) {
				$users = @file_get_contents($filename);
				
				if ($users == '') return $this->no_user_list();
				
				return $users;
			}
		}
		
		// if nothing works just forget it
		return $this->no_user_list();
	}
	

	// this really shouldn't be here, but, oh well...
	public function no_user_list()
	{
		return "<p>About this site</p>

			<p>This site was built using Indexhibit / Index + Exhibit</p>

			<p>Indexhibit is a web application used to build and maintain an archetypal, invisible website format that combines text, image, movie and sound.</p>

			<p>Visit <a href='http://www.indexhibit.org/'>Indexhibit</a> to learn more.</p>
		<p>...................................................................................................</p>";
	}
}


?>