<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Error output class
* 
* @version 1.0
* @author Vaska 
*/
class Errors
{
	public function __construct()
	{
		
	}
	
	/**
	* Returns error page
	*
	* @param string $message
	* @param string $template
	* @return string
	*/
	public function show_error($message, $template = 'error')
	{
		$message = "<p>$message</p>";
		
		ob_start();
		include_once TPLPATH . $template . '.php';
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
	
	/**
	* Returns login page
	*
	* @param string $message
	* @param string $template
	* @return string
	*/
	public function show_login($message, $template = 'login')
	{
		$login = $message;
		
		ob_start();
		include_once TPLPATH . $template . '.php';
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}