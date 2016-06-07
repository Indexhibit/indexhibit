<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Publish class
*
* It actually just validates the title for publishing
* 
* @version 1.0
* @author Vaska
*/
class Publish
{
	public $title 		= null;
	public $section		= null;
	public $pdate;
	public $date_format	= 1;
	public $date;
	public $published;
	public $no_title 	= false;
	public $okslash		= false;

	/**
	* Returns string
	*
	* @param string $url
	* @return string
	*/
	public function urlStrip($url)
	{
		$search = '/\/+/';
		$replace = '/';

		return preg_replace($search, $replace, $url);
	}

	/**
	* Returns string
	*
	* @param void
	* @return string
	*/
	public function makeTitle()
	{
		$this->title = explode(" ", $this->title);
		$this->title = implode("-", $this->title);
		
		// we should make sure we don't end with - and no --'s
		
		return $this->title;
	}

	/**
	* Returns 'romanized' string
	*
	* @param void
	* @return string
	*/
	public function cleanTitle()
	{
		$this->title = utf8Deaccent($this->title, 0);
		$this->title = utf8Romanize($this->title);
			
		// need to rewrite this
		$search = ($this->okslash == false) ? "/[^a-z0-9- ]/i" : "/[^a-z0-9-\/ ]/i";
		$this->title = preg_replace($search, '', $this->title);
			
		return $this->title;
	}

	/**
	* Returns string
	*
	* @param void
	* @return string
	*/
	public function processTitle()
	{
		$this->title = $this->cleanTitle($this->title);
		return strtolower($this->makeTitle($this->title));
	}

	/**
	* Returns string
	*
	* @param void
	* @return string
	*/
	public function makeURL()
	{
		$this->title = $this->processTitle($this->title);
		return ($this->okslash == false) ? $this->urlStrip('/' . $this->section . '/' . $this->title . '/') : 
			$this->urlStrip('/' . $this->section . '/' . $this->title . '/');
	}
	

	/**
	* Returns string
	*
	* @param void
	* @return string
	*/
	public function make_date_url()
	{
		$this->title = $this->processTitle($this->title);
		
		$date = $this->mk_date();
		$title = ($this->no_title == false) ? $this->title . '/' : '';
		
		return $this->urlStrip('/' . $this->section . $date . '/' . $title);
	}
	
	
	public function mk_date() 
	{
		if ($this->date_format == 1) // /year/month/day
		{
			$this->create_date();
			return '/' . $this->date['year'] . '/' . $this->date['month'] . '/' . $this->date['day'];
		}
		elseif ($this->date_format == 2) // /year/month
		{
			$this->create_date();
			return '/' . $this->date['year'] . '/' . $this->date['month'];
		}
		elseif ($this->date_format == 3)  // /year
		{
			$this->create_date();
			return '/' . $this->date['year'];
		}
		elseif ($this->date_format == 4)  // need to do this one
		{
			//$this->create_date();
			return '/';
			//return '/' . $this->date['year'];
		}
		elseif ($this->date_format == 5) // photoblog
		{
			$this->no_title = true;
			$this->create_date();
			return '/' . $this->date['year'] . $this->date['month'] . $this->date['day'];
		}
		else
		{
			return;
		}
	}
	
	
	public function create_date()
	{
		$this->date['year']  	= substr($this->pdate, 0, 4);
		$this->date['month'] 	= substr($this->pdate, 5, 2);
		$this->date['day']	 	= substr($this->pdate, 8, 2);
		
		return $this->date;	
	}
	
	
	public function path_types()
	{
		return array(1 => '/section/year/month/day/title/',
			2 => '/section/year/month/title/',
			3 => '/section/year/title/',
			4 => '/section/title/',
			5 => '/section/yyyymmdd/');
	}	
}