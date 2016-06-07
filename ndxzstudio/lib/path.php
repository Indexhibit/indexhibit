<?php

	class Path
	{
		// define properties 
		public $title;
		public $search;
		public $replace;
		public $date;
		public $published;
		public $name;
		public $filename;
		public $filetype;
		public $section;
		public $pubtype;
		public $types;
		public $lang;
		public $location;
		public $suffix;
		public $template;
		public $url;
		
		// new ones
		public $prepend;
		public $lang_table;
		
		// newest - used
		public $method = 'page';
		public $date_type = 0;
		public $type;
		public $path = '/';
		public $id = 0;
		public $td = "<td>Site<br />http://www.site.com</td>";
		public $temp;
		public $date_format;


	    function get_path()
		{
			$function = 'do_' . $this->method;
			$this->$function();
			$this->path = $this->urlStrip($this->path);
		}
		
		public function do_page()
		{
			$this->mk_lang();
			$this->mk_section();
			$this->mk_date();
			$this->mk_title();
			$this->path .= '/';
			$this->mk_dupe(); // REVIEW
		}
		
		public function do_inc()
		{
			$this->mk_inc();
		}
		
		public function path_finish()
		{
			$this->urlStrip($this->path);
		}
		
		public function mk_lang() 
		{
			$OBJ =& get_instance();
			
			if ($this->lang != '') 
			{
				$this->path .= $this->lang;
				$this->td .= td($this->urlStrip($OBJ->lang->word('lang') . br() . '/' . $this->lang));
			}
		}
			
		public function mk_section() 
		{
			$OBJ =& get_instance();
			
			if ($this->section != '') 
			{
				$this->path .= $this->section;
				$this->td .= td($this->urlStrip($OBJ->lang->word('section') . br() . '/' . $this->section));
			}
		}
		
		public function path_types()
		{
			return array(1 => '/section/year/month/day/title/',
				2 => '/section/year/month/title/',
				3 => '/section/year/title/',
				4 => '/section/title/');
		}
		
		public function mk_date() 
		{
			$OBJ =& get_instance();
			
			if ($this->published == '') $this->published = date('Y-m-d');
			
			if ($this->date_format == 1) // /year/month/day
			{
				$this->create_date();
				$this->path .= '/' . $this->date['year'] . '/' . $this->date['month'] . '/' . $this->date['day'];
				$this->td .= td($this->urlStrip($OBJ->lang->word('date') . br() . '/' . $this->date['year'] . '/' . $this->date['month'] . '/' . $this->date['day']));
			}
			elseif ($this->date_format == 2) // /year/month
			{
				$this->create_date();
				$this->path .= '/' . $this->date['year'] . '/' . $this->date['month'];
				$this->td .= td($this->urlStrip($OBJ->lang->word('date') . br() . '/' . $this->date['year'] . '/' . $this->date['month']));
			}
			elseif ($this->date_format == 3)  // /year
			{
				$this->create_date();
				$this->path .= '/' . $this->date['year'];
				$this->td .= td($this->urlStrip($OBJ->lang->word('date') . br() . '/' . $this->date['year']));
			}
			elseif ($this->date_format == 4)  // need to do this one
			{
				$this->create_date();
				$this->path .= '/' . $this->date['year'];
				$this->td .= td($this->urlStrip($OBJ->lang->word('date') . br() . '/' . $this->date['year']));
			}
			else
			{
				// nothing
			}
		}
		
		public function mk_title() 
		{
			$OBJ =& get_instance();
			
			if ($this->title != '') 
			{
				$this->path .= '/' . $this->process_title();
				$this->td .= td($this->urlStrip($OBJ->lang->word('title') . br() . '/' . $this->process_title()));
			}
		}
		
		public function mk_dupe() 
		{
			$OBJ =& get_instance();
			
			// check for dupe
			$check = $OBJ->db->fetchArray("SELECT p_obj_ref_id 
				FROM ".PX."published 
				WHERE p_url = '" . $this->urlStrip($this->path) . "'");

			// if dupe alert
			if ($check)
			{
				// let's just append things
				$previous = count($check);
				$previous = $previous + 1 . '/';
				$this->path = $this->path . $previous;
			}
		}
		
		public function mk_inc() 
		{
			global $default;
			
			switch($this->type)
			{
				case 2: // js
					$this->path .= $default['inc_path'][2];
					$this->path .= '/' . $this->process_title();
					$this->path .= '.' . $default['inc_pfx'][2];
				break;
				case 3: // css
					$this->path .= $default['inc_path'][3];
					$this->path .= '/' . $this->process_title();
					$this->path .= '.' . $default['inc_pfx'][3];
				break;
				case 4: // ajax/script
					$this->path .= $default['inc_path'][4];
					$this->path .= '/' . $this->process_title();
					$this->path .= '.' . $default['inc_pfx'][4];
				break;
				case 5: // xml
					$this->path .= $default['inc_path'][5];
					$this->path .= '/' . $this->process_title();
					$this->path .= '.' . $default['inc_pfx'][5];
				break;
				default:
					$this->path = '';
				break;
			}
		}
	
	
		// cleaning the url
		public function urlStrip($url)
		{
			$search = '/\/+/';
			$replace = '/';

			return preg_replace($search, $replace, $url);
		}


		public function make_title()
		{
			$this->title = explode(" ", $this->title);
			$this->title = implode("-", $this->title);
			return $this->title;
		}


		public function clean_title()
		{
			load_helper('output');
			load_helper('romanize');
			
			$this->title = utf8Deaccent($this->title, 0);
			$this->title = utf8Romanize($this->title);
			$this->title = preg_replace('/[^a-z0-9- ]/i', '', $this->title);
			
			return $this->title;
		}


		public function process_title()
		{
			$this->title = $this->clean_title($this->title);
			$this->title = strtolower($this->make_title($this->title));
			
			$this->title = ($this->title == '') ? $this->id : $this->title;
			
			return $this->title;
		}
		
		
		public function create_date()
		{
			// input format = 0000-00-00
			if (!isset($this->published)) return;
			
			$this->date['year']  	= substr($this->published, 0, 4);
			$this->date['month'] 	= substr($this->published, 5, 2);
			$this->date['day']	 	= substr($this->published, 8, 2);
			
			return $this->date;	
		}
	}


?>