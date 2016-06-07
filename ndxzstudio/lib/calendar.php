<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Calendar class
* 
* Returns an array of the specified month.
*
* @version 1.0
* @author Vaska 
*/

class Calendar
{	
	public $startDay = 0;
	public $startMonth = 1;
	public $day_names_long = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
	public $day_names_short = array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa");
	public $month_names_long = array("January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December");                      
	public $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	public $range = array();
	public $prevnext = array();
	public $prevnext_year = array();
    
	public function getMonthView($month, $year)
	{
		$temp = $this->getMonthHTML($month, $year);
		
		$tempe = $temp;
		$this->range['first'] = $tempe[0];
		$this->range['last'] = array_pop($tempe);
		
		$this->prevnext['previous'] = date('Y-m', mktime(0, 0, 0, $month-1, 1, $year));
		$this->prevnext['next'] = date('Y-m', mktime(0, 0, 0, $month+1, 1, $year));
		
		$this->prevnext_year['previous'] = date('Y-m', mktime(0, 0, 0, $month, 1, $year-1));
		$this->prevnext_year['next'] = date('Y-m', mktime(0, 0, 0, $month, 1, $year+1));
		
		return $temp;
	}
    
	public function getDaysInMonth($month, $year)
	{
		if ($month < 1 || $month > 12) {
			return 0;
		}
   
		$d = $this->daysInMonth[$month - 1];
   
		if ($month == 2) {
			// Check for leap year
			if ($year%4 == 0) {
				if ($year%100 == 0)	{
					if ($year%400 == 0) {
						$d = 29;
					}
				} else {
					$d = 29;
				}
			}
		}
    
		return $d;
	}
	
	public function getMonthHTML($m, $y)
	{
		$ab = $this->adjustDate($m, $y);
		$month = $ab[0];
		$year = $ab[1];
		$daysInMonth = $this->getDaysInMonth($month, $year);
		$date = getdate(mktime(12, 0, 0, $month, 1, $year));
		$first = $date["wday"];

		$d = $this->startDay + 1 - $first;

		while ($d <= $daysInMonth) 
		{	
			// we need this in week format
			for ($i = 0; $i < 7; $i++) 
			{
				// making the array
				if (($d > 0) && ($d <= $daysInMonth)) 
				{
					// these are the day IN the current month
					$s[] = date('Y-m-d', mktime(12, 0, 0, $m, $d, $y));
				} 
				else 
				{
					// these are the days before and after
					$s[] = ($d <= 0) ?
						date('Y-m-d', mktime(12, 0, 0, $m, $d, $y)) : 
						date('Y-m-d', mktime(12, 0, 0, $m + 1, $d - $daysInMonth, $y));
				}

				$d++;
			}
		}

		return $s;  	
	}
    
	public function adjustDate($month, $year)
	{
		$ab = array();  
		$ab[0] = $month;
		$ab[1] = $year;
        
		while ($ab[0] > 12) 
		{
			$ab[0] -= 12;
			$ab[1]++;
		}
        
		while ($ab[0] <= 0)	
		{
			$ab[0] += 12;
			$ab[1]--;
		}
		
		return $ab;
	}
}