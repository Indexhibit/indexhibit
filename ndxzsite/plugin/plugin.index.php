<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Frontend 'index' class
*
* Used for generating frontend index
* 
* @version 1.0
* @author Vaska 
*/

class Index
{
	var $types;
	var $sections;
	var $section;
	var $exhibits;
	var $exhibit;
	var $output;
	var $active_section;
	var $active_exhibit;
	var $active_flag = false;
	var $li_class;
	var $pages;
	var $next;
	var $previous;
	var $counter = 0;
	var $current;

	public function __construct()
	{
		$OBJ =& get_instance();
		global $default;

		$this->types = $default['section_types'];
		
		$this->active_section = $OBJ->vars->exhibit['section_id'];
		$this->active_exhibit = $OBJ->vars->exhibit['id'];
	}
	
	// returns array
	function get_sections()
	{
		$OBJ =& get_instance();
		
		// only for exhibits hence the 'object'
		$this->sections = $OBJ->db->fetchArray("SELECT id, title, url, sec_proj, content, sec_hide,   
			section, sec_desc, hidden, secid, pwd, section_top, pdate, new, sec_group, sec_pwd, status, 
			sec_subs              
			FROM ".PX."objects, ".PX."sections  
			WHERE obj_ref_id = secid   
			AND section_top = '1' 
			AND sec_hide != '1' 
			ORDER BY sec_ord ASC");
	
		// all the pages in one query
		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, iframe,  
			section_top, pwd, pdate, new, section_id 
			FROM ".PX."objects, ".PX."sections   
			WHERE status = '1' 
			AND section_id = secid 
			AND hidden != '1'   
			AND object = 'exhibits'
			AND section_top = '0'  
			ORDER BY sec_ord ASC, ord ASC");
		
		if ($pages)
		{	
			foreach ($pages as $key => $o)
			{
				$this->pages['section_' . $o['section_id']][] = array('id' => $o['id'], 
					'title' => $o['title'],
					'url' => $o['url'],
					'ord' => $o['ord'],
					'year' => $o['year'],
					'link' => $o['link'],
					'target' => $o['target'], 
					'iframe' => $o['iframe'],
					'section_top' => $o['section_top'],
					'pwd' => $o['pwd'],
					'pdate' => $o['pdate'],
					'new' => $o['new']);
			}
		}

		if (!$this->sections) return;
	}
	
	function load_index()
	{
		$OBJ =& get_instance();
		$this->get_sections();
		
		$out = '';
		
		if (!$this->sections) return;
		
		foreach ($this->sections as $key => $section)
		{
			$this->section = $section;
			
			// if there is a hook let's do that instead
			$check = 'index_alter_' . $this->section['secid'];
			
			if ($OBJ->hook->registered_hook($check))
			{
				$out .= $OBJ->hook->do_action($check);
			}
			else 
			{
				$f = 'ndxz_index_' . $this->types[$section['sec_proj']];
				$out .= (method_exists($this, $f)) ? $this->$f($section) : '';
			}
		}
		
		// if there are actually no viewable pages
		//if ($li == '') return;

		return $out;
	}
	
	
	function next_exhibit()
	{
		// we need to check if the array exists as well...
		if (isset($this->library[$this->current + 1]))
		{
			$this->next = "<a href='" . $OBJ->baseurl . ndxz_rewriter($this->library[$this->current + 1]['url']) . "' title='Next'>Next</a>";
		}
	}
	
	
	function previous_exhibit()
	{
		// we need to check if the array exists as well...
		if (isset($this->library[$this->current - 1]))
		{
			$this->previous = "<a href='" . $OBJ->baseurl . ndxz_rewriter($this->library[$this->current - 1]['url']) . "' title='Next'>Previous</a>";
		}
	}
	
	function previous_next_exhibit()
	{
		$this->next_exhibit();
		$this->previous_exhibit();

		$html = $this->previous;
		if (($this->previous != '') && ($this->next != '')) $html .= ' / ';
		$html .= $this->next;
		
		return $html;
	}
	
	
	function exhibit_page()
	{
		$OBJ =& get_instance();
		
		// library the functions so we can easily do next and previous links
		$this->library[$this->counter] = $this->exhibit;
		
		$password = ($this->exhibit['pwd'] == true) ? " password" : '';

		if ($OBJ->vars->exhibit['id'] == $this->exhibit['id'])
		{
			$active = " active$password";
			$this->active_flag = true;
			$this->current = $this->counter;
		}
		else
		{	
			$active = $password;
		}
		
		$this->counter++;

		//$password = ($this->exhibit['pwd'] == true) ? " password" : '';
		//$active = ($OBJ->vars->exhibit['id'] == $this->exhibit['id']) ? " active$password" : $password;
		$new = ($this->exhibit['new'] == 1) ? "&nbsp;<sup class='new_exhibit'></sup>": '';
		
		$class = ($this->li_class == '') ? 'exhibit_title' : $this->li_class;

		// we build the link for site pages or iframed pages or external links
		return ($this->exhibit['link'] == '') ? "<li id='exhibit_" . $this->exhibit['id'] . "' class='" . $class . "{$active}'><a href='" . $OBJ->baseurl . ndxz_rewriter($this->exhibit['url']) . "'>" . $this->exhibit['title'] . "</a>{$new}</li>\n" : 
			$this->iframed_link($active, $this->exhibit['id'], $this->exhibit['title'], $this->exhibit['url'], $this->exhibit['link'], $this->exhibit['target'], $this->exhibit['iframe']);
	}
	
	
	function iframed_link($active, $id, $title, $url, $link='', $target=0, $iframe=0)
	{
		$OBJ =& get_instance();

		$link = ($iframe == 1) ? $OBJ->baseurl . ndxz_rewriter($url) : $link;

		$target = ($iframe == 1) ? '' : ($target == 1) ? " target='_new'" : '';
		//$target = ($target == 1) ? " target='_new'" : '';

		return "<li id='exhibit_$id' class='exhibit_title exhibit_link$active'><a href='$link'{$target}>" . $title . "</a></li>\n";
	}
	
	
	function ndxz_index_tags()
	{
		$OBJ =& get_instance();

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, section_top, 
			content, pwd, pdate, new          
			FROM ".PX."objects   
			WHERE status = '1' 
			AND hidden != '1' 
			AND section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0'  
			ORDER BY ord ASC");

		/// this is the first hook
		// if the section top is unpublished we need to make it a non-link - just a title
		if ($OBJ->hook->registered_hook('section_title'))
		{
			$section_title = $OBJ->hook->do_action('section_title', $this->section);
		}
		else
		{
			$active = ($OBJ->vars->exhibit['id'] == $this->section['id']) ? ' active_section_title' : '';

			if ($this->section['status'] == 1)
			{
				$section_title = "<li id='section_title_" . $this->section['secid'] . "'><span class='section_title{$active}'><a href='" . $OBJ->baseurl . ndxz_rewriter($this->section['url']) . "' id='section_link_" . $this->section['secid'] . "'>" . $this->section['sec_desc'] . "</a></span>\n";
			}
			else
			{
				$section_title = "<li id='section_title_" . $this->section['secid'] . "'><span id='section_link_" . $this->section['secid'] . "' class='section_title{$active}'>" . $this->section['sec_desc'] . "</span>\n";
			}
		}
		
		// yes, this is correct
		if (($this->section['hidden'] == 1) && (!$pages)) return;
		
		$active_section = ($OBJ->vars->exhibit['section_id'] == $this->section['secid']) ? ' active_section' : '';
		
		$s = "<ul class='section{$active_section}' id='section_" . $this->section['secid'] . "'>\n";
		
		if ($this->section['hidden'] != 1) $s .= $section_title;

		if ($pages) 
		{
			foreach ($pages as $page) 
			{
				$this->exhibit = $page;
				$s .= $this->exhibit_page();
			}
		}

		$s .= $this->ndxz_all_tags_list();
		
		if ($this->section['hidden'] != 1) $s .= "</li>\n";
		$s .= "</ul>\n";

		return $s;
	}


	function ndxz_all_tags_list()
	{
		$OBJ =& get_instance();

		// get the tags for images only?
		$tags = $OBJ->db->fetchArray("SELECT id, link, target, pwd, title, url, tag_name, tag_group, new  
			FROM ".PX."tagged, ".PX."tags, ".PX."objects, ".PX."media  
			WHERE object = 'tag' 
			AND tagged_object = 'img' 
			AND tag_id = tagged_id 
			AND media_id = tagged_obj_id 
			AND obj_ref_id = tag_id 
			GROUP BY id 
			ORDER BY tag_name ASC");
			
		if (!$tags) return;
		
		$active_section = ($OBJ->vars->exhibit['section_id'] == $this->section['secid']) ? ' active_section' : '';

		$s = "<ul class='section all_tags{$active_section}'>\n";

		if ($tags)
		{
			// special class name for li
			$this->li_class = 'tag_name';

			foreach ($tags as $tag) 
			{
				$this->exhibit = $tag;
				$s .= $this->exhibit_page();
			}
		}
		
		$s .= "</ul>\n";
		
		// clear it out
		$this->li_class = '';

		return $s;
	}
	

	function ndxz_index_chronological()
	{
		$OBJ =& get_instance();

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, iframe, content, pwd, pdate, new, hidden           
			FROM ".PX."objects   
			WHERE status = '1' 
			AND hidden != '1' 
			AND section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0'  
			ORDER BY year DESC, ord ASC");	

		// rewrite the array with years
		if ($pages)
		{
			foreach ($pages as $rewrite)
			{
				$new[$rewrite['year']][] = array(
					'id'		=> $rewrite['id'],
					'year'		=> $rewrite['year'],
					'title'		=> $rewrite['title'],
					'hidden'	=> $rewrite['hidden'],
					'new'		=> $rewrite['new'],
					'url'		=> $rewrite['url'],
					'link'		=> $rewrite['link'],
					'pwd'		=> $rewrite['pwd']
				);
			}
		}
		else
		{
			$new = array();
		}
		
		// yes, this is correct
		if (($this->section['hidden'] == 1) && (empty($new))) return;
		
		$active_section = ($OBJ->vars->exhibit['section_id'] == $this->section['secid']) ? ' active_section' : '';
		
		$s = "<ul class='section{$active_section}' id='section_" . $this->section['secid'] . "'>\n";
		
		/// this is the first hook
		// if the section top is unpublished we need to make it a non-link - just a title
		if ($OBJ->hook->registered_hook('section_title'))
		{
			$section_title = $OBJ->hook->do_action('section_title', $this->section);
		}
		else
		{
			$active = ($OBJ->vars->exhibit['id'] == $this->section['id']) ? ' active_section_title' : '';

			if ($this->section['status'] == 1)
			{
				$section_title = "<li><span id='section_title_" . $this->section['secid'] . "' class='section_title{$active}'><a href='" . $OBJ->baseurl . ndxz_rewriter($this->section['url']) . "' id='section_link_" . $this->section['secid'] . "'>" . $this->section['sec_desc'] . "</a></span>\n";
			}
			else
			{
				$section_title = "<li id='section_title_" . $this->section['secid'] . "'><span id='section_link_" . $this->section['secid'] . "' class='section_title{$active}'>" . $this->section['sec_desc'] . "</span>\n";
			}
		}
		
		if ($this->section['hidden'] != 1) $s .= $section_title;
		if ($this->section['hidden'] != 1) $s .= "<ul>\n";
		
		if (!empty($new))
		{
			$i = 1;  $flag = false;

			foreach ($new as $key => $pages) 
			{
				$li = '';

				foreach ($pages as $page)
				{
					// check here for active page - throw a flag if true
					if ($OBJ->vars->exhibit['id'] == $page['id']) $flag = true;
					
					$this->exhibit = $page;
					$li .= $this->exhibit_page();
				}
				
				$active_subsection_title = ($flag == true) ? " active_subsection_title" : '';

				$s .= "<li><span id='subsection_title_" . $this->section['secid'] . "_$i' class='subsection_title{$active_subsection_title}'>" . str_replace('_', ' ', $key) . "</span>\n";
				
				$active_subsection = ($flag == true) ? ' active_subsection' : '';
				$flag = false; // reset the flag for next time
			
				$s .= "<ul class='subsection{$active_subsection}'>\n" . $li . "</ul>\n";
				$s .= "</li>\n";
			
				// clear it out
				$li = ''; $i++;
			}
		}
		
		if ($this->section['hidden'] != 1) $s .= "</ul>\n";
		if ($this->section['hidden'] != 1) $s .= "</li>\n";
		$s .= "</ul>\n";
		
		// last check ???
		if (($this->section['status'] != 1) && (empty($new))) return;
		
		return $s;	
	}
	
	
	function ndxz_index_default()
	{
		$OBJ =& get_instance();
		
		// do not show hidden sections
		if ($this->section['sec_hide'] == 1) return;
		
		$subs = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, iframe, 
			content, pwd, pdate, new, hidden, subdir, section_sub, status, sub_order, sub_hide              
			FROM ".PX."objects, ".PX."subsections   
			WHERE section_id = '" . $this->section['secid'] . "' 
			AND sub_sec_id = section_id 
			AND object = 'exhibits'
			AND section_top = '0' 
			AND subdir = '1' 
			AND sub_id = section_sub 
			ORDER BY sub_order ASC");
		
		if ($subs)
		{
			// rewrite the subs array
			foreach ($subs as $sub) $tmp_subs[$sub['id']] = $sub;
		}
		else
		{
			// if there aren't any subs
			$tmp_subs = array();
			//return;
		}

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, iframe, 
			content, pwd, pdate, new, hidden, subdir, section_sub            
			FROM ".PX."objects  
			WHERE status = '1' 
			AND hidden != '1' 
			AND section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0' 
			AND subdir != '1' 
			ORDER BY ord ASC");	

		//if (!$pages) return;
		$tmp = array();
		
		if ($pages)
		{
			foreach ($pages as $key => $rw)
			{
				// we need to separate subs from pages
				if ($rw['section_sub'] == '')
				{
					// this is the first array
					$tmp[0][] = $rw;
				}
				else
				{
					$tmp[$rw['section_sub']][] = $rw;
				}
			}
		}

		// yes, this is correct
		//if ($this->section['sec_hide'] == 1) return;
		
		$active_section = ($OBJ->vars->exhibit['section_id'] == $this->section['secid']) ? ' active_section' : '';
		
		$s = "<ul class='section{$active_section}' id='section_" . $this->section['secid'] . "'>\n";
		
		$active = ($OBJ->vars->exhibit['id'] == $this->section['id']) ? ' active_section_title' : '';

		if ($this->section['status'] == 1)
		{
			$section_title = "<li><span id='section_title_" . $this->section['secid'] . "' class='section_title{$active}'><a href='" . $OBJ->baseurl . ndxz_rewriter($this->section['url']) . "' id='section_link_" . $this->section['secid'] . "'>" . $this->section['sec_desc'] . "</a></span>\n";
		}
		else
		{
			$section_title = "<li id='section_title_" . $this->section['secid'] . "'><span id='section_link_" . $this->section['secid'] . "' class='section_title{$active}'>" . $this->section['sec_desc'] . "</span>\n";
		}
		
		if ($this->section['hidden'] != 1) $s .= $section_title;
		if ($this->section['hidden'] != 1) $s .= "<ul>\n";

		// pages not in a subdir
		if (isset($tmp[0]))
		{
			foreach ($tmp[0] as $do)
			{
				$this->exhibit = $do;
				$s .= $this->exhibit_page();
			}
		}

		$t = 1; $flag = false;

		if (!empty($tmp_subs))
		{
			$i = 1;

			foreach ($tmp_subs as $key => $sub) 
			{
				// hidden subsections
				//if ($sub['sub_hide'] != 1)
				//{
					$li = '';
				
					if (isset($tmp[$sub['section_sub']]))
					{
						foreach ($tmp[$sub['section_sub']] as $page)
						{
							// check here for active page - throw a flag if true
							if ($OBJ->vars->exhibit['id'] == $page['id']) $flag = true;

							$this->exhibit = $page;
							$li .= $this->exhibit_page();
						}
					}
					
					if ($OBJ->vars->exhibit['id'] == $sub['id']) $flag = true;
					$active_subsection_title = ($flag == true) ? " active_subsection_title" : '';

					if ($sub['hidden'] != 1)  $s .= ($sub['status'] == 1) ? "<li><span id='subsection_title_" . $this->section['secid'] . "_$i' class='subsection_title{$active_subsection_title}'><a href='" . $OBJ->baseurl . ndxz_rewriter($sub['url']) . "'>" . $sub['title'] . "</a></span>\n" : "<li><span id='subsection_title_" . $this->section['secid'] . "_$i' class='subsection_title{$active_subsection_title}'>" . $sub['title'] . "</span>\n";

					$active_subsection = ($flag == true) ? " active_subsection" : '';
					$flag = false; // reset the flag for next time

					if ($li != '') $s .= "<ul class='subsection{$active_subsection}'>\n" . $li . "</ul>\n";
					if ($sub['hidden'] != 1)  $s .= "</li>\n";
			
					$t++; $i++;
				//}
			}
		}
		
		if ($this->section['hidden'] != 1) $s .= "</ul>\n";
		if ($this->section['hidden'] != 1) $s .= "</li>\n";
		$s .= "</ul>\n";
		
		// last check ???
		//if (($this->section['status'] != 1) && (empty($tmp_subs))) return;

		return $s;	
	}


	function ndxz_index_search_utility()
	{
		$OBJ =& get_instance();

		$pages = $OBJ->db->fetchArray("SELECT id, title, url, ord, year, link, target, iframe, 
			section_top, content, pwd, pdate, new          
			FROM ".PX."objects   
			WHERE status = '1' 
			AND hidden != '1' 
			AND section_id = '" . $this->section['secid'] . "'  
			AND object = 'exhibits'
			AND section_top = '0'  
			ORDER BY ord ASC");	
			
		////////////////////////////////
		
		// yes, this is correct
		if (($this->section['hidden'] == 1) && (empty($pages))) return;
		
		$s = "<ul class='section' id='section_" . $this->section['secid'] . "'>\n";
		
		/// this is the first hook
		// if the section top is unpublished we need to make it a non-link - just a title
		if ($OBJ->hook->registered_hook('section_title'))
		{
			$section_title = $OBJ->hook->do_action('section_title', $this->section);
		}
		else
		{
			$active = ($OBJ->vars->exhibit['id'] == $this->section['id']) ? ' active' : '';

			if ($this->section['status'] == 1)
			{
				$section_title = "<li id='section_title_" . $this->section['secid'] . "'><span class='section_title{$active}'><a href='" . $OBJ->baseurl . ndxz_rewriter($this->section['url']) . "' id='section_link_" . $this->section['secid'] . "'>" . $this->section['sec_desc'] . "</a></span>\n";
			}
			else
			{
				$section_title = "<li id='section_title_" . $this->section['secid'] . "'><span id='section_link_" . $this->section['secid'] . "' class='section_title{$active}'>" . $this->section['sec_desc'] . "</span>\n";
			}
		}
		
		if ($this->section['hidden'] != 1) $s .= $section_title;
		if ($this->section['hidden'] != 1) $s .= "<ul>\n";
		
		$li = '';

		if ($pages)
		{
			foreach ($pages as $page)
			{
				$this->exhibit = $page;
				$li .= $this->exhibit_page();
			}
		}

		// the search page
		$s .= $this->ndxz_default_search();
		
		if ($this->section['hidden'] != 1) $s .= "</ul>\n";
		if ($this->section['hidden'] != 1) $s .= "</li>\n";
		$s .= "</ul>\n";

		return $s;
	}
	
	
	function ndxz_default_search()
	{
		$s = "<li id='ndxz-searcher'>
<form name='ndxz_search' method='get' action=''>
<input type='text' name='ndxz_search' id='ndxz_search' /> 
<input type='submit' value='Search' id='ndxz_search_btn' />
</form>
</li>";

		return $s;
	}
}