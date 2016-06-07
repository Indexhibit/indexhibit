<?php

/**
* Statistics class
*
* Frontend statistics
* 
* @version 1.0
* @author Vaska 
*/
class Jxs_statistics
{
	var $uri;
	var $refer;
	var $last;

	/**
	* Returns null
	*
	* @param void
	* @return null
	*/
	function Jxs_statistics()
	{
		$this->uri = $_POST['url'];
		$this->refer = ($_POST['referrer'] == 'none') ? '' : $_POST['referrer'];
		$this->last = ((int) $_POST['last_visit'] == 1) ? 1 : 0;
	}
	
	
	function output()
	{
		$OBJ =& get_instance();
		
		$this->stat_insertHit();
		return null;
	}


	/**
	* Returns boolean
	*
	* @param string $ip
	* @return boolean
	*/
	function stat_ignore_hit($ip='')
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
	function stat_doStats()
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
	function stat_reduceURL($input='')
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
	function stat_getLanguage($lang='')
	{
		return (!preg_match("/([^,;]*)/", $lang, $langs)) ? 'n/a' : $langs[0];
	}


	/**
	* Returns search terms
	*
	* @param string $url
	* @return variable
	*/
	function stat_getKeywords($url='')
	{
		$searchterms = '';
		
		if (!isset($url['host'])) return '';
	
		// this should probably be updated
		$searches = array(
			array("/google\./i", 'q'),
			array("/bing\./i", 'q'),
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
	
	
	function archive_stats()
	{
		$OBJ =& get_instance();

		// anything older than the past 30 days
		$today = convertToStamp(getNow());
		$day = substr($today,6,2);
		$mn = substr($today,4,2);
		$yr = substr($today,0,4);

		$start_month = date('Y-m-d', mktime('00', '00', '00', $mn-2, $day, $yr));

		$months = $OBJ->db->fetchArray("SELECT hit_month FROM ".PX."stats WHERE hit_month <= '$start_month' GROUP BY hit_month");

		if ($months)
		{
			foreach ($months as $m)
			{
				$clean['stor_date'] = "$m[hit_month]";

				$clean['stor_hits'] = $OBJ->db->getCount("SELECT count(*) FROM ".PX."stats WHERE hit_month LIKE '$m[hit_month]%'");
				$clean['stor_unique'] = $OBJ->db->getCount("SELECT count(DISTINCT hit_addr) FROM ".PX."stats WHERE hit_month LIKE '$m[hit_month]%'");
				$clean['stor_referrer'] = $OBJ->db->getCount("SELECT count(DISTINCT hit_referrer) FROM ".PX."stats WHERE hit_month LIKE '$m[hit_month]%' AND hit_referrer != ''");
				
				// delete the record if it exists already
				$OBJ->db->deleteArray(PX.'stats_storage', "stor_date = '$m[hit_month]'");

				// let's insert them now
				$OBJ->db->insertArray(PX.'stats_storage', $clean);
			}

			// delete the archived
			$OBJ->db->deleteArray(PX.'stats', "hit_month <= '$start_month'");
		}
	}


	/**
	* Returns completed stat hit
	*
	* @param void
	* @return null
	*/
	function stat_insertHit()
	{
		$OBJ =& get_instance();
		
		if (!$OBJ)
		{
			$OBJ =& load_class('core', true, 'lib');
		}
	
		$stat = $this->stat_doStats();
		
		// ignore ip's listed in the config file
		//if ($this->stat_ignore_hit($stat['ip']) == true) return;
		
		// it needs to end with a '/' for it to be a stat
		//if ((substr($stat['uri'], -1) != '/')) return;

		// we don't refer to ourselves
		$found = strpos($this->stat_reduceURL($stat['ref']), $this->stat_reduceURL(BASEURL));
		$stat['ref'] = ($found === false) ? $stat['ref'] : '';
		
		// get country if the database exists
		/*
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
		*/
			
		$clean['hit_addr']		= $stat['ip'];
		//$clean['hit_lang']		= $stat['lang'];
		$clean['hit_referrer']	= $stat['ref'];
		$clean['hit_page']		= $stat['uri'];
		$clean['hit_keyword']	= $stat['keywords'];
		$clean['hit_time']		= getNow();
		$clean['hit_month']		= substr($clean['hit_time'], 0, 7);
		$clean['hit_day']		= substr($clean['hit_time'], 0, 10);
		
		$OBJ->db->insertArray(PX."stats", $clean);
		
		$this->add_page_count($clean['hit_page']);
		
		// what about archiving stats_absolute_deviation
		$this->archive_stats();
	
		return;
	}

}