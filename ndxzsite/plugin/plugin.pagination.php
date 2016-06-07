<?php if (!defined('SITE')) exit('No direct script access allowed');

class Pagination
{
	function get_prev_next_entries()
	{
		$OBJ =& get_instance();
	
		if ($OBJ->vars->exhibit['section_top'] == 1) return;
		if ($OBJ->vars->exhibit['section_id'] != 2) return;
	
		// get all entries
		$entries = $OBJ->db->fetchArray("SELECT title, url, id FROM ".PX."objects 
			WHERE object = '" . $OBJ->vars->exhibit['object'] . "' 
			AND section_top != '1' 
			AND status = '1' 
			AND section_id = '2' 
			ORDER BY ord ASC");
		
		// loop through the array to find current and set the others
		if ($entries)
		{
			foreach ($entries as $key => $entry)
			{
				// bingo!
				if ($entry['id'] == $OBJ->vars->exhibit['id'])
				{
					// previous
					$previous = (isset($entries[$key - 1])) ? $entries[$key - 1] : '';
					$next = (isset($entries[$key + 1])) ? $entries[$key + 1] : '';
				}
			}
		
			$s = "<div id='prev_new_entries'>";
			if (!empty($next)) $s .= "<span id='newer' style=''><a href='" . BASEURL . "$next[url]' title='Newer'>Previous</a></span> | ";
			if (!empty($previous)) $s .= "<span id='older' style=''><a href='" . BASEURL . "$previous[url]' title='Older'>Next</a></span>";
			$s .= "</div>";
		}
	
		return $s;
	}
}