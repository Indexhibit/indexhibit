<?php

function recent_stats()
{
	$OBJ =& get_instance();

	// anything older than the past 30 days
	$today = convertToStamp(getNow());
	$day = substr($today,6,2);
	$mn = substr($today,4,2);
	$yr = substr($today,0,4);

	$start_month = date('Y-m-d', mktime('00', '00', '00', $mn-2, $day, $yr));

	$months = $OBJ->db->fetchArray("SELECT hit_month FROM ".PX."stats WHERE hit_month > '$start_month' GROUP BY hit_month");

	if ($months)
	{
		foreach ($months as $m)
		{
			$clean['stor_date'] = "$m[hit_month]";

			$clean['stor_hits'] = $OBJ->db->getCount("SELECT count(*) FROM ".PX."stats WHERE hit_month LIKE '$m[hit_month]%'");
			$clean['stor_unique'] = $OBJ->db->getCount("SELECT count(DISTINCT hit_addr) FROM ".PX."stats WHERE hit_month LIKE '$m[hit_month]%'");
			
			// this is not behaving...
			$clean['stor_referrer'] = $OBJ->db->getCount("SELECT count(DISTINCT hit_referrer) FROM ".PX."stats WHERE hit_month LIKE '$m[hit_month]%' AND hit_referrer != ''");
			
			// delete the record
			$OBJ->db->deleteArray(PX.'stats_storage', "stor_date = '$m[hit_month]'");

			// let's insert them now
			$OBJ->db->insertArray(PX.'stats_storage', $clean);
		}

		// delete the archived
		$OBJ->db->deleteArray(PX.'stats', "hit_month <= '$start_month'");
	}
}

function process_stats()
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
		
			// let's insert them now
			$OBJ->db->insertArray(PX.'stats_storage', $clean);
		}
		
		// delete the archived
		$OBJ->db->deleteArray(PX.'stats', "hit_month <= '$start_month'");
	}
}


function getDailyHits()
{
	$timestamp = convertToStamp(getNow());
	
	$day 		= substr($timestamp,6,2);
	$mn 		= substr($timestamp,4,2);
	$yr 		= substr($timestamp,0,4);
	
	$days = array('today', 'yesterday', '2 days ago', '3 days ago', '4 days ago', '5 days ago', '6 days ago');
	
	$i = 0;
	foreach ($days as $d) {
		
		$out['first'] = date('Y-m-d H:i:s', mktime('00', '00', '00', $mn, $day-$i, $yr));
		$out['second'] = date('Y-m-d H:i:s', mktime('23', '59', '59', $mn, $day-$i, $yr));
		
		$arr[$d] = array($out['first'],$out['second']);
		$i++;
	}
	
	return $arr;
}


function getWeekHits()
{
	// create week beginning on sunday
	$timestamp = (date("w") == 0) ? 6 : date("w") - 1; 
	$timestamp = date("Ymd", strtotime("-" .$timestamp. " days"));
	
	$day 		= substr($timestamp,6,2);
	$mn 		= substr($timestamp,4,2);
	$yr 		= substr($timestamp,0,4);


	$weeks = array('this week', 'last week', '2 weeks ago', '3 weeks ago', '4 weeks ago');

	$i = 0;
	foreach ($weeks as $d) {
		
		$day = $day-$i;
		$oday = $day + 6;
		
		$out['first'] = date('Y-m-d H:i:s', mktime('00', '00', '00', $mn, $day, $yr));
		$out['second'] = date('Y-m-d H:i:s', mktime('23', '59', '59', $mn, $oday, $yr));
		$arr[$d] = array($out['first'],$out['second']);
		$i = 7;
	}
	
	return $arr;
}


function getMonthlyHits()
{
	$timestamp = convertToStamp(getNow());
	
	$mn 		= substr($timestamp,4,2);
	$yr 		= substr($timestamp,0,4);
	
	$months = array(
		'this month',
		'last month',
		'2 months ago',
		'3 months ago',
		'4 months ago',
		'5 months ago',
		'6 months ago',
		'7 months ago',
		'8 months ago',
		'9 months ago',
		'10 months ago',
		'11 months ago');
	
	$i = 0;
	foreach ($months as $d) {
		
		$out['first'] = date('Y-m', mktime('00', '00', '00', $mn-$i, '01', $yr));
		$id = ($i <= 1) ? $d : $out['first'];
		$arr[$id] = array($out['first']);
		$i++;
	}
	
	return $arr;
}