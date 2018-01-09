<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Variables class
*
* Carry common variables around
* Planned manipulation functions here in the future
* 
* @version 1.0
* @author Vaska 
*/
class Vars 
{
	public $site			= array();
	public $exhibit			= array();
	public $images			= array();
	public $settings		= array();
	public $default			= array();
	public $extra			= array();
	public $format_params 	= array();
	public $abstract		= array();
	public $route			= array();
	public $files_query 	= array();
	public $media 			= array();
	public $medias			= array();
	
	// useful when you are in sections or things like that
	public $results		= array();
	
	public function __construct()
	{
		global $default;

		$this->default = $default;
	}
}