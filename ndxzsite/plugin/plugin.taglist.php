<?php

class Taglist
{
	var $counter = 0;
	
	public function __construct()
	{
		
	}

	function load()
	{
		$OBJ =& get_instance();
		
		return $this->ndxz_all_tags_list();
	}

	function ndxz_all_tags_list()
	{
		$OBJ =& get_instance();

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

		$s = "<ul id='all_tags' class='section'>\n";

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
	
	function exhibit_page()
	{
		$OBJ =& get_instance();
		
		// library the functions so we can easily do next and previous links
		$this->library[$this->counter] = $this->exhibit;
		
		$password = ($this->exhibit['pwd'] == true) ? " password" : '';

		if ($OBJ->vars->exhibit['id'] == $this->exhibit['id'])
		{
			$active = " active";
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
		$new = ($this->exhibit['new'] == 1) ? "&nbsp;<sup>new</sup>": '';
		
		$class = ($this->li_class == '') ? 'exhibit_title' : $this->li_class;

		// we build the link for site pages or iframed pages or external links
		return ($this->exhibit['link'] == '') ? "<li id='exhibit_" . $this->exhibit['id'] . "' class='" . $class . "{$active}'><a href='" . $OBJ->baseurl . ndxz_rewriter($this->exhibit['url']) . "'>" . $this->exhibit['title'] . "</a>{$new}</li>\n" : 
			$this->iframed_link($active, $this->exhibit['title'], $this->exhibit['url'], $this->exhibit['link'], $this->exhibit['target'], $this->exhibit['iframe']);
	}
	
	
	function iframed_link($active, $title, $url, $link='', $target=0, $iframe=0)
	{
		$OBJ =& get_instance();

		$link = ($iframe == 1) ? $OBJ->baseurl . ndxz_rewriter($url) : $link;

		$target = ($iframe == 1) ? '' : ($target == 1) ? " target='_new'" : '';
		//$target = ($target == 1) ? " target='_new'" : '';

		return "<li class='section-page section-link$active'><a href='$link'{$target}>" . $title . "</a></li>\n";
	}
}