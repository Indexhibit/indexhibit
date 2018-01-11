<?php if (!defined('SITE')) exit('No direct script access allowed');


class Exhibits extends Router
{
	public $publishing = false;
	public $error		= false;
	public $error_msg;
	public $pub_status	= 0;
	public $page_id;
	public $object		= array();
	public $submits 	= array();
	public $abstract;
	
	// hidden option(s)
	public $enable_linker = true;
	public $enable_bg = false;
	
	public function __construct()
	{
		parent::__construct();
		
		// which object are we accessing?
		define('OBJECT', 'exhibits');
		
		$find['obj_ref_type'] = OBJECT;
		$this->object = $this->db->selectArray(PX.'objects_prefs', $find, 'record');
		
		// library of $_POST options
		$this->submits = array('upd_view', 'img_upload', 'publish_x',
			'add_page', 'delete_x', 'publish_page', 'upd_ord', 'upd_img_ord',
			'upd_section', 'upd_cbox', 'upd_delete', 'unpublish_x',
			'del_bg_img', 'bg_img_upload', 'upd_jxs', 'upd_img', 'upd_jxdelimg',
			'upd_jxtext');
	}
	
	public function _submit()
	{
		// from $_POST to method
		$this->posted($this, $this->submits);
	}
	
	
	public function page_index()
	{
		global $go, $default;
		
		$go['page'] = getURI('page', 0, 'digit', 5);

		$this->template->location = $this->lang->word('index');
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('create'),
			"?a=$go[a]&q=create", "rel=\"facebox;width=400;height=350;modal=true\"");
		
		// javascript stuff
		$this->template->add_js('jquery.js');
		$this->template->add_js('ui.core.js');
		$this->template->add_js('ui.sortable.js');
		
		$this->template->add_js('jquery.facebox.js');
		$this->template->add_css('jquery.facebox.css');
		$this->template->onready[] = "jQuery('a[rel*=facebox]').facebox();";
		
		$this->template->module_js[] = 'exhibits.js';
		$this->template->module_js[] = 'edits.js';
		$this->template->onready[] = "fake_sorts();";
		$this->template->onready[] = "index_sort();";
		
		load_module_helper('files', $go['a']);
		
		$script = "var action = '$go[a]';
var ide = '$go[id]';";
		
		$script = "" . $this->template->get_special_js() . "";
		
		$this->template->onready[] = $script; 
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = '';

		$body .= "<div id='mytest'>\n";
		$this->lib_class('organize');
		$body .= $this->organize->order();
		$body .= "</div>\n\n";
		
		$body .= div(p('&nbsp;'),"id='dhtml'");
				
		$this->template->body = $body;
		
