<?php if (!defined('SITE')) exit('No direct script access allowed');


// helpers for time things

// time function for right now
function getNow($now=TRUE)
{
	$OBJ =& get_instance();
	
	return ($now == TRUE) ?
		date("Y-m-d H:i:s",time()) :
		date("Y-m-d",time());
}


function getNowFormatted($format='Y-m-d H:i:s', $now=TRUE)
{
	$OBJ =& get_instance();
	
	$format = ($format == 'Y-m-d H:i:s') ? 'Y-m-d H:i:s' : $format;
	
	return ($now == TRUE) ?
		date($format, time()) :
		date($format, time());
}


function convertToStamp($timestamp)
{
	return date('YmdHis', strtotime($timestamp));
}


function convertDate($date='', $offset='', $format='')
{
	$date = ($date == '') ? getNow() : $date;
	$offset = ($offset == '') ? 0 : $offset;
	$format = ($format == '') ? '%d %B %Y' : $format;
	
	// messy
	$timestamp = str_replace(array('-', ':', ' '), array('', '', ''), $date);
	
	$time[0] = substr($timestamp, 8, 2); // hours
	$time[1] = substr($timestamp, 10, 2); // min
	$time[2] = substr($timestamp, 12, 2); // seconds
	$time[3] = substr($timestamp, 6, 2); // day
	$time[4] = substr($timestamp, 4, 2); // month
	$time[5] = substr($timestamp, 0, 4); // year
	
	// we need to adjust for the time offset
	$new = date('Y-m-d H:i:s', mktime($time[0]+$offset, $time[1], $time[2], $time[4], $time[3], $time[5]));

	return strftime($format, strtotime($new));
}

// use minutes
function adjust_now($mins=0, $date='')
{
	$now = ($date == '') ? convertToStamp(getNow()) : convertToStamp($date);

	$time[0] = substr($now, 8, 2); // hours
	$time[1] = substr($now, 10, 2); // min
	$time[2] = substr($now, 12, 2); // seconds
	$day = substr($now, 6, 2);
	$mn = substr($now, 4, 2);
	$yr = substr($now, 0, 4);

	return date('Y-m-d H:i:s', mktime($time[0], $time[1] + $mins, $time[2], $mn, $day, $yr));
}


// use minutes
function add_days($days=0, $date='')
{
	$now = ($date == '') ? convertToStamp(getNow()) : convertToStamp($date);

	$time[0] = substr($now, 8, 2); // hours
	$time[1] = substr($now, 10, 2); // min
	$time[2] = substr($now, 12, 2); // seconds
	$day = substr($now, 6, 2);
	$mn = substr($now, 4, 2);
	$yr = substr($now, 0, 4);

	return date('Y-m-d H:i:s', mktime($time[0], $time[1], $time[2], $mn, $day + $days, $yr));
}