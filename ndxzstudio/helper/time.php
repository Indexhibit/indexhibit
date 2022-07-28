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

	return strffftime($format, strtotime($new));
}


function strffftime(string $format, $timestamp = null, string $locale = null): string
{
	if (null === $timestamp) {
		$timestamp = new \DateTime;
	}
	elseif (is_numeric($timestamp)) {
		$timestamp = date_create('@' . $timestamp);
	}
	elseif (is_string($timestamp)) {
		$timestamp = date_create('!' . $timestamp);
	}

	if (!($timestamp instanceof \DateTimeInterface)) {
		throw new \InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.');
	}

	$locale = substr((string) $locale, 0, 5);

	$intl_formats = [
		'%a' => 'EEE',	// An abbreviated textual representation of the day	Sun through Sat
		'%A' => 'EEEE',	// A full textual representation of the day	Sunday through Saturday
		'%b' => 'MMM',	// Abbreviated month name, based on the locale	Jan through Dec
		'%B' => 'MMMM',	// Full month name, based on the locale	January through December
		'%h' => 'MMM',	// Abbreviated month name, based on the locale (an alias of %b)	Jan through Dec
		'%p' => 'aa',	// UPPER-CASE 'AM' or 'PM' based on the given time	Example: AM for 00:31, PM for 22:23
		'%P' => 'aa',	// lower-case 'am' or 'pm' based on the given time	Example: am for 00:31, pm for 22:23
	];

	$intl_formatter = function (\DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
		$tz = $timestamp->getTimezone();
		$date_type = \IntlDateFormatter::FULL;
		$time_type = \IntlDateFormatter::FULL;
		$pattern = '';

		// %c = Preferred date and time stamp based on locale
		// Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
		if ($format == '%c') {
			$date_type = \IntlDateFormatter::LONG;
			$time_type = \IntlDateFormatter::SHORT;
		}
		// %x = Preferred date representation based on locale, without the time
		// Example: 02/05/09 for February 5, 2009
		elseif ($format == '%x') {
			$date_type = \IntlDateFormatter::SHORT;
			$time_type = \IntlDateFormatter::NONE;
		}
		// Localized time format
		elseif ($format == '%X') {
			$date_type = \IntlDateFormatter::NONE;
			$time_type = \IntlDateFormatter::MEDIUM;
		}
		else {
			$pattern = $intl_formats[$format];
		}

		return (new \IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
	};

	// Same order as https://www.php.net/manual/en/function.strftime.php
	$translation_table = [
		// Day
		'%a' => $intl_formatter,
		'%A' => $intl_formatter,
		'%d' => 'd',
		'%e' => 'j',
		'%j' => function ($timestamp) {
			// Day number in year, 001 to 366
			return sprintf('%03d', $timestamp->format('z')+1);
		},
		'%u' => 'N',
		'%w' => 'w',

		// Week
		'%U' => function ($timestamp) {
			// Number of weeks between date and first Sunday of year
			$day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
			return intval(($timestamp->format('z') - $day->format('z')) / 7);
		},
		'%W' => function ($timestamp) {
			// Number of weeks between date and first Monday of year
			$day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
			return intval(($timestamp->format('z') - $day->format('z')) / 7);
		},
		'%V' => 'W',

		// Month
		'%b' => $intl_formatter,
		'%B' => $intl_formatter,
		'%h' => $intl_formatter,
		'%m' => 'm',

		// Year
		'%C' => function ($timestamp) {
			// Century (-1): 19 for 20th century
			return (int) $timestamp->format('Y') / 100;
		},
		'%g' => function ($timestamp) {
			return substr($timestamp->format('o'), -2);
		},
		'%G' => 'o',
		'%y' => 'y',
		'%Y' => 'Y',

		// Time
		'%H' => 'H',
		'%k' => 'G',
		'%I' => 'h',
		'%l' => 'g',
		'%M' => 'i',
		'%p' => $intl_formatter, // AM PM (this is reversed on purpose!)
		'%P' => $intl_formatter, // am pm
		'%r' => 'G:i:s A', // %I:%M:%S %p
		'%R' => 'H:i', // %H:%M
		'%S' => 's',
		'%T' => 'H:i:s', // %H:%M:%S
		'%X' => $intl_formatter,// Preferred time representation based on locale, without the date

		// Timezone
		'%z' => 'O',
		'%Z' => 'T',

		// Time and Date Stamps
		'%c' => $intl_formatter,
		'%D' => 'm/d/Y',
		'%F' => 'Y-m-d',
		'%s' => 'U',
		'%x' => $intl_formatter,
	];

	$out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
		if ($match[1] == '%n') {
			return "\n";
		}
		elseif ($match[1] == '%t') {
			return "\t";
		}

		if (!isset($translation_table[$match[1]])) {
			throw new \InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
		}

		$replace = $translation_table[$match[1]];

		if (is_string($replace)) {
			return $timestamp->format($replace);
		}
		else {
			return $replace($timestamp, $match[1]);
		}
	}, $format);

	$out = str_replace('%%', '%', $out);
	return $out;
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