		return;
	}
	
	
	public function get_abstracts($id='')
	{
		$abs = $this->db->fetchArray("SELECT * FROM ".PX."abstracts 
			WHERE ab_obj_id = '$id' 
			AND ab_obj = 'exhibit'");
			
		if (!$abs) { $this->abstract = false; return; }
		
		foreach ($abs as $ab)
		{
			$this->abstract[$ab['ab_var']] = $ab['abstract'];
		}
	}
	
	
	public function page_edit()
	{
		global $go, $default;
		
		$this->template->location = $this->lang->word('exhibit');
		
		// any abstracts for this one?
		//$this->abstracts->get_system_abstracts($go['id']);
		//$this->get_abstracts($go['id']);
		
		$site_vars = unserialize($this->access->settings['site_vars']);

		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('index'), "?a=$go[a]");

		$this->template->add_js('jquery.js');
		$this->template->add_js('jquery.ui.core.js');
		$this->template->add_js('jquery.ui.widget.js');
		$this->template->add_js('jquery.ui.mouse.js');
		$this->template->add_js('jquery.ui.sortable.js');
		$this->template->module_js[] = 'exhibits.js';
		$this->template->module_js[] = 'edits.js';
		
		$this->template->module_css[] = 'exhibits.css';
		
		// facebox is our popup method
		$this->template->add_js('jquery.facebox.js');
		$this->template->add_css('jquery.facebox.css');
		$this->template->onready[] = "jQuery('a[rel*=facebox]').facebox();";

			$this->template->ex_css[] = "ul#sizes { list-style-type: none; }
	ul#sizes li { padding: 3px; border-bottom: 1px solid #ccc; }
	.size-hover { background: #666; }";

			$script = "var action = '$go[a]';
var ide = '$go[id]';
var baseurl = '" . BASEURL . "';";


		$this->template->onready[] = "" . $this->template->get_special_js() . "";
		$this->template->ex_js[] = $script;
			
		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections   
			WHERE id = '$go[id]'  
			AND section_id = secid 
			AND object = obj_ref_type");
				
			$this->vars->exhibit = $rs;

			load_module_helper('files', $go['a']);
			load_helpers(array('editortools', 'output'));
			
			$this->rs = $rs;

			// ++++++++++++++++++++++++++++++++++++++++++++++++++++


			$body = ($this->error == TRUE) ?
				div($this->error_msg,"id='show-error'").br() : '';

			$body .= "<div id='tab' style='position: relative;'>\n";

			$body .= "<div class='c5'>\n";

			// left column
			$body .= "<div class='colA'>\n";
			$body .= "<div class='bg-grey corners'>\n";

			$body .= "<div>\n";
			
			$sec_top = ($rs['section_top'] == 1) ? ' sec-top' : '';

			// rewrite this so we can save texts...
			if ($rs['section_top'] == 1)
			{
				$body .= div("<h3><span class='inplace1' onclick=\"edit_sec_title(); return false;\" title=\"Edit\">$rs[sec_desc]</span></h3>", "style='padding: 5px 0 0 5px;'");
				$body .= input('htitle', 'hidden', "id='htitle'", $rs['sec_desc']);
				$body .= input('htitletype', 'hidden', "id='htitletype'", 'section');
			}
			elseif ($rs['subdir'] == 1)
			{
				$body .= div("<h3><span class='inplace1' onclick=\"edit_title(); return false;\" title='Edit' style='cursor: pointer;'>$rs[title]</span></h3>", "style='padding: 5px 0 0 5px;'");
				$body .= input('htitle', 'hidden', "id='htitle'", $rs['title']);
				$body .= input('htitletype', 'hidden', "id='htitletype'", 'subsection');
				$body .= input('hsubsection_id', 'hidden', "id='hsubsection_id'", $rs['section_sub']);
			}
			else
			{
				$body .= div("<h3><span class='inplace1' onclick=\"edit_title(); return false;\" title='Edit' style='cursor: pointer;'>$rs[title]</span></h3>", "style='padding: 5px 0 0 5px;'");
				$body .= input('htitle', 'hidden', "id='htitle'", $rs['title']);
				$body .= input('htitletype', 'hidden', "id='htitletype'", 'exhibit');
			}
			
			$body .= "</div>\n";
			
			// the editor
			$abody = "<div id='layout-a' class='padder'>\n";

			// make a hook for a different editor
			$this->lib_class('editor');
			$this->editor->content = $rs['content'];
			$this->editor->process = $rs['process'];
			$this->editor->content_id = 'content';
			$this->editor->advanced = true;
			$this->editor->css = "style='width: 670px; \width: 658px; w\idth: 658px; padding: 6px;'";
			$abody .= $this->editor->output();
			$abody .= "</div>\n";
			// end editor

			$bbody = "<div id='layout-b' class='padder' style='padding-top: 18px;'>\n";
			
			$display = ($rs['media_source'] == '0') ? "display: inline;" : "display: none;";
			
			$bbody .= "<div id='img-container'>";
			
			// site settings/vars
			$site_vars = unserialize($this->access->settings['site_vars']);
			$this->rs['site_vars'] = $site_vars;
			
			// implement the interface
			$class = 'filesource' . $default['filesource'][$rs['media_source']];
			$F =& load_class($class, true, 'lib');
			$F->rs = $rs;
			
			// get our output
			$bbody .= $F->getExhibitImages($go['id']);
			
			$bbody .= "</div>\n";
			$bbody .= "</div>\n";
			$this->template->onready[] = "apply_sort();";
			
			$body .= ($rs['placement'] != 1) ? $abody . $bbody : $bbody . $abody;

			// end images part

			$body .= "</div>\n";
			$body .= "</div>\n";
			// end left colum

			// right column
			$body .= "<div class='colB'>\n";
			$body .= "<div class='colB-pad'>\n";
			
			// site variables
			$site_vars = unserialize($this->access->settings['site_vars']);
			
			// publishing
			if ($rs['id'] != 1)
			{
				$body .= "<div class='as-holder'>" . label($this->lang->word('publish'));
				$body .= getOnOff($rs['status'], "class='listed' id='ajx-status'") . "</div>\n";
				$this->template->onready[] = "$('#ajx-status li').tabpost();";
				
				// always keep the bar with it
				$body .= "<div style='border-top: 1px dotted #ccc;'>&nbsp;</div>\n\n";
			}
			
			$settings_width = 700;
			
			// the deletor
			$d = (($go['id'] == 1) || ($rs['section_top'] == 1) || ($rs['subdir'] == 1)) ?
				" <input name='preview' type='image' src='asset/img/f-prev.gif' title='Preview' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' onclick=\"previewText($go[id]); return false;\" />" : 
				" <input name='preview' type='image' src='asset/img/f-prev.gif' title='Preview' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' onclick=\"previewText($go[id]); return false;\" /> <input name='delete' type='image' src='asset/img/delete.gif' title='".$this->lang->word('delete')."' onClick=\"javascript:return confirm('".$this->lang->word('are you sure')."');return false;\" class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' />";

			$f = '';
			
			// aditional options
			$body .= "<p class='as-holder' style='margin-bottom: 9px;'><label style='display: block; margin-bottom: 6px;'>Options</label><a href='?a=$go[a]&q=settings&id=$go[id]' rel=\"facebox;width=700;height=500\"><img src='asset/img/page-settings.gif' title='" . $this->lang->word('page options') . "' /></a> <a href='?a=$go[a]&q=isettings&id=$go[id]' rel=\"facebox;width=700;height=500\"><img src='asset/img/page-options.gif' title='" . $this->lang->word('exhibit options') . "' /></a>$d</p>";
			
			$body .= "<div style='border-top: 1px dotted #ccc;'>&nbsp;</div>\n\n";
			
			$uploadings_display = '';

			
			$body .= "<div id='uploadings' class='as-holder' style='margin-bottom: 9px;{$uploadings_display}'><label style='display: block; margin-bottom: 6px;'>" . $this->lang->word('Upload/Import') . "</label>\n";
			
			$body .= " <a href='?a=system&q=upload&id=$go[id]' rel=\"facebox;width=800;height=500;modal=true\"><img src='asset/img/page-upload.gif' title='" . $this->lang->word('upload files') . "' /></a>";
			
			$folders = " <a href='?a=system&q=folder&id=$go[id]' rel=\"facebox;width=600;height=500;modal=true\"><img src='asset/img/files.gif' title='" . $this->lang->word('folder load') . "' /></a> ";
			
			// bring folders back later
			//$body .= $folders . $f;
			$body .= $f;
			
			$body .= $this->hook->do_action('system_uploader_link');
			
			
			/////////////////////
			$body .= "</div>\n";
			
			$body .= "<div style='border-top: 1px dotted #ccc;'>&nbsp;</div>\n\n";
			
			// exhibit tags - maybe we'll bring this back later...
			if ($this->vars->site['tags'] == 1)
			{
				//$body .= "<div style='width:350px; float:left;'>\n";
				// tags
				$this->lib_class('tag');
				$this->tag->method = 'exh';
				$this->tag->id = $rs['id'];
				$this->tag->active_tags = $rs['tags'];

				$body .= "<div>\n";
				$body .= label($this->lang->word('tags') . ' ' . span(href($this->lang->word('add tags'), '#', "onclick=\"toggle('tag-add'); return false;\""))) . "\n";
				$body .= "<div id='tag-add' style='display: none; cursor: pointer;'>\n";
				$body .= input('add_tag', 'text', "id='new_tag' style='display: inline; width: 100px;'", null);
				$body .= "<input type='hidden' name='tag_group' id='tag_group' value=\"1\" /> ";
				$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"add_tags('exh'); return false;\"", $this->lang->word('submit'));
				$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"$('#tag-add').toggle(); return false;\"", $this->lang->word('cancel'));
				$body .= "<p>" . $this->lang->word('Separate multiple tags with a comma') . " ','.</p>\n";
				$body .= "</div>\n";
				//$body .= "</div>\n";

				$body .= "<div id='tag-box' style='display:block; padding:6px 0;'>\n";
				$body .= div($this->tag->get_active_tags2(), "id='tag-holder'");
				$body .= "</div>\n";
			}
			// end tags
			
			// additional options - miscellaneous
			$tmp = $this->hook->do_action('system_additional_link');	
			
			if ($tmp != '')
			{
				$body .= "<div style='border-top: 1px dotted #ccc;'>&nbsp;</div>\n\n";
				$body .= "<div class='as-holder'>\n";
				$body .= "<label>Additional Settings</label>\n";
				$body .= "<div style='margin-top: 6px;'>\n";
				$body .= $tmp;
				$body .= "</div>\n";
				$body .= "</div>\n\n";
			}
			// end additional options
			
			$body .= "</div>\n";
			// end advanced

			// hidden fields
			$body .= input('hord', 'hidden', NULL, $rs['ord']);
			$body .= input('hsection_id', 'hidden', "id='hsection_id'", $rs['section_id']);

			$body .= "</div>\n";
			// end right column

			$body .= "<div class='cl'><!-- --></div>\n";
			$body .= "</div>";

			$body .= "</div>\n";

		$this->template->body = $body;

		return;
	}
	
	
		public function page_settings()
		{
			global $go, $default;

			// move this around later
			$site_vars = unserialize($this->access->settings['site_vars']);

			if ($this->enable_bg == true) $this->template->onready[] = "$('#ajx-tiling li').tabpost();";

			$this->template->ex_css[] = "ul#sizes { list-style-type: none; }
	ul#sizes li { padding: 3px; border-bottom: 1px solid #ccc; }
	.size-hover { background: #fff20d; }";


			$this->template->module_js[] = 'exhibits.js';
			$this->template->module_js[] = 'edits.js';

			$this->template->module_css[] = 'exhibits.css';
			
			$this->template->ex_css[] = 'body { background: transparent; }';

			load_module_helper('files', 'exhibits');

			$this->template->pop_location = $this->lang->word('page options');

			$this->template->pop_links[] = array($this->lang->word('exhibit options'), "?a=$go[a]&q=isettings&id=$go[id]", null);
			
			$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

			$this->template->js[] = "jquery.js";

			$this->template->ex_js[] = "var action = '$go[a]';
	var ide = '$go[id]';";

			// ++++++++++++++++++++++++++++++++++++++
			// get the object
			$rs = $this->db->fetchRecord("SELECT * FROM ".PX."objects, ".PX."sections 
				WHERE id='$go[id]' 
				AND section_id = secid");

			// we need this for a bunch of things
			$bgcolor = ($rs['color'] == '') ? 'ffffff' : $rs['color'];

			$body = "<div style='width: 250px; float: left;'>\n";
			
			$body .= label($this->lang->word('hide exhibit from index')).br();
			$body .= getOnOff($rs['hidden'], "class='listed' id='ajx-hidden'");
			$this->template->onready[] = "$('#ajx-hidden li').tabpost();";

			$body .= label($this->lang->word('homepage')).br();
			$body .= getOnOff($rs['home'], "class='listed' id='ajx-home'");
			$this->template->onready[] = "$('#ajx-home li').tabpost();";
			
			if ($this->vars->site['passwords'] == 1)
			{
				if ($rs['section_top'] == 1)
				{
					// bring back section passwords after we have fixed the bad bug
					//$body .= label($this->lang->word('section password') . ' ' . span($this->lang->word('letters numbers only'))) . br();
					//$body .= input('password', 'text', "id='password' style='width: 100px; display: inline;' maxlength='12'", $rs['sec_pwd']) . input('sbmt_pwd', 'button', "onclick=\"update_sec_pwd(); return false;\"", $this->lang->word('submit')) . br();
				}
				else
				{
					$body .= label($this->lang->word('password') . ' ' . span($this->lang->word('letters numbers only'))) . br();
					$body .= input('password', 'text', "id='password' style='width: 100px; display: inline;' maxlength='12'", $rs['pwd']) . input('sbmt_pwd', 'button', "onclick=\"update_pwd(); return false;\"", $this->lang->word('submit')) . br();
				}
			}

			if ($this->vars->site['templates'] == 1)
			{
				$body .= ips($this->lang->word('template'), 'get_templates', 'template', $rs['template'], "id='ajx-template' style='width: 150px;'");
						$this->template->onready[] = "$('#ajx-template').change( function() { updateTemplate(); } );";
			}

			/////////////////////////////////////////////////////////

			$this->template->js[] = 'jquery.colorpick.js';
			$this->template->css[] = 'jquery.colorpick.css';

			// background color - this is a mess
			$body .= "<div style='margin-bottom: 12px;'>\n";
			$body .= "<label>".$this->lang->word('background color')."</label>\n";
			$body .= "<div id='bgcolor' style='margin-top: 3px;'><div style='background-color: #$bgcolor; width: 15px; height: 15px; border: 1px solid #ccc; cursor: pointer;'></div></div>";

			$this->template->onready[] = "$('#bgcolor').ColorPicker({
	color: '$bgcolor',
	onShow: function (colpkr) {
		$(colpkr).show();
		return false;
	},
	onHide: function (colpkr) {
		$(colpkr).hide();
		return false;
	},
	onSubmit: function (hsb, hex, rgb) {
		// update the color here
		$('#bgcolor div').css('backgroundColor', '#' + hex);
		$('.colorpicker').hide();
		getColor(hex);
		return false;
	}
	});";
			$body .= "</div>\n";

			/////////////////////////////////////////////////////////
			
			if ($rs['bgimg'] == '')
			{
				$body .= label(href($this->lang->word('Upload Background'), "?a=system&q=background&id=$go[id]"));
			}
			else
			{
				$body .= label($this->lang->word('background tiling')).br();
				$body .= getOnOff($rs['tiling'], "class='listed' id='ajx-tiling'");
				$this->template->onready[] = "$('#ajx-tiling li').tabpost();";
				
				$body .= label($this->lang->word('background image')).br();
				$body .= p(href('View', BASEURL . "/files/$rs[bgimg]", "target='_new'") . ' ' . 
					href('Delete', "?a=$go[a]&q=delbg&id=$go[id]&x=$rs[bgimg]"));
			}

			$body .= "</div>\n";

		// exhibit tags, maybe we'll bring this back later
		/*
		if ($this->vars->site['tags'] == 1)
		{
			$body .= "<div style='width: 400px; float: left;'>\n";
			$this->lib_class('tag');
			$this->tag->tags_enabled = $this->access->settings['tagging'];
			$this->tag->active_tags = $rs['tags'];
			$this->tag->id = $rs['id'];
			$this->tag->method = 'exh';

			$body .= "<div>\n";
			$body .= label('tags ' . span(href("add tags", '#', "onclick=\"toggle('tag-add'); return false;\""))) . "\n";
			$body .= "<div id='tag-add' style='display: none; cursor: pointer;'>\n";
			$body .= input('add_tag', 'text', "id='new_tag' style='display: inline; width: 100px;'", null);
			$body .= get_tag_groups(1, 'g', "id='group' style='display: inline; width: 45px;'");
			$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"add_tags('exh'); return false;\"", 'submit');
			$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"$('#tag-add').toggle(); return false;\"", 'cancel');
			$body .= "<p>Separate multiple tags with a comma ','.</p>\n";
			$body .= "</div>\n";
			$body .= "</div>\n";
			
			$grupo = ($rs['sec_group'] > 0) ? $rs['sec_group'] : '';

			$body .= "<div id='tag-box' style='display:block; padding:6px 0;'>\n";
			$body .= div($this->tag->get_active_tags2($grupo), "id='tag-holder'");
			$body .= "</div>\n";
			$body .= "</div>\n";
		}
		*/
		/////////////////////////////////////////////////////////

		$body .= "<div style='clear: left;'><!-- --></div>\n";

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}
	
	public function page_delbg()
	{
		global $go;
		
		load_helper('files');
		
		$this->db->updateRecord("UPDATE ".PX."objects SET bgimg = '' WHERE id = '$go[id]'");
		
		// need to validate x
		delete_image(DIRNAME . BASEFILES . '/' . $_GET['x']);
		
		system_redirect("?a=$go[a]&q=settings&id=$go[id]");
		exit;
	}
			

	public function page_isettings()
	{
		$OBJ =& get_instance();
		global $go, $default;
		
		// move this around later
		$site_vars = unserialize($this->access->settings['site_vars']);

		$this->template->ex_css[] = "ul#sizes { list-style-type: none; }
ul#sizes li { padding: 3px; border-bottom: 1px solid #ccc; }
.size-hover { background: #fff20d; }";

		$this->template->js[] = "jquery.js";
		$this->template->module_js[] = 'exhibits.js';
		$this->template->module_js[] = 'edits.js';
		$this->template->module_css[] = 'exhibits.css';

		load_module_helper('files', $go['a']);

		$this->template->pop_location = $this->lang->word('exhibit options');
		
		$this->template->pop_links[] = array($this->lang->word('page options'), "?a=$go[a]&q=settings&id=$go[id]", null);
		
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';";

		// triggers update of images
		if (isset($_GET['update']))
		{
			$this->template->onready[] = "postUpdatePresent();";
			$this->template->ex_js[] = "parent.update_gallery();";
		}

		// ++++++++++++++++++++++++++++++++++++++
		// get the object
		$rs = $this->db->fetchRecord("SELECT * FROM ".PX."objects WHERE id='$go[id]'");

		// clean this later...
		$OBJ->vars->exhibit = $rs;
		
		//////////////////
		// site settings/vars
		$site_vars = unserialize($this->access->settings['site_vars']);
		$this->rs['site_vars'] = $site_vars;
		
		// implement the interface
		$class = 'filesource' . $default['filesource'][$rs['media_source']];
		$F =& load_class($class, true, 'lib');
		$F->rs = $rs;
		
		// get our output
		$body = div($F->switchInterface(), "id='img-settings'");
		//////////////////		

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}
	
	
	public function page_create()
	{
		$OBJ =& get_instance();
		global $go, $default;
		
		if ($go['x'] == 'exhibit') $this->page_create_exhibit();
		if ($go['x'] == 'link') $this->page_create_link();

		$this->template->js[] = "jquery.js";

		load_module_helper('files', $go['a']);

		$this->template->pop_location = $this->lang->word('create');

		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';";

		$body = '';
		
		$body .= "<p>" . href('Exhibit', "?a=$go[a]&q=create&x=exhibit", null) . "</p>\n";
		$body .= "<p>" . href('Index link', "?a=$go[a]&q=create&x=link", null) . "</p>\n";

		$body .= "<div class='c4' style='display: none;'>\n";
		
		$body .= "<div class='col'>\n";
		$body .= ips($this->lang->word('page title'), 'input', 'title', NULL, "maxlength='50'", 'text', $this->lang->word('required'),'req');
		
		$body .= ips($this->lang->word('section'), 'getSection', 'section_id', NULL, NULL, NULL, $this->lang->word('required'),'req');
		
		$body .= ips($this->lang->word('project year'), 'getYear', 'year', date('Y'), NULL, NULL, NULL, 'req');

		//$body .= "<div id='indelinker'>" . ips($this->lang->word('index link') . ' ' . span('optional http://'), 'input', 'link', null, "maxlength='255'", 'text') . "</div>\n";
		
		$body .= "<div class='buttons'>";
		$body .= button('add_page', 'submit', "class='general_submit'", $this->lang->word('add page'));
		$body .= "</div>\n";
		
		$body .= "</div>\n";	
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>\n\n";

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}
	
	
		public function page_create_exhibit()
		{
			$OBJ =& get_instance();
			global $go, $default;

			$this->template->js[] = "jquery.js";
			$this->template->module_js[] = "edits.js";

			load_module_helper('files', $go['a']);

			$this->template->pop_location = $this->lang->word('create');

			$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

			$this->template->ex_js[] = "var action = '$go[a]';
	var ide = '$go[id]';";

			$body = '';
			$body .= "<div class='c4'>\n";

			$body .= "<div class='col'>\n";
			$body .= ips($this->lang->word('page title'), 'input', 'title', NULL, "id='title' maxlength='50'", 'text', $this->lang->word('required'),'req');

			$body .= ips($this->lang->word('section'), 'getSection', 'section_id', NULL, "id='section_id'", NULL, $this->lang->word('required'),'req');

			$body .= ips($this->lang->word('project year'), 'getYear', 'create_year', date('Y'), null, NULL, NULL, 'req');

			$body .= "<div class='buttons'>";
			$body .= button('add_page', 'submit', "class='general_submit' onclick=\"create_exhibit('exhibit'); return false;\"", $this->lang->word('add page'));
			$body .= "</div>\n";

			$body .= "</div>\n";	
			$body .= "<div class='cl'><!-- --></div>\n";
			$body .= "</div>\n\n";

			$this->template->body = $body;

			$this->template->output('popup');
			exit;
		}
		
		
			public function page_create_link()
			{
				$OBJ =& get_instance();
				global $go, $default;

				$this->template->js[] = "jquery.js";
				$this->template->module_js[] = "edits.js";

				load_module_helper('files', $go['a']);

				$this->template->pop_location = $this->lang->word('create');

				$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

				$this->template->ex_js[] = "var action = '$go[a]';
		var ide = '$go[id]';";

				$body = '';

				$body .= "<div class='c4'>\n";

				$body .= "<div class='col'>\n";
				$body .= ips($this->lang->word('page title'), 'input', 'title', NULL, "id='title' maxlength='50'", 'text', $this->lang->word('required'),'req');

				$body .= "<div id='indelinker'>" . ips($this->lang->word('index link') . ' ' . span('optional http://'), 'input', 'link', null, "id='link' maxlength='255'", 'text') . "</div>\n";
				
				$body .= ips($this->lang->word('section'), 'getSection', 'section_id', NULL, "id='section_id'", NULL, $this->lang->word('required'),'req');
				
				$body .= "<input type='hidden' name='year' id='year' value='" . date('Y') . "' />\n";

				$body .= "<div class='buttons'>";
				$body .= button('add_page', 'submit', "class='general_submit' onclick=\"create_exhibit('link'); return false;\"", $this->lang->word('add page'));
				$body .= "</div>\n";

				$body .= "</div>\n";	
				$body .= "<div class='cl'><!-- --></div>\n";
				$body .= "</div>\n\n";

				$this->template->body = $body;

				$this->template->output('popup');
				exit;
			}
	
	
		public function page_link()
		{
			global $go, $default;

			$this->template->location = $this->lang->word('exhibit');

			$site_vars = unserialize($this->access->settings['site_vars']);

			// any abstracts for this one?
			$this->get_abstracts($go['id']);

			// sub-locations
			$this->template->sub_location[] = array($this->lang->word('index'), "?a=$go[a]");

			$this->template->add_js('jquery.js');
			$this->template->add_js('ui.core.js');
			$this->template->add_js('ui.sortable.js');
			$this->template->module_js[] = 'exhibits.js';
			$this->template->module_js[] = 'edits.js';

			$this->template->module_css[] = 'exhibits.css';

			$this->template->add_js('jquery.facebox.js');
			$this->template->add_css('jquery.facebox.css');
			$this->template->onready[] = "jQuery('a[rel*=facebox]').facebox();";

			// testing
			$this->template->ex_css[] = "ul#sizes { list-style-type: none; }
ul#sizes li { padding: 3px; border-bottom: 1px solid #ccc; }
.size-hover { background: #666; }";

				$script = "var action = '$go[a]';
		var ide = '$go[id]';";


			$this->template->onready[] = "" . $this->template->get_special_js() . "";
			$this->template->ex_js[] = $script;

				// the record
				$rs = $this->db->fetchRecord("SELECT * 
					FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections   
					WHERE id = '$go[id]' 
					AND (object = '".OBJECT."' OR object = 'section') 
					AND section_id = secid 
					AND object = obj_ref_type");

				load_module_helper('files', $go['a']);
				load_helpers(array('editortools', 'output'));

				$this->rs = $rs;

				// ++++++++++++++++++++++++++++++++++++++++++++++++++++


				$body = ($this->error == TRUE) ?
					div($this->error_msg,"id='show-error'").br() : '';

				$body .= "<div id='tab' style='position: relative;'>\n";

				$body .= "<div class='c5'>\n";

				// left column
				$body .= "<div class='colA'>\n";
				$body .= "<div class='bg-grey corners' style='min-height: 400px;'>\n";

				$body .= "<div>\n";

				$sec_top = ($rs['section_top'] == 1) ? ' sec-top' : '';

				// rewrite this so we can save texts...
				if ($rs['section_top'] == 1)
				{
					$body .= div("<h3><span class='inplace1 sec-title{$sec_top}' onclick=\"edit_sec_title(); return false;\">$rs[sec_desc]</span></h3>", "style='padding: 5px 0 0 5px;'");
					$body .= input('htitle', 'hidden', "id='htitle'", $rs['sec_desc']);
				}
				else
				{
					$body .= "<div style='float: left; width: 90%;'>\n";
					
					$body .= div("<h3><span class='inplace1' onclick=\"edit_title(); return false;\" title='Edit' style='cursor: pointer;'>$rs[title]</span></h3>", "style='padding: 5px 0 0 5px;'");
					$body .= input('htitle', 'hidden', "id='htitle'", $rs['title']);
					$body .= input('htitletype', 'hidden', "id='htitletype'", 'exhibit');
					
					$body .= "</div>\n";
					$body .= "<div style='float: right; width: 10%; text-align: right;'>\n";
					$body .= "<input name='delete' type='image' src='asset/img/delete.gif' title='".$this->lang->word('delete')."' onClick=\"javascript:return confirm('".$this->lang->word('are you sure')."');return false;\" class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' />";
					$body .= "</div>\n";
					$body .= "<div style='clear: both;'><!-- --></div>\n";
				}

				$body .= "<div class='col' style='margin-top: 18px;'>\n";

				$body .= label($this->lang->word('link')) . br();
				$body .= "<h3 id='linkr' style='font-size: 18px;'><span class='inplace2' onclick=\"edit_link(); return false;\" style='font-weight: normal;'>$rs[link]</span></h3>";
				$body .= input('hlink', 'hidden', "id='hlink'", $rs['link']);

				$body .= "</div>\n";
				$body .= "<div class='cl'><!-- --></div>\n";
				$body .= "</div>\n";

				$bbody = "<div id='layout-b' class='padder' style='padding-top: 18px;'>\n";

				$display = ($rs['media_source'] == '0') ? "display: inline;" : "display: none;";

				$bbody .= "<p>" . $this->lang->word('Only the first image is used on site.') . "</p>\n";
				$bbody .= "<div id='img-container'>";

				// site settings/vars
				$site_vars = unserialize($this->access->settings['site_vars']);
				$this->rs['site_vars'] = $site_vars;

				// implement the interface
				$class = 'filesource' . $default['filesource'][$rs['media_source']];
				$F =& load_class($class, true, 'lib');
				$F->rs = $rs;

				// get our output
				$bbody .= $F->getExhibitImages($go['id']);

				$bbody .= "</div>\n";
				$bbody .= "</div>\n";
				$this->template->onready[] = "apply_sort();";

				$body .= $bbody;

				// end images part
				$body .= "<div id='iframe' style='background: #fff; position: absolute; top: 1px; left: 0; width: 475px; height: 300px; display: none;'><iframe src='?a=system&q=swf' frameborder='0' scrolling='auto' width='475' height='300'></iframe></div>\n";

				$body .= "</div>\n";
				$body .= "</div>\n";
				// end left colum

				// right column
				$body .= "<div class='colB'>\n";
				$body .= "<div class='colB-pad'>\n";

				// move this around later
				$site_vars = unserialize($this->access->settings['site_vars']);

				// publishing
				if ($rs['section_top'] == 1)
				{
					$body .= div(label($this->lang->word('published') . $url), "style='margin-bottom: 24px;'");
				}
				else
				{
					$body .= "<div class='as-holder'>" . label($this->lang->word('publish'));
					$body .= getOnOff($rs['status'], "class='listed' id='ajx-status'") . "</div>\n";
					$this->template->onready[] = "$('#ajx-status li').tabpost();";
				}

				$body .= "<div style='border-top: 1px dotted #ccc;'>&nbsp;</div>\n\n";
				
				$body .= label($this->lang->word('use iframe')) . br();	
				$body .= getOnOff($rs['iframe'], "class='listed' id='ajx-iframe'");
				$this->template->onready[] = "$('#ajx-iframe li').tabpost();";

				$body .= label($this->lang->word('open in new window')) . br();	
				$body .= getOnOff($rs['target'], "class='listed' id='ajx-target'");
				$this->template->onready[] = "$('#ajx-target li').tabpost();";
				
				$body .= "<div style='border-top: 1px dotted #ccc;'>&nbsp;</div>\n\n";

				$settings_width = 700;

				// the deletor
				if ($go['id'] != 1)
				{
				$d = ($rs['section_top'] != 1) ?
					" <input name='delete' type='image' src='asset/img/delete.gif' title='".$this->lang->word('delete')."' onClick=\"javascript:return confirm('".$this->lang->word('are you sure')."');return false;\" class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' />" : " <input name='preview' type='image' src='asset/img/f-prev.gif' title='Preview (without saving)' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' onclick=\"previewText($go[id]); return false;\" /> ";
				}
				else
				{
					$d = " <input name='preview' type='image' src='asset/img/f-prev.gif' title='Preview (without saving)' class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom: 0;' onclick=\"previewText($go[id]); return false;\" />";
				}
				
				
				$uploadings_display = '';

				$body .= "<div id='uploadings' class='as-holder' style='margin-bottom: 9px;{$uploadings_display}'><label style='display: block; margin-bottom: 6px;'>" . $this->lang->word('Upload/Import') . "</label>\n";


				$body .= "<a href='?a=system&q=upload&id=$go[id]' rel=\"facebox;height=400;width=500;modal=true\" id='img-upldr'><img src='asset/img/page-upload.gif' title='" . $this->lang->word('upload files') . "' /></a>";

				$body .= "</div>\n";
				
				//////////////////////////////////////////////

				$body .= "</div>\n";	

				// end advanced

				// hidden fields
				$body .= input('hord', 'hidden', NULL, $rs['ord']);
				$body .= input('hsection_id', 'hidden', "id='hsection_id'", $rs['section_id']);

				$body .= "</div>\n";
				// end right column

				$body .= "<div class='cl'><!-- --></div>\n";
				$body .= "</div>";

				$body .= "</div>\n";

			$this->template->body = $body;

			return;
		}

	
	
	public function page_view()
	{
		global $go;

		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."media 
			WHERE media_id = '$go[id]' 
			AND media_obj_type = '".OBJECT."'");
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = "<div style='width:125px; float:left;'><img src='" . BASEURL . GIMGS . "/th-$rs[media_file]' width='100' /><br /><br /><a href='" . BASEURL . GIMGS . "/$rs[media_file]' target='_new'>" . $this->lang->word('view full size') . "</a></div>\n";

		$body .= "<div style='width:495px; float:left;'>\n";
		$body .= ips($this->lang->word('image title'), 'input', 'media_title', 
			$rs['media_title'], "id='media_title' maxlength='255'", 'text');
		$body .= ips($this->lang->word('image caption'), 'input', 'media_caption', 
			$rs['media_caption'], "id='media_caption' maxlength='255'", 'text');
			
		// buttons
		$body .= "<input type='button' value='" . $this->lang->word('cancel') . "' onclick=\"getExhibit(); return false;\" />\n";
		$body .= "<input type='button' value='" . $this->lang->word('delete') . "' onclick=\"deleteImage('$rs[media_file]'); return false;\" />\n";
		$body .= "<input type='button' value='" . $this->lang->word('update') . "' onclick=\"updateImage($rs[media_id]); return false;\" />\n";
		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";

		header ('Content-type: text/html; charset=utf-8');
		echo $body;
		exit;
	}
	
	
	public function paginate_images($current=0)
	{
		global $go;
		
		// the record
		$rs = $this->db->fetchArray("SELECT media_id,   
			FROM ".PX."media 
			WHERE media_ref_id = '$go[id]' 
			AND (media_ord)
			ORDER BY media_order ASC");
		
		$previous = 0;
		$next = 0;
		
		foreach ($rs as $do)
		{
			
		}
	}
	
		
	
	public function page_prv()
	{
		$OBJ =& get_instance();
		global $go, $default;
		
		$this->template->ex_css[] = "ul#sizes { list-style-type: none; }
ul#sizes li { padding: 3px; border-bottom: 1px solid #ccc; }
.size-hover { background: #fff20d; }";

		$this->template->js[] = "jquery.js";
		$this->template->module_js[] = 'exhibits.js';
		$this->template->module_js[] = 'edits.js';
		$this->template->module_css[] = 'exhibits.css';

$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';";

		$this->template->onready[] = "$('#img-settings').mouseup(function(){ setTimeout('refresher()', 3000); });";
		$this->template->ex_js[] = "function refresher(){ $('a#refresh').click(); }";

		load_module_helper('files', $go['a']);

		$body = "<div id='pvw' style='background: #fff20d;'>\n";

		$body .= "<div style='padding: 18px 18px 9px 18px;'>\n";
		$body .= "<label style='display: block; margin-bottom: 6px;'>" . $this->lang->word('preview') . "</label>\n";

		$body .= href('&larr; ' . $this->lang->word('edit'), "?a=$go[a]&amp;q=edit&amp;id=$go[id]", "id='edit'");
		$body .= ' ';
		$body .= href($this->lang->word('refinements'), "#", "onclick=\"$('#edit-options').toggle(); return false;\"");

		$body .= ' ';
		
		$body .= href($this->lang->word('refresh'), "#", "id='refresh' onclick=\"window.frames[0].location.reload();\"");

		$body .= "<br /><br />";
		
		$body .= "<div id='edit-options' style='display: none;'>\n";
		//////////////////////////////////
		$rs = $this->db->fetchRecord("SELECT * FROM ".PX."objects WHERE id='$go[id]'");

		// clean this later...
		$OBJ->vars->exhibit = $rs;
		
		//////////////////
		// site settings/vars
		$site_vars = unserialize($this->access->settings['site_vars']);
		$this->rs['site_vars'] = $site_vars;
		
		// implement the interface
		$class = 'filesource' . $default['filesource'][$rs['media_source']];
		$F =& load_class($class, true, 'lib');
		$F->rs = $rs;
		
		// get our output
		$body .= div($F->editOptions(), "id='img-settings'");
		
		// show custom options if they exist
		//if ($OBJ->vars->exhibit['custom_options_flag'] == true)
		//{
			//$body .= "<p><a href=''>Global Format Options</a></p>";
		//}
		///////////////////////////////////
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";
		
		$body .= "<iframe id='theiframe' src='?a=system&amp;q=prv&amp;id=$go[id]' frameborder='0' width='100%' height='100%' style='top: 0; left: 0; bottom: 0; right: 0; position: fixed; z-index: 1;'></iframe>\n";
		
		$this->template->body = $body;
		
		$this->template->output('prv');
		exit;
	}
	


	public function page_jximg()
	{
		global $go, $default;
		load_module_helper('files', $go['a']);
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections   
			WHERE id = '$go[id]'  
			AND section_id = secid 
			AND object = obj_ref_type");
		
		// site settings/vars
		$site_vars = unserialize($this->access->settings['site_vars']);
		$this->rs['site_vars'] = $site_vars;
		
		// implement the interface
		$class = 'filesource' . $default['filesource'][$rs['media_source']];
		$F =& load_class($class, true, 'lib');
		$F->rs = $rs;
		
		header ('Content-type: text/html; charset=utf-8');
		echo $F->getExhibitImages($go['id']);
		exit;
	}
	
	
	public function page_jxload()
	{
		global $go, $default;
		
		load_module_helper('files', $go['a']);
		
		if (isset($_POST['jxload']))
		{
			// perform the upload
			$this->sbmt_img_upload();
			
			$more = "parent.updateImages();";
		}
		
		$more = (!isset($more)) ? '' : $more;
		
		$this->template->add_js('jquery.js');
		$this->template->add_js('jquery.multifile.js');

		$this->template->ex_css[] = "#uploader input { font-size: 9px; }
		#files_list div, #files_list input { margin: 0 0 1px 0; padding: 0; }";

		$this->template->ex_js[] = $more;
		
		$body = "<div style='text-align:left;' id='uploader'>\n";
		$body .= "<form enctype='multipart/form-data' action='?a=$go[a]&q=jxload&id=$go[id]' method='post'>\n";
		
		$body .= "<div style='float:left; width:200px;'>\n";
		$body .= "<input id='my_file_element' type='file' name='filename[]' >\n";
		$body .= "<input type='submit' name='jxload' value='" . $this->lang->word('upload') . "'>\n";
		$body .= "</form>\n";
		$body .= p('<strong>' . $this->lang->word('filetypes') . ':</strong> ' . $this->lang->word('allowed formats') . br() . '<strong>' . $this->lang->word('max file size') . ':</strong> ' . getLimit(), "class='red'");
		$body .= "</div>\n";
		
		$body .= "<div style='float:left; width:400px; text-align:right;'>\n";
		$body .= "<div id='files_list'></div>\n";
		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>\n";
		
		$body .= "<script>\n";
		// this tells us how many we can upload at a time
		$body .= "var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), " . $default['exhibit_imgs'] . " );\n";
		$body .= "multi_selector.addElement( document.getElementById( 'my_file_element' ) );\n";
		$body .= "</script>\n";
		
		$this->template->body = $body;
		
		$this->template->output('iframe');
		exit;
	}
	

	public function page_jxbg()
	{
		global $go;
		
		if (isset($_POST['upload']))
		{
			if (isset($_POST['deletion']))
			{
				load_module_helper('files', $go['a']);
				$clean['bgimg'] = '';
				$this->db->updateArray(PX.'objects', $clean, "id='$go[id]'");
				
				$filename = $_POST['filename'];

				// we need to delete the picture too...
				if (file_exists(DIRNAME . '/files/' . $filename))
				{
					unlink(DIRNAME . '/files/' . $filename);
				}
			}
			else
			{
				// perform the upload
				$this->sbmt_bg_img_upload();
			}
		}

		
		$this->template->add_js('jquery.js');

		$script = "<style type='text/css'>
		body { text-align: left; }
		</style>
		
		<script type='text/javascript'>
		$(document).ready(function()
		{
			$('#iform').change( function() { 
				$('#iform')[0].submit();
				parent.updating(\"" . $this->lang->word('updating') . "\");
			});

			$('#iform #delete').click( function() { 
				$('#iform')[0].submit();
				parent.updating(\"" . $this->lang->word('updating') . "\");
			});
		});
		</script>";

		$this->template->add_script = $script;
		
		// the record
		$rs = $this->db->fetchRecord("SELECT bgimg  
			FROM ".PX."objects   
			WHERE id = '$go[id]'");
			
		if ($rs['bgimg'] != '')
		{
			$body = "<form action='?a=$go[a]&q=jxbg&id=$go[id]' method='post' name='iform' id='iform'>\n";		
			$body .= "<div>\n";
			$body .= "<a href='" . BASEURL . BASEFILES . "/$rs[bgimg]' target='_new'><img src='" . BASEURL . BASEFILES . "/$rs[bgimg]' width='25' style='padding-top:2px;' valign='center' border='0' /></a>";
			$body .= " <input type='button' name='delete' id='delete' value='" . $this->lang->word('delete') . "' style='padding-top:0;' />\n";
			$body .= "<input type='hidden' name='upload' value='1' />\n";
			$body .= "<input type='hidden' name='deletion' value='1' />\n";
			$body .= "<input type='hidden' name='filename' value='$rs[bgimg]' />\n";
			$body .= "</div>\n";
			$body .= "</form>\n";
		}
		else
		{
			$body = "<form enctype='multipart/form-data' action='?a=$go[a]&q=jxbg&id=$go[id]' method='post' name='iform' id='iform'>\n";		
			$body .= "<div>\n";
			$body .= "<input type='file' id='jxbg' name='jxbg' />\n";
			$body .= "<input type='hidden' name='upload' value='1' />\n";
			$body .= "</div>\n";
			$body .= "</form>\n";
		}	
		
		$this->template->body = $body;
		
		$this->template->output('iframe');
		exit;
	}
	
	
	public function publisher()
	{
		($this->pub_status == 1) ? $this->sbmt_publish_x() : $this->sbmt_unpublish_x();
	}

	
	// we need a way to protect these page from outside access
	public function sbmt_add_page()
	{
		$OBJ->template->errors = TRUE;
		global $go;
		
		// can we do this better?
		$processor =& load_class('processor', TRUE, 'lib');
	
		$clean['title'] = $processor->process('title', array('notags', 'reqNotEmpty'));
		$clean['section_id'] = $processor->process('section_id', array('notags', 'reqNotEmpty'));
		$clean['year'] = $processor->process('year', array('notags' ,'reqNotEmpty'));
		$clean['link'] = $processor->process('link', array('notags'));
		
		if ($clean['link'] != '') 
		{
			$clean['content'] = "<plugin:ndxz_iframed {{link}} />";
			$clean['process'] = "0";
		}

		if ($processor->check_errors())
		{
			// get our error messages
			$error_msg = $processor->get_errors();
			$this->errors = TRUE;
			$GLOBALS['error_msg'] = $error_msg;
			$this->template->special_js = "toggle('add-page');";
			return;
		}
		else
		{
			// we need to deal with the order of things...
			$this->db->updateRecord("UPDATE ".PX."objects SET
				ord 		= ord + 1 
				WHERE 
				section_id	= ".$this->db->escape($clean['section_id'])." 
				AND section_top != '1'");
			
			// a few more things
			$clean['udate'] 	= getNow();
			$clean['object'] 	= OBJECT;
			$clean['ord']		= 1;
			$clean['creator']	= $this->access->prefs['ID'];
			
			$last = $this->db->insertArray(PX.'objects', $clean);
			
			($clean['link'] == '') ? system_redirect("?a=$go[a]&q=edit&id=$last") : 
				system_redirect("?a=$go[a]&q=link&id=$last");
		}
		
		return;
	}
	
	
	public function sbmt_publish_x()
	{
		global $default;
		
		$this->publishing = TRUE;
		
		//AND object = '".OBJECT."'
		
		// get record
		$rs = $this->db->fetchRecord("SELECT id, title, secid, sec_path, status, section_top, section_sub, subdir       
			FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections 
			WHERE id = '".$this->page_id."'  
			AND obj_ref_type = object 
			AND section_id = secid");
			
		// is it a sub directory?
		//$subdir_flag = ($rs['subdir'] == 1) ? true : false;
		
		// not again
		if ($rs['status'] == 1) return;
		
		if ($rs['section_top'] != 1)
		{	
			load_helper('output');
			load_helper('romanize');
			$URL =& load_class('publish', TRUE, 'lib');
			
			// make the url
			$URL->title = strip_tags($rs['title']); // we should remove other stuff too
			$path = $rs['sec_path'];
			
			// if subdir we need more information
			if ($rs['section_sub'] != 0)
			{
				$sub = $this->db->fetchRecord("SELECT sub_folder       
					FROM ".PX."subsections 
					WHERE sub_id = '" . $rs['section_sub'] . "'");

				if ($rs['subdir'] == 1)
				{
					// it's a sub directory top
					$URL->title = strip_tags($sub['sub_folder']);
					$path = $rs['sec_path'] . '/';
					$URL->okslash = true;
				}
				else
				{
					// it's a sub directory page
					$path = $rs['sec_path'] . '/' . $sub['sub_folder'] . '/';
				}
			}

			// make the url
			$URL->section = $path;
			$check_url = $URL->makeURL();
		
			// check for dupe
			$check = $this->db->fetchArray("SELECT id 
				FROM ".PX."objects 
				WHERE url = '$check_url' 
				AND id != '$rs[id]'");
			
			// if dupe alert
			if ($check)
			{
				// let's just append things
				$previous = count($check);
				$previous = $previous + 1 . '/';
			}
			else
			{
				$previous = '';
			}
		
			$clean['url'] 		= $check_url . $previous;

			// need to update table
			$clean['url'] 		= $clean['url'];
		}
		
		$clean['status'] 	= 1;
		$clean['udate'] 	= getNow();
		$clean['pdate'] 	= getNow();

		$this->db->updateArray(PX.'objects', $clean, "id='".$this->page_id."'");
	}
	
	
	public function sbmt_unpublish_x()
	{
		// get record
		$rs = $this->db->fetchRecord("SELECT section_top, subdir      
			FROM ".PX."objects  
			WHERE id = '".$this->page_id."'");

		// need to update table
		$clean['status'] 	= 0;
		$clean['udate'] 	= getNow();
		$clean['pdate'] 	= '0000-00-00 00:00:00';
		
		// we don't adjust urls for section tops and subdirs
		if (($rs['section_top'] == 1) || ($rs['subdir'] == 1)) { } else { $clean['url'] = ''; }

		$this->db->updateArray(PX.'objects', $clean, "id='".$this->page_id."'");
	}
	
	
	public function sbmt_delete_x()
	{
		global $go;
		
		if ($go['id'] == 1) 
		{
			system_redirect("?a=$go[a]"); // this can not be deleted
			exit;
		}
		
		$processor =& load_class('processor', TRUE, 'lib');
	
		$clean['hsection_id'] = $processor->process('hsection_id',array('notags','digit'));
		$clean['hord'] = $processor->process('hord',array('notags','digit'));
		
		$this->db->deleteArray(PX.'objects', "id='$go[id]'");
		
		// we need to deal with the order of things...
		$this->db->updateRecord("UPDATE ".PX."objects SET
			ord 		= ord - 1 
			WHERE 
			section_id	= ".$this->db->escape($clean['hsection_id'])." 
			AND ord		>= ".$this->db->escape($clean['hord'])."");
			
		// we also need to delete all of it's files
		$files = $this->db->fetchArray("SELECT media_mime, media_file, media_ref_id, media_thumb, media_dir 
			FROM ".PX."media 
			WHERE media_ref_id = '$go[id]'");
		
		global $default;
	
		$allowed = array_merge($default['images'], $default['media'], $default['sound'], $default['files'], $default['flash']);
			
		if ($files)
		{
			foreach ($files as $file)
			{
				if (in_array($file['media_mime'], $allowed))
				{
				// if cover art
				if ($file['media_thumb'] != '')
				{
					$file = $file['media_thumb'];
					$id = $go['id'];
					//$thumb = $file['media_thumb'];

					$this->delete_image(DIRNAME . GIMGS . '/' . $file); // source image
					$this->delete_image(DIRNAME . GIMGS . '/' . $id . '_' . $file); // image
					$this->delete_image(DIRNAME . GIMGS . '/th-' . $id . '_' . $file); // thumbnail
					$this->delete_image(DIRNAME . GIMGS . '/sys-' . $file); // system thumbnail
					$this->delete_image(DIRNAME . GIMGS . '/systh-' . $file); // system thumbnail
				}
				
				if (in_array($file['media_mime'], $default['images']))
				{
					if ($file['media_dir'] == '')
					{
						$file = $file['media_file'];
						$id = $go['id'];
						//$thumb = $file['media_thumb'];

						$this->delete_image(DIRNAME . GIMGS . '/' . $file); // source image
						$this->delete_image(DIRNAME . GIMGS . '/' . $id . '_' . $file); // image
						$this->delete_image(DIRNAME . GIMGS . '/th-' . $id . '_' . $file); // thumbnail
						$this->delete_image(DIRNAME . GIMGS . '/sys-' . $file); // system thumbnail
						$this->delete_image(DIRNAME . GIMGS . '/systh-' . $file); // system thumbnail
					}
				}
				else
				{
					if ($file['media_dir'] == '')
					{
						$file = $file['media_file'];

						$this->delete_image(DIRNAME . '/files/' . $file); // source image
					}
				}
				}
			}
			
			// delete all records for files
			$this->db->deleteArray(PX.'media', "media_ref_id='$go[id]'");
		}
		
		system_redirect("?a=$go[a]");	
		exit;
	}
	
	
	public function sbmt_upd_delete()
	{
		global $go;
		
		$file = $this->db->fetchRecord("SELECT media_id,media_ref_id,media_file 
			FROM ".PX."media 
			WHERE media_id='$go[id]'");
		
		if ($file)
		{
			if (file_exists(DIRNAME . GIMGS . '/' . $file['media_file']))
			{
				unlink(DIRNAME . GIMGS . '/' . $file['media_file']);
				$this->db->deleteArray(PX.'media', "media_id='$file[media_id]'");
			}
		}
		
		system_redirect("?a=$go[a]&q=edit&id=$file[media_ref_id]");
		exit;	
	}
	
	
	public function sbmt_upd_view()
	{
		global $go;
		
		$processor =& load_class('processor', TRUE, 'lib');
	
		$clean['media_title'] = $processor->process('media_title', array('nophp'));
		$clean['media_caption'] = $processor->process('media_caption', array('nophp'));


		if ($processor->check_errors())
		{
			// get our error messages
			$error_msg = $processor->get_errors();
			$this->errors = TRUE;
			$GLOBALS['error_msg'] = $error_msg;
			return;
		}
		else
		{
			$clean['media_udate'] = getNow();

			$this->db->updateArray(PX.'media', $clean, "media_id='$go[id]'"); 
			
			system_redirect("?a=$go[a]&q=view&id=$go[id]");
			exit;
		}
	}
	
	
	// ordering pages
	public function sbmt_upd_ord()
	{
		$vars = explode('.', $_POST['order']);

		foreach ($vars as $next)
		{
			$var[] = explode('=', $next);
		}

		foreach ($var as $out)
		{
			// this gives us the section id (and year or subsection)
			// looking for subsection
			if (preg_match("/-sub-/", $out[0]))
			{
				$out[0] = str_replace(array('sort', 'sub-'), array('', ''), $out[0]);
				$out[1] = preg_replace('/[^[:digit:]]/', '', $out[1]);
				if (($out[0] != '') && ($out[1] != '')) $blah['sub_' . $out[0]][] = $out[1];
			}
			// looking for the year
			elseif (preg_match("/-yr-/", $out[0]))
			{
				$out[0] = str_replace(array('sort', 'yr-'), array('', ''), $out[0]);
				$out[1] = preg_replace('/[^[:digit:]]/', '', $out[1]);
				if (($out[0] != '') && ($out[1] != '')) $blah['yr_' . $out[0]][] = $out[1];
			}
			else
			{
				$out[0] = preg_replace('/[^[:digit:]]/', '', $out[0]);
				$out[1] = preg_replace('/[^[:digit:]]/', '', $out[1]);
				if (($out[0] != '') && ($out[1] != '')) $blah[$out[0]][] = $out[1];
			}
		}
		
		foreach ($blah as $key => $do)
		{
			$i = 1;
			foreach ($do as $it)
			{
				// if it's a sub
				if (preg_match("/^sub_/", $key))
				{
					$tmp = str_replace('sub_', '', $key);
					$tmparr = explode('-', $tmp);
					
					$year = '';
					$section_id = $tmparr[0];
					$section_sub = "section_sub = '" . $tmparr[1] . "',";
				}
				// it's a year
				elseif (preg_match("/^yr_/", $key))
				{
					// get the year - it's at the end
					$yeara = substr($key, -4);
					$year = "year = ".$this->db->escape($yeara).",";
					
					// get the section_id...everything but the year
					$tmp = str_replace('yr_', '', $key);
					$tmparr = explode('-', $tmp);
					$section_id = $tmparr[0];
					//$section_id = preg_replace("/$yeara$/", '', $key);
					$section_sub = "section_sub = '',";
				}
				else
				{
					// no year
					$year = '';
					$section_sub = "section_sub = '',";
					
					// need the section id
					$section_id = $key;
				}
				
				//we only sort pages...not section tops
				$this->db->updateRecord("UPDATE ".PX."objects SET
					ord 		= ".$this->db->escape($i).",
					$section_sub 
					$year 
					section_id 	= ".$this->db->escape($section_id)."
					WHERE 
					id			= ".$this->db->escape($it)." 
					AND			section_top = '0'");
				
			$i++;
			}
		}
		
		// make this better later
		header ('Content-type: text/html; charset=utf-8');
		echo $this->lang->word('updated');
		exit;
	}
	
	
	public function sbmt_upd_section()
	{
		if ($_POST['update_value'] == '') { echo 'Error'; exit; }
		
		$clean['sec_desc'] = $_POST['update_value'];
		$clean['secid'] = str_replace('s', '', $_POST['element_id']);
		
		$this->db->updateArray(PX.'sections', $clean, "secid=$clean[secid]");
		
		// back to our page
		header ('Content-type: text/html; charset=utf-8');
		echo $clean['sec_desc'];
		exit;
	}
	

	
	public function sbmt_bg_img_upload()
	{
		global $go, $default;
		$dir = DIRNAME . BASEFILES . '/';
		$types = $default['images'];
		
		$IMG =& load_class('media', TRUE, 'lib');
		
		$thetype = explode('.', strtolower($_FILES['jxbg']['name']));
		$thetype = array_pop($thetype);
		
		$name = $go['id'] . '_background' . '.' . $thetype;
		
		if (in_array($thetype, $types))
		{
			if ($_FILES['jxbg']['size'] < $IMG->upload_max_size)
			{
				// if uploaded we can work with it
				if (move_uploaded_file($_FILES['jxbg']['tmp_name'], $dir . '/' . $name)) 
				{
					$clean['bgimg'] 	= $name;

					$this->db->updateArray(PX.'objects', $clean, "id='$go[id]'");
					@chmod($dir . '/' . $name, 0755);
					return;
				}
				else
				{
					// error on upload
				}
			}
			else
			{
				// too big
			}
		}
	}
	
	
	public function sbmt_upd_img_ord()
	{
		// make this more safe
		$vars = explode('.', $_POST['order']);

		foreach ($vars as $out)
		{
			$out = preg_replace('/[^[:digit:]]/', '', $out);
			$order[] = $out;
		}
		
		if (is_array($order))
		{
			// we no longer need this?
			//array_pop($order);

			$i = 1;
			foreach ($order as $do)
			{
				$this->db->updateRecord("UPDATE ".PX."media SET
					media_order 	= ".$this->db->escape($i)." 
					WHERE 
					media_id		= ".$this->db->escape($do)."");
				$i++;
			}
		}
		
		// make this better later
		header ('Content-type: text/html; charset=utf-8');
		echo $this->lang->word('updated');
		exit;
	}
	
	
	// only images, nothing fancy here...
	public function sbmt_img_upload()
	{
		global $go, $default;
		
		$OBJ->template->errors = TRUE;
		
		load_module_helper('files', $go['a']);
		$IMG =& load_class('media', TRUE, 'lib');
		
		// we'll query for all our defaults first...
		$rs = $this->db->fetchRecord("SELECT thumbs, images  
			FROM ".PX."objects    
			WHERE id = '$go[id]' 
			AND object = '".OBJECT."'");
			
			
		// we need to get these from some defaults someplace
		$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
		$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
		$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
		$IMG->quality = $default['img_quality'];
		$IMG->makethumb	= TRUE;
		$IMG->path = DIRNAME . GIMGS . '/';

		load_helper('output');
		$URL =& load_class('publish', TRUE, 'lib');
			
		// +++++++++++++++++++++++++++++++++++++++++++++++++++
		
		// oh so messy
		// our input array is a mess - clean out empty elements
		$_FILES['filename']['name'] = array_diff($_FILES['filename']['name'], array(""));
		$_FILES['filename']['tmp_name'] = array_diff($_FILES['filename']['tmp_name'], array(""));
		$_FILES['filename']['size'] = array_diff($_FILES['filename']['size'], array(""));
		
		// rewrite arrays
		foreach ($_FILES['filename']['tmp_name'] as $key => $file)
		{
			$new_images[] = array('temp'=>$file, 'name'=>$_FILES['filename']['name'][$key],
				'size'=>$_FILES['filename']['size'][$key]);
		}
		
		if (empty($new_images)) return;
		
		// reverse the array
		rsort($new_images);


		$x = 0;
		$added_x = array();
		
		foreach ($new_images as $key => $image)
		{
			if ($image['size'] < $IMG->upload_max_size)
			{
				$test = explode('.', strtolower($image['name']));
				$thetype = array_pop($test);
				
				$URL->title = implode('_', $test);
				$new_title = $URL->processTitle();
			
				$IMG->type = '.' . $thetype;
				$IMG->filename = $IMG->checkName($go['id'] . '_' . $new_title) . '.' . $thetype;
			
				if (in_array($thetype, array_merge($default['images'], $default['flash'])))
				{
					// if uploaded we can work with it
					if (move_uploaded_file($image['temp'], 
						$IMG->path . '/' . $IMG->filename)) 
					{
						$x++;
					
						$IMG->image = $IMG->path . '/' . $IMG->filename;
						$IMG->uploader();

						$clean['media_id'] = 'NULL';
						$clean['media_order'] = $x;
						$clean['media_ref_id'] = $go['id'];
						$clean['media_file'] = $IMG->filename;
						$clean['media_mime'] = $thetype;
						$clean['media_obj_type'] = OBJECT;
						$clean['media_x'] = $IMG->out_size['x'];
						$clean['media_y'] = $IMG->out_size['y'];
						$clean['media_kb'] = $IMG->file_size;

						$added_x[$x] = $this->db->insertArray(PX.'media', $clean);
						
						@chmod($IMG->path . '/' . $IMG->filename, 0755);
					}
					else
					{
						// file not uploaded
					}
				}
				else
				{
					// need to report back if things don't work
					// not a valid format
				}
			}
			else
			{
				// nothing, it's too big
			}
		}

		// update the order of things
		if ($x > 0)
		{
			$this->db->updateRecord("UPDATE ".PX."media SET
				media_order = media_order + $x 
				WHERE 
				(media_id NOT IN (" .implode(',', $added_x). ")) 
				AND media_ref_id = '$go[id]'");
		}
	}
	
	
	public function sbmt_upd_jximg()
	{
		global $go;
				
		load_module_helper('files', $go['a']);
		
		header ('Content-type: text/html; charset=utf-8');
		
		$clean['media_id'] = (int) $_POST['id'];
		$clean['media_title'] = ($_POST['v'] == '') ? '' : utf8Urldecode($_POST['v']);
		$clean['media_caption'] = ($_POST['x'] == '') ? '' : utf8Urldecode($_POST['x']);
		
		$this->db->updateArray(PX.'media', $clean, "media_id=$clean[media_id]");
		
		header ('Content-type: text/html; charset=utf-8');
		echo $this->lang->word('updating');
		exit;
	}


	public function sbmt_upd_jxtext()
	{
		global $go, $default;
		$OBJ =& get_instance();
		
		header ('Content-type: text/html; charset=utf-8');
		
		load_module_helper('files', $go['a']);
		
		$clean['id'] = (int) $_POST['id'];
		$_POST['content'] = ($_POST['v'] == '') ? '' : utf8Urldecode($_POST['v']);
		
		// we need preference on processing
		$rs = $this->db->fetchRecord("SELECT process, status, url   
			FROM ".PX."objects    
			WHERE id = '$clean[id]'");
		
		$processor =& load_class('processor', TRUE, 'lib');
		$this->lib_class('editor');
		//load_helper('textprocess');
		
		$clean['content'] = $processor->process('content', array('nophp'));
		$clean['content'] = $this->editor->textProcess($clean['content'], $rs['process']);
		$clean['udate'] 	= getNow();

		$this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");
		
		// delete the cached file
		if ($rs['status'] == 1) $this->delete_file($rs['url']);
		
		header ('Content-type: text/html; charset=utf-8');
		echo $this->lang->word('updating');
		exit;
	}
	
	
	// for caching
	public function delete_file($url='')
	{
		if ($url == '') return;

		load_helpers(array('time'));
		$CACHE =& load_class('cache', true, 'lib');
		$CACHE->delete_cached($url);
	}
	
	
	public function delete_image($image='')
	{
		if (file_exists($image))
		{
			@unlink($image);
		}
	}

	
	public function sbmt_upd_jxdelimg()
	{
		global $go;
		
		// id here really is the name of the file
		$clean['media_id'] = $_POST['f'];
		
		$this->db->deleteArray(PX.'media', "media_file='$clean[media_id]'");
		
		$file = $_POST['id'] . '_' . $clean['media_id'];
		
		//load_helper('files');
		$this->delete_image(DIRNAME . GIMGS . '/' . $clean['media_id']); // source image
		$this->delete_image(DIRNAME . GIMGS . '/' . $file); // image
		$this->delete_image(DIRNAME . GIMGS . '/th-' . $file); // thumbnail
		$this->delete_image(DIRNAME . GIMGS . '/sys-' . $file); // system thumbnail
		$this->delete_image(DIRNAME . GIMGS . '/systh-' . $file); // system thumbnail
		
		header ('Content-type: text/html; charset=utf-8');
		echo $this->lang->word('updating');
		exit;
	}
	
	
	public function sbmt_upd_jxs()
	{
		global $go;
		
		$clean['id'] = (isset($_POST['id'])) ? (int) $_POST['id'] : 0;

		switch ($_POST['x']) {
		case 'ajx-status':
			if ($clean['id'] == 1) break;
			$clean['status'] = (int) $_POST['v'];
			$this->pub_status = $clean['status'];
			$this->page_id = $clean['id'];
			$this->publisher();
			break;
		case 'ajx-highlight':
			$clean['extra1'] = (int) $_POST['v'];
			$this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");
			echo 'true';
			exit;
			break;
		case 'ajx-secdisp':
			$cleand['sec_disp'] = (int) $_POST['v'];
			
			// need to get the section part
			$id = $this->db->fetchRecord("SELECT obj_ref_id FROM ".PX."objects WHERE id='$clean[id]'");
			
			$this->db->updateArray(PX.'sections', $cleand, "secid='$id[obj_ref_id]'");
			header ('Content-type: text/html; charset=utf-8');
			echo $this->lang->word('updating');
			exit;
			break;
		case 'gettags':
			$rs = $this->db->fetchRecord("SELECT tags FROM ".PX."objects WHERE id='$clean[id]'");

			// tags
			$this->lib_class('tag');
			$this->tag->id = $clean['id'];
			$this->tag->active_tags = $rs['tags'];
			
			header ('Content-type: text/html; charset=utf-8');
			echo $this->tag->show_active_tags2();
			exit;
			break;
			
		case 'getitags':
			$rs = $this->db->fetchRecord("SELECT media_tags FROM ".PX."media WHERE media_id='$clean[id]'");

			// tags
			$this->lib_class('tag');
			$this->tag->active_tags = $rs['media_tags'];

			header ('Content-type: text/html; charset=utf-8');
			echo $this->tag->show_active_tags();
			exit;
			break;
		case 'ajx-operand':
			$clean['operand'] = (int) $_POST['v'];
			break;
		case 'ajx-titling':
			$clean['titling'] = (int) $_POST['v'];
			break;
						
		case 'ajx-images':
			$clean['images'] = (int) $_POST['v'];
			break;
		case 'ajx-thumbs':
			$clean['thumbs'] = (int) $_POST['v'];
			break;
		case 'ajx-shape':
			$clean['thumbs_shape'] = (int) $_POST['v'];
			break;
		case 'processing':
			$clean['process'] = ((int) $_POST['v'] == 1) ? 0 : 1;
			break;
		case 'ajx-home':
			$clean['home'] = (int) $_POST['v'];
			
			// need to set all to 0
			if ($clean['home'] == 1)
			{
				$this->db->updateArray(PX.'objects', array('home' => '0'), "home='1'");
			}
			else
			{
				$this->db->updateArray(PX.'objects', array('home' => '0'), "home='1'");
				$this->db->updateArray(PX.'objects', array('home' => '1'), "id='1'");
			}
			break;
		case 'ajx-hidden':
			$clean['hidden'] = (int) $_POST['v'];
			break;
		case 'ajx-sechidden':
			$section['sec_hide'] = (int) $_POST['v'];
			$temp['secid'] = (int) $_POST['s'];

			$this->db->updateArray(PX.'sections', $section, "secid='$temp[secid]'");
			header ('Content-type: text/html; charset=utf-8');
			echo "Done";
			exit;
			
			break;
		case 'ajx-tiling':
			$clean['tiling'] = (int) $_POST['v'];
			break;
		case 'ajx-target':
			$clean['target'] = (int) $_POST['v'];
			break;
		case 'ajx-iframe':
			$clean['iframe'] = (int) $_POST['v'];
			break;
		case 'ajx-place':
			$clean['placement'] = (int) $_POST['v'];
			break;
		case 'ajx-perm':
			$clean['perm'] = (int) $_POST['v'];
			break;
		case 'color':
			$clean['color'] = $_POST['v'];
			break;
		case 'password':
			$clean['pwd'] = $_POST['v'];
			break;
		case 'secpassword':
			// we need the section info too
			$section['sec_pwd'] = $_POST['v'];
			$temp['secid'] = (int) $_POST['s'];
			$this->db->updateArray(PX.'sections', $section, "secid='$temp[secid]'");
			echo "Done"; 
			exit;
			break;
		case 'year':
			$clean['year'] = $_POST['v'];
			break;
		case 'present':
			$clean['format'] = $_POST['v'];
			break;
		case 'source':
			$clean['media_source'] = $_POST['v'];
			// if we are coming from 'folder' we need to look for database entries and delete
			// and also delete their thumbs...
			// get the list from the table and the delete the thumbs...
			break;
		case 'folder':
			// every time this is changed we have to
			// 1 delete any entries in the database for the old
			// delete the thumbs and system images?
			$clean['media_source_detail'] = $_POST['v'];
			// list all the files...
			break;
		case 'template':
			$clean['template'] = $_POST['v'];
			break;
		case 'break':
			$clean['break'] = (int) $_POST['v'];
			break;
		case 'activity':
			$clean['new'] = (int) $_POST['v'];
			$clean['new'] = ($clean['new'] == 1) ? 0 : 1;
			
			$this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");
			
			if ($clean['new'] == 1)
			{
				echo '0'; exit;
			}
			else
			{
				echo '1'; exit;
			}
			break;
		case 'title':
			load_module_helper('files', $go['a']);
			$clean['title'] = trim(utf8Urldecode($_POST['v']));
			
			// FIX THIS UP!
			if ($clean['title'] == '') { echo "<span style='color: red;'>" . $this->lang->word('error') . "</span>"; exit; }
			// update the subsection title too
			if ($_POST['t'] == 'subsection')
			{
				$this->db->updateRecord("UPDATE ".PX."subsections SET
					sub_title 	= '$clean[title]' 
					WHERE 
					sub_id		= " . $_POST['sub'] . "");
			}

			break;
		case 'sectitle':
			load_module_helper('files', $go['a']);
			
			if ($_POST['t'] == 'section')
			{
				$section['sec_desc'] = trim(utf8Urldecode($_POST['v']));
				$temp['secid'] = (int) $_POST['s'];

				// FIX THIS UP!
				if ($section['sec_desc'] == '') { echo "<span style='color: red;'>" . $this->lang->word('error') . "</span>"; exit; }
				$this->db->updateArray(PX.'sections', $section, "secid='$temp[secid]'");
			}
			else // subsection
			{
				$section['sub_title'] = trim(utf8Urldecode($_POST['v']));
				$temp['sub_id'] = (int) $_POST['s'];

				// FIX THIS UP!
				if ($section['sub_title'] == '') { echo "<span style='color: red;'>" . $this->lang->word('error') . "</span>"; exit; }
				$this->db->updateArray(PX.'subsections', $section, "sub_id='$temp[sub_id]'");
			}
			
			header ('Content-type: text/html; charset=utf-8');
			echo $this->lang->word('updating');
			exit;
			break;
		case 'link':
			load_module_helper('files', $go['a']);
			$clean['link'] = trim(utf8Urldecode($_POST['v']));
			break;
		case 'ajx_ord':

			$temp = explode('.', $_POST['order']);
			
			$this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");
			
			header ('Content-type: text/html; charset=utf-8');
			echo $clean['title'];
			exit;
			break;
		
		case 'create':

			if ($_POST['st'] == 'exhibit')
			{
				$clean['title'] = $_POST['t'];
				// how to validate this?
				$clean['section_id'] = $_POST['s'];
				$clean['year'] = (int) $_POST['y'];
				$q = 'edit';
			}
			else
			{
				$clean['link'] = $_POST['l'];
				$clean['title'] = $_POST['t'];
				// how to validate this?
				$clean['section_id'] = $_POST['s'];
				$q = 'link';
				$clean['content'] = "<plugin:ndxz_iframed {{link}} />";
			}
			
			// check for subsection
			if (preg_match("/^[0-9]+\.[0-9]/", $clean['section_id'], $match))
			{
				$tmp = explode('.', $clean['section_id']);
				$clean['section_sub'] = array_pop($tmp);
				$clean['section_id'] = $tmp[0];
			}
		
			// we need to deal with the order of things...
			$this->db->updateRecord("UPDATE ".PX."objects SET
				ord 		= ord + 1 
				WHERE 
				section_id	= ".$this->db->escape($_POST['s'])." 
				AND section_top != '1'");
			
			// a few more things
			$clean['udate'] 	= getNow();
			$clean['object'] 	= 'exhibits';
			$clean['ord']		= 1;
			$clean['creator']	= $this->access->prefs['ID'];
			
			$last = $this->db->insertArray(PX.'objects', $clean);
			
			echo BASEURL . "/ndxzstudio/?a=$go[a]&q=$q&id=$last";
		
			exit;
			break;
		}
		
		if ($clean['id'] > 0) $this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");
		
		header ('Content-type: text/html; charset=utf-8');
		echo $this->lang->word('updating');
		exit;
	}
}