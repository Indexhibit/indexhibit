<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* Statisticis class
*
* Frontend statistics - partly crufted from Shortstats
* 
* @version 1.0
* @author Vaska 
*/
class Statistics
{
	/**
	* Returns null
	*
	* @param void
	* @return null
	*/
	public function Statistics()
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
		$stat['ip']		 	= $_SERVER['REMOTE_ADDR'];
		$stat['url']		= parse_url($stat['ref']);
		
		//$stat['domain']		= (isset($stat['url']['host'])) ?
		//	eregi_replace("^www.", "", $stat['url']['host']) :
		//	'';
		//$stat['agent']		= $_SERVER['HTTP_USER_AGENT'];
		//$stat['browser']	= $this->stat_getAgent($stat['agent']);
		//$stat['lang']		= $this->stat_getLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		
		$stat['uri']		= $_SERVER['REQUEST_URI'];
		$stat['keywords']	= $this->stat_getKeywords($stat['url']);
		
		// referrer - wonky
		$stat['ref']		= (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
		$stat['ref'] 		= ($stat['ref'] == null) ? '' : $stat['ref'];
	
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
	* Returns array of browser info
	*
	* @param string $ua
	* @return array
	*/
	public function stat_getAgent($ua)
	{
		$browser['platform']	= "Indeterminable";
		$browser['browser']		= "Indeterminable";
	
		// test for platform
		$platforms = array('Windows'=>'Win', 'Macintosh'=>'Mac', 'Linux'=>'Linux');
	
		foreach ($platforms as $key => $test) 
		{		
			if (eregi($test, $ua)) $browser['platform'] = $key;
		}
	
		// this should probably be updated...
		// add AppleWebkit
		$browsers = array(
			array('Netscape', 'Mozilla/4', 'Mozilla/([[:digit:]\.]+)'),
			array('Mozilla', 'Mozilla/5', 'rv(:| )([[:digit:]\.]+)'),
			array('Safari', 'Safari', 'Safari/([[:digit:]\.]+)'),
			array('Firefox', 'Firefox', 'Firefox/([[:digit:]\.]+)'),
			array('Netscape', 'Netscape', 'Netscape[0-9]?/([[:digit:]\.]+)'),
			array('Internet Explorer', 'MSIE', 'MSIE ([[:digit:]\.]+)'),
			array('Crawler/Search Engine', 'Crawl', 'stop'),
			array('Crawler/Search Engine', 'bot', 'stop'),
			array('Crawler/Search Engine', 'slurp', 'stop'),
			array('Lynx', 'Lynx', 'Lynx/([[:digit:]\.]+)'),
			array('Links', 'Links', '\(([[:digit:]\.]+)')
			);
	
		foreach ($browsers as $test) {
		
			if (eregi($test[1], $ua)) 
			{
				$browser['browser'] = $test[0];
			}
		}
	
		return $browser;
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
			//print_r($OBJ);
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
		$clean['hit_lang']		= $stat['lang'];
		$clean['hit_referrer']	= $stat['ref'];
		$clean['hit_page']		= $stat['uri'];
		$clean['hit_keyword']	= $stat['keywords'];
		$clean['hit_time']		= getNow();
		$clean['hit_month']		= substr(getNow(), 0, 7);
		
		//$clean['hit_os']		= $stat['browser']['platform'];
		//$clean['hit_browser']	= $stat['browser']['browser'];
		//$clean['hit_agent']	= $stat['agent'];
		//$clean['hit_domain']	= $stat['domain'];
			
		$OBJ->db->insertArray(PX."stats", $clean);
	
		return;
	}

}


?>