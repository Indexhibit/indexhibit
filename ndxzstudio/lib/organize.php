<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Organize class
* 
* @version 1.0
* @author Vaska 
*/
class Organize 
{
	public $settings		= array();
	public $prefs			= array();
	public $obj_org;
	public $out;
	public $section;
	public $subsection;
	
	public function order()
	{
		return $this->sectional();
	}
	
	// generates the node for an exhibit
	public function exhibit()
	{
		$OBJ =& get_instance();
		global $go;

		$status = ($this->out['status'] == 1) ? 'published' : 'draft';
		$status = (($status == 'published') && ($this->out['new'] == 1)) ? 'published-new' : $status;

		$hidden = ($this->out['hidden'] == 1) ? "<span class='hidden'>" . $OBJ->lang->word('hidden') . "</span> " : '';

		$edit = ($this->out['link'] == '') ? 
			href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=edit&amp;id=" . $this->out['id']) : 		
			href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=link&amp;id=" . $this->out['id']);

		$password = ($this->out['pwd'] != '') ? ' password' : '';
		$link = ($this->out['link'] != '') ? ' linked' : '';

		$here = ($this->out['status'] == 1) ? 'undraft' : 'draft';
		$here = (($here == 'undraft') && ($this->out['new'] == 1)) ? 'undraftnew' : $here;

		$activity = ($here == 'draft') ? "<span class='activity draft'><!-- --></span>" : 
			"<span id='activity-" . $this->out['id'] . "' class='activity $here' onclick=\"make_new(" . $this->out['id'] . "); return false;\"><!-- --></span>";
			
		$tagged = (isset($this->out['tagged'])) ? $this->out['tagged'] : '';
		
		$homed = ($this->out['home'] == 1) ? 
			"<span class='home' style='font-size: 11px;'>" . $OBJ->lang->word('home') . "</span> " : '';
			
		///$title = ($this->out['section_top'] == 1) ? $this->out['sec_desc'] : $this->out['title'];

		return li($activity . "<span class='drag-title{$password}{$link}'>" . strip_tags($this->out['title']) . 
			$tagged .  '</span>' . span(href($OBJ->lang->word('preview'), "?a=$go[a]&amp;q=prv&amp;id=" . 
			$this->out['id']) . ' ' . $edit,
			"class='options' style='color: #000;'") . $homed . $hidden,
			"class='sortableitem' id='item" . $this->out['id'] . "'") . "\n";
	}
	
	
	public function exhibit_chron()
	{
		$OBJ =& get_instance();
		global $go;

		$status = ($this->out['status'] == 1) ? 'published' : 'draft';
		$status = (($status == 'published') && ($this->out['new'] == 1)) ? 'published-new' : $status;

		$hidden = ($this->out['hidden'] == 1) ? "<span class='hidden'>" . $OBJ->lang->word('hidden') . "</span> " : '';

		$edit = ($this->out['link'] == '') ? 
			href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=edit&amp;id=" . $this->out['id']) : 		
			href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=link&amp;id=" . $this->out['id']);

		$password = ($this->out['pwd'] != '') ? ' password' : '';
		$link = ($this->out['link'] != '') ? ' linked' : '';

		$here = ($this->out['status'] == 1) ? 'undraft' : 'draft';
		$here = (($here == 'undraft') && ($this->out['new'] == 1)) ? 'undraftnew' : $here;

		$activity = ($here == 'draft') ? "<span class='activity draft'><!-- --></span>" : 
			"<span id='activity-" . $this->out['id'] . "' class='activity $here' onclick=\"make_new(" . $this->out['id'] . "); return false;\"><!-- --></span>";
			
		//$tagged = ($this->out['tagged'] != '') ? $this->out['tagged'] : '';
		$tagged = '';
		
		$homed = ($this->out['home'] == 1) ? 
			"<span class='home' style='font-size: 11px;'>" . $OBJ->lang->word('home') . "</span> " : '';

		return li($activity . "<span class='drag-title{$password}{$link}'>" . strip_tags($this->out['title']) . 
			$tagged .  '</span>' . span(href($OBJ->lang->word('preview'), "?a=$go[a]&amp;q=prv&amp;id=" . 
			$this->out['id']) . ' ' . $edit,
			"class='options' style='color: #000;'") . $homed . $hidden,
			"class='sortableitem' id='item" . $this->out['id'] . "'") . "\n";
	}
	
	
	// generates the node for the section top
	public function titler($list='')
	{
		$OBJ =& get_instance();
		global $go;

		$passwordr = ($this->section['pwd'] != '') ? ' password' : '';
		
		$status = ($this->section['status'] == 1) ? ' undraft' : ' draft';
	
		$sect = span("<a href='#' onclick=\"section_toggle(" . $this->section['secid'] . "); return false;\" title='" . $this->section['url'] . "'>" . $this->section['sec_desc'] . "</a> &nbsp;&nbsp;<span style='color: #666; font-size: 10px;'>" . $this->section['sec_path'] . "</span>", "id='s" . $this->section['secid'] . "' class='{$passwordr}{$status}' style='padding-left: 13px;'");
	
		$linker = href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=edit&amp;id=" . $this->section['id']);
		$previewer = href($OBJ->lang->word('preview'), "?a=$go[a]&amp;q=prv&amp;id=" . $this->section['id']);
	
		$hiddern = ($this->section['hidden'] == 1) ? 
			"<span class='hidden' style='font-size: 11px;'>" . $OBJ->lang->word('hidden') . "</span> " : '';
			
		$homed = ($this->section['home'] == 1) ? 
			"<span class='home' style='font-size: 11px;'>" . $OBJ->lang->word('home') . "</span> " : '';
	
		return ul("\n" . li($sect.
			span($previewer . ' ' . $linker . ' ',
				"class='options' style='color: #000;'") . $homed . $hiddern,
				"class='group'") . "\n" . $list,
				"class='sortable' id='sort" . $this->section['secid'] . "'");
	}
	
	
	// generates the node for a section top using chronology
	public function titler_chron($key, $list='')
	{
		$OBJ =& get_instance();
		global $go;
		
		$passwordr = ($this->section['pwd'] != '') ? ' password' : '';
		
		$status = ($this->section['status'] == 1) ? ' undraft' : ' draft';
	
		$sect = span("<a href='#' onclick=\"section_chron_toggle('" . $this->section['secid'] . "-$key'); return false;\" title='" . $this->section['url'] . "'>" . $this->section['sec_desc'] . "</a> &nbsp;&nbsp;<span style='color: #ccc; font-size: 10px;'>Path: " . $this->section['sec_path'] . "</span>", "id='s" . $this->section['secid'] . "' class='{$passwordr}{$status}' style='padding-left: 13px;'");
	
		$linker = href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=edit&amp;id=" . $this->section['id']);
		$previewer = href($OBJ->lang->word('preview'), "?a=$go[a]&amp;q=prv&amp;id=" . $this->section['id']);
	
		$hiddern = ($this->section['hidden'] == 1) ? 
			"<span class='hidden' style='font-size: 11px;'>" . $OBJ->lang->word('hidden') . "</span> " : '';
			
		$homed = ($this->section['home'] == 1) ? 
			"<span class='home' style='font-size: 11px;'>" . $OBJ->lang->word('home') . "</span> " : '';
	
		return ul("\n" . li($sect.
			span($previewer . ' ' . $linker . ' ',
				"class='options' style='color: #000;'") . $homed . $hiddern,
				"class='group'") . "\n" . $list,
				"class='sortable' id='sort" . $this->section['secid'] . "-$key'");
	}
	
	
	public function index_defaultold()
	{
		$OBJ =& get_instance();
		global $rs;

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, section_top, 
			content, pwd, pdate, new, status, hidden, home             
			FROM ".PX."objects    
			WHERE section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0'  
			ORDER BY ord ASC");	

		if (!$pages) return;

		$s = '';

		foreach ($pages as $page) 
		{
			$this->out = $page;
			$s .= $this->exhibit();
		}

		return $s;
	}
	
	
	public function index_all_tags()
	{
		$OBJ =& get_instance();
		global $rs;

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, section_top, 
			content, pwd, pdate, new, status, hidden, home           
			FROM ".PX."objects   
			WHERE section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibit'
			AND section_top = '0'  
			ORDER BY ord ASC");	

		$s = '';
		
		if ($pages)
		{
			foreach ($pages as $page) 
			{
				$this->out = $page;
				$s .= $this->exhibit();
			}
		}
		
		// add the tags
		// get the tags
		$tags = $OBJ->db->fetchArray("SELECT id, link, target, pwd, title, url, tag_name, tag_group 
			FROM ".PX."tagged, ".PX."tags, ".PX."objects 
			WHERE object = 'tag' 
			AND tag_id = tagged_id 
			AND obj_ref_id = tag_id 
			GROUP BY tag_name 
			ORDER BY tag_name ASC");

		if ($tags)
		{
			foreach ($tags as $tag) $rw[] = $tag['tag_name'];
			
			$s .= "<li style='font-size: 11px; padding-left: 6px;'><span style='color: #999;'>Active Tags:</span> " . implode(', ', $rw) . ".</li>\n";
		}

		return $s;
	}
	
	
	public function index_default()
	{
		$OBJ =& get_instance();
		global $rs, $go;
		
		$this->subsections = $OBJ->db->fetchArray("SELECT *           
			FROM ".PX."objects, ".PX."subsections   
			WHERE section_id = '" . $this->section['secid'] . "' 
			AND subdir = '1' 
			AND sub_sec_id = '" . $this->section['secid'] . "' 
			AND sub_id = section_sub 
			ORDER BY sub_order ASC");

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, section_top, 
			content, pwd, pdate, new, status, hidden, home, section_sub            
			FROM ".PX."objects   
			WHERE section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0' 
			AND subdir = '0' 
			ORDER BY ord ASC");

		//if (!$pages) return;

		$s = ''; $y = 0; $i = 0;
		
		//print_r($pages); exit;
		
		// i think we should rewrite the array in order with the section subs
		if ($pages)
		{
			foreach ($pages as $page)
			{
				if ($page['section_sub'] == '')
				{
					// this is the first array
					$tmp[0][] = $page;
				}
				else
				{
					$tmp[$page['section_sub']][] = $page;
				}
			}
		}

		
		// <ul class='sortable' id='sort2-2008'>
		// <li class='group'><span id='s2-2008'>
		
		//<ul class='sortable' id='sort19-2'>
		
		//print_r($pages); exit;
		$current = '';
		$the_year = false;
		$total = count($pages);
		$i = 1;
		
		// get the first set
		if (isset($tmp[0])) {
		if (is_array($tmp[0]))
		{
			$s .= "<ul class='sortable' id='sort" . $this->section['secid'] . "' style='margin: 0;'>\n";
			
			foreach ($tmp[0] as $do)
			{
				//$s .= "<ul class='sortable' id='sort" . $this->section['secid'] . "'>\n";
				$this->out = $do;
				$s .= $this->exhibit_default();
				//$s .= "</ul>\n";
			}
			
			$s .= "</ul>\n";
			
			unset($tmp[0]);
		} 
		}
		else
		{
			// we still need to reserve the space for dragging things in and out
			$s .= "<ul class='sortable' id='sort" . $this->section['secid'] . "' style='margin: 0;'>\n";
			$s .= "</ul>\n";
		}
		
		//print_r($this->subsections); exit;
		
		if ($this->subsections)
		{
		foreach ($this->subsections as $key => $sub)
		{
			$path = $this->section['sec_path'] . '/' . $sub['sub_folder'];
			
			$status = ($sub['status'] == 1) ? 'published' : 'draft';
			$status = (($status == 'published') && ($sub['new'] == 1)) ? 'published-new' : $status;

			$hidden = ($sub['hidden'] == 1) ? "<span class='hidden'>" . $OBJ->lang->word('hidden') . "</span> " : '';

			$edit = href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=edit&amp;id=" . $sub['id']);

			$password = ($sub['pwd'] != '') ? ' password' : '';
			$link = ($sub['link'] != '') ? ' linked' : '';

			$here = ($sub['status'] == 1) ? 'undraft' : 'draft';
			$here = (($here == 'undraft') && ($sub['new'] == 1)) ? 'undraftnew' : $here;

			$activity = ($here == 'draft') ? "<span class='activity draft'><!-- --></span>" : 
				"<span id='activity-" . $sub['id'] . "' class='activity $here'><!-- --></span>";

			$tagged = '';

			$homed = ($sub['home'] == 1) ? 
				"<span class='home' style='font-size: 11px;'>" . $OBJ->lang->word('home') . "</span> " : '';

			$insert = li($activity . "<span class='{$password}{$link}' id='s" . $this->section['secid'] . "-" . $key . "' style='padding-left: 13px;'>" . strip_tags($sub['title']) . 
				" <strong style='font-weight: normal; font-size: 10px; color: #666;'>&nbsp;&nbsp;$path</strong></span>" . span(href($OBJ->lang->word('preview'), "?a=$go[a]&amp;q=prv&amp;id=" . 
				$sub['id']) . ' ' . $edit,
				"class='options' style='color: #000;'"). $homed . $hidden,
				"class='sortableitem group corners' style='background-color: #ebebeb; border-bottom: 1px solid #ccc;'") . "\n";
			
			$s .= "<ul class='sortable' id='sort" . $this->section['secid'] . "-sub-" . $sub['sub_id'] . "' style='margin: 0 0 0 0;'>\n$insert\n\n";
			
			if (isset($tmp[$sub['sub_id']]))
			{
				foreach($tmp[$sub['sub_id']] as $do)
				{
					$this->out = $do;
					$s .= $this->exhibit_default();
				}
			}
			
			$s .= "</ul>\n";
		}
		}

		return $s;
	}
	
	
	
	public function exhibit_default()
	{
		$OBJ =& get_instance();
		global $go;

		$status = ($this->out['status'] == 1) ? 'published' : 'draft';
		$status = (($status == 'published') && ($this->out['new'] == 1)) ? 'published-new' : $status;

		$hidden = ($this->out['hidden'] == 1) ? "<span class='hidden'>" . $OBJ->lang->word('hidden') . "</span> " : '';

		$edit = ($this->out['link'] == '') ? 
			href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=edit&amp;id=" . $this->out['id']) : 		
			href($OBJ->lang->word('edit'), "?a=$go[a]&amp;q=link&amp;id=" . $this->out['id']);

		$password = ($this->out['pwd'] != '') ? ' password' : '';
		$link = ($this->out['link'] != '') ? ' linked' : '';

		$here = ($this->out['status'] == 1) ? 'undraft' : 'draft';
		$here = (($here == 'undraft') && ($this->out['new'] == 1)) ? 'undraftnew' : $here;

		$activity = ($here == 'draft') ? "<span class='activity draft'><!-- --></span>" : 
			"<span id='activity-" . $this->out['id'] . "' class='activity $here' onclick=\"make_new(" . $this->out['id'] . "); return false;\"><!-- --></span>";
			
		$tagged = '';
		
		$homed = ($this->out['home'] == 1) ? 
			"<span class='home' style='font-size: 11px;'>" . $OBJ->lang->word('home') . "</span> " : '';

		return li($activity . "<span class='drag-title{$password}{$link}'>" . strip_tags($this->out['title']) . 
			$tagged .  '</span>' . span(href($OBJ->lang->word('preview'), "?a=$go[a]&amp;q=prv&amp;id=" . 
			$this->out['id']) . ' ' . $edit,
			"class='options' style='color: #000;'") . $homed . $hidden,
			"class='sortableitem' id='item" . $this->out['id'] . "'") . "\n";
	}
	
	
	
	public function index_tag_group()
	{
		$OBJ =& get_instance();
		global $rs;

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, section_top, 
			content, pwd, pdate, new, status, hidden, home           
			FROM ".PX."objects   
			WHERE section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0'  
			ORDER BY ord ASC");	

		if (!$pages) return;

		$s = '';

		foreach ($pages as $page) 
		{
			$this->out = $page;

			// get the tags
			if ($this->section['sec_proj'] == 2)
			{
				$tag = $OBJ->db->fetchRecord("SELECT tag_name FROM ".PX."tags 
					INNER JOIN ".PX."tagged ON tagged_obj_id = '" . $this->out['id'] . "'
					WHERE tag_id = tagged_id 
					AND tag_group = '" . $this->section['sec_group'] . "' 
					AND tagged_object = 'exh'");

				$this->out['tagged'] = (isset($tag['tag_name'])) ? 
					" &nbsp;&nbsp;<em class='gry-text'>Tag: $tag[tag_name]</em>" : '';
			}

			$s .= $this->exhibit();
		}

		return $s;
	}
	
	
	public function index_search_utility()
	{
		$OBJ =& get_instance();
		global $rs;

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, section_top, 
			content, pwd, pdate, new, status, hidden, home           
			FROM ".PX."objects   
			WHERE section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibit'
			AND section_top = '0'  
			ORDER BY ord ASC");

		$s = '';
		
		if ($pages)
		{
			foreach ($pages as $page) 
			{
				$this->out = $page;

				$s .= $this->exhibit();
			}
		}
		
		$s .= "<li style='font-size: 11px; color: #0ff; padding-left: 6px;'><span style='color: #999;'>Search:</span> This is a search section.</li>\n";

		return $s;
	}
	
	
	public function index_chronological()
	{
		$OBJ =& get_instance();
		global $rs;

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, section_top, 
			content, pwd, pdate, new, status, hidden, home            
			FROM ".PX."objects   
			WHERE section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0'  
			ORDER BY year DESC, ord ASC");	

		if (!$pages) return;

		$s = ''; $y = 0;
		
		// <ul class='sortable' id='sort2-2008'>
		// <li class='group'><span id='s2-2008'>
		
		//<ul class='sortable' id='sort19-2'>
		
		//print_r($pages); exit;
		$current = '';
		$the_year = false;
		$total = count($pages);
		$i = 1;

		foreach ($pages as $page) 
		{
			// this is really quite messy - brain was slow today
			if ($the_year != false) { if ($current != $page['year']) { $s .= "</ul>\n"; }}
			// first
			$current = ($current != $page['year']) ? $page['year'] : $current;
			if ($the_year == false) { $the_year = true; }
			
			// get the year part
			if ($y != $page['year'])
			{
				$s .= "<ul class='sortable' id='sort" . $this->section['secid'] . "-yr-" . $page['year'] . "' style='margin-bottom: 0;'>\n<li class='group corners' style='font-size: 9px; font-weight: bold; padding-top: 2px; padding-bottom: 2px; background: #ebebeb url(asset/img/diagonals.gif) repeat;'><span id='s" . $this->section['secid'] . "-" . $page['year'] . "' style='padding-left: 3px;'>$page[year]</span></li>\n\n";
			}
			
			$this->out = $page;
			$s .= $this->exhibit_chron();
			
			// the end
			if ($total == $i) { $s .= "</ul>\n"; }
			$y = $page['year'];
			$i++;
		}

		return $s;
	}
	

	public function sectional()
	{
		$OBJ =& get_instance();
		global $go, $default;
		
		$body = '';

		$types = array(0 => 'default', 1 => 'chronological', 2 => 'subdir', 3 => 'all_tags', 4 => 'search_utility');

		// get sections
		$sections = $OBJ->db->fetchArray("SELECT *           
			FROM ".PX."objects, ".PX."sections  
			WHERE obj_ref_id = secid   
			AND section_top = '1'  
			ORDER BY sec_ord ASC");

		if (!$sections) return; 
		
		$s = '';
		
		foreach ($sections as $key => $section)
		{
			$f = 'index_' . $types[$section['sec_proj']];

			$this->section = $section;
			
			$list = (method_exists($this, $f)) ?
				call_user_func(array(&$this, $f)) :
				$this->index_default();
			
			$s .= ($section['sec_proj'] != 1) ? $this->titler($list) : $this->titler_chron($key, $list);
			$list = '';
		}
		
		return $s;
	}
}