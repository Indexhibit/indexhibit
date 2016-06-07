<?php if (!defined('SITE')) exit('No direct script access allowed');

class Cache
{
	public $uri;
	public $mod;
	public $cached = FALSE;
	public $modular;
	public $include_file;
	public $file_path;
	public $file_name;
	public $expires;
	public $expired;
	public $extension;
	public $output;
	public $allowed = false;
	public $defaults = array();
	public $ext;
	
	public function Cache()
	{
		global $default;

		$this->defaults = $default;
	}
	
	public function cache_age()
	{
		return adjust_now(-$this->defaults['cache_time']);
	}

	public function show_cached()
	{
		if (file_exists($this->uri)) include $this->uri;
	}

	public function check_cached($uri='')
	{
		// REVIEW LATER
		// check for inputs - no caching if they exist
		if ((!empty($_GET)) || (!empty($_POST))) $this->defaults['caching'] = false;

		///if ($this->defaults['caching'] != true) return false;

		$this->uri = DIRNAME . '/ndxzsite/cache/' . md5($uri) . '.php';
		
		// if cached 'true'
		if ($this->page_cached($this->uri) == true)
		{
			$this->cached = ($this->cache_age() < date("Y-m-d H:i:s", filemtime($this->uri))) ? true : false;
		}
		else
		{
			$this->cached = false;
		}
	}
	
	public function page_cached()
	{
		return (file_exists($this->uri)) ? true : false;
	}

	public function makeCache($url, $content, $allowed=true)
	{
		$OBJ =& get_instance();

		// if there is no content don't do it
		if ($content == '') return;
		if ($allowed == false) return;
		
		if ($OBJ->vars->exhibit['page_cache'] == true)
		{
			$filename = md5($url) . '.php';

			if (!$handle = fopen(DIRNAME . '/ndxzsite/cache/' . $filename, 'w+')) 
			{
				// error note
			}
		
			// add an expiration date/time to file
			if (is_dir(DIRNAME . '/ndxzsite/cache/') && is_writable(DIRNAME . '/ndxzsite/cache/')) 
			{
				// use file_put_contents instead?

				if (fwrite($handle, $content) === FALSE) 
				{  
					// error note
				}
			
				fclose($handle);
			}
		
			clearstatcache();
		}
		
		return;
	}
	
	
	public function delete_cached($url)
	{
		if (file_exists(DIRNAME . '/ndxzsite/cache/' . md5($url) . '.php'))
			@unlink(DIRNAME . '/ndxzsite/cache/' . md5($url) . '.php');

		return;
	}
	
	
	public function delete_all_cache()
	{
		if (!$dirhandle = @opendir(DIRNAME . '/ndxzsite/cache/')) return;

		while (false !== ($filename = readdir($dirhandle))) 
		{
			if ($filename != "." && $filename != "..") 
			{
				$filename = DIRNAME . '/ndxzsite/cache/' . $filename;
				@unlink($filename);
			}
		}
	}
	
	
	public function delete_all_dimgs()
	{
		if (!$dirhandle = @opendir(DIRNAME . '/files/dimgs/')) return;

		while (false !== ($filename = readdir($dirhandle))) 
		{
			if ($filename != "." && $filename != "..") 
			{
				$filename = DIRNAME . '/files/dimgs/' . $filename;
				@unlink($filename);
			}
		}
	}
}