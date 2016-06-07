<?php if (!defined('SITE')) exit('No direct script access allowed');


class old_index
{
	function load_index()
	{
		$OBJ =& get_instance();

		return ($OBJ->vars->exhibit['obj_org'] == 1) ? $this->chronological() : $this->sectional();
	}
	

	// chronological navigation type
	function chronological()
	{
		$OBJ =& get_instance();

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, 
			section, sec_desc, sec_disp, year, secid, sec_proj     
			FROM ".PX."objects, ".PX."sections 
			WHERE status = '1' 
			AND hidden != '1' 
			AND section_id = secid  
			AND section_top != '1' 
			ORDER BY sec_ord ASC, year DESC, ord ASC");
		
		if (!$pages) return 'Error with pages query';
	
		foreach($pages as $reord)
		{
			// two is our projects
			if ($reord['sec_proj'] != 1)
			{
				$order[$reord['sec_desc']][] = array(
					'id' => $reord['id'],
					'title' => $reord['title'],
					'url' => $reord['url'],
					'year' => $reord['year'],
					'secid' => $reord['secid'],
					'disp' => $reord['sec_disp']);
			}
			else
			{
				$order[$reord['year']][] = array(
					'id' => $reord['id'],
					'title' => $reord['title'],
					'url' => $reord['url'],
					'year' => $reord['year'],
					'secid' => $reord['secid'],
					'disp' => $reord['sec_disp']);
			}
		}
	
		$s = '';
	
		foreach($order as $key => $out)
		{
			$s .= "<ul>\n";
		
			if ($out[0]['disp'] == 1) $s .= "<li class='section-title'>" . $key . "</li>\n";
		
			foreach($out as $page)
			{
				$active = ($OBJ->vars->exhibit['id'] == $page['id']) ? " class='active'" : '';
				
				$s .= "<li$active><a href='" . BASEURL . ndxz_rewriter($page['url']) . "'>" . $page['title'] . "</a></li>\n";
			}
		
			$s .= "</ul>\n\n";
		}

		return $s;
	}


	// sections navigation
	function sectional()
	{
		$OBJ =& get_instance();

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, 
			section, sec_desc, sec_disp, year, secid    
			FROM ".PX."objects, ".PX."sections 
			WHERE status = '1' 
			AND hidden != '1' 
			AND section_id = secid  
			AND section_top != '1' 
			ORDER BY sec_ord ASC, ord ASC");
		
		if (!$pages) return 'Error with pages query';
	
		foreach($pages as $reord)
		{
			$order[$reord['sec_desc']][] = array(
				'id' => $reord['id'],
				'title' => $reord['title'],
				'url' => $reord['url'],
				'year' => $reord['year'],
				'secid' => $reord['secid'],
				'disp' => $reord['sec_disp']);
		}
	
		$s = '';
	
		foreach($order as $key => $out)
		{
			$s .= "<ul>\n";
		
			if ($out[0]['disp'] == 1) $s .= "<li class='section-title'>" . $key . "</li>\n";
		
			foreach($out as $page)
			{
				$active = ($OBJ->vars->exhibit['id'] == $page['id']) ? " class='active'" : '';
				
				$s .= "<li$active><a href='" . BASEURL . ndxz_rewriter($page['url']) . "'>" . $page['title'] . "</a></li>\n";
			}
		
			$s .= "</ul>\n\n";
		}

		return $s;
	}
}