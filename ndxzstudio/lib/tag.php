<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Tag class
*
* Tagging
* 
* @version 1.0
* @author Vaska 
*/
class Tag 
{
	public $tags	= array();
	public $active_tags;
	public $tag;
	public $tag_id;
	public $method;
	public $id;
	public $tags_enabled = false;
	
	public function __construct()
	{
		$OBJ =& get_instance();
		$OBJ->template->add_js('tags.js');
		$this->method = 'exh'; // default
	}
	
	// get tags
	public function get_all_tags()
	{
		$OBJ =& get_instance();

		$this->tags = $OBJ->db->fetchArray("SELECT * FROM ".PX."tags ORDER BY tag_name ASC");
	}
	
	
	public function remove_duplicates($tag_id)
	{
		$OBJ =& get_instance();
		
		$tagged = $OBJ->db->fetchArray("SELECT * ".PX."tagged 
			WHERE tagged_id = '$tag_id' 
			ORDER BY tagged_obj_id ASC");
			
		$tag = 0;
			
		if ($tagged)
		{
			foreach ($tagged as $tag)
			{
				
			}
		}
		
		return;
	}
	
	
	public function get_all_tags_count()
	{
		$OBJ =& get_instance();
		
		$this->get_all_tags();
		
		$body = '';
		
		if (!empty($this->tags))
		{
			$body = "<div style='line-height: 1.5em;' id='master-tag-list'>";

			// rewrite the array
			foreach ($this->tags as $tag)
			{
				// let's count things here...
				$count = $OBJ->db->getCount("SELECT count(*) FROM ".PX."tagged, ".PX."media 
					WHERE tagged_id = '$tag[tag_id]' 
					AND media_id = tagged_obj_id");
			
				$body .= "<div style='float: left; width: 170px; height: 21px;'>";
				//$body .= "<a href='?a=system&q=edittags&id=$tag[tag_id]' rel=\"facebox;width=900;height=550;modal=true\" title='Edit'>+</a>&nbsp;";
				$body .= "<a href='?a=system&q=showtag&id=$tag[tag_id]' rel=\"facebox;width=900;height=550;modal=true\">" . $tag['tag_name'] . " ($count)</a>";
				$body .= "</div>";
			}
		
			//$body .= implode(', ', $rwtag) . '.';
			$body .= "<div style='clear: left;'><!-- --></div>";
			$body .= "</div>";
		}
		
		if ($body == '') return;
		
		return $body;
	}
	
	
	// active - input is a string
	public function get_active_tags()
	{
		$OBJ =& get_instance();
		$tags = $OBJ->db->fetchArray("SELECT * FROM ".PX."tags 
			ORDER BY tag_group ASC, tag_name ASC");

		$out = '';
		
		$active = ($this->active_tags != '') ? explode(',', $this->active_tags) : array();
		
		$group = 1;
		
		if ($tags)
		{
			foreach ($tags as $key => $tag)
			{
				if ($tag['tag_group'] != $group) $out .= br(2);

				if (preg_match("/^#/", $tag['tag_name'])) // colored tag
				{
					$class = (in_array($tag['tag_id'], $active)) ? " class='active'": " class='inactive'";

					$edit = "<a href='#' onclick=\"edit_tag($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red; font-weight: bold;' title='Edit Tag'>+</a>&nbsp;";
					
					$yep = "<a href='#' onclick=\"upd_tags($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red;' title='Edit Tag'>&nbsp;&nbsp;&nbsp;&nbsp;</a>";

					$out .= span($edit . $yep, "$class style='background: $tag[tag_name];'" . " id='tag$tag[tag_id]'") . ' ';
				}
				else
				{
					$class = (in_array($tag['tag_id'], $active)) ? " class='active'": " class='inactive'";
				
					$edit = "<a href='#' onclick=\"edit_tag($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red; font-weight: bold;' title='Edit Tag'>+</a>&nbsp;";
					
					$yep = "<a href='#' onclick=\"upd_tags($tag[tag_id], '" . $this->method . "'); return false;\" style='text-decoration: none;' title='Use Tag'>" . str_replace('_', '&nbsp;', $tag['tag_name']) . "</a>";
					
					$out .= span($edit . $yep, $class . " id='tag$tag[tag_id]'") . ' ';
				}
				
				$group = $tag['tag_group'];
			}
		}
		
		return $out;
	}
	
	
	// active - input is a string
	public function get_active_tags2($grupo='')
	{
		$OBJ =& get_instance();

		$tags = $OBJ->db->fetchArray("SELECT * FROM ".PX."tags ORDER BY tag_group ASC, tag_name ASC");
			
		$activ = $OBJ->db->fetchArray("SELECT tagged_id FROM ".PX."tagged 
			WHERE tagged_obj_id='$this->id' 
			AND tagged_object = '$this->method'");
		/*
		echo "SELECT tagged_id FROM ".PX."tagged 
			WHERE tagged_obj_id='$this->id' 
			AND tagged_object = '$this->method'"; exit;
		*/

		if ($activ)
		{
			foreach ($activ as $go) $active[] = $go['tagged_id'];
		}
		else
		{
			$active = array();
		}

		$out = '';
		
		//$active = ($this->active_tags != '') ? explode(',', $this->active_tags) : array();
		
		//$group = 1;
		
		$tflag = true;
		$teflag = false;
		
		if ($tags)
		{
			foreach ($tags as $key => $tag)
			{
				if (preg_match("/^#/", $tag['tag_name'])) // colored tag
				{
					$class = (in_array($tag['tag_id'], $active)) ? " class='active'": " class='inactive'";

					//$edit = "<a href='#' onclick=\"edit_tag($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red; font-weight: bold;' title='Edit Tag'>+</a>&nbsp;";
					
					$yep = "<a href='#' onclick=\"upd_tags($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red;' title='Edit Tag'>&nbsp;&nbsp;&nbsp;&nbsp;</a>";
					
					$arr[] = span($yep, "$class" . " id='tag$tag[tag_id]'");
				}
				else
				{
					$class = (in_array($tag['tag_id'], $active)) ? " class='active'": " class='inactive'";
				
					//$edit = "<a href='#' onclick=\"edit_tag($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red; font-weight: bold;' title='Edit Tag'>+</a>&nbsp;";
					
					$yep = "<a href='#' onclick=\"upd_tags($tag[tag_id], '" . $this->method . "'); return false;\" style='text-decoration: none;' title='Use Tag'>" . str_replace('_', '&nbsp;', $tag['tag_name']) . "</a>";
					
					//$out .= span($edit . $yep, $class . " id='tag$tag[tag_id]'") . ' ';
					
					$arr[] = span($yep, "$class" . " id='tag$tag[tag_id]'");
				}
				
				//$group = $tag['tag_group'];
			}
		}
		
		$out = '';
		if (empty($arr)) return '';
		
		// alernate output
		if (is_array($arr))
		{
			//$i = 1;
			//foreach ($arr as $key => $go)
			//{
				$out .= div(implode(', ', $arr));
				
			//	$i++;
			//}
		}

		return $out;
	}
	

	// active - input is a string
	public function show_active_tags()
	{
		$OBJ =& get_instance();
		$tags = $OBJ->db->fetchArray("SELECT * FROM ".PX."tags ORDER BY tag_name ASC");

		$out = '';
		
		$active = ($this->active_tags != '') ? explode(',', $this->active_tags) : array();
		
		if ($tags)
		{
			foreach ($tags as $key => $tag)
			{
				if (in_array($tag['tag_id'], $active))
				{
					if (preg_match("/^#/", $tag['tag_name'])) // colored tag
					{
						$out .= span('&nbsp;&nbsp;&nbsp;&nbsp;', "id='tag$tag[tag_id]' class='active'  style='font-size: 9px; background: $tag[tag_name]; border: 1px solid $tag[tag_name];'") . ' ';
					}
					else
					{
						$out .= span(str_replace('_', '&nbsp;', $tag['tag_name']), "id='tag$tag[tag_id]' class='active'  style='font-size: 9px;'") . ' ';
					}
				}
			}
		}
		
		return $out;
	}
	
	
	// this is only used internally?
	// active - input is a string
	public function show_active_tags2($group=0)
	{
		$OBJ =& get_instance();
		$tags = $OBJ->db->fetchArray("SELECT * FROM ".PX."tags ORDER BY tag_name ASC");
		
		//echo $group; exit;

		$out = '';
		
		$activ = $OBJ->db->fetchArray("SELECT tagged_id FROM ".PX."tagged 
			WHERE tagged_obj_id = '$this->id' 
			AND tagged_object = '$this->method'");
		
		if ($activ)
		{
			foreach ($activ as $go) $active[] = $go['tagged_id'];
		}
		else
		{
			$active = array();
			
			// this works just fine?
			$out .= span('None', "style='font-size: 9px; background: #ebebeb;'");
		}
		
		if ($tags)
		{
			foreach ($tags as $key => $tag)
			{
				if (in_array($tag['tag_id'], $active))
				{
					if (preg_match("/^#/", $tag['tag_name'])) // colored tag
					{
						$out .= span('&nbsp;&nbsp;&nbsp;&nbsp;', "id='tag$tag[tag_id]' class='active'  style='font-size: 9px; background: $tag[tag_name]; border: 1px solid $tag[tag_name];'") . ' ';
					}
					else
					{
						$out .= span(str_replace('_', '&nbsp;', $tag['tag_name']), "id='tag$tag[tag_id]' class='active'  style='font-size: 9px;'") . ' ';
					}
				}
			}
		}
		
		return $out;
	}
	
	
	
	// active - input is a string
	public function get_tags_search()
	{
		$OBJ =& get_instance();
		$tags = $OBJ->db->fetchArray("SELECT * FROM ".PX."tags 
			ORDER BY tag_group ASC, tag_name ASC");

		$out = '';
		
		$active = ($this->active_tags != '') ? explode(',', $this->active_tags) : array();
		
		$group = 1;
		
		if ($tags)
		{
			foreach ($tags as $key => $tag)
			{
				if ($tag['tag_group'] != $group) $out .= br(2);

				if (preg_match("/^#/", $tag['tag_name'])) // colored tag
				{
					$class = (in_array($tag['tag_id'], $active)) ? " class='active'": " class='inactive'";

					$edit = "<a href='#' onclick=\"edit_tag($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red; font-weight: bold;' title='Edit Tag'>+</a>&nbsp;";
					
					$yep = "<a href='#' onclick=\"upd_tags($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red;' title='Edit Tag'>&nbsp;&nbsp;&nbsp;&nbsp;</a>";

					$out .= span($edit . $yep, "$class style='background: $tag[tag_name];'" . " id='tag$tag[tag_id]'") . ' ';
				}
				else
				{
					$class = (in_array($tag['tag_id'], $active)) ? " class='active'": " class='inactive'";
				
					$edit = "<a href='#' onclick=\"edit_tag($tag[tag_id], '" . $this->method . "'); return false;\" id='tag$tag[tag_id]' style='text-decoration: none; color: red; font-weight: bold;' title='Edit Tag'>+</a>&nbsp;";
					
					$yep = "<a href='#' onclick=\"upd_tags($tag[tag_id], '" . $this->method . "'); return false;\" style='text-decoration: none;' title='Use Tag'>" . str_replace('_', '&nbsp;', $tag['tag_name']) . "</a>";
					
					$out .= span($edit . $yep, $class . " id='tag$tag[tag_id]'") . ' ';
				}
				
				$group = $tag['tag_group'];
			}
		}
		
		return $out;
	}
	
	
	// new tag
	public function new_tag()
	{
		$OBJ =& get_instance();
		
		load_module_helper('files', 'exhibits');
		$clean['tag_name'] = trim(str_replace(' ', '_', utf8Urldecode($this->tag)));
		$OBJ->db->insertArray(PX.'tags', $clean);
	}
	
	// update tag
	public function upd_tag()
	{
		$OBJ =& get_instance();
		$clean['tag_name'] = $this->tag;
		$OBJ->db->updateArray(PX.'tags', $clean, "tag_id='" . $this->tag_id . "'");
	}

	// delete tag
	public function del_tag()
	{
		$OBJ =& get_instance();
		$OBJ->db->deleteArray(PX.'tags', "tag_id='" . $this->tag_id . "'");
	}
}