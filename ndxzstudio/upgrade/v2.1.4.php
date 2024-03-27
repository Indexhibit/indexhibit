<?php

class upgrade_214
{
	var $error;
	var $messages;
	var $version = '2.1.4';
	var $charset = 'utf8';
	var $collate = 'utf8_unicode_ci';

	function upgrade()
	{
		$OBJ =& get_instance();
		
		$OBJ->db->updateArray(PX.'settings', array('version' => '2.1.4'), "adm_id = '1'");
	}
}
