<?php

class upgrade_215
{
	var $error;
	var $messages;
	var $version = '2.1.5';
	var $charset = 'utf8';
	var $collate = 'utf8_unicode_ci';

	function upgrade()
	{
		$OBJ =& get_instance();
		
		// forcing a change to the users table for user ID = 1 - just in case
		$OBJ->db->updateArray(PX.'users', array('ID' => '1'), "user_admin = '1'");
		$OBJ->db->updateArray(PX.'settings', array('version' => '2.1.5'), "adm_id = '1'");
	}
}