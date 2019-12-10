<?php if (!defined('SITE')) exit('No direct script access allowed');

/*
Plugin Name: New Statistics
Plugin URI: http://www.indexhibit.org/plugin/new_statistics/
Description: Statistics
Version: 1.0
Author: Indexhibit
Author URI: http://indexhibit.org/
Type: front
Hook: enable_statistics
Function: new_statistics:counter
Order: 22
End
*/

class New_statistics
{
	var $uri;
	var $refer;
	var $last;
	
	public function __construct()
	{
		$this->uri = (isset($_SERVER['REQUEST_URI'])) ? htmlspecialchars($_SERVER['REQUEST_URI']) : '';
		$this->refer = (isset($_SERVER['HTTP_REFERER'])) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : '';
	}
	
	public function counter()
	{
		$this->stat_insertHit();
	}

	/**
	* Returns boolean
	*
	* @param string $ip
	* @return boolean
	*/
	public function stat_ignore_hit($ip='')
	{
		global $default;
		
		$ignored_ips = $default['ignore_ip'];

		foreach ($ignored_ips as $ips) 
		{
			if (strpos($ip, $ips, 0) === 0) return true;
		}

		return false;
	}


	/**
	* Returns array of stats info
	*
	* @param void
	* @return array
	*/
	public function stat_doStats()
	{
		$stat['ref']		= $this->refer;
		$stat['ip']		 	= $_SERVER['REMOTE_ADDR'];
		$stat['url']		= parse_url($stat['ref']);
		$stat['uri']		= $this->uri;
		$stat['keywords']	= $this->stat_getKeywords($stat['url']);
		$stat['ref'] 		= ($stat['ref'] == '') ? '' : $stat['ref'];
	
		return $stat;
	}


	/**
	* Returns string
	* (to avoid our own site from referrer stats)
	*
	* @param string $input
	* @return string
	*/
	public function stat_reduceURL($input='')
	{
		if (!$input) return NULL;
	
		$url = parse_url($input);
		return preg_replace('/^www./', '', $url['host']);
	}


	/**
	* Returns string
	*
	* @param string $lang
	* @return string
	*/
	public function stat_getLanguage($lang='')
	{
		return (!preg_match("/([^,;]*)/", $lang, $langs)) ? 'n/a' : $langs[0];
	}


	/**
	* Returns search terms
	*
	* @param string $url
	* @return variable
	*/
	public function stat_getKeywords($url='')
	{
		$searchterms = '';
		
		if (!isset($url['host'])) return '';
	
		// this should probably be updated
		// add duckduckgo
		$searches = array(
			array("/google\./i", 'q'),
			array("/alltheweb\./i", 'q'),
			array("/yahoo\./i", 'p'),
			array("/search\.aol\./i", 'query'),
			array("/search\.msn\./i", 'q')
		);
		
		foreach ($searches as $search)
		{
			if (preg_match($search[0], $url['host'])) 
			{
				parse_str($url['query'], $q);
				return $q[$search[1]];
			}	
		}
	
		return $searchterms;
	}


	/**
	* Returns completed stat hit
	*
	* @param void
	* @return null
	*/
	public function stat_insertHit()
	{
		$OBJ =& get_instance();
		
		if (!$OBJ)
		{
			$OBJ =& load_class('core', true, 'lib');
		}
	
		$stat = $this->stat_doStats();
		
		// ignore ip's listed in the config file
		if ($this->stat_ignore_hit($stat['ip']) == true) return;
		
		// it needs to end with a '/' for it to be a stat
		if ((substr($stat['uri'], -1) != '/')) return;

		// we don't refer to ourselves
		$found = strpos($this->stat_reduceURL($stat['ref']), $this->stat_reduceURL(BASEURL));
		$stat['ref'] = ($found === false) ? $stat['ref'] : '';
		
		// get country if the database exists
		if ($stat['ip'] != '')
		{
			$ip = sprintf("%u", ip2long($stat['ip']));
		
			$rs = $OBJ->db->fetchRecord("SELECT country_name FROM iptocountry 
				WHERE ip_from <= " . $OBJ->db->escape($ip) . " AND ip_to >= " . $OBJ->db->escape($ip) . "");
	
			if ($rs) 
			{
				$c = trim(ucwords(preg_replace("/([A-Z\xC0-\xDF])/e",
					"chr(ord('\\1')+32)", $rs['country_name'])));
				
				$clean['hit_country'] = $c;
			}
		}
			
		$clean['hit_addr']		= $stat['ip'];
		$clean['hit_referrer']	= $stat['ref'];
		$clean['hit_page']		= $stat['uri'];
		$clean['hit_keyword']	= $stat['keywords'];
		
		$clean['hit_time']		= getNow();
		$clean['hit_month']		= substr($clean['hit_time'], 0, 7);
		$clean['hit_day']		= substr($clean['hit_time'], 0, 10);
			
		$OBJ->db->insertArray(PX."stats", $clean);
		
		$this->add_page_count($clean['hit_page']);
	
		return;
	}
	
	// perhaps we add a way to turn this on and off?
	function add_page_count($url='')
	{
		if ($url == '') return;
		
		$OBJ =& get_instance();
		
		// check if it exists
		$check = $OBJ->db->fetchRecord("SELECT stor_url FROM ".PX."stats_exhibits WHERE stor_url = '$url'");
		
		if ($check)
		{
			// update the count
			$OBJ->db->updateRecord("UPDATE ".PX."stats_exhibits SET stor_count = stor_count + 1 WHERE stor_url = '$url'");
		}
		else
		{
			// first time - add the record
			$OBJ->db->insertArray(PX . 'stats_exhibits', array('stor_url' => $url, 'stor_count' => 1));
		}
		
		return;
	}
}