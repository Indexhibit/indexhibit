<?php

class upgrade_213
{
	var $error;
	var $messages;
	var $version = '2.1.3';
	var $charset = 'utf8';
	var $collate = 'utf8_unicode_ci';

	function upgrade()
	{
		$OBJ =& get_instance();
		
		$OBJ->db->updateArray(PX.'settings', array('version' => '2.1.3'), "adm_id = '1'");
	}
}
