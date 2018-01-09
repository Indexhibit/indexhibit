<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Sub directories class
* 
* @version 1.0
* @author Vaska 
*/
class Subdirs
{
	public $new_title;
	public $new_dir;
	public $new_id;
	public $input_flag = 0;
	public $serialized;
	public $secid;
	public $del_dir;
	public $section = array();
	public $singleSub = array();
	public $id;
	
	public function __construct()
	{

	}
	
	public function get_section_info()
	{
		$OBJ =& get_instance();

		$this->section = $OBJ->db->fetchRecord("SELECT * FROM ".PX."sections WHERE secid='$this->secid'");
	}
	
	public function del_subdir_input()
	{
		$OBJ =& get_instance();
		
		$output['sec_subs'] = ''; $i = 0;
		
		// get the current subdirs
		//$subs = $OBJ->db->fetchRecord("SELECT sec_subs FROM ".PX."sections WHERE secid='$this->secid'");
		//$subs = unserialize($subs['sec_subs']);
		

		$OBJ->db->deleteArray(PX.'subsections', "sub_id='" . $OBJ->subdirs->del_dir . "'");
		
		return;
	}
	
	public function process_subdir_input()
	{
		$OBJ =& get_instance();
		
		// check to see if they already exist
		$this->get_section_info();
		
		$this->new_dir = preg_replace('/\/+/', '/', $this->new_dir);
		
		$tmp = array('sub_folder' => $this->new_dir, 'sub_title' => $this->new_title, 'sub_sec_id' => $this->secid, 'sub_order' => 999);
		$last = $OBJ->db->insertArray(PX.'subsections', $tmp);

		return $last;
	}
	
	public function process_subdir_update()
	{
		$OBJ =& get_instance();

		$subs = $this->section;
		$subs = unserialize($subs['sec_subs']);

		$tmp = array();
		
		foreach ($subs as $sub)
		{
			if ($this->new_id == $sub['subid'])
			{
				$tmp[] = array('dir' => $this->new_dir, 'title' => $this->new_title, 'subid' => $this->new_id);
				$this->singleSub = serialize(array('dir' => $this->new_dir, 'title' => $this->new_title, 'subid' => $this->new_id));
			}
			else
			{
				$tmp[] = $sub;
			}
		}

		$output['sec_subs'] = serialize($tmp);
		$OBJ->db->updateArray(PX.'sections', $output, "secid='$this->secid'");
		
		$OBJ->subdirs->serialized = $output['sec_subs'];
		return;
	}
	
	
	public function getSub($sub=array())
	{
		$OBJ =& get_instance();
		
		$sub = $OBJ->db->fetchRecord("SELECT * FROM ".PX."subsections 
			WHERE sub_id = '" . $OBJ->subdirs->new_id . "' ");
		
		$this->get_section_info();

		if ($sub)
		{
			$html = "<span class='subtitle handle' style='cursor: default;'>$sub[sub_title]</span> " . $this->section['sec_path'] . "/<span class='subdir handle'>$sub[sub_folder]</span></div> <div style='float: right;'><span><a href='#' onclick=\"delete_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Delete</a> <a href='#' onclick=\"edit_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Edit</a></span></div><div style='clear: both;'><!-- --></div>\n";
			
			//$html = "<div><span class='subtitle'>$sub[sub_title]</span> " . $this->section['sec_path'] . "/<span class='subdir handle'>$sub[sub_folder]</span></div> <span><a href='#' onclick=\"delete_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Delete</a> <a href='#' onclick=\"edit_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Edit</a></span>\n";
			
			//$html = "<div><span class='handle' id='n$sub[sub_id]'>Handle</span> <span class='subtitle'>$sub[sub_title]</span> " . $this->section['sec_path'] . "/<span class='subdir'>$sub[sub_folder]</span></div> <span><a href='#' onclick=\"delete_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Delete</a> <a href='#' onclick=\"edit_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Edit</a></span>\n";

			return $html;
		}
	}
	
	
	public function getSubs($id=0)
	{
		$OBJ =& get_instance();
		
		$subs = $OBJ->db->fetchArray("SELECT * FROM ".PX."subsections 
			WHERE sub_sec_id = '$id' 
			ORDER BY sub_order ASC");
		
		$this->secid = $id;
		$this->get_section_info();

		if ($subs)
		{
			$html = "<ul id='subdirs'>\n"; $i = 0;

			foreach ($subs as $key => $sub)
			{
				$pre = ($this->secid == 1) ? '' : $this->section['sec_path'];

				$html .= "<li id='subdir_node_$sub[sub_id]'><div style='float: left;' id='n$sub[sub_id]'><span class='subtitle handle' style='cursor: pointer;'>$sub[sub_title]</span> " . $pre . "/<span class='subdir handle'>$sub[sub_folder]</span></div> <div style='float: right;'><span><a href='#' onclick=\"delete_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Delete</a> <a href='#' onclick=\"edit_subdir(" . $this->secid . ", $sub[sub_id]); return false;\">Edit</a></span></div><div style='clear: both;'><!-- --></div></li>\n";

				$i++;
			}
			
			$html .= "</ul>\n";

			return $html;
		}
	}
	
	public function create_subdir($flag=0)
	{
		$OBJ =& get_instance();
		
		// need to add our javascript parts
		$OBJ->template->add_js('jquery.js');
		$OBJ->template->add_js('subdirs.js');
		$OBJ->template->add_js('ui.core.js');
		$OBJ->template->add_js('ui.sortable.js');
		
		
		$html = "<label>Title</label><input id='sub_title' name='sub_title' type='text' value=\"\" />\n";
		$html .= "<label>folder</label><input id='sub_dir' name='sub_dir' type='text' value=\"\" />\n";
		$html .= "<div class='buttons'><button type='button' class='' onclick=\"add_subdir(" . $OBJ->vars->route['id'] . ", $flag); return false;\">Add</button></div>\n";
		
		return $html;
	}
}