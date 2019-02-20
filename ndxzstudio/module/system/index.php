<?php if (!defined('SITE')) exit('No direct script access allowed');


class System extends Router
{
	public $submits		= array();
	public $tabs		= array();
	public $reset		= false;

	public function __construct()
	{
		parent::__construct();
		
		// library of $_POST options
		$this->submits = array('upd_user', 'upd_settings', 'add_sec',
			'edit_sec', 'del_sec', 'upd_profile', 'upd_jxs', 'upd_ord', 'upd_jxtag',
			'clear_cache', 'upd_jxcode', 'upd_img', 'upd_tsettings', 'upd_backup', 'upd_jxbackup',
			'upd_options', 'add_user', 'upd_user', 'del_user', 'deact_user', 'sendlogin',
			'add_alt', 'upd_alt', 'del_alt', 'upd_plugins_edit', 'clear_dimgs', 'upd_prefs', 'abstract',
			'upd_format_edit', 'upd_theme', 'del_tag', 'merge_tag', 'edit_tag', 'upd_jxs_opt');
			
		$this->tabs = $this->default['system_admin'];
	}
	
	public function _submit()
	{
		// from $_POST to method
		$this->posted($this, $this->submits);
	}
	
	public function page_extend()
	{
		global $go;

		$this->hook->do_action('system_extension_' . $go['x']);
	}
	
	public function sbmt_abstract()
	{
		$OBJ =& get_instance();
		
		$abstract 	= (int) $_POST['ab'];
		$ab_obj 	= $_POST['o'];
		$ab_obj_id 	= (int) $_POST['i'];
		$ab_var		= $_POST['v'];
		
		// load abstracts class
		$OBJ->lib_class('abstracts');
		
		// commands for abstract actions: insert, update, delete
		if ((int) $_POST['ab'] == 0)
		{
			// delete instructions
			$OBJ->abstracts->abstract_delete($ab_var, $ab_obj, $ab_obj_id, null);
			
			echo 'done';
			exit;
		}
		
		if ((int) $_POST['s'] == 1)
		{
			// update
			$OBJ->abstracts->abstract_update($abstract, $ab_var, $ab_obj, $ab_obj_id, null);
		}
		else
		{
			// insert
			$OBJ->abstracts->abstract_create($abstract, $ab_var, $ab_obj, $ab_obj_id);
		}

		echo 'done';
		exit;
	}
	
	////////////////////////////
	
	
	public function page_users()
	{
		global $go, $default;
		
		if ($go['x'] == 'del') { $this->page_users_del(); }
		if ($go['x'] == 'edit') { $this->page_users_edit(); }
		
		$this->template->location = $this->lang->word('main');
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('new'),
			'#', "onclick=\"toggle('add-user'); return false;\"");
		
		load_module_helper('files', $go['a']);
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$b = $this->toggler();

		$b .= "<div id='add-user' style='display: none; margin-bottom:18px;' class='bg-grey'>\n";
		$b .= "<div class='c3'>\n";
		
		$b .= "<div class='col'>\n";
		$b .= ips($this->lang->word('user name'), 'input', 'user_name', NULL, "maxlength='50'", 'text', $this->lang->word('required'),'req');
		$b .= input('add_user', 'submit', $attr='', $this->lang->word('add user'));
		$b .= "</div>\n";
		
		$b .= "<div class='col'>\n";
		$b .= ips($this->lang->word('user last name'), 'input', 'user_surname', NULL, "maxlength='50'", 'text', $this->lang->word('required'),'req');
		$b .= "</div>\n";
		
		$b .= "<div class='cl'><!-- --></div>\n</div>\n";
		$b .= "</div>\n\n";
		
		// query for users
		$users = $this->db->fetchArray("SELECT * FROM ".PX."users ORDER BY user_surname ASC");
		
		// the tabs
		$b .= "<table class='modtable'>\n";
		$b .= "<tr>\n";
		$b .= th('&nbsp;', "width='5%' class='toptext'");
		$b .= th($this->lang->word('user name'), "width='35%' class='toptext'");
		$b .= th('&nbsp;', "width='20%' class='toptext'");
		$b .= th($this->lang->word('user id'), "width='20%' class='toptext'");
		$b .= th($this->lang->word('active'), "width='10%' class='toptext'");
		$b .= th('&nbsp;', "width='10%' class='toptext txt-right'");
		$b .= "</tr>\n";
		$b .= "</table>\n";
		
		$b .= "<table class='modtable' style='margin-bottom: 18px;'>\n";
		
		foreach ($users as $key => $user)
		{
			$b .= "<tr".row_color(" class='color'").">\n";
			$b .= ($user['user_img'] == '') ? 
				td("<div style='width: 35px; height: 35px; border: 1px solid #ccc;'><!-- --></div>", "width='5%' class='cell-doc'") : 
				td("<div style='width: 35px; height: 35px; border: 1px solid #ccc;'><img src='" . BASEURL . "/files/$user[user_img]' width='35'</div>", "width='5%' class='cell-doc'");
			$b .= td($user['user_surname'] . ', ' . $user['user_name'], "width='35%' class='cell-doc' style='vertical-align: top;'");
			$b .= td('&nbsp;', "width='20%' class='cell-doc' style='vertical-align: top;'");
			$b .= td($user['userid'], "width='20%' class='cell-doc' style='vertical-align: top;'");
			
			if ($user['user_active'] == 1)
			{
				$b .= td(span($this->lang->word('active'), "class='grn-text'"), "width='10%' class='cell-doc' style='vertical-align: top;'");
			}
			else
			{
				$b .= td(span($this->lang->word('inactive'), "class='red-text'"), "width='10%' class='cell-doc' style='vertical-align: top;'");
			}
			
			$delete = ($this->access->is_admin()) ?
				($user['ID'] != 1) ? href($this->lang->word('delete'), "?a=$go[a]&q=users&x=del&id=$user[ID]", "onclick=\"return confirm('" . $this->lang->word('are you sure') . "');\"") . ' ' : '' : '';
			
			$b .= td(href($this->lang->word('edit'), "?a=$go[a]&q=users&x=edit&id=$user[ID]"), "width='10%' class='cell-doc txt-right' style='vertical-align: top;'");
			$b .= "</tr>\n";
		}

		$b .= "</table>\n";
				
		$this->template->body = $b;
		
		return;
	}
	
	// WE WILL WANT TO RUN SOME KIND OF VALIDATION HERE!
	public function sbmt_upd_options()
	{
		global $go;
		
		$processor =& load_class('processor', TRUE, 'lib');

		$clean['site_vars'] = serialize($_POST['site']);
		
		$clean['caching'] = $processor->process('caching', array('digit'));
		
		$this->db->updateArray(PX.'settings', $clean, "adm_id = '1'");
		
		// need to leave an update notification
		$this->template->action_update = 'updated';
		
		// we need a system redirect to display updates...
		system_redirect("?a=$go[a]&q=$go[q]");
	}
	
	
	// check the validation
	public function sbmt_upd_plugins_edit()
	{
		// we should check the inputs
		// passwords need special treatment
		//if (isset($_POST['option']['password'])) { $_POST['option']['password'] = sha1($_POST['option']['password']); }
		
		// we need to process the 'options' and look for rules
		//$this->process_options($_POST['option']); exit;

		// maybe have requireds?
		$clean['pl_options'] = $this->process_options($_POST['option']);
		$clean['pl_options'] = serialize($clean['pl_options']);
		
		// update things
		$this->db->updateArray(PX.'plugins', $clean, "pl_id = '" . $this->go['id'] . "'");
		
		// need to leave an update notification
		$this->template->action_update = 'updated';
	}
	
	
	public function process_options($options=array())
	{
		if (empty($options)) return '';
		
		foreach ($options as $key => $option)
		{
			if (preg_match("/^md5_/i", $key))
			{
				$new_key = str_replace('md5_', '', $key);
				$arr[$new_key] = md5(sha1(md5(sha1($option))));
			}
			else
			{
				$arr[$key] = $option;
			}
		}
		
		return $arr;
	}
	
	
	public function sbmt_upd_format_edit()
	{
		// we should check the inputs
		// passwords need special treatment
		//if (isset($_POST['option']['password'])) { $_POST['option']['password'] = sha1($_POST['option']['password']); }

		// maybe have requireds?
		$clean['pl_options'] = serialize($_POST['option']);
		
		// update things
		$this->db->updateArray(PX.'plugins', $clean, "pl_id = '" . $this->go['id'] . "'");
		
		// need to leave an update notification
		$this->template->action_update = 'updated';
	}
	
	
	public function page_index()
	{
		system_redirect("?a=system&q=theme");
		exit;
	}
	
	
	public function page_preferences()
	{
		global $go, $default;

		$this->template->location_override = $this->lang->word('user');
		$this->template->location = $this->lang->word('preferences');
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		// uploading an image?
		if (isset($_FILES['filename']))
		{
			if ($_FILES['filename']['tmp_name'] != '')
			{
				$IMG =& load_class('media', TRUE, 'lib');
				
				// we need to get these from some defaults someplace
				$IMG->thumbsize = 75;
				$IMG->maxsize = 9999;
				$IMG->quality = $default['img_quality'];
				$IMG->makethumb	= true;
				
				// not sure why we need the trailing slash here
				$dir = DIRNAME . '/files/';
				$types = array_merge($default['images']);
				$IMG->path = $dir;
				
				$new_images['name'] = $_FILES['filename']['name'];
				$new_images['temp'] = $_FILES['filename']['tmp_name'];
				$new_images['size'] = $_FILES['filename']['size'];

				$test = explode('.', strtolower($new_images['name']));
				$thetype = array_pop($test);
				$IMG->type = '.' . $thetype;
				$IMG->filename = $this->access->prefs['user_name'] . '_' . $this->access->prefs['user_surname'] . '.' . $thetype;
				$IMG->origname = $IMG->filename;

				// if uploaded we can work with it
				if (move_uploaded_file($new_images['temp'], $IMG->path . '/' . $IMG->filename)) 
				{
					$IMG->image = $IMG->path . '/' . $IMG->filename;
					$IMG->user_image();
					
					// add to database
					$clean['user_img'] = $IMG->filename;
					$this->db->updateArray(PX.'users', $clean, "ID='" . $this->access->prefs['ID'] . "'");
				}
				///////////////////////////////////////////////////
			}
		}
		
		if (isset($_POST['upd_delusrimg']) && $_POST['delusrimg'])
		{
			if (file_exists(DIRNAME . '/files/' . $_POST['delusrimg']))
			{
				unlink(DIRNAME . '/files/' . $_POST['delusrimg']);
			}
			
			$clean['user_img'] = '';
			$this->db->updateArray(PX.'users', $clean, "ID='" . $this->access->prefs['ID'] . "'");
		}
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."users 
			WHERE ID = '" . $this->access->prefs['ID'] . "'");
			
		load_helper('output');
		load_module_helper('files', $go['a']);
		
		$body = '';
		
		if (isset($_GET['flag']))
		{
			$body .= "<div style='background: red; color: white; padding: 18px; margin-bottom: 18px;'><strong>Required:</strong> Update your info and select a login/password set you can remember. The default login/password is index1/exhibit</div>\n";
		}
		
		$body .= "<div class='c3 bg-grey corners'>\n";

		$body .= "<div class='col'>\n";
		$body .= ips($this->lang->word('user name'), 'input', 'user_name', $rs['user_name'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
		$body .= ips($this->lang->word('user last name'), 'input', 'user_surname', $rs['user_surname'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
		$body .= ips($this->lang->word('user email'), 'input', 'email', $rs['email'], "maxlength='100'", 'text', $this->lang->word('required'), 'req');
		
		$body .= ips($this->lang->word('login'), 'input', 'userid', $rs['userid'], "maxlength='12'", 'text', $this->lang->word('required').' '.$this->lang->word('number chars'), 'req');
		$body .= ips($this->lang->word('change password'), 'input', 'password', NULL, "maxlength='12'", 'password', $this->lang->word('required').' '.$this->lang->word('number chars'), 'req');
		$body .= ips($this->lang->word('confirm password'), 'input', 'cpassword', NULL, "maxlength='12'", 'password', $this->lang->word('if change'),'req');
		$body .= ips($this->lang->word('your language'), 'getLanguage', 'user_lang', $rs['user_lang'], NULL, 'text');
		
		$body .= input('huser_lang', 'hidden', NULL, $rs['user_lang']);
		
		$body .= "<div class='buttons'>";

		$body .= button('upd_prefs', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		$body .= "</div>\n";
		
		// upload your happy mug ;)
		$body .= "<div class='col'>\n";
		
		//$body .= "<p><label>" . $this->lang->word('user image') . "</label></p>\n";
		
		// if file exists - bring back later
		/*
		if ($rs['user_img'] == '')
		{
			$this->template->form_type = true;

			$body .= "<input type='file' style='font-size: 9px;' name='filename' />";
		}
		else
		{
			$body .= "<div id='cover_image'>\n";
			$body .= "<p><img src='" . BASEURL . "/files/" . $rs['user_img'] . "' width='75' /></p>\n";
			$body .= "<p><input type='submit' value='Delete Image' name='upd_delusrimg' /></p>\n";
			$body .= "<input type='hidden' value='" . $rs['user_img'] . "' name='delusrimg' />\n";
			$body .= "</div>\n";
		}
		*/
		
		$body .= "</div>";
		// end column two

		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	
	
	public function page_upgrade()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location = $this->lang->word('upgrade');
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * FROM " . PX . "settings WHERE adm_id = '1'");
		
		// review this one later
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$x = (isset($_GET['x'])) ? $_GET['x'] : '';
		
		if ($x != 'complete')
		{
			$files = array();

			// need to list and order the updates
			if (is_dir(DIRNAME . '/ndxzstudio/upgrade/'))
			{
				if ($fp = opendir(DIRNAME . '/ndxzstudio/upgrade/')) 
				{
					while (($file = readdir($fp)) !== false) 
					{
						if (preg_match("/^v/", $file)) $files[] = $file;
					}
				
					sort($files);
				}

				closedir($fp);
			}
			
			//array_multisort($files, SORT_DESC, SORT_STRING);
		
			if (!empty($files))
			{
				foreach ($files as $file)
				{
					$ver = str_replace(array('v', '.php'), array('', ''), $file);
				
					if (VERSION <= $ver)
					{
						// include the file
						if (file_exists(DIRNAME . '/ndxzstudio/upgrade/' . $file))
						{
							require_once(DIRNAME . '/ndxzstudio/upgrade/' . $file);
							$upgrade = 'upgrade_' . str_replace('.', '', $ver);
							$TMP = new $upgrade;
							$TMP->upgrade();
							//$messages[] = $TMP->messages;
						}
					}
				}
			
				// update settings with last version
				//$this->db->updateArray(PX.'settings', array('version' => $ver), "adm_id = '1'");
			
				// need a system redirect here...
				system_redirect("?a=$go[a]&q=upgrade&x=complete");
				exit;
			}
		}

		$body = "<div style='height: 400px;'>";
		$body .= "<h3>Upgrade completed</h3>";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	
	
	public function page_settings()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location = $this->lang->word('settings');
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * FROM " . PX . "settings WHERE adm_id = '1'");
		
		// review this one later
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		$body .= "<div>";
		$body .= "<div class='c3'>\n";
		
		// First column
		$body .= "<div class='col'>\n";
		$body .= "<h3 style='margin-bottom: 18px;'>" . $this->lang->word('localization') . "</h3>";
		
		$body .= ips($this->lang->word('time now'), 'getTimeOffset', 'site_offset', $rs['site_offset']);
		$body .= ips($this->lang->word('time format'), 'getTimeFormat', 'site_format', $rs['site_format']);
		$body .= ips($this->lang->word('your language'), 'getLanguage', 'site_lang', $rs['site_lang'], NULL, 'text');
		
		$body .= "</div>\n";
		
		// second column
		$body .= "<div class='col'>\n";
		$body .= "<h3 style='margin-bottom: 18px;'>" . $this->lang->word('site attributes') . "</h3>";
		
		$body .= ips($this->lang->word('use passwords'), 'get_yes_no', 'site[passwords]', $this->vars->site['passwords']);
		
		$body .= ips($this->lang->word('use templates'), 'get_yes_no', 'site[templates]', $this->vars->site['templates']);
		
		$body .= ips($this->lang->word('use tags'), 'get_yes_no', 'site[tags]', $this->vars->site['tags']);
		
		$body .= ips($this->lang->word('caching'), 'get_yes_no', 'caching', $rs['caching']);
			
		$body .= "</div>\n";
		
		// third column
		$body .= "<div class='col'>\n";
		$body .= "<h3 style='margin-bottom: 18px;'>" . $this->lang->word('Caching') . "</h3>";
		
		$body .= "<div style='padding-top: 5px;'>\n";
		
		$body .= "<p><input type='submit' name='clear_cache' value='" . $this->lang->word('Clear Site Cache') . "' /></p>";
		$body .= "<p><input type='submit' name='clear_dimgs' value='" . $this->lang->word('Clear Dynamic Images') . "' /></p>";
		$body .= "</div>\n";

		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		
		$body .= "<div class='buttons'>";		
		$body .= button('upd_settings', 'submit', "class='general_submit'", $this->lang->word('update'));		
		$body .= "</div>\n";
		
		$body .= "</div>";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	
	
	public function page_theme()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location = $this->lang->word('settings');
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * FROM " . PX . "settings WHERE adm_id = '1'");
		
		// review this one later
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		$body .= "<div class='c3'>\n";
		
		// First column
		$body .= "<div class='col'>\n";
		$body .= ips($this->lang->word('exhibition name'), 'input', 'obj_name', 
			$rs['obj_name'], "maxlength='50'", 'text', $this->lang->word('required'), 'req');
		
		$body .= "<label>".$this->lang->word('theme')."</label>\n";
		$body .= getThemes(DIRNAME . '/ndxzsite/', $rs['obj_theme']);	
		
		$body .= "<div class='buttons'>";
				
		$body .= button('upd_theme', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		
		$body .= "</div>\n";
		
		// second column
		$body .= "<div class='col' style='width: 300px;'>\n";
		
		$body .= label($this->lang->word('pre nav text').' '.span($this->lang->word('html allowed')));
		
		// admin editor
		$this->lib_class('editor');
		$this->editor->content = $rs['obj_itop'];
		$this->editor->process = 1;
		$this->editor->content_id = 'obj_itop';
		$this->editor->css = "style='width: 290px; height: 225px;'";
		$this->editor->canvas = 1;
		$body .= $this->editor->admin_editor();
		$this->editor->html = ''; // need to clear it out
		//////////////
			
		$body .= "</div>\n";
		
		// third column
		$body .= "<div class='col' style='width: 300px;'>\n";
		
		$body .= label($this->lang->word('post nav text').' '.span($this->lang->word('html allowed')));
		
		// admin editor
		$this->editor->content = $rs['obj_ibot'];
		$this->editor->process = 1;
		$this->editor->content_id = 'obj_ibot';
		$this->editor->css = "style='width: 290px; height: 225px;'";
		$this->editor->canvas = 2;
		$body .= $this->editor->admin_editor();
		//////////////

		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	
	///////////////// NEW STATISTICS
	
	public function page_statistics()
	{
		global $go;
		
		if ($go['x'] == 'refer') { $this->page_refer(); }
		if ($go['x'] == 'alt') { $this->page_override(); }
		if ($go['x'] == 'hits') { $this->page_hits(); }
		
		$this->abstracts->get_system_abstracts($go['id']);
		
		// check to see if we have stats override in abstracts
		if (isset($this->abstracts->abstract['statistics']))
		{
			$this->page_override();
		}
		
		load_module_helper('stats', $go['a']);
		
		// js
		$this->template->add_js('jquery.js');
		$this->template->add_module_js('system.js');
		
		// default/validate $_GET
		$go['page'] = getURI('page', 30, 'digit', 2);

		$this->template->location = $this->lang->word('main');

		$today = convertToStamp(getNow());
		$day = substr($today,6,2);
		$mn = substr($today,4,2);
		$yr = substr($today,0,4);
		$thirtydays = date('Y-m-d', mktime('00', '00', '00', $mn-1, $day, $yr));
		$fourweeks = date('Y-m-d', mktime('00', '00', '00', $mn, $day-28, $yr));
		$paged = date('Y-m-d', mktime('00', '00', '00', $mn, ($day - $go['page']), $yr));
		
		// we archive anything before this date...
		recent_stats();
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		$body .= "<div class='c4'>\n";
		
		// these need to add up the stats_storage table data
		// need to add the untallied months too
		$total_since_hits = $this->db->getCount("SELECT sum(stor_hits) FROM ".PX."stats_storage");
		$total_since_refer_hits = $this->db->getCount("SELECT sum(stor_referrer) FROM ".PX."stats_storage");
		$total_avg_uniques = $this->db->getCount("SELECT avg(stor_unique) FROM ".PX."stats_storage");
		
		$body .= "<div class='col'>\n";
		$body .= span($this->lang->word('statistics since')) . br();
		$body .= strong(convertDate($this->access->settings['installdate']), "style='display: block; margin-top: 3px;'");
		$body .= "</div>\n";

		$body .= "<div class='col'>\n";
		$body .= span($this->lang->word('total page visits')) . br();
		$body .= "<h3 style='font-size: 24px; margin-top: 3px;'>" . $total_since_hits . '</h3>';
		$body .= "</div>\n";
		
		$body .= "<div class='col'>\n";
		$body .= span($this->lang->word('total referrals')) . br();
		$body .= "<h3 style='font-size: 24px; margin-top: 3px;'>" . $total_since_refer_hits . '</h3>';
		$body .= "</div>\n";
		
		$body .= "<div class='col'>\n";
		$body .= span($this->lang->word('average uniques per month')) . br();
		$body .= "<h3 style='font-size: 24px; margin-top: 3px;'>" . number_format($total_avg_uniques, 2) . '</h3>';
		$body .= "</div>\n";
		
		$body .= "<div style='clear: left;'><!-- --></div>\n";
		$body .= "</div>\n";
		
		///// +++++++++++++++ GRAPH OF 30 DAYS OF HITS +++++++++
		
		$totalz = $this->db->fetchArray("SELECT hit_day, COUNT(hit_day) FROM ".PX."stats GROUP BY hit_day ORDER BY hit_day ASC");
		
		// rewrite array...get largest value...
		$largest = 0; $height = 150;
		
		if ($totalz)
		{
			foreach ($totalz as $rw)
			{
				$dayz[$rw['hit_day']] = $rw['COUNT(hit_day)'];
			
				$largest = ($rw['COUNT(hit_day)'] > $largest) ? $rw['COUNT(hit_day)'] : $largest;
			}
		}
		
		$body .= "<div style='margin: 5px 0; padding: 20px 10px 5px 10px; background: white;' class='corners'>\n";

		$body .= "<div class='col'>\n";
		$body .= "<ul style='height: 150px;'>";
		
		if ($totalz)
		{
			$factor = ($height / $largest); $i = 1; $rw = array();
			$end = count($dayz);
		}
		
		for($i=0; $i<28; $i++)
		{
			$d = date('Y-m-d', mktime('00', '00', '00', $mn, $day-$i, $yr));
			$weekend = date('w', mktime('00', '00', '00', $mn, $day-$i, $yr));
			$weekend_date = (($weekend == 0) || ($weekend == 6)) ? true : false;
			$color = ($weekend_date == true) ? 'ccc' : '6adceb';
			
			if (isset($dayz[$d]))
			{
				// check if there are referrers too
				if (isset($refz[$d]))
				{
					// need to do math to get the values of things based upon 'largest'...
					$factor = ($height / $largest);
					$hits = round($factor * $refz[$d]);

					$ref = "<div style='height: " . ($hits + 20) . "px; background-color: #000; opacity: 0.25; position: absolute; bottom: 0; left: 0; width: 27px; z-index: 2;'><strong style='padding: 3px 0 0 0; display: block; text-align: center; font-weight: normal; font-size: 9px; color: white;'>" . $refz[$d] . "</strong></div>\n\n";
				}
				else
				{
					$ref = '';
				}
				
				// check if there are referrers too
				if (isset($unqz[$d]))
				{
					// need to do math to get the values of things based upon 'largest'...
					$factor = ($height / $largest);
					$hits = round($factor * $unqz[$d]);

					$unq = "<div style='height: " . ($hits + 20) . "px; background-color: #fff20d; opacity: 0.7; position: absolute; bottom: 0; left: 0; width: 27px; z-index: 2;'><strong style='padding: 3px 0 0 0; display: block; text-align: center; font-weight: normal; font-size: 9px; color: black;'>" . $unqz[$d] . "</strong></div>\n\n";
				}
				else
				{
					$unq = '';
				}
				
				// need to do math to get the values of things based upon 'largest'...
				$factor = ($height / $largest);
				$hits = round($factor * $dayz[$d]);
				$padding = $height - $hits;
				
				$color = ($d == "$yr-$mn-$day") ? '0c0' : $color;
				if ($dayz[$d] == 0) $color = 'white';
				
				$rw[] = "<li style='list-style-type: none; height: " . ($height + 20) . "px; width: 30px; float: left; position: relative;'><div style='height: " . ($hits + 20) . "px; background: #{$color}; position: absolute; bottom: 0; left: 0; z-index: 1; width: 27px;'><strong style='padding: 3px 0 0 0; display: block; text-align: center; font-weight: normal; font-size: 9px;'>" . $dayz[$d] . "</strong></div>$ref $unq<div style='height: 12px; width: 27px; position: absolute; bottom: -16px; left: 0; z-index: 3; text-align: center; font-size: 9px;'>" . substr($d, 8, 2) . "</div></li>\n\n";
			}
			else
			{
				$color = ($d == "$yr-$mn-$day") ? '0c0' : $color;
				$color = 'f3f3f3';

				$rw[] = "<li style='list-style-type: none; height: " . ($height + 20) . "px; width: 30px; float: left; position: relative;'><div style='height: 20px; background: #{$color}; position: absolute; bottom: 0; left: 0; z-index: 1; width: 27px;'><strong style='padding: 3px 0 0 0; display: block; text-align: center; font-weight: normal; font-size: 9px;'>0</strong></div><div style='height: 12px; width: 27px; position: absolute; bottom: -16px; left: 0; z-index: 3; text-align: center; font-size: 9px;'>" . substr($d, 8, 2) . "</div></li>\n\n";
			}
		}
		
		// revert the order and implode the array 
		$reversed = array_reverse($rw);
		$body .= implode("", $reversed);
		
		$body .= "</ul>";
		$body .= "<div style='clear: left;'><!-- --></div>";
		$body .= "<div style='margin-top: 24px;'><!-- --></div>\n";
		//$body .= "</div>";
		
		$body .= "<div style='clear: left;'><!-- --></div>";
		$body .= "</div>";
		
		///// ++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= "<div class='half'>\n";
		
		/////////////////////////////////////////////
		
		$body .= "<div class='col1'>\n";
		
		$months = getMonthlyHits(NULL);
		$body .= "<table class='table380' cellpadding='0' cellspacing='0' border='0'>\n";
		$body .= "<tr>\n";
		$body .= th($this->lang->word('year'), "class='toptext' width='40%'");
		$body .= th($this->lang->word('total'), "class='toptext cell-middle' width='20%'");
		$body .= th($this->lang->word('unique'), "class='toptext cell-middle' width='20%'");
		$body .= th($this->lang->word('refers'), "class='toptext cell-middle' width='20%'");
		$body .= "</tr>\n";
		
		$i = 1;
		
		$monthly = $this->db->fetchArray("SELECT * FROM ".PX."stats_storage ORDER BY stor_date DESC");
		
		if ($monthly)
		{
			foreach ($monthly as $key => $out) 
			{
				$body .= "<tr".row_color(" class='color'").">\n";
				$body .= td($this->lang->word($out['stor_date']), "class='cell-doc'");
				$body .= td($out['stor_hits'],"class='cell-middle'");
				$body .= td($out['stor_unique'],"class='cell-middle'");
				$body .= td($out['stor_referrer'],"class='cell-middle'");
				$body .= "</tr>\n";
				$i++;
			}
		}
		$body .= "</table>\n";

		$body .= "</div>\n";
		
		// ++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= "<div class='col2'>\n";
		
		// ++++++++++++++++++++++++++++++++++++++++++++
		
		// top pages
		$hits = $this->db->fetchArray("SELECT hit_page, COUNT(hit_page) FROM ".PX."stats GROUP BY hit_page");
		
		if ($hits)
		{
			// rewrite for ease of use
			foreach ($hits as $hit)
			{
				$rwh[$hit['hit_page']] = $hit['COUNT(hit_page)'];
			}
		
			asort($rwh, SORT_NUMERIC);
			$sorted = array_reverse($rwh);

			$body .= "<table class='table380' cellpadding='0' cellspacing='0' border='0'>\n";
			$body .= "<tr>\n";
			$body .= th($this->lang->word('popular pages') . ' ' . span('past month'), "class='toptext' width='40%'");
			$body .= th($this->lang->word('total'), "class='toptext cell-doc txt-right' width='20%'");
			$body .= "</tr>\n";
		
			$i = 1;
			foreach ($sorted as $key => $out) 
			{
				if ($i <= 10)
				{
					$body .= "<tr" . row_color(" class='color'") . ">\n";
					$body .= td(href($key, BASEURL . ndxz_rewriter($key), "target='_new'"), "class='cell-doc'");
					$body .= td($out, "class='cell-doc txt-right'");
					$body .= "</tr>\n";
				
					$i++;
				}
			}
			
			$body .= "<tr" . row_color(" class='color'") . ">\n";
			$body .= td('&nbsp;', "class='cell-doc'");
			$body .= td(href('more', "?a=$go[a]&amp;q=statistics&x=hits", ""), "class='cell-doc txt-right'");
			$body .= "</tr>\n";

			$body .= "</table>\n";
		}
		
		///////////////////////
		
		// top referrers...
		// we need to forget our own host...
		$refers = $this->db->fetchArray("SELECT hit_referrer, COUNT(hit_referrer) AS 'refer' FROM ".PX."stats WHERE hit_referrer != '' GROUP BY hit_referrer ORDER BY refer DESC LIMIT 10");
		
		if ($refers) 
		{
			$body .= "<table class='table380' cellpadding='0' cellspacing='0' border='0'>\n";
			$body .= "<tr>\n";
			$body .= th($this->lang->word('top 10 referrers').' '.
			span("(".$this->lang->word('past 30').")","class='small-txt'")
			,"class='toptext' width='75%'");
			$body .= th($this->lang->word('Total'), "class='toptext cell-doc txt-right' width='25%'");
			$body .= "</tr>\n";
		
			$i = 1;
			foreach ($refers as $out) 
			{
				//print_r($out);
				$body .= "<tr".row_color(" class='color'").">\n";
				$host = parse_url($out['hit_referrer']);
				$body .= td(href($host['host'], $out['hit_referrer'], "target='_new'"),"class='cell-doc'");
				$body .= td($out['refer'],"class='cell-doc txt-right'");
				$body .= "</tr>\n";
				$i++;
			}
			
			$body .= "<tr" . row_color(" class='color'") . ">\n";
			$body .= td('&nbsp;', "class='cell-doc'");
			$body .= td(href('more', "?a=$go[a]&amp;q=statistics&x=refer", ""), "class='cell-doc txt-right'");
			$body .= "</tr>\n";

			$body .= "</table>\n";
		}
		
		/*
		// top search terms...
		$terms = $this->db->fetchArray("SELECT hit_keyword, COUNT(hit_keyword) AS 'keywords' FROM ".PX."stats WHERE hit_keyword != '' AND hit_time > '$paged' GROUP by hit_keyword ORDER BY keywords DESC LIMIT 10");
		if (is_array($terms)) 
		{
			$body .= "<table class='table380' cellpadding='0' cellspacing='0' border='0'>\n";
			$body .= "<tr>\n";
			$body .= th($this->lang->word('top 10 keywords').' '.
			span("(".$this->lang->word('past 30').")","class='small-txt'")
			,"class='toptext' width='75%'");
			$body .= th('Total',"class='toptext cell-doc txt-right' width='25%'");
			$body .= "</tr>\n";
		
			$i = 1;
			foreach ($terms as $out) 
			{
				$body .= "<tr".row_color(" class='color'").">\n";
				$keyword = ($out['hit_keyword'] == '') ? 'Unknown' : $out['hit_keyword'];
				$body .= td($keyword, "class='cell-doc'");
				$body .= td($out['keywords'], "class='cell-doc txt-right'");
				$body .= "</tr>\n";
				$i++;
			}
			
			$body .= "<tr" . row_color(" class='color'") . ">\n";
			$body .= td('&nbsp;', "class='cell-doc'");
			$body .= td(href('more', "?a=$go[a]&amp;q=statistics&x=keywords", ""), "class='cell-doc txt-right'");
			$body .= "</tr>\n";
		
			$body .= "</table>\n";
		}
		*/
		
		// ++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= "</div>\n";
		
		// ++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= "<div class='cl'><!-- --></div>\n\n";
		$body .= "</div>\n";
		
		$body .= "</div>\n";
		
		$body .= "<div class='buttons'>";

		$body .= button('reset', 'button', "class='general_delete' onclick=\"reset_stats(); return false;\"", $this->lang->word('reset statistics'));
				
		$body .= "</div>\n";		
		$body .= "</div>\n";
		
				
		$this->template->body = $body;
		
		return;
	}
	
	
	public function page_refer()
	{
		global $go;
		
		// default/validate $_GET
		$go['page'] = getURI('page', 0, 'digit', 5);

		$this->template->location = $this->lang->word('main');
		
		load_module_helper('files', $go['a']);
		

		$today = convertToStamp(getNow());
		$day = substr($today,6,2);
		$mn = substr($today,4,2);
		$yr = substr($today,0,4);
		$thirtydays = date('Y-m-d', mktime('00', '00', '00', $mn-1, $day, $yr));
			
		$refers = $this->db->fetchArray("SELECT hit_referrer, COUNT(hit_referrer) AS 'refer' FROM ".PX."stats WHERE hit_referrer != '' GROUP BY hit_referrer ORDER BY refer DESC LIMIT 1000");
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = $this->toggler();
		
		// table for all our results
		$body .= "<table cellpadding='0' cellspacing='0' border='0'>\n";
		$body .= "<tr class='top'>\n";
		$body .= "<th width='90%' class='toptext'><strong>".$this->lang->word('referrer')."</strong></th>\n";
		$body .= "<th width='10%' class='toptext'><strong>".$this->lang->word('refers')."</strong></th>\n";
		$body .= "</tr>\n";
		$body .= "</table>\n";
		
		// dynamic output for table
		$body .= "<table cellpadding='0' cellspacing='0' border='0'>\n";

		if (!$refers)
		{
			$body .= tr(td('No hits yet', "colspan='2'"));
		}
		else
		{
			foreach($refers as $referrer) 
			{
				$body .= tr(
					td($referrer['hit_referrer'],"width='90%' class='cell-doc'").
					td($referrer['refer'],"width='10%' class='cell-mid'"),
						row_color(" class='color'"));
			}
		}
		// end dynamic rows output
		$body .= "</table>\n";
		
		$this->template->body = $body;
		$this->template->output('index');
		exit;
	}
	
	
	public function page_hits()
	{
		global $go;
		
		// default/validate $_GET
		$go['page'] = getURI('page', 0, 'digit', 5);

		$this->template->location = $this->lang->word('main');
		
		load_module_helper('files', $go['a']);
		

		$today = convertToStamp(getNow());
		$day = substr($today,6,2);
		$mn = substr($today,4,2);
		$yr = substr($today,0,4);
		$thirtydays = date('Y-m-d', mktime('00', '00', '00', $mn-1, $day, $yr));
		
		$rs = $this->db->fetchArray("SELECT * FROM ".PX."stats_exhibits ORDER BY stor_count DESC");
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = $this->toggler();
		
		// table for all our results
		$body .= "<table cellpadding='0' cellspacing='0' border='0'>\n";
		$body .= "<tr class='top'>\n";
		$body .= "<th width='90%' class='toptext'><strong>".$this->lang->word('page')."</strong></th>\n";
		$body .= "<th width='10%' class='toptext'><strong>".$this->lang->word('visits')."</strong></th>\n";
		$body .= "</tr>\n";
		$body .= "</table>\n";
		
		// dynamic output for table
		$body .= "<table cellpadding='0' cellspacing='0' border='0'>\n";
		if (!$rs)
		{
			$body .= tr(td('No hits yet', "colspan='2'"));
		}
		else
		{
			foreach($rs as $ar) {
			$body .= tr(
				td($ar['stor_url'],"width='90%' class='cell-doc'").
				td($ar['stor_count'],"width='10%' class='cell-mid'"),
					row_color(" class='color'"));
			}
		}
		// end dynamic rows output
		$body .= "</table>\n";
		
		$this->template->body = $body;
		$this->template->output('index');
		exit;
	}
	
	
	public function sbmt_del_alt()
	{
		global $go;

		$this->abstracts->abstract_delete('statistics', 'system', 0);
		$this->abstracts->abstract_delete('stats_override', 'system', 0);
		
		system_redirect("?a=$go[a]");
		exit;
	}
	
	public function sbmt_add_alt()
	{
		$processor =& load_class('processor', TRUE, 'lib');
		
		$code = $processor->process('code', array('nophp'));

		$this->abstracts->abstract_create($code, 'statistics', 'system', 0);
		$this->abstracts->abstract_create(1, 'stats_override', 'system', 0);
	}
	
	public function sbmt_upd_alt()
	{
		$processor =& load_class('processor', TRUE, 'lib');
		
		$code = $processor->process('code', array('nophp'));

		$this->abstracts->abstract_update($code, 'statistics', 'system', 0);
	}
	
	
	public function page_override()
	{
		global $go;

		//if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		
		$this->template->location = $this->lang->word('main');

		$this->template->add_js('jquery.js');

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."users 
			WHERE ID = '".$this->access->prefs['ID']."'");

		load_helper('output');
		load_module_helper('files', $go['a']);
		
		$body = $this->toggler();

		$body .= "<div class='c1 bg-grey corners'>\n";
		$body .= "<h3 style='margin-bottom: 18px;'>This page is still a work-in-progress.</h3>\n";

		$body .= "<div class='col'>\n";
		$body .= p(label('Javascript Override ' . span('Insert here')));
		$body .= p(textarea($this->abstracts->abstract['statistics'], "style='width: 700px; height: 250px;'", 'code'));
		$body .= "<div>\n";
		$body .= "<input type='submit' name='del_alt' value='Delete' /> \n";
		
		$body .= (isset($this->abstracts->abstract['statistics'])) ?
			"<input type='submit' name='upd_alt' value='Save' />\n" :
			"<input type='submit' name='add_alt' value='Add' />\n";
			
		$body .= "</div>\n";
		$body .= "</div>\n";

		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";

		$this->template->body = $body;
		$this->template->output('index');
		exit;
	}
	
	///////////////// END NEW STATISTICS
	
	
	public function page_profile()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location = $this->lang->word('profile');
		
		// sub-locations
		//$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]");
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * FROM " . PX . "profile 
			INNER JOIN " . PX . "settings ON adm_id='1' 
			WHERE pr_id = '1'");
		
		$this->template->add_js('jquery.js');
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = "<link href='http://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.11/themes/flick/jquery-ui.css' rel='stylesheet' type='text/css' /><style type='text/css'>
		.ui-menu .ui-menu-item a,.ui-menu .ui-menu-item a.ui-state-hover, .ui-menu .ui-menu-item a.ui-state-active {
			font-weight: normal;
			margin: -1px;
			text-align:left;
			font-size:14px;
			}
		.ui-autocomplete-loading { background: white url('/images/ui-anim_basic_16x16.gif') right center no-repeat; }
		</style>
		<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js'></script>
		<script type='text/javascript'>
		public function getcitydetails(fqcn) {

			if (typeof fqcn == 'undefined') fqcn = jQuery('#f_elem_city').val();

			cityfqcn = fqcn;

			if (cityfqcn) {

			 jQuery.getJSON(
					'http://gd.geobytes.com/GetCityDetails?callback=?&fqcn='+cityfqcn,
				 function (data) {
							 jQuery('#hpr_country').val(data.geobytescountry);
							 jQuery('#hpr_region_id').val(data.geobytesregionlocationcode);
							 jQuery('#hpr_region').val(data.geobytesregion);
							 jQuery('#hpr_city').val(data.geobytescity);
				 }
			 );
			}
		}
		jQuery(function () 
		 {
			 jQuery('#f_elem_city').autocomplete({
				source: function (request, response) {
				 jQuery.getJSON(
					'http://gd.geobytes.com/AutoCompleteCity?callback=?&q='+request.term,
					public function (data) {
					 response(data);
					}
				 );
				},
				minLength: 3,
				select: function (event, ui) {
				 var selectedObj = ui.item;
				 jQuery('#f_elem_city').val(selectedObj.value);
				 getcitydetails(selectedObj.value);
				 return false;
				},
				open: function () {
				 jQuery(this).removeClass('ui-corner-all').addClass('ui-corner-top');
				},
				close: function () {
				 jQuery(this).removeClass('ui-corner-top').addClass('ui-corner-all');
				}
			 });
			 jQuery('#f_elem_city').autocomplete('option', 'delay', 100);
			});
		</script>";
		
		$body .= $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		
		$body .= "<div class='c2'>\n";
		$body .= "<div class='col'>\n";
		$body .= "<h2>" . $rs['obj_name'] . "</h2>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "<div class='cl'><!-- --></div>\n";
		
		$body .= "<div class='c4'>\n";
		
		$body .= "<h2 style='margin: 0 0 18px 5px;'>Tell us about yourself. Explain this...</h2>";
		
		// First column
		$body .= "<div class='col'>\n";
		
		// company name
		$body .= ips($this->lang->word('name'), 'input', 'pr_name', $rs['pr_name'], "maxlength='100'", 'text', $this->lang->word('required'), 'req');

		// job title
		$body .= ips($this->lang->word('profession'), 'input', 'pr_title', $rs['pr_title'], "maxlength='50'", 'text');
		
		//$body .= div(p(input('upd_profile', 'submit', null, $this->lang->word('update'))), "");
		
		//$body .= "</div>\n";
		
		//$body .= "<div class='col' style='width: 410px;'>\n";
		
		// city
		//$body .= ips($this->lang->word('city'), 'input', 'pr_city', $rs['pr_city'], "maxlength='150'", 'text');
		$body .= "<label>City</label><input class='ff_elem' type='text' name='ff_nm_from[]' value='' id='f_elem_city'/>";
		
		$body .= "<input type='hidden' name='hpr_city' value='' id='hpr_city'/>";
		$body .= "<input type='hidden' name='hpr_country' value='' id='hpr_country'/>";
		$body .= "<input type='hidden' name='hpr_region' value='' id='hpr_region'/>";
		$body .= "<input type='hidden' name='hpr_region_id' value='' id='hpr_region_id'/>";
		
		// address 1
		$body .= ips($this->lang->word('organization'), 'get_yes_no', 'pr_freelance', $rs['pr_freelance']);
		
		// address 2
		$body .= ips($this->lang->word('student'), 'get_yes_no', 'pr_freelance', $rs['pr_freelance']);
		
		// freelance status
		$body .= ips($this->lang->word('freelance'), 'get_yes_no', 'pr_freelance', $rs['pr_freelance']);
		$body .= "</div>\n";
		
		//$body .= "<div class='col' style='width: 410px;'>\n";
		//$body .= label($this->lang->word('description')) . br();
		//$body .= textarea(stripForForm($rs['pr_desc'], 1), "style='width: 405px; height: 205px;'", 'pr_desc');
		//$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	

	public function page_utilities()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location = $this->lang->word('utilities');
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."objects_prefs 
			WHERE obj_ref_type = 'exhibit'");
			
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= $this->toggler();
		
		$body .= "<div class='bg-grey'>\n";
		$body .= "<div class='c3'>\n";
		
		if (upgrades() == true) $body .= p('Upgrade.');
		
		// First column
		$body .= "<div class='col'>\n";
		$body .= label($this->lang->word('current version')) . br();
		$body .= "<h2>v" . VERSION . "</h2>\n";
		
		$body .= "</div>\n";
		
		// second column
		$body .= "<div class='col'>\n";
		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	
	
	public function page_tagprefs()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->pop_location = $this->lang->word('tag preferences');
		
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		$this->template->add_js('jquery.js');
		$this->template->add_js('jquery.facebox.js');
		$this->template->add_js('tags.js');
		$this->template->add_css('jquery.facebox.css');
		$this->template->onready[] = "jQuery('a[rel*=facebox]').facebox();";
		
		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';
var baseurl = '" . BASEURL . "';";
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		$tag = $this->db->fetchRecord("SELECT * FROM ".PX."objects_prefs WHERE obj_ref_type='tag'");
		$settings = unserialize($tag['obj_settings']);
		
		// if this format has an entry...
		$check = $this->db->fetchRecord("SELECT * FROM ".PX."plugins 
			WHERE pl_file = 'format." . $settings['format'] . ".php'");
			
		if ($check)
		{
			// preferences enabled 
			$prefs = "<p style='margin: 2px 0 12px;'><a href='?a=$go[a]&q=formats&x=popedit&id=$check[pl_id]'>" . $this->lang->word('format preferences') . "</a></p>\n\n";
		}
		else
		{
			// enable the preferences
			$prefs = "<p style='margin: 2px 0 12px;'><a href='?a=system&q=formats&x=enable&file=format." . $settings['format'] . ".php'>" . $this->lang->word('format preferences') . "</a></p>";
		}
		
		$body = ips($this->lang->word('section'), 'getSection', 'tag[section_id]', $settings['section_id']);
		$body .= input('h_secid', 'hidden', null, $settings['section_id']);
			
		$body .= ips($this->lang->word('template'), 'getTemplate', 'tag[template]', $settings['template']);
		
		$body .= "<label>" . $this->lang->word('exhibition format') . "</label>\n";
		$body .= getTagPresent(DIRNAME . '/ndxzsite/plugin/', $settings['format']);
		$body .= $prefs;
		$body .= label($this->lang->word('thumb max') . showHelp($this->lang->word('thumb max')));
		$body .= getThumbSize($settings['thumbs'], "class='listed' id='ajx-thumbs'");
		$body .= label($this->lang->word('thumbs shape') . showHelp($this->lang->word('thumbs shape')));
		$body .= getImageShape($settings['thumbs_shape'], "");
		$body .= ips($this->lang->word('image break'), 'get_break', 'tag[break]', $settings['break']);
		$body .= ips($this->lang->word('titles'), 'get_yes_no', 'tag[titling]', $settings['titling']);
		
		$body .= "<div class='buttons'>";
				
		$body .= button('upd_tsettings', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		
		$this->template->body = $body;
		$this->template->output('popup');
		exit;
	}
	
	
		public function page_edittags()
		{
			global $go, $default;

			if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

			$this->template->pop_location = $this->lang->word('edit tag');
			
			$this->template->pop_links[] = array($this->lang->word('back'), "?a=system&q=showtag&id=" . (int) $go['id'] . "");

			$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
			
			$this->template->ex_js[] = "var action = '$go[a]';
		var ide = '$go[id]';";

			$this->template->add_js('jquery.js');
			
			if ($this->reset == true)
			{
				// issue the reset commands
				// reload the tags list at the parent
				$this->template->onready[] = "parent.reload_master_tags();";
			}
			
			$this->template->add_js('jquery.facebox.js');
			$this->template->add_js('tags.js');
			$this->template->add_css('jquery.facebox.css');
			$this->template->onready[] = "jQuery('a[rel*=facebox]').facebox();";
			$this->template->onready[] = "$('#select-tag').change(function() { var select = $('#select-tag').val(); location.href = location.href + '&id=' + select; });";

			$this->template->ex_js[] = "var action = '$go[a]';
	var ide = '$go[id]';
	var baseurl = '" . BASEURL . "';";

			$body = (isset($this->error)) ?
				div($this->error_msg,"id='show-error'").br() : '';

			load_module_helper('files', $go['a']);
			load_helpers(array('editortools', 'output'));

			$tag = $this->db->fetchRecord("SELECT * FROM ".PX."objects_prefs WHERE obj_ref_type='tag'");
			$settings = unserialize($tag['obj_settings']);

			// +++++++++++++++++++++++++++++++++++++++++++++++++++

			// First column
			$body = "<div class='col'>\n";

			if ($go['id'] != 0)
			{
				$tag = $this->db->fetchRecord("SELECT * FROM ".PX."tags 
					WHERE tag_id = '$go[id]'");
					
				if ($tag)
				{
					//$body .= $tag['tag_name'];
					
					$body .= ips($this->lang->word('tag name'), 'input', 'tag_name', $tag['tag_name'], "maxlength='50'", 'text', $this->lang->word('required'), 'req');
					
					$body .= "<div class='buttons'>";

					$body .= button('del_tag', 'button', "class='general_delete' onclick=\"location.href = baseurl + '/ndxzstudio/?a=system&q=deltag&id=$go[id]'\"", $this->lang->word('delete'));

					$body .= button('edit_tag', 'submit', "class='general_submit'", $this->lang->word('update'));

					$body .= "</div>\n";
				}
			}
			else
			{
				// show all tags
				$this->lib_class('tag');
				$this->tag->get_all_tags();

				if ($this->tag->tags)
				{
					$body .= "<select id='select-tag'>\n";
					$body .= "<option value=''>Select</option>";

					// rewrite the array
					foreach ($this->tag->tags as $tag)
					{
						$selected = ($go['id'] == $tag['tag_id']) ? " selected='selected'" : '';
						$body .= "<option value='$tag[tag_id]'$selected>$tag[tag_name]</option>";
					}

					$body .= "</select>";
				}
			}
			
			$body .= "</div>\n";

			$body .= "<div class='cl'><!-- --></div>\n";
			$body .= "</div>";

			$this->template->body = $body;
			$this->template->output('popup');
			exit;
		}
	
	
	
	public function page_tag()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location = $this->lang->word('tags');

		$this->template->sub_location[] = array($this->lang->word('tag preferences'),
			"?a=$go[a]&q=tagprefs", "rel=\"facebox;width=900;height=500;modal=true\"");
		
		$this->template->add_js('jquery.js');
		$this->template->add_js('jquery.facebox.js');
		$this->template->add_js('tags.js');
		$this->template->add_css('jquery.facebox.css');
		$this->template->onready[] = "jQuery('a[rel*=facebox]').facebox();";
		
		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';
var baseurl = '" . BASEURL . "';";
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		$tag = $this->db->fetchRecord("SELECT * FROM ".PX."objects_prefs WHERE obj_ref_type='tag'");
		$settings = unserialize($tag['obj_settings']);
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		$body .= "<div class='c3'>\n";
		
		$body .= "<div style='margin: 5px 5px 27px 5px;'>\n";
		
		$body .= "<div style='float: left; width: 200px;'>\n";
		$body .= "<h3 style='margin-bottom: 9px;'>" . $this->lang->word('Tags Management') . "</h3>\n";
		$body .= "</div>\n";
		
		$body .= "<div style='cursor: pointer; float: right; width: 300px;'>\n";
		$body .= "<p style='margin: 0;'><strong>" . $this->lang->word('Add Tags') . "</strong> <small>" . $this->lang->word("Separate multiple tags with a comma ','.") . "</small></p>\n";
		$body .= input('add_tag', 'text', "id='new_tag' style='display: inline; width: 200px;'", null);
		$body .= "<input type='hidden' name='tag_group' id='tag_group' value=\"1\" /> ";
		$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"add_master_tags('img'); return false;\"", $this->lang->word('submit'));
		$body .= "</div>\n";
		
		$body .= "<div style='clear: both;'><!-- --></div>\n";	
		$body .= "</div>\n";

		$body .= "<div class='col' style='width: 850px; min-height: 400px;'>\n";

		// show all tags
		$this->lib_class('tag');
		$body .= $this->tag->get_all_tags_count();

		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	
	
	public function page_deltag()
	{
		$OBJ =& get_instance();
		global $go, $default;

		$this->template->js[] = "jquery.js";

		load_module_helper('files', $go['a']);

		$this->template->pop_location = $this->lang->word('tagged');

		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

		$this->template->ex_js[] = "var action = '$go[a]';
	var ide = '$go[id]';";
	
		if ($this->reset == true)
		{
			// issue the reset commands
			// reload the tags list at the parent
			$this->template->onready[] = "parent.reload_master_tags();";

			// close the window
			$this->template->onready[] = "parent.faceboxClose(); return false;";
		
			$this->template->body = '';
			$this->template->output('popup');
			exit;
		}

		$body = "<div class='c3'>\n";
		
		// First column
		$body .= "<div class='col'>\n";
		
		// need to get the tag info directly
		$tagged = $this->db->fetchRecord("SELECT * FROM ".PX."tags WHERE tag_id = '$go[id]'");
		
		$body .= "<h3 style='margin-bottom: 9px;'>Tag: $tagged[tag_name]</h3>\n";
		
		$body .= p(label("Merge Tag"));
		
		$body .= p("This action will merge all of this tags associations with the selected tag and delete the current tag. This action can not be undone.");
		
		// we put the select here
		$this->lib_class('tag');
		$this->tag->get_all_tags();
		
		if ($this->tag->tags)
		{
			$body .= "<select name='select-tag'>\n";

			foreach ($this->tag->tags as $tag)
			{	
				if ($tag['tag_id'] != $go['id'])
				{
					$body .= "<option value='$tag[tag_id]'>$tag[tag_name]</option>\n";
				}
			}
			
			$body .= "</select>\n";
		}
		
		$body .= "<div class='buttons' style='display: block; margin-bottom: 24px;'>";
		$body .= button('merge_tag', 'submit', "class='general_submit' onclick=\"return confirm('Are you sure?');\"", $this->lang->word('merge'));
		$body .= "</div>\n";
		
		$body .= br(3);
		
		$body .= p(label("Delete Tag"));
		
		$body .= p("Deleting will permanently remove this tag and all associations. This action can not be undone.", "style='color: red;'");
		
		$body .= "<div class='buttons' style='display: block; margin-bottom: 24px;'>";
		$body .= button('del_tag', 'submit', "class='general_delete' onclick=\"return confirm('Are you sure?');\"", $this->lang->word('delete'));
		$body .= "</div>\n";
		
		$body .= "<input type='hidden' name='the-tag' value='$go[id]' />\n";
		
		$body .= "</div>\n";
		$body .= "<div style='clear: left;'><!-- --></div>\n";
		$body .= "</div>\n";

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}
	
	
	public function page_showtag()
	{
		$OBJ =& get_instance();
		global $go, $default;

		$this->template->js[] = "jquery.js";

		load_module_helper('files', $go['a']);

		$this->template->pop_location = $this->lang->word('tagged');

		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

		$this->template->ex_js[] = "var action = '$go[a]';
	var ide = '$go[id]';";

		$body = '';
		
		// implement the interface
		$class = 'filesource' . $default['filesource'][4];
		$F =& load_class($class, true, 'lib');
		//$F->rs = $rs;
		
		// need to get the tag info directly
		$tagged = $this->db->fetchRecord("SELECT * FROM ".PX."tags WHERE tag_id = '$go[id]'");
		
		$body .= "<h3 style='margin-bottom: 9px;'>" . $this->lang->word('Tagged:') . " $tagged[tag_name]</h3>\n";
		
		$body .= "<p style='margin-bottom: 12px;'>";
		$body .= "<a href='?a=system&q=showuntag&id=$go[id]'>" . $this->lang->word('Show Untagged') . "</a> ";
		$body .= "<a href='?a=system&q=edittags&id=$go[id]' title='Edit'>" . $this->lang->word('Edit Tag') . "</a>";
		$body .= "</p>";
		
		// get our output
		$body .= $F->getTagged($go['id']);

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}
	
	
	public function page_showuntag()
	{
		$OBJ =& get_instance();
		global $go, $default;

		$this->template->js[] = "jquery.js";

		load_module_helper('files', $go['a']);

		$this->template->pop_location = $this->lang->word('untagged');

		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

		$this->template->ex_js[] = "var action = '$go[a]';
	var ide = '$go[id]';";

		$body = '';
		
		// implement the interface
		$class = 'filesource' . $default['filesource'][4];
		$F =& load_class($class, true, 'lib');

		// need to get the tag info directly
		$tagged = $this->db->fetchRecord("SELECT * FROM ".PX."tags WHERE tag_id = '$go[id]'");
		
		$body .= "<h3 style='margin-bottom: 9px;'>" . $this->lang->word('UnTagged:') . " $tagged[tag_name]</h3>\n";
		
		$body .= "<p style='margin-bottom: 12px;'><a href='?a=system&q=showtag&id=$go[id]'>" . $this->lang->word('Show Tagged') . "</a> <span>" . $this->lang->word('Click thumbnail(s) below to automatically tag file') . "</span></p>";
		
		// get our output
		$body .= $F->getUnTagged($go['id']);

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}
	
	
	public function page_options()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location = $this->lang->word('options');
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= $this->toggler();
		
		$body .= "<div>";
		$body .= "<div class='bg-grey corners'>\n";
		
		$body .= "<div style='padding: 5px;'>\n";
		$body .= "<input type='submit' name='upd_backup' value='Backup Site SQL' /> ";
		$body .= " <input type='submit' name='clear_cache' value='Clear Site Cache' /> ";
		$body .= " <input type='submit' name='clear_dimgs' value='Clear Dynamic Images' />";
		$body .= "</div>\n";
		
		$body .= "<div class='c3'>\n";
		
		// First column
		$body .= "<div class='col'>\n";
		
		$body .= ips($this->lang->word('use passwords'), 'get_yes_no', 'site[passwords]', $this->vars->site['passwords']);
		
		$body .= ips($this->lang->word('use templates'), 'get_yes_no', 'site[templates]', $this->vars->site['templates']);
		
		$body .= ips($this->lang->word('use tags'), 'get_yes_no', 'site[tags]', $this->vars->site['tags']);
		
		$body .= ips($this->lang->word('caching'), 'get_yes_no', 'caching', $this->vars->settings['caching']);
		
		$body .= "<div class='buttons'>";
				
		$body .= button('upd_options', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		$body .= "<div>";
		
		$this->template->body = $body;
		
		return;
	}
	
	
	public function page_tsearch()
	{
		$this->template->pop_location = $this->lang->word('tag search');

		$this->lib_class('tag');
		$this->tag->active_tags = '';
		$this->tag->method = 'exh';

		$body = "<div id='tag-box' style='display:block; padding:6px 0;'>\n";
		$body .= div($this->tag->get_tags_search(), "id='tag-holder'");
		$body .= "</div>\n";
		
		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}


		public function page_sections()
		{
			global $go;

			if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
			
			if ($go['id'] >= 1) { $this->page_section(); }

			$this->template->location_override = $this->lang->word('system');
			$this->template->location = $this->lang->word('sections');

			$this->template->add_js('jquery.js');
			//$this->template->add_js('ui.dimensions.js');
			$this->template->add_js('ui.core.js');
			$this->template->add_js('ui.sortable.js');
			$this->template->module_js[] = 'settings.js';

			$this->template->onready[] = "apply_sort();";

			$this->template->ex_css[] = "ul#sizes { list-style-type: none; }
	ul#sizes li { padding: 3px; border-bottom: 1px solid #ccc; position: relative; }
	.size-hover { background: #fff; height: 21px; }
	.hovering { background: #fff; }
	.dragging { background: #fff; }";

			// ++++++++++++++++++++++++++++++++++++++++++++++++++++

			// the record
			$rs = $this->db->fetchRecord("SELECT * 
				FROM ".PX."users 
				WHERE ID = '".$this->access->prefs['ID']."'");

			load_helper('output');
			load_module_helper('files', $go['a']);

			$body = $this->toggler();

			$body .= "<div class='c1 bg-grey corners'>\n";

			$body .= "<div class='col'>\n";
			$body .= getSections();
			$body .= "</div>\n";

			$body .= "<div class='cl'><!-- --></div>";
			$body .= "</div>";

			$this->template->body = $body;

			return;
		}
	
	
	public function page_section()
	{
		global $go, $default;

		$this->template->location = $this->lang->word('section');
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."sections 
			WHERE secid = '$go[id]'");
			
		$this->template->ex_js[] = "var ide = $go[id];";
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		$body .= "<div class='c3'>\n";
		
		$body .= "<div style='margin: 5px;'>\n";
		$body .= "<label>" . $this->lang->word('path') . "</label>";
		$body .= "<h2>" . BASEURL . "$rs[sec_path]</h2>" . br();
		$body .= "</div>\n";
		
		// First column
		$body .= "<div class='col'>\n";
		
		$body .= ips($this->lang->word('section name'), 'input', 'sec_desc', 
			$rs['sec_desc'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
			
		$body .= ips($this->lang->word('folder name'), 'input', 'section', 
			$rs['section'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
			
		$body .= ($default['subdir'] == true) ? ips($this->lang->word('section path'), 'getSectionPrepend', 'sec_prepend', $rs['sec_path']) : input('sec_prepend', 'hidden', NULL, '/');
		
		$body .= ips($this->lang->word('section hide'), 'getGeneric', 'sec_hide', $rs['sec_hide']);
		
		$proj = ($rs['sec_group'] >= 1) ? $rs['sec_proj'] . '.' . $rs['sec_group'] : $rs['sec_proj'];
		
		$body .= ips($this->lang->word('section organization'), 'get_section_type', 'sec_proj', $proj);
		
		$body .= ips($this->lang->word('section password'), 'input', 'sec_pwd', 
			$rs['sec_pwd'], "maxlength='32'", 'text');
			
		// ADVANCED!
		$body .= ips($this->lang->word('section object'), 'get_section_object', 'sec_obj', $rs['sec_obj']);
		
		$body .= "<div class='buttons'>";

		if ($rs['secid'] != 1)
		{
			// let's run a check on this
			$exhibits = $this->db->fetchArray("SELECT id FROM ".PX."objects 
				WHERE section_id = '$go[id]' 
				AND section_top != '1'");
			
			if (!$exhibits)
			{
				$body .= button('del_sec', 'submit', "class='general_delete' onclick=\"javascript:return confirm('" . $this->lang->word('sure delete section') . "');return false;\"", $this->lang->word('delete'));
			}
			else
			{
				$body .= button('del_sec', 'submit', "class='general_delete' onclick=\"alert('" . $this->lang->word('you need to delete/move all exhibits/pages from this section first.') . "');return false;\"", $this->lang->word('delete'));
			}
		}
		
		$body .= button('edit_sec', 'submit', "class='general_submit'", $this->lang->word('update'));
		
		$body .= "</div>\n";
		
		$body .= input('hsecid', 'hidden', NULL, $rs['secid']);
		$body .= input('hsec_ord', 'hidden', NULL, $rs['sec_ord']);
		
		// we aren't really using this though
		$new_section = explode('/', $rs['sec_path']);
		array_pop($new_section);
		$new_section = preg_replace("/\/\//", '/', '/' . implode('/', $new_section));
		$body .= input('hsec_path', 'hidden', NULL, $new_section);
			
		$body .= "</div>\n";
		
		// this is where we display subdirectories if they exist
		// enable later
		if ($rs['sec_proj'] == 0)
		{
			// second column
			$this->lib_class('subdirs');
			$this->subdirs->secid = $this->vars->route['id'];
		
			$body .= "<div class='col' style='width: 600px;'>\n";
			$body .= "<label style='display: block; margin-bottom: 6px;'>Subsections</label>\n";
			$body .= "<div id='thesubsections' style='margin-bottom: 18px;'>\n";
			$body .= $this->subdirs->getSubs($rs['secid']);
			$body .= "</div>\n";
			
			$body .= "<div>\n";
			$body .= "<p><a href='#' onclick=\"$('#addsubdirs').toggle(); return false;\">Add Subsection</a></p>\n";
			$flag = ($rs['sec_subs'] != '') ? 1 : 0;
			$body .= "<div id='addsubdirs' style='display: none;'>\n";
			$body .= $this->subdirs->create_subdir($flag);
			$body .= "</div>\n";
			$body .= "</div>\n";
			$body .= "</div>\n";
		
			//$body .= "<div class='cl'><!-- --></div>\n";
			//$body .= "</div>";
		}
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		
		$this->template->body = $body;
		$this->template->output('index');
		exit;
	}
	
	
	public function page_formats()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
			
		if ($go['x'] == 'add') { $this->page_extensions_add(); }
		if ($go['x'] == 'enable') { $this->page_format_enable(); }
		if ($go['x'] == 'disable') { $this->page_format_disable(); }
		if ($go['x'] == 'popedit') { $this->page_format_popedit(); }
		if ($go['x'] == 'edit') { $this->page_format_edit(); }
		if ($go['id'] >= 1) { $this->page_extension(); }

		$this->template->location_override = $this->lang->word('system');
		$this->template->location = $this->lang->word('extensions');

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		$this->template->add_js('jquery.facebox.js');
		$this->template->add_css('jquery.facebox.css');
		$this->template->onready[] = "jQuery('a[rel*=facebox]').facebox();";

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// the record
		$rs = $this->db->fetchArray("SELECT * 
			FROM ".PX."plugins 
			WHERE pl_type = 'format' 
			ORDER BY pl_file ASC, pl_name ASC");
		
		// rewrite the array	
		if ($rs)
		{
			foreach ($rs as $rw)
			{
				$plugin[$rw['pl_file']] = array('pl_id' => $rw['pl_id'], 'pl_file' => $rw['pl_file'], 'pl_options' => $rw['pl_options'], 'pl_options_build' => $rw['pl_options_build']);
			}
		}
		
		$override_formats = $this->hook->get_format_header(DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/');
		/*
		[0] => Array
		        (
		            [filename] => format.visual_index.php
		            [name] => Visual Index Customized
		            [title] => Visual Index Customized
		            [pluginURI] => http://www.indexhibit.org/format/visual-index/
		            [description] => Default Indexhibit format.
		            [author] => Indexhibit
		            [authorURI] => http://indexhibit.org/
		            [version] => 1.0
		            [options] => default_settings
		            [params] => format,images,thumbs,shape,placement,break,titling
		            [objects] => 
		        )
		*/

		// need to rewrite the array for easier use
		if ($override_formats)
		{
			foreach ($override_formats as $formatss)
			{
				$check1[$formatss['filename']] = $formatss;
			}
		}
		else
		{
			$check1 = array();
		}

		$formats = $this->hook->get_format_header(DIRNAME . '/ndxzsite/plugin/');
		
		// need to rewrite the array for easier use
		if ($formats)
		{
			foreach ($formats as $format)
			{
				if (!isset($check1[$format['filename']]))
				{
					$check2[$format['filename']] = $format;
				}
			}
		}
		else
		{
			$check2 = array();
		}
		
		$formats = array_merge($check1, $check2);

		load_helper('output');
		load_module_helper('files', $go['a']);

		$body = $this->toggler();

		$body .= "<div class='c1 bg-grey corners'>\n";

		$body .= "<div class='col' style='width: 870px;'>\n";
		
		// if there are uninstall extensions
		//$body .= $this->extensions_add_check();
		////////////////////////////////////////////////////////////////////////////////////
		
		$b = "<h3 style='background: none; margin: 0 0 0 3px; border-bottom: 1px solid #ccc;'>" . $this->lang->word('installed exhibition formats') . "</h3>\n";
		$b .= "<div style='background: #fff;'>\n";
		$b .= "<table class='modtable'>\n";
		$b .= "<tr>\n";
		$b .= th($this->lang->word('name') . ' / ' . $this->lang->word('description'), "width='50%' class='toptext'");
		$b .= th($this->lang->word('creator'), "width='20%' class='toptext'");
		$b .= th("&nbsp;", "width='7%' class='toptext cell-doc'");
		$b .= th("&nbsp;", "width='8%' class='toptext cell-doc'");
		$b .= th('&nbsp;', "width='15%' class='toptext txt-right'");
		$b .= "</tr>\n";
		$b .= "</table>\n";

		$b .= "<table class='modtable'>\n";
		
		$row = '';
		
		if ($formats)
		{
			foreach ($formats as $key => $format)
			{
				//print_r($format); exit;

				$b .= "<tr".row_color(" class='color'")." valign='top'>\n";
				
				// bring this back later
				$namer = ($format['pluginURI'] == '') ? $format['name'] : href($format['name'], $format['pluginURI'], "target='_new'");
				$namer = $format['name'];
				
				$tmp = array();
				
				if ($format['params'] == '')
				{
					$yp = '';
				}
				else
				{
					$p = explode(',', $format['params']);
					
					foreach ($p as $ps)
					{
						$tmp[] = trim($ps);
					}
				}
		
				$b .= td(strong($namer) . br() . $format['description'], "width='50%' class='cell-doc'");
				
				// use this later - it has the author link setup
				//$linky = ($format['authorURI'] == '') ? $format['author'] : href($format['author'], $format['authorURI'], "target='_new'");
				
				$linky = $format['author'];
				
				$b .= td($linky, "width='20%' class='cell-doc'");
				$b .= td("&nbsp;", "width='7%' class='cell-doc'");
				$b .= td('&nbsp;', "width='8%' class='cell-doc'");

				if (!isset($plugin[$format['filename']]))
				{
					// check if options are possible via "options builder"
					$disable = '';
					
					if ($format['options'] == '')
					{
						$disable = '';

						$edit = $this->lang->word('no options');
					}
					else
					{	
						$edit = href($this->lang->word('enable'), "?a=$go[a]&q=formats&x=enable&file=" . $format['filename']);
					}
				}
				else
				{
					$disable = '';

					if ($plugin[$format['filename']]['pl_options'] == '')
					{
						// need to activate them
						$disable = ' ' . href($this->lang->word('disable'), "?a=$go[a]&q=$go[q]&x=disable&id=" . $plugin[$format['filename']]['pl_id'], "onclick=\"return confirm('" . $this->lang->word('are you sure?') . "');\"");
						
						$edit = href($this->lang->word('options'), "?a=$go[a]&q=$go[q]&x=edit&id=" . $plugin[$format['filename']]['pl_id'] . "");
					}
					else
					{
						$disable = ' ' . href($this->lang->word('disable'), "?a=$go[a]&q=$go[q]&x=disable&id=" . $plugin[$format['filename']]['pl_id'], "onclick=\"return confirm('" . $this->lang->word('are you sure?') . "');\"");
	
						// edit them
						$edit = href($this->lang->word('options'), "?a=$go[a]&q=$go[q]&x=edit&id=" . $plugin[$format['filename']]['pl_id'] . "");
					}
				}
				
				$b .= td($edit . $disable, 
					"width='15%' class='cell-doc txt-right'");
				$b .= "</tr>\n";
			}
		}
		else
		{
			$b .= "<tr><td colspan='4'>" . $this->lang->word('none found') . "</td></tr>\n";
		}
		
		$b .= "</table>\n";
		$b .= "</div>\n";
		$body .= $b;
		////////////////////////////////////////////////////////////////////////////////////
		$body .= "</div>\n";

		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";

		$this->template->body = $body;
		return;
	}
	
	
	public function page_plugins()
	{
		global $go;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
			
		if ($go['x'] == 'add') { $this->page_extensions_add(); }
		if ($go['x'] == 'enable') { $this->page_extensions_enable(); }
		if ($go['x'] == 'disable') { $this->page_extensions_disable(); }
		if ($go['x'] == 'edit') { $this->page_extensions_edit(); }
		if ($go['id'] >= 1) { $this->page_extension(); }

		$this->template->location_override = $this->lang->word('system');
		$this->template->location = $this->lang->word('extensions');
		
		// sub-locations
		//$this->template->sub_location[] = array($this->lang->word('add extensions'),"?a=$go[a]&q=extensions&x=add");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// the record
		$rs = $this->db->fetchArray("SELECT * 
			FROM ".PX."plugins 
			WHERE pl_primary = '1' 
			AND pl_type != 'format' 
			ORDER BY pl_file ASC, pl_name ASC");
		
		$installed = array();
		
		if ($rs) foreach ($rs as $rw) $installed[] = $rw['pl_file'];

		load_helper('output');
		load_module_helper('files', $go['a']);

		$body = $this->toggler();

		$body .= "<div class='c1 bg-grey corners'>\n";

		$body .= "<div class='col' style='width: 870px;'>\n";
		
		// if there are uninstall extensions
		//$body .= $this->extensions_add_check();
		
		//$formats = $this->hook->get_plugins_header(DIRNAME . '/ndxzsite/plugin/');
		
		$this->lib_class('plugins');
		$this->plugins->get_plugins_info();
		
		// is this right?
		if ($this->plugins->plugin) foreach ($this->plugins->plugin as $key => $frw) $available[] = $key;

		////////////////////////////////////////////////////////////////////////////////////
		
		$b = "<h3 style='background: none; margin: 0 0 0 3px; border-bottom: 1px solid #ccc;'>" . $this->lang->word('Installed Plugins') . "</h3>\n";
		$b .= "<div style='background: #fff;'>\n";
		$b .= "<table class='modtable'>\n";
		$b .= "<tr>\n";
		$b .= th($this->lang->word('name') . ' / ' . $this->lang->word('description'), "width='65%' class='toptext'");
		$b .= th('&nbsp;', "width='5%' class='toptext'");
		$b .= th($this->lang->word('creator'), "width='20%' class='toptext'");
		$b .= th('&nbsp;', "width='10%' class='toptext txt-right'");
		$b .= "</tr>\n";
		$b .= "</table>\n";

		$b .= "<table class='modtable'>\n";
		
		$row = '';
		
		if ($rs)
		{
			foreach ($rs as $key => $pg)
			{
				$file = str_replace(array('plugin.', '.php'), array('', ''), $pg['pl_file']);

				$b .= "<tr".row_color(" class='color'")." valign='top'>\n";
			
				$version = ($pg['pl_version'] == '') ? '' : ' v' . $pg['pl_version'];
				
				//$name = (isset($pg['pl_uri'])) ? href($pg['pl_name'], $pg['pl_uri'], "target='_new'") : $pg['pl_name'];
				$name = $pg['pl_name'];
				
				$b .= td(strong($name . $version) . ' ' . span("($pg[pl_file])", "class='gry-text sml-text'") . br(). $pg['pl_desc'], "width='65%' class='cell-doc'");
				
				$b .= td('&nbsp;', "width='5%' class='cell-doc'");
				
				//$creator = ($pg['pl_www'] != '') ? href($pg['pl_creator'], $pg['pl_www']) : $pg['pl_creator'];
				$creator = $pg['pl_creator'];
				
				$b .= td($creator, "width='20%' class='cell-doc'");
				
				$edit = ($pg['pl_options_build'] == '') ? '' :
					href($this->lang->word('options'), "?a=$go[a]&q=$go[q]&x=edit&id=$pg[pl_id]", "rel=\"facebox;width=900;height=500\"") . ' ';
					
				// for installation purposes
				$the_file = str_replace(array('plugin.', '.php'), array('', ''), $pg['pl_file']);
					
				$disable = href($this->lang->word('disable'), "?a=$go[a]&q=plugins&x=disable&file=$the_file", "onclick=\"return confirm('" . $this->lang->word('Are you sure?') . "');\"");
				
				$b .= td($edit . ' ' . $disable, "width='10%' class='cell-doc txt-right'");
				$b .= "</tr>\n";
			}
		}
		else
		{
			$b .= "<tr><td colspan='4'>" . $this->lang->word('None Found') . "</td></tr>\n";
		}
		
		$b .= "</table>\n";
		$b .= "</div>\n";
		//$body .= $b;
		////////////////////////////////////////////////////////////////////////////////////
		
		$b .= "<h3 style='background: none; margin: 36px 0 0 3px; border-bottom: 1px solid #ccc;'>" . $this->lang->word('Available Plugins for Installation') . "</h3>\n";
		$b .= "<div style='background: #fff;'>\n";
		//$b .= "<h3 style='background: none;'>Available Plugins for Installation</h3>\n";
		$b .= "<table class='modtable'>\n";
		$b .= "<tr>\n";
		$b .= th($this->lang->word('name') . ' / ' . $this->lang->word('description'), "width='65%' class='toptext'");
		$b .= th('&nbsp;', "width='5%' class='toptext'");
		$b .= th($this->lang->word('creator'), "width='20%' class='toptext'");
		//$b .= th('&nbsp;', "width='15%' class='toptext cell-middle'");
		$b .= th('&nbsp;', "width='10%' class='toptext txt-right'");
		$b .= "</tr>\n";
		$b .= "</table>\n";

		$b .= "<table class='modtable'>\n";
		
		////////////////////////////////////////////////////////////////////////
		// Not installed plugins
		if (!$this->plugins->plugin)
		{
			$b .= "<tr><td colspan='4'>" . $this->lang->word('None Found') . "</td></tr>\n";
		}
		else
		{
			foreach ($this->plugins->plugin as $key => $do)
			{
				// is it already installed?
				if (!in_array($key, $installed))
				{
					$b .= "<tr".row_color(" class='color'")." valign='top'>\n";
					
					$version = ($do['pl_version'] == '') ? '' : ' v' . $do['pl_version'];
					
					//$name = (isset($do['pl_uri'])) ? href($do['pl_name'], $do['pl_uri'], "target='_new'") : $do['pl_name'];
					$name = $do['pl_name'];
					
					$b .= td(strong($name . $version) . ' ' . span("($key)", "class='gry-text sml-text'") . br(). $do['pl_desc'], "width='65%' class='cell-doc'");
					
					$b .= td('&nbsp;', "width='5%' class='cell-doc'");
				
					//$creator = (isset($do['pl_www'])) ? href($do['pl_creator'], $do['pl_www']) : $do['pl_creator'];
					$creator = $do['pl_creator'];
					
					// for installation purposes
					$the_file = str_replace(array('plugin.', '.php'), array('', ''), $key);
				
					$b .= td($creator, "width='20%' class='cell-doc'");
					//$b .= td($do['pl_space'], "width='15%' class='cell-middle'");
					$b .= td(href($this->lang->word('enable'), "?a=$go[a]&q=plugins&x=enable&file=$the_file", "onclick=\"return confirm('" . $this->lang->word('Are you sure?') . "');\""), "width='10%' class='cell-doc txt-right'");
					$b .= "</tr>\n";
				}
			}
		}
		
		$b .= "</table>\n";
		$b .= "</div>\n";
		////////////////////////////////////////////////////////////////////////////

		$body .= $b;
		
		///////////////////////////////////////////////////////////////////////////////////
		$body .= "</div>\n";

		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";
		
		////////////////////////////////////////////////////////////////////////////
		

		$this->template->body = $body;
		return;
	}
	
	public function page_tag_edit()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		//$this->template->location_override = $this->lang->word('system');
		//$this->template->location = $this->lang->word('format');
		
		$this->template->pop_location = $this->lang->word('format settings');
		
		//$this->template->pop_links[] = array($this->lang->word('page settings'), "?a=$go[a]&q=settings&id=$go[id]", null);
		
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]&q=$go[q]");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		//$this->template->onready[] = "apply_sort();";

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."plugins 
			WHERE pl_id = '$go[id]'");
			
		if (!$rs) { system_redirect("?a=$go[a]&q=$go[q]"); }

		load_helpers(array('output', 'html'));
		load_module_helper('files', $go['a']);

		$body = "<div class='c4'>\n";
		
		$body .= "<div class='col'>\n";
		$body .= "<h2 style='margin-bottom: 9px;'>$rs[pl_name] $rs[pl_version]</h2>\n";
		$body .= "<p><strong>By $rs[pl_creator]</strong></p>\n";
		$body .= "<p>$rs[pl_desc]</p>\n";
		$body .= "</div>\n";
		
		$body .= "<div class='col'>\n";
		////////////////////////////////////////////////////////////////////////////////////

		// let's unpack our options builder
		// look into this stripslashes problem later..
		$build = ($rs['pl_options'] == '') ? array() : unserialize(stripslashes($rs['pl_options']));
		$opt = $rs['pl_options_build'];
		
		//echo DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']; exit;
		
		$path = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']))
			? DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']
			: DIRNAME . '/ndxzsite/plugin/' . $rs['pl_file'];
		
		// load the file
		include_once($path);
		
		// this is our method
		$f = $rs['pl_options_build'];
			
		$EXH = new Exhibit;
		$EXH->settings = $build;
		
		// we should probably add method exists
		$body .= $EXH->$f();
		
		$body .= "<div class='buttons'>";
				
		$body .= button('upd_format_edit', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		
		////////////////////////////////////////////////////////////////////////////////////

		$body .= "</div>\n";
		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";
		
		$this->template->body = $body;
		$this->template->output('popup');
		exit;
	}
	
	
	public function page_format_edit()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location_override = $this->lang->word('system');
		$this->template->location = $this->lang->word('format');
		
		$this->template->pop_location = $this->lang->word('format settings');
		
		//$this->template->pop_links[] = array($this->lang->word('page settings'), "?a=$go[a]&q=settings&id=$go[id]", null);
		
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]&q=$go[q]");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body = $this->toggler();

		$body .= "<div class='c4 bg-grey corners' style='min-height: 400px;'>\n";

		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."plugins 
			WHERE pl_id = '$go[id]'");
			
		if (!$rs) { system_redirect("?a=$go[a]&q=$go[q]"); }

		load_helpers(array('output', 'html'));
		load_module_helper('files', $go['a']);
		
		$body .= "<div class='col'>\n";
		$body .= "<h2 style='margin-bottom: 9px;'>$rs[pl_name] $rs[pl_version]</h2>\n";
		$body .= "<p><strong>By $rs[pl_creator]</strong></p>\n";
		$body .= "<p>$rs[pl_desc]</p>\n";
		$body .= "</div>\n";
		
		$body .= "<div class='col'>\n";
		////////////////////////////////////////////////////////////////////////////////////

		// let's unpack our options builder
		// look into this stripslashes problem later..
		$build = ($rs['pl_options'] == '') ? array() : unserialize(stripslashes($rs['pl_options']));
		$opt = $rs['pl_options_build'];
		
		//echo DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']; exit;
		
		$path = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']))
			? DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']
			: DIRNAME . '/ndxzsite/plugin/' . $rs['pl_file'];
		
		// load the file
		include_once($path);
		
		// this is our method
		$f = $rs['pl_options_build'];
			
		$EXH = new Exhibit;
		$EXH->settings = $build;
		
		// we should probably add method exists
		$body .= $EXH->$f();
		//$body .= "</div>\n";
		
		$body .= "<div class='buttons'>";
				
		$body .= button('upd_format_edit', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		
		////////////////////////////////////////////////////////////////////////////////////

		$body .= "</div>\n";
		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";

		$this->template->body = $body;
		$this->template->output('index');
		exit;
	}
	
	
	
	public function page_format_popedit()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		//$this->template->location_override = $this->lang->word('system');
		//$this->template->location = $this->lang->word('format');
		
		$this->template->pop_location = $this->lang->word('format settings');
		
		//$this->template->pop_links[] = array($this->lang->word('page settings'), "?a=$go[a]&q=settings&id=$go[id]", null);
		
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]&q=$go[q]");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		//$this->template->onready[] = "apply_sort();";

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."plugins 
			WHERE pl_id = '$go[id]'");
			
		if (!$rs) { system_redirect("?a=$go[a]&q=$go[q]"); }

		load_helpers(array('output', 'html'));
		load_module_helper('files', $go['a']);

		$body = "<div class='c4'>\n";
		
		$body .= "<div class='col'>\n";
		$body .= "<h2 style='margin-bottom: 9px;'>$rs[pl_name] $rs[pl_version]</h2>\n";
		$body .= "<p><strong>By $rs[pl_creator]</strong></p>\n";
		$body .= "<p>$rs[pl_desc]</p>\n";
		$body .= "</div>\n";
		
		$body .= "<div class='col'>\n";
		////////////////////////////////////////////////////////////////////////////////////

		// let's unpack our options builder
		// look into this stripslashes problem later..
		$build = ($rs['pl_options'] == '') ? array() : unserialize(stripslashes($rs['pl_options']));
		$opt = $rs['pl_options_build'];
		
		//echo DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']; exit;
		
		$path = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']))
			? DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']
			: DIRNAME . '/ndxzsite/plugin/' . $rs['pl_file'];
		
		// load the file
		include_once($path);
		
		// this is our method
		$f = $rs['pl_options_build'];
			
		$EXH = new Exhibit;
		$EXH->settings = $build;
		
		// we should probably add method exists
		$body .= $EXH->$f();
		
		$body .= "<div class='buttons'>";
				
		$body .= button('upd_format_edit', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		
		////////////////////////////////////////////////////////////////////////////////////

		$body .= "</div>\n";
		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";
		
		$this->template->body = $body;
		$this->template->output('popup');
		exit;
	}
	
	
	
	public function page_extensions_edit()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }

		$this->template->location_override = $this->lang->word('system');
		$this->template->location = $this->lang->word('extensions');
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]&q=$go[q]");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		//$this->template->onready[] = "apply_sort();";

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."plugins 
			WHERE pl_id = '$go[id]'");
			
		if (!$rs) { system_redirect("?a=$go[a]&q=$go[q]"); }

		load_helpers(array('output', 'html'));
		load_module_helper('files', $go['a']);

		$body = $this->toggler();

		$body .= "<div class='c4 bg-grey corners' style='min-height: 400px;'>\n";
		
		$body .= "<div class='col'>\n";
		$body .= "<h2 style='margin-bottom: 9px;'>$rs[pl_name] $rs[pl_version]</h2>\n";
		$body .= "<p><strong>By $rs[pl_creator]</strong></p>\n";
		$body .= "<p>$rs[pl_desc]</p>\n";
		$body .= "</div>\n";
		
		$body .= "<div class='col'>\n";
		
		////////////////////////////////////////////////////////////////////////////////////
		
		// let's unpack our options builder
		// look into this stripslashes problem later..
		$build = ($rs['pl_options'] == '') ? array() : unserialize(stripslashes($rs['pl_options']));
		$opt = $rs['pl_options_build'];
		
		//echo DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']; exit;
		
		$path = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']))
			? DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $rs['pl_file']
			: DIRNAME . '/ndxzsite/plugin/' . $rs['pl_file'];
		
		// load the file
		include_once($path);
		
		// let's get the class
		$c = explode(':', $rs['pl_function']);
		$class = $c[0];
		
		// this is our method
		$f = $rs['pl_options_build'];
			
		$EXH = new $class;
		$EXH->options = $build;
		
		// we should probably add method exists
		$body .= $EXH->$f();

		$body .= "<div class='buttons'>";
				
		$body .= button('upd_plugins_edit', 'submit', "class='general_submit'", $this->lang->word('update'));
				
		$body .= "</div>\n";
		
		////////////////////////////////////////////////////////////////////////////////////
		$body .= "</div>\n";
		
		/*
		$body .= "<div class='col'>\n";
		$body .= "<label>Plugin</label>\n";
		// we need to encode the value
		$body .= "<p><input type='text' value=\"$rs[pl_usage]\" /></p>\n";
		$body .= "<label>Usage Info</label>\n";
		$body .= "<p>$rs[pl_usage_desc]</p>\n";
		$body .= "</div>\n";
		*/

		$body .= "<div class='cl'><!-- --></div>";
		$body .= "</div>";

		$this->template->body = $body;
		$this->template->output('index');
		exit;
	}
	
	
	
	public function extensions_add_check()
	{
		global $go;

		// the record
		$rs = $this->db->fetchArray("SELECT pl_file  
			FROM ".PX."plugins 
			GROUP BY pl_file 
			ORDER BY pl_name ASC");
			
		// damn, rewrite the array
		$i = 0; $in_db = array();
		if ($rs)
		{
			foreach ($rs as $yes) { $in_db[] = $yes['pl_file']; $i++; }
		}
			
		//print_r($in_db); exit;

		load_helper('output');
		load_module_helper('files', $go['a']);

		$b = "";

		////////////////////////////////////////////////////////////////////////////////////
		// look forward to cleaning this up someday...
		// the point of this is so we can put more than one plugin per file
		
		// get all the extend files...which beging with 'extend'...
		$extensions = get_extensions_list(DIRNAME . '/ndxzsite/plugin/');
		$diff = array_diff($extensions, $in_db);
		
		$add = '';
		
		if (is_array($diff))
		{
			$i = 0;
			foreach ($diff as $gob)
			{
				$file = DIRNAME . '/ndxzsite/plugin/' . $gob;
				$fp = fopen($file, 'r');
				$plugin_data = fread($fp, 8192);
				fclose($fp);
				
				preg_match ( '|Plugin Name:(.*)$|mi', $plugin_data, $name );
				preg_match ( '|Plugin URI:(.*)$|mi', $plugin_data, $uri );
				preg_match ( '|Version:(.*)|i', $plugin_data, $version );
				preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
				preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
				preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
				
				foreach ( array ('name', 'uri', 'version', 'description', 'author_name', 'author_uri' ) as $field ) 
				{
					if (! empty ( ${$field} ))
						${$field} = trim ( ${$field} [1] );
					else
						${$field} = '';
				}
				
				$plugin_data = array ('filename' => $file, 'Name' => $name, 'Title' => $name, 'PluginURI' => $uri, 'Description' => $description, 'Author' => $author_name, 'AuthorURI' => $author_uri, 'Version' => $version );

				$plugins_header[] = $plugin_data;
			}
		}
		
		print_r($plugin_data); exit;
		
		// this means we are all installed - show nothing
		if (!isset($arr[0][0])) return;
		
		$b = "<div style='background: #fff;'>\n";
		$b .= "<table class='modtable'>\n";
		$b .= "<tr>\n";
		$b .= th($this->lang->word('name') . ' / ' . $this->lang->word('description'), "width='50%' class='toptext'");
		$b .= th($this->lang->word('creator'), "width='20%' class='toptext'");
		$b .= th('&nbsp;', "width='15%' class='toptext cell-middle'");
		$b .= th('&nbsp;', "width='15%' class='toptext txt-right'");
		$b .= "</tr>\n";
		$b .= "</table>\n";

		$b .= "<table class='modtable'>\n";
		
		if (is_array($arr))
		{	
			foreach ($arr as $key => $do)
			{
				$b .= "<tr".row_color(" class='color'")." valign='top'>\n";
				$b .= td(strong($do['pl_name']) . ' ' . span("($do[pl_file])", "class='gry-text sml-text'") . br(). $do['pl_desc'], "width='50%' class='cell-doc'");
				
				$creator = (isset($do['pl_www'])) ? href($do['pl_creator'], $do['pl_www']) : $do['pl_creator'];
				
				$the_file = str_replace(array('plugin.', '.php'), array('', ''), $key);
				
				$b .= td($creator, "width='20%' class='cell-doc'");
				$b .= td($do['pl_space'], "width='15%' class='cell-middle'");
				$b .= td(href($this->lang->word('enable'), "?a=$go[a]&q=extensions&x=enable&file=$the_file", "onclick=\"return confirm('Are you sure?');\""), "width='15%' class='cell-doc txt-right'");
				$b .= "</tr>\n";
			}
		}
		else
		{
			$b .= "<tr><td colspan='4'>None Found</td></tr>\n";
		}
		
		$b .= "</table>\n";

		return $b;
	}
	
	
	public function page_extensions_enable()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		
		// do some validating
		$file = (string) $_GET['file'];
		$the_file = 'plugin.' . $file . '.php';

		$this->template->location_override = $this->lang->word('system');
		$this->template->location = $this->lang->word('extensions');
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]&q=$go[q]");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// better check to see if it's already in the database
		$rs = $this->db->fetchRecord("SELECT pl_file  
			FROM ".PX."plugins 
			WHERE pl_file = '$the_file'");
			
		if ($rs) return;
		
		$this->lib_class('plugins');
		$this->plugins->file = $the_file;
		$this->plugins->get_plugin_header();
		
		if (is_array($this->plugins->plugin))
		{
			foreach ($this->plugins->plugin as $key => $plugin)
			{
				foreach ($plugin as $k => $p)
				{
					$p['pl_primary'] = ($k == 0) ? 1 : 0;
					$p['pl_file'] = $key;
					
					// we really need this?
					//$p = array_map('mysql_real_escape_string', $p);
					$last = $this->db->insertArray(PX.'plugins', $p);
					
					// if primary has options
					if ($k == 0)
					{
						$flag = ($p['pl_options_build'] != '') ? true : false;
						
						if ($flag == true)
						{
							$flagged_last = $last;
						}
					}
				}
			}
		}
		
		if ($flag == true)
		{
			system_redirect("?a=$go[a]&q=$go[q]&x=edit&id=$flagged_last");
		}
		else
		{
			system_redirect("?a=$go[a]&q=$go[q]");
		}
		
		exit;
	}
	
	
	
	public function page_format_enable()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		
		// do some validating
		$file = (string) $_GET['file'];
		//$the_file = 'format.' . $file . '.php';
		$the_file = $file;
		
		//echo $the_file; exit;

		$this->template->location_override = $this->lang->word('system');
		$this->template->location = $this->lang->word('extensions');
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]&q=$go[q]");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// better check to see if it's already in the database
		$rs = $this->db->fetchRecord("SELECT pl_file  
			FROM ".PX."plugins 
			WHERE pl_file = '$the_file'");
			
		$path = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $the_file))
			? DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/'
			: DIRNAME . '/ndxzsite/plugin/';
		
		// load the file
		include_once($path . '/' . $the_file);
		
		$plugin = $this->hook->get_format_header_single($path, $the_file);
		
		if (!$rs)
		{
			if (is_array($plugin))
			{
				$thefile = str_replace(array('format.', '.php'), array('', ''), $the_file);
				
				$p['pl_primary'] = 1;
				$p['pl_file'] = $plugin['filename'];
				$p['pl_type'] = 'format';
				$p['pl_name'] = $plugin['name'];
				$p['pl_uri'] = $plugin['pluginURI'];
				$p['pl_version'] = $plugin['version'];
				$p['pl_function'] = $thefile . '_settings';
				$p['pl_hook'] = $thefile;
				$p['pl_creator'] = $plugin['author'];
				$p['pl_www'] = $plugin['authorURI'];
				$p['pl_desc'] = $plugin['description'];
				$p['pl_options_build'] = $plugin['options'];
					
				// we really need this?
				//$p = array_map('mysqli_real_escape_string', $p);
					
				$last = $this->db->insertArray(PX.'plugins', $p); 
			}
		}

		system_redirect("?a=$go[a]&q=$go[q]&x=edit&id=$last");
		exit;
	}
	
	
	public function page_tag_enable()
	{
		$OBJ =& get_instance();
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		
		// do some validating
		$file = (string) $_GET['file'];
		$the_file = $file;
		
		//echo $the_file; exit;

		$this->template->location_override = $this->lang->word('system');
		$this->template->location = $this->lang->word('extensions');
		
		// sub-locations
		$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]&q=$go[q]");

		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';

		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		// better check to see if it's already in the database
		$rs = $this->db->fetchRecord("SELECT pl_file  
			FROM ".PX."plugins 
			WHERE pl_file = '$the_file'");
			
		$path = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/' . $the_file))
			? DIRNAME . '/ndxzsite/' . $OBJ->vars->settings['obj_theme'] . '/'
			: DIRNAME . '/ndxzsite/plugin/';
		
		// load the file
		include_once($path);
		
		$plugin = $this->hook->get_format_header_single($path, $the_file);
		
		if (!$rs)
		{
			if (is_array($plugin))
			{	
				$thefile = str_replace(array('format.', '.php'), array('', ''), $the_file);
					
				$p['pl_primary'] = 1;
				$p['pl_file'] = $plugin['filename'];
				$p['pl_type'] = 'format';
				$p['pl_name'] = $plugin['name'];
				$p['pl_uri'] = $plugin['pluginURI'];
				$p['pl_version'] = $plugin['version'];
				$p['pl_function'] = $thefile . '_settings';
				$p['pl_hook'] = $thefile;
				$p['pl_creator'] = $plugin['author'];
				$p['pl_www'] = $plugin['authorURI'];
				$p['pl_desc'] = $plugin['description'];
				$p['pl_options_build'] = $plugin['options'];
					
				// we really need this?
				$p = array_map('mysqli_real_escape_string', $p);
					
				$last = $this->db->insertArray(PX.'plugins', $p);
			}
		}

		system_redirect("?a=$go[a]&q=$go[q]&x=tagedit&id=$last");
		exit;
	}
	
	
	public function page_extensions_disable()
	{
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		
		// do some validating
		$file = (string) $_GET['file'];
		$the_file = 'plugin.' . $file . '.php';

		$this->db->deleteArray(PX.'plugins', "pl_file = '$the_file'");

		system_redirect("?a=$go[a]&q=$go[q]");
		exit;
	}
	
	
	public function page_format_disable()
	{
		global $go;

		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		
		// do some validating
		$file = (int) $go['id'];

		$this->db->deleteArray(PX.'plugins', "pl_id = '$file'");

		system_redirect("?a=$go[a]&q=$go[q]");
		exit;
	}
	
	
	public function page_extension()
	{
		global $go, $default;

		$this->template->location = $this->lang->word('section');
		
		// sub-locations
		//$this->template->sub_location[] = array($this->lang->word('settings'),"?a=$go[a]&q=settings");
		//$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]");
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."sections 
			WHERE secid = '$go[id]'");
			
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		$body .= "<div class='c3'>\n";
		
		// First column
		$body .= "<div class='col'>\n";
		
		$body .= "<label>" . $this->lang->word('path') . "</label>";
		$body .= "<h2>" . BASEURL . "$rs[sec_path]</h2>" . br();
		
		$body .= ips($this->lang->word('section name'), 'input', 'sec_desc', 
			$rs['sec_desc'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
			
		$body .= ips($this->lang->word('folder name'), 'input', 'section', 
			$rs['section'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
		
		//$input .= ($default['subdir'] == true) ? getSectionPrepend(null, 'sec_prepend', null) : 
			//input('sec_prepend', 'hidden', NULL, '/');
			
		$body .= ($default['subdir'] == true) ? ips($this->lang->word('section path'), 'getSectionPrepend', 'sec_prepend', $rs['sec_path']) : input('sec_prepend', 'hidden', NULL, '/');
		
		$body .= ips($this->lang->word('section hide'), 'getGeneric', 'sec_hide', $rs['sec_hide']);
		
		$proj = ($rs['sec_group'] >= 1) ? $rs['sec_proj'] . '.' . $rs['sec_group'] : $rs['sec_proj'];
		
		$body .= ips($this->lang->word('projects section'), 'get_section_type', 'sec_proj', $proj);
		
		if ($rs['secid'] != 1)
		{
			$body .= input('del_sec', 'submit', "onclick=\"javascript:return confirm('" . $this->lang->word('sure delete section') . "');return false;\"", $this->lang->word('delete'));
		}
		
		$body .= input('edit_sec', 'submit', NULL, $this->lang->word('update'));
		
		$body .= input('hsecid', 'hidden', NULL, $rs['secid']);
		$body .= input('hsec_ord', 'hidden', NULL, $rs['sec_ord']);
		
		// we aren't really using this though
		$new_section = explode('/', $rs['sec_path']);
		array_pop($new_section);
		$new_section = preg_replace("/\/\//", '/', '/' . implode('/', $new_section));
		$body .= input('hsec_path', 'hidden', NULL, $new_section);
			
		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		
		$this->template->body = $body;
		$this->template->output('index');
		exit;
	}
	
	
	public function page_logout()
	{
		$this->access->logout();
	}
	
	
	public function page_files()
	{
		global $go;
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		//$this->template->add_js('modEdit.js');
		
		$this->template->pop_location = $this->lang->word('files manager');
		
		//$this->template->pop_links[] = array($this->lang->word('discover files'), "?a=$go[a]&amp;q=find");
		$this->template->pop_links[] = array($this->lang->word('swf upload'), "?a=$go[a]&amp;q=swf");
		//$this->template->pop_links[] = array($this->lang->word('upload files'), "?a=$go[a]&amp;q=upload");
		
		// ++++++++++++++++++++++++++++++++++++++
		
		$body = div(getFiles(), "id='p-files'");
		
		// need to clean this up
		$body .= "<div style='float: right; width: 400px; border: 1px solid #ccc; height: 300px; background: #f3f3f3;'>\n";
		$body .= "<iframe name='show' style='width: 400px; height: 300px; overflow: auto;'></iframe>\n";
		$body .= "</div>\n";
		
		$body .= "<div class='cl'><!-- --></div>\n";
		
		$body .= div(p('&nbsp;'));
		
		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}
	
	
	public function page_find()
	{
		global $go, $default;
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		if (isset($_GET['x']))
		{
			$clean['media_file'] = $_GET['x'];
			$clean['media_mime'] = array_pop( explode('.', $clean['media_file']) );
			$clean['media_udate'] = getNow();
			$clean['media_uploaded'] = $clean['media_udate'];
			$clean['media_ref_id'] = $go['id'];
			$clean['media_obj_type'] = 'exhibit';
			
			// if it's an image or an swf we'll get the dimensions
			if (in_array( $clean['media_mime'], array_merge($default['images'], $default['flash']) ))
			{
				$size = getimagesize(DIRNAME . '/files/' . $clean['media_file']);
				
				$clean['media_x'] = $size[0];
				$clean['media_y'] = $size[1];
			}
	
			$this->db->insertArray(PX.'media', $clean);
			
			// issue an onload to update the list of thumbs
			$this->template->onready[] = "parent.updateImages();";
		}
		
		$this->template->add_js('jquery.js');	
		$this->template->add_js('modEdit.js');
		
		$this->template->pop_location = $this->lang->word('find files');
		
		$this->template->pop_links[] = array($this->lang->word('external video'), "?a=$go[a]&amp;q=ext&id=$go[id]");
		$this->template->pop_links[] = array($this->lang->word('upload'), "?a=$go[a]&amp;q=swf&id=$go[id]");
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		// ++++++++++++++++++++++++++++++++++++++
		
		$body = find_files();
		$body .= "<div class='cl'><!-- --></div>\n";		
		$body .= div(p('&nbsp;'));
		
		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}
	
	
	
	public function page_view()
	{
		global $go, $default;
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		//$this->template->add_js('jquery.js');	
		//$this->template->add_js('modEdit.js');
		
		$this->template->pop_location = $this->lang->word('find files');
		
		$this->template->pop_links[] = array($this->lang->word('upload'), "?a=$go[a]&amp;q=swf&id=$go[id]");
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		// ++++++++++++++++++++++++++++++++++++++
		
		$this->template->body = "<div>&nbsp;</div>";
		
		$this->template->output('popup');
		exit;
	}
	
	
	public function page_links()
	{
		global $go;
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		$this->template->add_js('jquery.js');
		$this->template->add_js('alexking.quicktags.js');
		$this->template->add_js('modEdit.js');
		
		$this->template->pop_location = $this->lang->word('links manager');
		
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		// ++++++++++++++++++++++++++++++++++++++
		
		$body = "<h3 style='margin-bottom: 9px;'>Site Link</h3>";
		$body .= div(linksManager());
		$body .= input($this->lang->word('submit'), 'button', "onclick=\"parent.edInsertSysLink(edCanvas, 2, sysLink.value);\"", 'Submit');
		
		$body .= "<div style='margin: 9px 18px 18px 0;'><hr /></div>";
		
		$body .= "<h3 style='margin-bottom: 9px;'>" . $this->lang->word('external link') . "</h3>\n";

		//$body .= "<select name=\"selectType\" class=\"list\" onChange=\"switch_default();\" id='selectType' style=\"width:225px;\">\n";
		//$body .= "<option value='1'>" . $this->lang->word('hyperlink') . "</option>\n";
		//$body .= "<option value='2'>" . $this->lang->word('email') . "</option>\n";
		//$body .= "</select>\n";	
		
		$body .=  "<p style='margin-bottom: 3px;'><input type='radio' name='selectType' value='1' checked='checked'  onclick=\"$('#enterLink').val('http://');\"/> " . $this->lang->word('hyperlink') . ' ';
		$body .=  "<input type='radio' name='selectType' value='2' onclick=\"$('#enterLink').val('mailto:');\" /> " . $this->lang->word('email') . " </p>";
		
		$body .=  "<p style='margin-bottom: 3px;'><input type='checkbox' name='targetinfo' id='targetinfo' value='1' /> Open in new window</p>";

		$body .= "<p style='margin-bottom: 3px;'>" . $this->lang->word('urlemail') . "<br />\n";
		$body .= "<input type=\"text\" name=\"enterLink\" id='enterLink' class=\"txtfld\" style=\"width:225px;\" value='http://' /></p>\n";

		
		$body .= "<script type='text/javascript'>var edCanvas = parent.document.getElementById('jxcontent');</script>\n";
		
		$body .= "<input type=\"button\" value=\"Submit\" onclick=\"var target = ($('#targetinfo').is(':checked')) ? '_blank' : ''; parent.edInsertLink(edCanvas, 2, enterLink.value, target);\" id='ed_link' /></p>\n";
		
		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}
	
	
	public function page_editfile()
	{
		global $go, $default;
		
		load_helper('html');
		
		$this->template->js[] = 'jquery.js';
		
		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';";
		
		if (isset($_GET['x']))
		{
			$clean['media_file'] = $_GET['x'];
			$clean['media_mime'] = array_pop( explode('.', $clean['media_file']) );
			$clean['media_udate'] = getNow();
			$clean['media_uploaded'] = $clean['media_udate'];
			
			// if it's an image or an swf we'll get the dimensions
			if (in_array( $clean['media_mime'], array_merge($default['images'], $default['flash']) ))
			{
				$size = getimagesize(DIRNAME . '/files/' . $clean['media_file']);
				
				$clean['media_x'] = $size[0];
				$clean['media_y'] = $size[1];
			}
	
			$last = $this->db->insertArray(PX.'media', $clean);
			
			system_redirect("?a=$go[a]&q=editfile&id=$last");
			exit;
		}
		
		$this->template->pop_location = $this->lang->word('edit file info');
		
		$this->template->pop_links[] = array($this->lang->word('files manager'), "?a=$go[a]&amp;q=files");
		$this->template->pop_links[] = array($this->lang->word('upload'), "?a=$go[a]&amp;q=upload");
		
		// ++++++++++++++++++++++++++++++++++++++
		
		$rs = $this->db->fetchRecord("SELECT * FROM ".PX."media 
			WHERE media_id = '$go[id]' 
			AND media_obj_type = ''");
		
		if (!$rs)
		{
			$body = p($this->lang->word('none found'));
		}
		else
		{
			$body = "<div style='width: 265px; float: left;'>\n";
			$body .= "<h2>$rs[media_file]</h2>\n";
			$body .= br();

			$body .= ips($this->lang->word('image title'), 'input', 'media_title', $rs['media_title'], "maxlength='35'", 'text');

			if (in_array($rs['media_mime'], array_merge($default['images'], $default['flash'], $default['media'])))
			{
				$body .= ips($this->lang->word('width'), 'input', 'media_x', $rs['media_x'], "maxlength='4'", 'text', $this->lang->word('if applicable'));
				$body .= ips($this->lang->word('height'), 'input', 'media_y', $rs['media_y'], "maxlength='4'", 'text', $this->lang->word('if applicable'));
			}

			$body .= input('upd_editfile', 'submit', NULL, $this->lang->word('update'));
			$body .= input('upd_delfile','submit', "onclick=\"javascript:return confirm('".$this->lang->word('are you sure')."');return false;\"", $this->lang->word('delete'));
			$body .= input('upd_file', 'hidden', NULL, $this->lang->word('update'));
			$body .= "</div>\n";			
			$body .= "<div style='width:350px; float:left;'>\n";
			$body .= "</div>\n";
		}
		
		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}
	
	
	public function page_detach()
	{
		global $go;
		
		$clean['media_thumb'] = '';
		$clean['media_xr'] = '';
		$clean['media_yr'] = '';
		
		$this->db->updateArray(PX.'media', $clean, "media_id = '$go[id]'");
		
		// delete files
		// need to throw a flag to update the exhibit images
		
		system_redirect("?a=$go[a]&q=img&id=$go[id]");
	}
	
	
	public function page_uploading()
	{
		global $go, $default;
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		$this->template->pop_location = $this->lang->word('upload');
		
		//$this->template->pop_links[] = array($this->lang->word('files manager'), "?a=$go[a]&amp;q=files");
		
		$this->template->form_type = TRUE;
		
		// ++++++++++++++++++++++++++++++++++++++
		
		//$body = "<div id='uploadings' class='as-holder' style='margin-bottom: 9px;'><label style='display: block; margin-bottom: 6px;'>Upload/Import</label>\n";
		
		//$folders = " <a href='?a=system&q=folder&id=$go[id]' rel=\"shadowbox;player=iframe;height=400;width=500\"><img src='asset/img/files.gif' title='" . $this->lang->word('folder load') . "' /></a> ";
		
		$folders = " <a href='?a=system&q=folder&id=$go[id]' rel=\"facebox;width=400;height=350;modal=true\"><img src='asset/img/files.gif' title='" . $this->lang->word('folder load') . "' /></a> ";
		
		$x = "<a href='?a=system&q=swf&id=$go[id]' rel=\"facebox;height=400;width=500;modal=true\" id='img-upldr'><img src='asset/img/page-upload.gif' title='" . $this->lang->word('upload files') . "' /></a>";
		
		$body = $x . $folders;
		
		$body .= $this->hook->do_action('system_uploader_link');
		
		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}
	
	
	public function page_pluginsert()
	{
		global $go, $default;
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		$this->template->pop_location = $this->lang->word('insert plugin');
		
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'system.js';
		
		$this->template->form_type = TRUE;
		
		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';
var baseurl = '" . BASEURL . "';";
		
		// ++++++++++++++++++++++++++++++++++++++
		
		$hook_array = $this->hook->do_action_array('system_plugin_insert');
		
		if (is_array($hook_array))
		{
			$html = "<select id='pluginsert' onchange=\"var tmp = $('#pluginsert').val(); update_location('/ndxzstudio/' + tmp);\">\n";

			$html .= "<option value=''>Select</option>\n";

			foreach ($hook_array as $hook)
			{
				$html .= "<option value='$hook[link]'>$hook[title]</option>\n";
			}
			
			$html .= "</select>\n\n";
		}
		else
		{
			$html = 'Nope';
		}
		
		$body = $html;
		
		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}
	
	
	public function page_fileupload()
	{
		$this->lib_class('upload');

		header('Pragma: no-cache');
		header('Cache-Control: private, no-cache');
		header('Content-Disposition: inline; filename="files.json"');

		switch ($_SERVER['REQUEST_METHOD']) {
		    case 'HEAD':
		    case 'GET':
		        $this->upload->get();
		        break;
		    case 'POST':
		        $this->upload->post();
		        break;
		    case 'DELETE':
		        $this->upload->delete();
		        break;
		    default:
		        header('HTTP/1.0 405 Method Not Allowed');
		}
		
		exit;
	}
	
	
	public function page_upload()
	{
		global $go, $default;
		
		load_helper('html');
		load_module_helper('files', $go['a']);
		
		$this->template->pop_location = $this->lang->word('upload');
		
		//$this->template->pop_links[] = array($this->lang->word('files manager'), "?a=$go[a]&amp;q=files");
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		// we may need to check for the $oid later
		// check if it's allowed to upload to this exhibit
		$rs = $this->db->fetchRecord("SELECT media_source    
			FROM ".PX."objects 
			WHERE id = '$go[id]'");

		if ($rs['media_source'] != 0)
		{ 
			// can not upload to this exhibit
			$this->template->body = "You can not upload to this exhibit. Change the media source in options.";
			$this->template->output('popup');
			exit;
		}
		
		$this->template->form_type = TRUE;
		//$this->template->form_action = '/ndxzstudio/lib/upload.php';
		$this->template->form_action = "?a=system&q=fileupload&id=$go[id]";
		
		$this->template->css[] = "jquery.fileupload-ui.css";
		$this->template->ex_css[] = "#p-container { margin-top: 1px; }";
		
		$this->template->js[] = "jquery.js";
		//$this->template->js[] = "jquery-ui.min.js";
		$this->template->js[] = "jquery.ui.widget.js";
		$this->template->js[] = "jquery.tmpl.min.js";
		$this->template->js[] = "upload/jquery.iframe-transport.js";
		$this->template->js[] = "upload/jquery.fileupload.js";
		$this->template->js[] = "upload/jquery.fileupload-fp.js";
		$this->template->js[] = "upload/jquery.fileupload-ui.js";
		$this->template->js[] = "upload/locale.js";
		$this->template->js[] = "upload/main.js";

		
		// we have a few scenarios here
		switch($go['q'])
		{
			case 'background' :
				$this->template->ex_js[] = "var maximagesize = 999999999999;";
				$multiple = '';
				$this->template->form_action = "?a=system&q=fileupload&id=$go[id]&x=background";

				$this->template->onready[] = "$('#mformpop').bind('fileuploadstop', function (e, data) { location.href = '?a=exhibits&q=settings&id=$go[id]'; });
$('#mformpop').fileupload('option' ,{ maxNumberOfFiles: 1, acceptFileTypes: /(png)|(jpe?g)|(gif)$/i });";
				break;
			case 'thumb' :
				$this->template->ex_js[] = "var maximagesize = $default[maxsize];";
				$multiple = '';
				$this->template->form_action = "?a=system&q=fileupload&id=$go[id]&x=coverart&xid=$_GET[xid]";

				$this->template->onready[] = "$('#mformpop').bind('fileuploadstop', function (e, data) { location.href = '?a=system&q=img&id=$_GET[xid]'; });
$('#mformpop').fileupload('option' ,{ maxNumberOfFiles: 1, acceptFileTypes: /(png)|(jpe?g)|(gif)$/i });";
				break;
			default :
				$this->template->ex_js[] = "var maximagesize = $default[maxsize];";
				$multiple = " multiple=";

				// acceptFileTypes = all 'file' types
				// maxFileSize = 

				$allowed = array_merge($default['media'], $default['sound'], $default['files'], $default['flash']);
				$allowed_types = '(' . implode(')|(', $allowed) . ')|';

				$this->template->onready[] = "$('#mformpop').bind('fileuploadstop', function (e, data) { parent.updateImages(); }); $('#mformpop').fileupload('option' ,{ acceptFileTypes: /{$allowed_types}(png)|(jpe?g)|(gif)$/i });";
				break;
		}
		
		// ++++++++++++++++++++++++++++++++++++++
		
		$body = '';
		
		$body = "<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class='row fileupload-buttonbar'>
            <div class='span7 buttons'>
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class='btn btn-success fileinput-button add-files'>
                    <span>" . $this->lang->word('Add files') . "</span>
                    <input type='file' name='files[]' multiple>
                </span>
                <button type='submit' class='btn btn-primary start'>
                    <span>" . $this->lang->word('Upload') . "</span>
                </button>
                <button type='reset' class='btn btn-warning cancel'>
                    <span>" . $this->lang->word('Cancel') . "</span>
                </button>
                <button type='button' class='btn btn-danger delete' style='display: none;'>
                    <span>" . $this->lang->word('Delete') . "</span>
                </button>
                <input type='checkbox' class='toggle' style='display: none;'>
            </div>
            <div class='span5'>
                <!-- The global progress bar -->
                <div class='progress progress-success progress-striped active fade' style='display: none;'>
                    <div class='bar' style='width:0%;'></div>
                </div>
            </div>
        </div>
        <!-- The loading indicator is shown during file processing -->
        <div class='fileupload-loading'></div>
        <br>
        <!-- The table listing the files available for upload/download -->
        <table class='table table-striped table-uploading'><tbody class='files' data-toggle='modal-gallery' data-target='#modal-gallery'></tbody></table>
		
		<!-- The template to display files available for upload -->
		<script id='template-upload' type='text/x-tmpl'>
		{% for (var i=0, file; file=o.files[i]; i++) { %}
		    <tr class='template-upload fade'>
		        <td class='preview'><span class='fade'></span></td>
		        <td class='name'><span>{%=file.name%}</span></td>
		        <td class='size'><span>{%=o.formatFileSize(file.size)%}</span></td>
		        {% if (file.error) { %}
		            <td class='error' colspan='2'><span class='label label-important'>{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
		        {% } else if (o.files.valid && !i) { %}
		            <td>
		                <div class='progress progress-success progress-striped active'><div class='bar' style='width:0%;'></div></div>
		            </td>
		            <td class='start'>{% if (!o.options.autoUpload) { %}
					<div class='buttons'>
		                <button class='btn btn-primary' style='display: none;'>
		                    <span>{%=locale.fileupload.start%}</span>
		                </button>
					</div>
		            {% } %}</td>
		        {% } else { %}
		            <td colspan='2'></td>
		        {% } %}
		        <td class='cancel'>{% if (!i) { %}
					<div class='buttons'>
		            <button class='btn btn-warning cancel'>
		                <span>{%=locale.fileupload.cancel%}</span>
		            </button>
					</div>
		        {% } %}</td>
		    </tr>
		{% } %}
		</script>
		<!-- The template to display files available for download -->
		<script id='template-download' type='text/x-tmpl'>
		{% for (var i=0, file; file=o.files[i]; i++) { %}
		    <tr class='template-download fade'>
		        {% if (file.error) { %}
		            <td></td>
		            <td class='name'><span>{%=file.name%}</span></td>
		            <td class='size'><span>{%=o.formatFileSize(file.size)%}</span></td>
		            <td class='error' colspan='2'><span class='label label-important'>{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
		        {% } else { %}
		            <td class='preview'>{% if (file.thumbnail_url) { %}
		                <a href='{%=file.url%}' title='{%=file.name%}' rel='gallery' download='{%=file.name%}'><img src='{%=file.thumbnail_url%}'></a>
		            {% } %}</td>
		            <td class='name'>{%=file.name%}</td>
		            <td class='size'><span>{%=o.formatFileSize(file.size)%}</span></td>
		            <td colspan='2'></td>
		        {% } %}
		        <td class='delete'>
					<div class='buttons'>
		            <button class='btn btn-danger delete' data-type='{%=file.delete_type%}' data-url='{%=file.delete_url%}'>
		                <span>{%=locale.fileupload.destroy%}</span>
		            </button>
					</div>
		            <input type='checkbox' name='delete' value='1' style='display: none;'>
		        </td>
		    </tr>
		{% } %}
		</script>";

		$this->template->body = $body;
		
		$this->template->output('popup');
		exit;
	}
	
	
	
	public function array_neighbor($id, $current)
	{
		global $go;

		$rs = $this->db->fetchArray("SELECT media_id, media_order   
			FROM ".PX."media 
			WHERE media_ref_id = '$current' 
			ORDER BY media_order ASC");
			
		foreach ($rs as $rw) $arr[] = array($rw['media_id']);
		
		$nx = 0;
		$px = 0;
		$last = 0;

		$n = false;
		$p = false;
		$c = false;
		$nn = false;
		
		foreach ($arr as $ck)
		{
			if ($nn == true) { $nx = $ck[0]; $nn = false; }
			if ($ck[0] == $id) { $c = true; $px = $last; $nn = true; }
			if ($c == true) { $c = false; }
			
			$last = $ck[0];
		}
		
		$s = ($px != 0) ? "<a href='?a=$go[a]&amp;q=img&amp;id=$px' title='" . $this->lang->word('Previous') . "'>" . $this->lang->word('Previous') . "</a> " : "<span style='color: #ebebeb;'>" . $this->lang->word('Previous') . "</span> ";
		$s .= ($nx != 0) ? " <a href='?a=$go[a]&amp;q=img&amp;id=$nx' title='" . $this->lang->word('Next') . "'>" . $this->lang->word('Next') . "</a>" : " <span style='color: #ebebeb;'>" . $this->lang->word('Next') . "</span> ";

		return $s;
	}


	public function page_tagfile()
	{
		global $go, $default;

		// the record
		$rs = $this->db->fetchRecord("SELECT * FROM ".PX."media WHERE media_id = '$go[id]'");
			
		$title = ($rs['media_file_replace'] != '') ? $rs['media_file_replace'] : $rs['media_file'];
		
		$mimer = "<span style='color: #fff; padding: 2px; font-size: 8px; text-transform: uppercase;' class='file-$rs[media_mime]'>$rs[media_mime]</span> ";

		$this->template->pop_location = $mimer . $title;
		$this->template->pop_links[] = array($this->lang->word('back'), "?a=system&q=showtag&id=" . (int) $_GET['tag'] . "");
		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

		$this->template->js[] = "jquery.js";
		
		// issue an onload to update the list of thumbs
		// how do we trigger this?
		//$this->template->onready[] = "parent.updateImages();";

		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';";

		load_module_helper('files', 'system');
		load_helpers(array('output', 'types'));
	
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++

		//$np = div($this->array_neighbor($rs['media_id'], $rs['media_ref_id']), "style='margin-bottom: 18px;'");
		
		// if thumbnail show it...else...link to uploader
		// if it's not a picture (meaning, it is a video or otherwise)
		if (!in_array($rs['media_mime'], $default['images']))
		{
			// video, flash, java, other
			if ($rs['media_thumb'] == '')
			{
				$body = "<div style='width:125px; float:left;'>&nbsp;\n";
				$body .= "<div style='width: 100px; height: 75px; background: #ccc;'>&nbsp;</div><br />";
				$body .= "<a href='" . BASEURL . BASEFILES . "/$rs[media_file]' target='_new'>" . $this->lang->word('view source file') . "</a></div>\n";
				$body .= "</div>\n";
			}
			else
			{
				$body = "<div style='width:125px; float:left;'>\n";
				
				//$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_thumb]' width='100' /><br /><br /><a href='?a=$go[a]&q=detach&id=$rs[media_id]'>" . $this->lang->word('detach cover') . "</a><br /><br />\n";
				$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_thumb]' width='100' /><br /><br />\n";
				$body .= "<a href='" . BASEURL . BASEFILES . "/$rs[media_file]' target='_new'>" . $this->lang->word('view source file') . "</a></div>\n";
			}
		}
		else // if it's a picture!
		{
			$insert = "<br /><br /><a href='#' onClick=\"parent.ModInsGimg('$rs[media_ref_id]_$rs[media_file]', '', '');return false;\" class='inserter'>Insert image</a>";
			
			$body = "<div style='width:125px; float:left;'>\n";
			
			// if it does not have cover art
			if ($rs['media_thumb'] != '')
			{
				// the uploaded image
				$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_file]' width='100' /><br /><br />\n";
				// cover art
				//$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_thumb]' width='100' /><br /><br /><a href='?a=$go[a]&q=detach&id=$rs[media_id]'>" . $this->lang->word('detach cover') . "</a><br /><br />\n";
			}
			else // cover art! AND, it has an actual image too!
			{
				$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_file]' width='100' /><br /><br />\n";
			}
			
			$body .= "<a href='" . BASEURL . GIMGS . "/$rs[media_file]' target='_new'>" . $this->lang->word('view source file') . "</a></div>\n";
		}
		
		if ($rs['media_mime'] == 'url')
		{
			//$body .= ips($this->lang->word('input url'), 'input', 'media_file', 
			//	$rs['media_file'], "id='media_file' maxlength='255' style='width: 250px; padding: 4px 1px; border: 1px solid #999;'", 'text');
		}
		
		if ($this->vars->site['tags'] == 1)
		{
		$body .= "<div style='width:600px; float:left;'>\n";
		// tags
		$this->lib_class('tag');
		$this->tag->method = 'img';
		$this->tag->id = $rs['media_id'];
		$this->tag->active_tags = $rs['media_tags'];
		$this->tag->method = 'img';

		$body .= "<div>\n";
		$body .= label($this->lang->word('tags') . ' ' . span(href($this->lang->word('add tags'), '#', "onclick=\"toggle('tag-add'); return false;\""))) . "\n";
		$body .= "<div id='tag-add' style='display: none; cursor: pointer;'>\n";
		$body .= input('add_tag', 'text', "id='new_tag' style='display: inline; width: 100px;'", null);
		$body .= "<input type='hidden' name='tag_group' id='tag_group' value=\"1\" /> ";
		$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"add_tags('img'); return false;\"", 'submit');
		$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"$('#tag-add').toggle(); return false;\"", 'cancel');
		$body .= "<p>" . $this->lang->word('Separate multiple tags with a comma') . " ','.</p>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";

		$body .= "<div id='tag-box' style='display:block; padding:6px 0;'>\n";
		$body .= div($this->tag->get_active_tags2(), "id='tag-holder'");
		$body .= "</div>\n";
		// end tags
		}
		
		$body .= "</div>\n";

		$body .= "<div class='cl'><!-- --></div>\n";

		$this->template->body = $body;
		$this->template->output('popup');
		exit;
	}
	
	
		public function page_img()
		{
			global $go, $default;

			// the record
			$rs = $this->db->fetchRecord("SELECT * FROM ".PX."media WHERE media_id = '$go[id]'");
				
			$title = ($rs['media_file_replace'] != '') ? $rs['media_file_replace'] : $rs['media_file'];
			
			$mimer = "<span style='color: #fff; padding: 2px; font-size: 8px; text-transform: uppercase;' class='file-$rs[media_mime]'>$rs[media_mime]</span> ";

			$this->template->pop_location = $mimer . $title;
			$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");

			$this->template->js[] = "jquery.js";
			
			// issue an onload to update the list of thumbs
			// how do we trigger this?
			//$this->template->onready[] = "parent.updateImages();";

			$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';";

			load_module_helper('files', 'system');
			load_helpers(array('output', 'types'));
		
			// ++++++++++++++++++++++++++++++++++++++++++++++++++++

			$np = div($this->array_neighbor($rs['media_id'], $rs['media_ref_id']), "style='margin-bottom: 18px;'");
			
			// if thumbnail show it...else...link to uploader
			// if it's not a picture (meaning, it is a video or otherwise)
			if (!in_array($rs['media_mime'], $default['images']))
			{
				if ($rs['media_thumb'] == '')
				{
					$body = "<div style='width:125px; float:left;'>$np\n";
					$body .= "<a href='?a=$go[a]&q=thumb&id=$rs[media_ref_id]&x=coverart&xid=$rs[media_id]'>" . $this->lang->word('attach cover') . "</a><br /><br />";
					$body .= href($this->lang->word('Insert file'), '#', plugin_insert($rs) . " class='inserter'");
					$body .= "</div>\n";
				}
				else
				{
					$insert = "<br /><br /><a href='#' onClick=\"parent.ModInsGimg('$rs[media_ref_id]_$rs[media_thumb]', '', '');return false;\" class='inserter'>Insert image</a>";
					
					$media = "<br />" . href($this->lang->word('Insert file'), '#', plugin_insert($rs) . " class='inserter'");
					
					$body = "<div style='width:125px; float:left;'>$np\n";
					
					$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_thumb]' width='100' /><br /><br /><a href='?a=$go[a]&q=detach&id=$rs[media_id]'>" . $this->lang->word('detach cover') . "</a><br /><br />\n";
					
					$body .= "<a href='" . BASEURL . BASEFILES . "/$rs[media_file]' target='_new'>" . $this->lang->word('view source file') . "</a>$insert $media</div>\n";
				}
			}
			else // if it's a picture!
			{
				$insert = "<br /><br /><a href='#' onClick=\"parent.ModInsGimg('$rs[media_ref_id]_$rs[media_file]', '', '');return false;\" class='inserter'>" . $this->lang->word('Insert image') . "</a>";
				
				$body = "<div style='width:125px; float:left;'>$np\n";
				
				// if it does not have cover art
				if ($rs['media_thumb'] != '')
				{
					// the uploaded image
					$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_file]' width='100' /><br /><br />\n";
					// cover art
					$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_thumb]' width='100' /><br /><br /><a href='?a=$go[a]&q=detach&id=$rs[media_id]'>" . $this->lang->word('detach cover') . "</a><br /><br />\n";
				}
				else // cover art! AND, it has an actual image too!
				{
					$body .= "<img src='" . BASEURL . GIMGS . "/th-$rs[media_ref_id]_$rs[media_file]' width='100' /><br /><br />\n";
					$body .= "<a href='?a=$go[a]&q=thumb&id=$rs[media_ref_id]&x=coverart&xid=$rs[media_id]'>" . $this->lang->word('attach cover') . "</a><br /><br />";
				}
				
				$body .= "<a href='" . BASEURL . GIMGS . "/$rs[media_file]' target='_new'>" . $this->lang->word('view source file') . "</a>$insert</div>\n";
			}

			$body .= "<div style='width:275px; float:left;'>\n";
			
			if ($rs['media_mime'] == 'url')
			{
				$body .= ips($this->lang->word('input url'), 'input', 'media_file', 
					$rs['media_file'], "id='media_file' maxlength='255' style='width: 250px; padding: 4px 1px; border: 1px solid #999;'", 'text');
			}

			$body .= ips($this->lang->word('image title'), 'input', 'media_title', 
				$rs['media_title'], "id='media_title' maxlength='255' style='width: 250px; padding: 4px 1px; border: 1px solid #999;'", 'text');

			$body .= label($this->lang->word('image caption')) . br();
			
			// mini editor
			$this->lib_class('editor');
			$this->editor->content = $rs['media_caption'];
			$this->editor->process = 1;
			$this->editor->content_id = 'media_caption';
			$this->editor->css = "style='width: 250px; height: 100px;'";
			$body .= $this->editor->mini_editor();
			//////////////
			
			// width and height parts	
			if (in_array($rs['media_mime'], array_merge($default['media'], $default['services'], $default['flash'])))
			{
				$body .= "<p>\n";
				$body .= "<label>" . $this->lang->word('Width') . "</label> <input type='text' name='media_x' value='$rs[media_x]' style='width: 35px; display: inline;' maxlength='4' />\n";
				$body .= " &nbsp;&nbsp;<label>" . $this->lang->word('Height') . "</label> <input type='text' name='media_y' value='$rs[media_y]' style='width: 35px; display: inline;' maxlength='4' />\n";
				$body .= "</p>\n";
			}
			
			$ractive = ($rs['media_hide'] == 1) ? " checked='checked'" : '';
			$rinactive = ($rs['media_hide'] != 1) ? " checked='checked'" : '';
			
			$body .= "<p><input type='radio' name='media_hide' value='0'{$rinactive} /> " . $this->lang->word('Active') . " &nbsp;&nbsp;<input type='radio' name='media_hide' value='1'{$ractive} /> " . $this->lang->word('Inactive') . "</p>\n";

			$body .= "<input type='submit' name='upd_img' value='" . $this->lang->word('update') . "' />\n";
			$body .= "</div>\n";
			
			// need site vars
			//$site_vars = unserialize($this->bars->settings['site_vars']);
			
			if ($this->vars->site['tags'] == 1)
			{
			$body .= "<div style='width:350px; float:left;'>\n";
			// tags
			$this->lib_class('tag');
			$this->tag->method = 'img';
			$this->tag->id = $rs['media_id'];
			$this->tag->active_tags = $rs['media_tags'];
			$this->tag->method = 'img';

			$body .= "<div>\n";
			$body .= label($this->lang->word('tags') . ' ' . span(href($this->lang->word('add tags'), '#', "onclick=\"toggle('tag-add'); return false;\""))) . "\n";
			$body .= "<div id='tag-add' style='display: none; cursor: pointer;'>\n";
			$body .= input('add_tag', 'text', "id='new_tag' style='display: inline; width: 100px;'", null);
			$body .= "<input type='hidden' name='tag_group' id='tag_group' value=\"1\" /> ";
			$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"add_tags('img'); return false;\"", $this->lang->word('submit'));
			$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"$('#tag-add').toggle(); return false;\"", $this->lang->word('cancel'));
			$body .= "<p>" . $this->lang->word('Separate multiple tags with a comma') . " ','.</p>\n";
			$body .= "</div>\n";
			$body .= "</div>\n";

			$body .= "<div id='tag-box' style='display:block; padding:6px 0;'>\n";
			$body .= div($this->tag->get_active_tags2(), "id='tag-holder'");
			$body .= "</div>\n";
			// end tags
			}
			
			$body .= "</div>\n";

			$body .= "<div class='cl'><!-- --></div>\n";

			$this->template->body = $body;
			$this->template->output('popup');
			exit;

			header ('Content-type: text/html; charset=utf-8');
			echo $body;
			exit;
		}
	
	
	public function page_tags()
	{
		global $go, $default;

		load_module_helper('files', $go['a']);

		$this->template->pop_location = $this->lang->word('tags');

		$this->template->js[] = "jquery.js";
		
		$this->template->ex_js[] = "var action = '$go[a]';
var ide = '$go[id]';";

		// ++++++++++++++++++++++++++++++++++++++
		// get the object
		$rs = $this->db->fetchRecord("SELECT tags FROM ".PX."objects WHERE id='$go[id]'");

		// tags
		$this->lib_class('tag');
		$this->tag->active_tags = $rs['tags'];
		$this->tag->method = 'exh';

		$body = "<div style='margin: 3px 0 0 0; text-align: left;'>\n";
		
		$body .= "<div>\n";
		$body .= label('tags' . ' ' . span(href("add tags", '#', "onclick=\"toggle('tag-add'); return false;\""))) . "\n";
		$body .= "<div id='tag-add' style='display: none; cursor: pointer;'>\n";
		$body .= input('add_tag', 'text', "id='new_tag' style='display: inline; width: 100px;'", null);
		$body .= get_tag_groups($tag['tag_group'], 'g', "id='group' style='display: inline; width: 45px;'");
		$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"add_tags('exh'); return false;\"", 'submit');
		$body .= input('new_tag', 'button', "style='display: inline;' onclick=\"$('#tag-add').toggle(); return false;\"", 'cancel');
		$body .= "<p>Separate multiple tags with a comma ','.</p>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";
		
		$body .= "<div id='tag-box' style='display:block; padding:6px 0;'>\n";
		$body .= div($this->tag->get_active_tags(), "id='tag-holder'");
		$body .= "</div>\n";
		
		$body .= "</div>\n\n";
		// end tags
		
		$body .= "";

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}


	public function page_folder()
	{
		global $go, $default;
		
		load_helper('html');
		load_helper('files');
		
		$this->template->pop_location = $this->lang->word('upload/import');

		$this->template->pop_links[] = array($this->lang->word('close'), '#', "onclick=\"parent.faceboxClose(); return false;\"");
		
		$this->template->module_css[] = "settings.css";
		
		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'system.js';
		
		// ++++++++++++++++++++++++++++++++++++++
		
		if (isset($_POST['ins_all_files']))
		{
			$check = $this->db->fetchArray("SELECT media_file FROM ".PX."media 
				WHERE media_dir = '$_POST[h_media_info]' AND media_ref_id = '$go[id]'");
				
			if ($check)
			{
				foreach ($check as $c) { $tmp[] = $c['media_file']; }
				$check = $tmp;
			}
			else
			{
				$check = array('0');
			}
			
			$i = 1; $a = '';
			
			$body .= "<div style='height: 200px; overflow: auto;'>\n";
			$theFiles = getTheFiles(DIRNAME . '/files/' . $_POST['h_media_info'] . '/', array(''));
			$RSZ = load_class('resize', true, 'lib');
			$RSZ->folder_load_images($theFiles, $go['id'], 9999, 'image', $_POST['h_media_info']);
			
			$body .= p('Files autoloaded.');

			$body .= "</div>\n";
		}
		
		if (isset($_POST['ins_files']))
		{
			$check = $this->db->fetchArray("SELECT media_file FROM ".PX."media 
				WHERE media_dir = '$_POST[media_info]' AND media_ref_id = '$go[id]'");
				
			if ($check)
			{
				foreach ($check as $c) { $tmp[] = $c['media_file']; }
				$check = $tmp;
			}
			else
			{
				$check = array('0');
			}
			
			$i = 1; $a = '';

			// we list the files here...
			$body = "<p><label>Folder: " . $_POST['media_info'] . "</label></p>\n";
			$body .= "<p>Click filename to load individually or <a href='#' onclick=\"file_add_all($go[id], 0, 0, '$_POST[media_info]'); return false;\">autoload all files to this exhibition.</a></p>\n";
			$body .= "<div style='height: 200px; overflow: auto;'>\n";
			$theFiles = getTheFiles(DIRNAME . '/files/' . $_POST['media_info'] . '/', array(''));

			foreach ($theFiles as $f)
			{
				if (!in_array($f, $check))
				{
					$a .= li(href($f, '#', "onclick=\"file_add_single($go[id], $i, '$f', '$_POST[media_info]'); return false;\""), "id='file-$i' style='margin: 5px 0;'");
					$i++;
				}
			}
			
			$body .= ($a == '') ? p("None found") : "<ul id='thefiles'>" . $a . "</ul>";
			//$body .= ($a == '') ? '' : p(input('ins_all_files', 'submit', null, 'Autoload all files'));
			$body .= ($a == '') ? '' : input('h_media_info', 'hidden', null, $_POST['media_info']);
			$body .= "</div>\n";
			
			// need to alert the image updater
			//$this->template->onready[] = "parent.updateImages();";
		}
		else
		{
			// this is where we list the files...
			$body = "<label>" . $this->lang->word('folders') . ' ' . span('Select one') . "</label>\n";
			$body .= "<div style='height: 200px;'>\n";
			$folders = get_the_folders(DIRNAME . '/files/', null, array('gimgs', 'dimgs'));
			
			if (is_array($folders))
			{
				$files = get_the_files_from_folders($folders, null);
			}
			
			if (is_array($files)) $body .= show_the_folders_files($files);

			$body .= "</div>\n";
		}

		$this->template->body = $body;

		$this->template->output('popup');
		exit;
	}
	
	
	public function page_thumb()
	{
		$this->page_upload();
		exit;
	}
	

	public function page_background()
	{
		$this->page_upload();
		exit;		
	}

	
	
	public function sbmt_upd_img()
	{
		global $go;
				
		load_module_helper('files', $go['a']);
		
		$p =& load_class('processor', TRUE, 'lib');
		
		if (isset($_POST['media_file']))
		{
			$clean['media_file'] = $p->process('media_file', array('nophp'));
		}
		
		$clean['media_title'] = $p->process('media_title', array('nophp'));
		$clean['media_caption'] = $p->process('media_caption', array('nophp'));
		//$clean['media_file_replace'] = $p->process('media_file_replace', array('nophp'));
		$clean['media_x'] = $p->process('media_x', array('digit'));
		$clean['media_y'] = $p->process('media_y', array('digit'));
		$clean['media_hide'] = $p->process('media_hide', array('boolean'));
		
		// we need to process text on the caption - make this a hook later
		$this->lib_class('editor');
		$clean['media_caption'] = $this->editor->textProcess($clean['media_caption'], 1);
		
		$this->db->updateArray(PX.'media', $clean, "media_id='$go[id]'");
		
		$this->template->action_update = 'Updated';
	}
	
	
	public function page_prv()
	{
		$OBJ =& get_instance();
		global $go;
		
		// query for our variables
		$OBJ->vars->exhibit = $this->db->fetchRecord("SELECT * 
			FROM ".PX."objects, ".PX."objects_prefs, ".PX."sections  
			INNER JOIN ".PX."settings ON adm_id = '1' 
			WHERE id = '$go[id]'
			AND section_id = secid 
			AND object = obj_ref_type");
			
		if (!$OBJ->vars->exhibit) show_error('no results');
		
		// we need to make a trigger to stop some things at this end
		//$OBJ->vars->exhibit['cms'] = true;
		
		$OBJ->abstracts->front_abstracts();
		
		// get plugins (all of them)
		include_once DIRNAME . '/ndxzsite/plugin/index.php';
		
		$OBJ->baseurl = BASEURL;
		$OBJ->vars->exhibit['baseurl'] = BASEURL;
		$OBJ->vars->exhibit['basename'] = BASENAME;
		$OBJ->vars->exhibit['basefiles'] = BASEFILES;
		$OBJ->vars->exhibit['gimgs'] = GIMGS;
		
		$OBJ->vars->exhibit['cms'] = true;
		$OBJ->vars->exhibit['ajax'] = false;

		// we want to lose all of this
		$GLOBALS['rs'] = $OBJ->vars->exhibit;
		
		// get the front end helper class
		load_helpers(array('time'));
		
		$OBJ =& get_instance();
		$OBJ->lib_class('hook');
		$OBJ->hook->load_hooks_front();

		$OBJ->lib_class('page', true, 'lib');
		$OBJ->page->loadExhibit();
		$OBJ->page->init_page();
		
		// makin' stuff happen
		$OBJ->parse_class($this->vars->default['parse']);
		$OBJ->parse->vars = $OBJ->vars->exhibit;

		// this allows us some control over outputs...
		// like adding extra exhibits or variables via user input
		$OBJ->parse->pre_parse($OBJ->vars->exhibit['content']);
		
		if ($OBJ->hook->registered_hook('pre_load')) $OBJ->hook->do_action('pre_load');
		
		// time for some action
		$template = (file_exists(DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/' . $OBJ->vars->exhibit['template'])) ? $OBJ->vars->exhibit['template'] : 'index.php';
		$filename = DIRNAME . '/ndxzsite/' . $OBJ->vars->exhibit['obj_theme'] . '/' . $template;
		$fp = @fopen($filename, 'r');
		$contents = fread($fp, filesize($filename));
		fclose($fp);
		
		//echo 'here';

		$OBJ->parse->code = $contents;
		$output = $OBJ->parse->parsing();

		// caching - if enabled and possible

		// do we need a header output here?
		echo $output;
		exit;
	}

	
	public function sbmt_upd_file()
	{
		global $go;
		
		if (isset($_POST['upd_delfile']))
		{
			$file = $this->db->fetchRecord("SELECT media_id,media_file FROM ".PX."media 
				WHERE media_id='$go[id]' 
				AND media_obj_type = ''");
			
			if ($file)
			{
				if (file_exists(DIRNAME . '/files/' . $file['media_file']))
				{
					unlink(DIRNAME . '/files/' . $file['media_file']);
					$this->db->deleteArray(PX.'media', "media_id='$file[media_id]'");
				}
			}
		}
		else
		{
			$processor =& load_class('processor', TRUE, 'lib');
			
			$clean['media_title'] = $processor->process('media_title', array('notags'));
			//$clean['media_caption'] = $processor->process('media_caption',array('nophp'));
			$clean['media_x'] = $processor->process('media_x', array('digit'));
			$clean['media_y'] = $processor->process('media_y', array('digit'));

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
				$this->db->updateArray(PX.'media', $clean, "media_id='$go[id]'"); 
			}
		}
		
		system_redirect("?a=$go[a]&q=files");
	}
	
	
	public function sbmt_clear_cache()
	{
		global $go, $default;

		$CACHE =& load_class('cache', TRUE, 'lib');
		$CACHE->delete_all_cache();

		// send an update notice
		$this->template->action_update = 'cache cleared';		
	}
	
	
	public function sbmt_clear_dimgs()
	{
		global $go, $default;

		$CACHE =& load_class('cache', TRUE, 'lib');
		$CACHE->delete_all_dimgs();

		// send an update notice
		$this->template->action_update = 'dimgs cache cleared';		
	}
	
	
	public function sbmt_upd_backup()
	{
		global $go, $default, $indx;
		$OBJ =& get_instance();

		////////////////////////////////
		$tables = $OBJ->db->fetchArray('SHOW TABLES FROM ' . $indx['db']);
		$sql = '';
		
		// let's rewrite the tables array for better use
		foreach ($tables as $key => $table)
		{
			// these tables get too big for this...
			if (!in_array($table['Tables_in_' . $indx['db']], array('iptocountry', PX . 'stats')))
			{
				$new_tables[] = $table['Tables_in_' . $indx['db']];
			}
		}
		
		foreach ($new_tables as $table)
		{
			$rs = $OBJ->db->fetchArray("SELECT * FROM " . $table);
			
			// RESEARCH THIS
			// $sql .= "\n\nTRUNCATE `" . $table . "`;\n";
			
			if (is_array($rs))
			{
				foreach ($rs as $out)
				{
					$sql .= $this->get_data($table, $out);
				}
			}
		}
		
		$filename = "out_".date("Y-m-d_H-i",time());
		header("Content-type: application/sql");
		//header("Content-disposition: csv" . date("Y-m-d") . ".sql");
		header( "Content-disposition: filename=" . $filename . ".sql");
		print $sql;
		exit;

		// send an update notice
		$this->template->action_update = 'backup exported';		
	}
	
	
	public function get_data($table, $arr)
	{
		$OBJ =& get_instance();

		foreach ($arr as $key => $do) 
		{ 
			if ($do != '') 
			{
				$fields[] = $key;
				$values[] = $OBJ->db->escape( addslashes($do) );
			}
		} 
 
		return "INSERT INTO `$table` (`" . implode("`, `", $fields) . "`)" .
		" VALUES (" . implode(', ', $values) . ");\n" ;
		
		return $d;
	}
	
	
	public function sbmt_edit_tag()
	{
		global $go;
		
		// need to clean it up...unencode...
		load_module_helper('files', $go['a']);
		$clean['tag_name'] = utf8Urldecode($_POST['tag_name']);
		$clean['tag_name'] = str_replace(' ', '_', trim($clean['tag_name']));

		$this->db->updateArray(PX.'tags', $clean, "tag_id='$go[id]'");
		
		$this->update_tag($clean['tag_name'], $go['id']);
			
		// need to complete the cycle
		$this->reset = true;
	}
	
	
	public function sbmt_del_tag()
	{
		global $go, $default;
		
		$delete = (int) $_POST['the-tag'];
		
		// delete tag page
		$this->db->deleteArray(PX.'objects', "object='tag' AND obj_ref_id='$delete'");
		
		// delete tag
		$this->db->deleteArray(PX.'tags', "tag_id='$delete'");
		
		// delete associations
		$this->db->deleteArray(PX.'tagged', "tagged_id='$delete'");	
		
		// need to complete the cycle
		$this->reset = true;
	}
	
	
	public function sbmt_merge_tag()
	{
		global $go, $default;
		
		$merge = (int) $_POST['select-tag'];
		$delete = (int) $_POST['the-tag'];
		
		// deal with dupes first - get a list of tagged for 'select-tag'
		$tagged = $this->db->fetchArray("SELECT tagged_obj_id FROM ".PX."tagged 
			WHERE tagged_id = '$merge'");
			
		if ($tagged)
		{
			// rewrite the array for better use
			foreach ($tagged as $tag)
			{
				$new_tagged[] = $tag['tagged_obj_id'];
			}
			
			// we scan $delete tags and delete any dupes as they are covered by $merge now
			$this->db->query("DELETE FROM ".PX."tagged 
				WHERE tagged_id = '$delete' 
				AND tagged_obj_id IN ('" . implode("','", $new_tagged) . "')");
		}
		
		// merge first
		$this->db->updateArray(PX.'tagged', array('tagged_id' => $merge), "tagged_id='$delete'");
		
		// delete tag page
		$this->db->deleteArray(PX.'objects', "object='tag' AND obj_ref_id='$delete'");
		
		// delete tag
		$this->db->deleteArray(PX.'tags', "tag_id='$delete'");
		
		// need to complete the cycle
		$this->reset = true;
	}
	
	
	public function sbmt_upd_settings()
	{
		global $go, $default;

		$processor =& load_class('processor', TRUE, 'lib');
		
		$clean['site_offset'] = $processor->process('site_offset', array('digit'));
		$clean['site_format'] = $processor->process('site_format', array('notags'));
		$clean['site_lang'] = $processor->process('site_lang', array('notags'));

		$clean['site_vars'] = serialize($_POST['site']);
		$clean['caching'] = $processor->process('caching', array('digit'));

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
			$this->db->updateArray(PX.'settings', $clean, "adm_id='1'");

			// send an update notice
			$this->template->action_update = 'updated';
			system_redirect("?a=$go[a]&q=$go[q]");
		}		
	}
	
	
	public function sbmt_upd_theme()
	{
		global $go, $default;

		$processor =& load_class('processor', TRUE, 'lib');

		$clean['obj_name'] = $processor->process('obj_name',array('notags','reqNotEmpty'));
		$clean['obj_itop'] = $processor->process('obj_itop',array('nophp'));
		$clean['obj_ibot'] = $processor->process('obj_ibot',array('nophp'));
		$clean['obj_theme']	= $processor->process('obj_theme', array('notags'));
		$clean['obj_org']	= $processor->process('obj_org', array('notags'));

		// process the text...
		$this->lib_class('editor');
		
		$clean['obj_itop'] = $this->editor->textProcess($clean['obj_itop'], 1);
		$clean['obj_ibot'] = $this->editor->textProcess($clean['obj_ibot'], 1);

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
			$this->db->updateArray(PX.'settings', $clean, "adm_id='1'");

			// send an update notice
			$this->template->action_update = 'updated';
		}		
	}
	
	
	public function sbmt_upd_profile()
	{
		global $go, $default;

		$p =& load_class('processor', TRUE, 'lib');
		//load_helper('textprocess');

		$clean['pr_desc'] = $p->process('pr_desc', array('nophp'));
		$clean['pr_title'] = $p->process('pr_title', array('notags'));
		$clean['pr_addr1'] = $p->process('pr_addr1', array('notags'));
		$clean['pr_addr2'] = $p->process('pr_addr2', array('notags'));
		$clean['pr_city'] = $p->process('pr_city', array('notags'));
		$clean['pr_zip'] = $p->process('pr_zip', array('notags'));
		$clean['pr_country'] = $p->process('pr_country', array('notags'));
		$clean['pr_email'] = $p->process('pr_email', array('notags'));
		$clean['pr_phone'] = $p->process('pr_phone', array('notags'));
		$clean['pr_ichat'] = $p->process('pr_ichat', array('notags'));
		$clean['pr_msn'] = $p->process('pr_msn', array('notags'));
		$clean['pr_skype'] = $p->process('pr_skype', array('notags'));
		$clean['pr_freelance'] = $p->process('pr_freelance', array('notags'));

		// process the text...
		$clean['pr_desc'] = textProcess($clean['pr_desc'], 1);

		if ($p->check_errors())
		{
			// get our error messages
			$error_msg = $p->get_errors();
			$this->errors = TRUE;
			$GLOBALS['error_msg'] = $error_msg;
			return;
		}
		else
		{
			$this->db->updateArray(PX.'profile', $clean, "pr_id='1'");

			// send an update notice
			$this->template->action_update = 'updated';
		}		
	}
	

	
	public function toggler()
	{
		global $go;

		$out = '';
		
		$go['type'] = getURI('q', 'site', 'alpha', 15);
		
		$site = unserialize($this->access->settings['site_vars']);
		
		// this doesn't really work all that well because of a scope issue
		if ($this->hook->registered_hook('add_system_tab')) 
		{
			$this->hook->do_action('add_system_tab');
		}
		
		foreach ($this->tabs as $key => $tab)
		{
			$show = ($tab == $go['type']) ? " class='tabOn'" : " class='tabOff'";
			$q = ('site' == $tab) ? '' : "&amp;q=$tab" ;
			
			$out .= ($tab == 'spacer') ? 
				li('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', "style='float:left;'") : 
				li(href($this->lang->word('tab_' . $tab), "?a=$go[a]$q"), "style='float:left;'$show");
		}
		
		return ul($out, "class='tabs' style='margin-left: 6px;'").div('<!-- -->', "class='cl'");
	}
	
	

	public function sbmt_add_sec()
	{
		$OBJ =& get_instance();
		$OBJ->template->errors = TRUE;
		global $go;
		
		// can we do this better?
		$processor =& load_class('processor', TRUE, 'lib');
	
		$clean['sec_desc'] = $processor->process('sec_desc', array('notags', 'reqNotEmpty'));
		$clean['section'] = $processor->process('section', array('notags', 'reqNotEmpty'));
		$temp['hsec_ord'] = $processor->process('hsec_ord', array('digit'));
		$temp['sec_prepend'] = $processor->process('sec_prepend', array('notags'));

		if ($processor->check_errors())
		{
			// get our error messages
			$error_msg = $processor->get_errors();
			$this->errors = TRUE;
			$GLOBALS['error_msg'] = $error_msg;
			$this->template->special_js = "toggle('add-sec');";
			return;
		}
		else
		{
			// a few more things
			$clean['sec_date'] 	= getNow();
			$clean['sec_ord']	= $temp['hsec_ord'] + 1;
			$clean['sec_obj']	= 'exhibits';
			
			// we need to romanize the path based upon 'section'
			load_helpers( array('output', 'romanize') );
			$folder_name = load_class('publish', TRUE, 'lib');
			$folder_name->title = trim($clean['section']);
			$clean['section'] = $folder_name->processTitle();
			
			// we need to clean up the path thing
			$clean['sec_path'] = $folder_name->urlStrip($temp['sec_prepend'] . '/' . $clean['section']);
			
			$last = $this->db->insertArray(PX.'sections', $clean);
			
			// need to create the actual page too
			$page['object'] = 'exhibits';
			$page['title'] = $clean['sec_desc'];
			$page['udate'] = getNow();
			$page['pdate'] = getNow();
			$page['creator'] = 1;
			$page['status'] = 0;
			$page['url'] = $folder_name->urlStrip($clean['sec_path'] . '/');
			$page['section_top'] = 1;
			$page['obj_ref_id'] = $last;
			$page['section_id'] = $last;
			$page['ord'] = '0';
			
			$this->db->insertArray(PX.'objects', $page);
			
			system_redirect("?a=$go[a]&q=section&id=$last");
		}
		
		return;
	}
	
	
	public function sbmt_edit_sec()
	{
		global $go;
		
		$processor =& load_class('processor', TRUE, 'lib');
		
		$temp['hsec_ord'] = $processor->process('hsec_ord', array('digit'));
		$temp['hsecid'] = $processor->process('hsecid', array('digit'));
	
		$clean['sec_desc'] = $processor->process('sec_desc', array('notags', 'reqnotempty'));
		$clean['section'] = $processor->process('section', array('nophp', 'reqnotempty'));
		$clean['sec_proj'] = $processor->process('sec_proj', array('nophp'));
		$clean['sec_report'] = $processor->process('sec_report', array('boolean'));
		$clean['sec_hide'] = $processor->process('sec_hide', array('boolean'));
		$clean['sec_pwd'] = $processor->process('sec_pwd', array('notags'));
		$temp['sec_prepend'] = $processor->process('sec_prepend', array('notags'));
		
		// this needs to update the objects table as well
		$clean['sec_obj'] = $processor->process('sec_obj', array('notags', 'reqnotempty'));

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
			// clean the projects project entry
			if (preg_match('/^[0-9].[0-9]/', $clean['sec_proj']))
			{
				$temp = explode('.', $clean['sec_proj']);
				$clean['sec_proj'] = $temp[0];
				$clean['sec_group'] = (isset($temp[1])) ? $temp[1] : '';
			}
			else
			{
				$clean['sec_group'] = '0';
			}
			
			// we need to romanize the path based upon 'section'
			load_helpers( array('output', 'romanize') );
			$folder_name = load_class('publish', TRUE, 'lib');
			$folder_name->title = trim($clean['section']);
			$clean['section'] = $folder_name->processTitle();
			
			// we need to clean up the path thing
			$prepend = (isset($temp['sec_prepend'])) ? $temp['sec_prepend'] : '';
			$clean['sec_path'] = $prepend . '/' . $clean['section'];
			$clean['sec_path'] = $folder_name->urlStrip($clean['sec_path']);
			
			if ($go['id'] == 1) $clean['sec_path'] = '/';
			
			// update the section
			$this->db->updateArray(PX.'sections', $clean, "secid='$go[id]'");
			
			// we need to update the page url
			// we need to check for duplicates again? oi vey...
			$new['url'] = $folder_name->urlStrip($clean['sec_path'] . '/');
			$new['object'] = $clean['sec_obj'];
			
			$this->db->updateArray(PX.'objects', $new, "obj_ref_id = '$go[id]' AND section_top = '1'");
			
			// send an update notice
			$this->template->action_update = 'updated';
		}
	}
	
	
	public function sbmt_upd_tsettings()
	{
		global $go;
		
		$clean['obj_settings'] = serialize($_POST['tag']);
		
		$processor =& load_class('processor', TRUE, 'lib');
		
		$temp['section_id'] 	= $_POST['tag']['section_id'];
		$temp['template'] 		= $_POST['tag']['template'];
		$temp['thumbs'] 		= $_POST['tag']['thumbs'];
		$temp['thumbs_shape'] 	= $_POST['tag']['thumbs_shape'];
		$temp['format'] 		= $_POST['tag']['format'];
		$temp['break'] 			= $_POST['tag']['break'];
		$temp['titling'] 		= $_POST['tag']['titling'];

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
			$this->db->updateArray(PX.'objects_prefs', $clean, "obj_ref_type='tag'");
			
			// update objects with correct template
			$this->db->updateArray(PX.'objects', $temp, "object='tag'");
			
			// update paths
			if ($temp['section_id'] != $_POST['h_secid']) $this->update_tag_paths($_POST['h_secid'], $temp['section_id']);
			//$this->update_tag_paths($_POST['h_secid'], $temp['section']);
			
			// send an update notice
			$this->template->action_update = 'updated';
			
			// update settings for return page
			$this->settings = $temp;
		}
	}
	
	
	
	public function update_tag_paths($old, $new)
	{
		// get records
		$old = (int) $old;
		
		$rs = $this->db->fetchArray("SELECT id, url, sec_path 
			FROM ".PX."objects 
			INNER JOIN ".PX."sections ON secid = '$new' 
			WHERE object = 'tag'");
			
		// for the section
		$old = $this->db->fetchRecord("SELECT sec_path 
			FROM ".PX."sections 
			WHERE secid = '$old'");
		
		if ($rs)
		{
			foreach ($rs as $do)
			{
				$older = str_replace('/', '\/', $old['sec_path']); // prep for preg_replace
				$tmp['url'] = preg_replace("/^$older/", $do['sec_path'], $do['url']);
				$this->db->updateArray(PX.'objects', $tmp, "id='$do[id]' AND object='tag'");
			}
		}
	}	
	
	
	public function sbmt_del_sec()
	{
		global $go;
		
		if ($go['id'] == 1) system_redirect("?a=$go[a]&q=sections");
		
		$processor =& load_class('processor', TRUE, 'lib');
		
		$temp['hsec_ord'] = $processor->process('hsec_ord', array('digit'));
		
		// delete section and object
		$this->db->deleteArray(PX.'sections', "secid = '$go[id]'");
		$this->db->deleteArray(PX.'objects', "obj_ref_id = '$go[id]' AND section_top = '1'");
		
		// don't delete pages - move them to the root section
		$this->db->updateRecord("UPDATE ".PX."objects SET 
			section_id = '1', ord = '999'
			WHERE 
			section_id = '$go[id]'");

		$this->db->updateRecord("UPDATE ".PX."sections SET 
			sec_ord = sec_ord-1
			WHERE 
			(sec_ord > '$temp[hsec_ord]')");
		
		system_redirect("?a=$go[a]&q=sections");
	}
	
	
	public function sbmt_upd_ord()
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
			$i = 1;
			foreach ($order as $do)
			{
				$this->db->updateRecord("UPDATE ".PX."sections SET
					sec_ord 	= ".$this->db->escape($i)." 
					WHERE 
					secid		= ".$this->db->escape($do)."");
				$i++;
			}
		}
		
		// make this better later
		header ('Content-type: text/html; charset=utf-8');
		//echo "<span class='notify'>".$this->lang->word('updated')."</span>";
		exit;
	}
	
	
	public function page_credits()
	{
		$this->template->module_css[] = 'credit.css';

		$body = "<h1>Indexhibit</h1>\n\n";
		$body .= "<p>(Index + Exhibit)</p>\n\n";
		$body .= p(href('Daniel Eatock', 'http://eatock.com/') . ', ' . href('Jeffery Vaska', 'http://vaska.com/') . ' and you.');
		
		//$body .= "<h2>Statement</h2>\n\n";
		$body .= "<h2>A web application used to build and maintain an archetypal, invisible website format that combines text, image, movie and sound.</h2>\n";
		
		//$body .= "<h2>Thank you</h2>\n\n";
		
		$this->template->body = $body;
		$this->template->output('popup');
		exit;
	}
	
	
	public function sbmt_upd_jxcode()
	{
		$OBJ =& get_instance();
		
		load_module_helper('files', 'system');
		
		$clean['name'] = $_POST['id'];
		$content = ($_POST['v'] == '') ? '' : utf8Urldecode($_POST['v']);
		
		// need to deal with \n
		$content = str_replace('\n', '&&&', $content);
		$content = stripslashes($content);
		$content = str_replace('&&&', '\n', $content);
		
		// output
		$filename = $clean['name'];

		if (!$handle = fopen(DIRNAME . '/ndxzsite/' . $filename, 'w+')) 
		{
			// error note
		}
	
		// this check needs to be more specific with file name
		if (is_dir(DIRNAME . '/ndxzsite/') && is_writable(DIRNAME . '/ndxzsite/')) 
		{
			if (fwrite($handle, trim($content)) === FALSE) 
			{  
				// error note
			}
		
			fclose($handle);
		}
	
		clearstatcache();
		
		header ('Content-type: text/html; charset=utf-8');
		echo "<span class='notify'>" . $this->lang->word('updating') . "</span>";
		exit;
	}
	
	
	public function page_jxbackup()
	{
		// validate this
		$clean['name'] = $_GET['edit'];
		$file = DIRNAME . '/ndxzsite' . $clean['name'];
		
		if (file_exists($file)) 
		{
			$name = explode('/', $clean['name']);
			$named = array_pop($name);
			
			header("Content-Type: application/force-download\n");
			header("Content-Disposition: attachment; filename=" . $named . ";");
			@readfile("$file");
		    exit;
		}
	}
	
	
	// returns our tag editor
	public function edtag($id=0)
	{
		global $go;

		load_module_helper('files', $go['a']);
		
		$method = $_POST['method'];
		
		// get tag info
		$tag = $this->db->fetchRecord("SELECT * FROM ".PX."tags WHERE tag_id='$id'");
		
		$s = "<div id='tag-edit' style='margin-bottom: 6px;'>\n";
		$s .= "<input type='text' name='tag-editor' id='tag-editor' value=\"" . str_replace('_', ' ', $tag['tag_name']) . "\" style='width: 100px; display: inline; margin-bottom: 1px;' /> ";
		$s .= "<input type='hidden' name='tag_group' id='tag_group' value=\"1\" /> ";
		$s .= "<input type='button' onclick=\"editor_tag($tag[tag_id], $go[id], '$method'); return false;\" value='Update' style='display: inline; margin-bottom: 1px;' />\n";
		$s .= "<input type='button' onclick=\"delete_tag($tag[tag_id], $go[id], '$method'); return false;\" value='x' style='display: inline; margin-bottom: 1px;' />\n";
		$s .= "<input type='button' onclick=\"$('#tag-edit').remove(); return false;\" value='Cancel' style='display: inline; margin-bottom: 1px;' />\n";
		$s .= "</div>\n";
		
		header ('Content-type: text/html; charset=utf-8');
		echo $s;
		exit;
	}
	
	
	public function resize_images($size=9999, $type='image')
	{
		global $go, $default;
		
		// query for all images
		$images = $this->db->fetchArray("SELECT * 
			FROM ".PX."media, ".PX."objects 
			WHERE media_ref_id = '$go[id]' 
			AND media_ref_id = id 
			AND media_obj_type = 'exhibits' 
			ORDER BY media_order ASC");
			
		if ($images[0]['media_source'] >= 1) exit;
			
		// let's delete first
		if ($images)
		{
			// yeah, we should consolidate our files better
			// this should go into a 'files' helper or something
			load_module_helper('files', 'exhibits');
			load_helper('files');

			foreach ($images as $image)
			{
				// check the mime
				if (in_array($image['media_mime'], $default['images']))
				{
					$file = ($image['media_thumb'] == '') ? $image['media_ref_id'] . '_' . $image['media_file'] :
						$image['media_ref_id'] . '_' . $image['media_thumb'];
				}
				else
				{
					$file = ($image['media_thumb'] != '') ? $image['media_ref_id'] . '_' . $image['media_thumb'] : '';
				}
				
				($type == 'image') ? 
					delete_image(DIRNAME . GIMGS . '/' . $file) : 
					delete_image(DIRNAME . GIMGS . '/th-' . $file);
			}
			
			//load_module_helper('files', $go['a']);
			$IMG =& load_class('media', TRUE, 'lib');
			
			if ($type == 'image')
			{
				$IMG->make_sys = false;
				$IMG->makethumb = false;
				$IMG->make_image = true;
			}
			else
			{
				$IMG->make_sys = false;
				$IMG->makethumb = true;
				$IMG->make_image = false;
			}

			// we'll query for all our defaults first...
			$rs = $this->db->fetchRecord("SELECT thumbs, images, thumbs_shape  
				FROM ".PX."objects    
				WHERE id = '$go[id]'");

			// we need to get these from some defaults someplace
			$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
			$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
			$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
			$IMG->quality = $default['img_quality'];
			
			//$IMG->path = ($) ? DIRNAME . GIMGS . '/';
			//$IMG->path = DIRNAME . GIMGS . '/';

			//load_helper('output');
			$URL =& load_class('publish', TRUE, 'lib');
			
			// do the resizery
			foreach ($images as $image)
			{	
				if (in_array($image['media_mime'], $default['images']))
				{
					// set the paths for this image
					$IMG->path = ($image['media_dir'] == '') ? DIRNAME . GIMGS . '/' : 
						DIRNAME . '/files/' . $image['media_dir'] . '/';

					$IMG->output_path = DIRNAME . GIMGS . '/';

					//$test = ($image['media_thumb'] == '') ? explode('.', strtolower($image['media_file'])) :
					//	explode('.', strtolower($image['media_thumb']));
						
					$test = ($image['media_thumb'] == '') ? $image['media_file'] : $image['media_thumb'];
							
					$tmp = ($image['media_thumb'] == '') ? explode('.', $image['media_file']) :
							explode('.', $image['media_thumb']);

					$thetype = array_pop($tmp);

					$IMG->type = '.' . $thetype;
					$IMG->filename = $test;
					$IMG->origname = $IMG->filename;
				
					$IMG->id = $go['id'] . '_';
					$IMG->filename = $IMG->filename;
				
					$IMG->image = $IMG->path . '/' . $IMG->filename;
					
					// check for black and white flag
					// another time...
					if (preg_match('/^bw/', $IMG->filename))
					{
						$IMG->bw_flag = true;
						//$test[0] = str_replace('bw_', '', $test[0]);
					}

					$IMG->uploader();
				}
				else
				{
					// set the paths for this image
					$IMG->path = DIRNAME . GIMGS . '/';
					$IMG->output_path = DIRNAME . GIMGS . '/';

					if ($image['media_thumb'] != '')
					{
						$test = explode('.', $image['media_thumb']);
						$thetype = array_pop($test);

						$IMG->type = '.' . $thetype;
						$IMG->filename = $test[0] . '.' . $thetype;
						$IMG->origname = $IMG->filename;
				
						$IMG->id = $go['id'] . '_';
						$IMG->filename = $IMG->filename;
				
						$IMG->image = $IMG->path . '/' . $IMG->filename;
						
						// check for black and white flag
						// another time...
						if (preg_match('/^bw/', $IMG->filename))
						{
							$IMG->bw_flag = true;
							//$test[0] = str_replace('bw_', '', $test[0]);
						}

						$IMG->uploader();
					}
				}
			
				//@chmod($IMG->path . '/' . $IMG->filename, 0755);
			}
		}

		echo true;
		exit;
	}
	
	
	public function sbmt_upd_jxs_opt()
	{
		$OBJ =& get_instance();
		$OBJ->lib_class('abstracts');

		/*
			example Array
			(
			    [upd_jxs_opt] => true
			    [id] => 247
			    [v] => 1
				[t] => exhibits
			    [x] => option-placement
			)
		*/
		
		// validate the inputs!
		$OBJ->abstracts->abstract_check($_POST['v'], $_POST['x'], $_POST['t'], $_POST['id']);
		
		echo 'Updating';
		exit;
	}
	
	public function sbmt_upd_jxs()
	{
		$clean['id'] = (int) $_POST['id'];

		switch ($_POST['x']) {
		case 'subdir':
			if ($_POST['v'] == 'add')
			{
				$OBJ =& get_instance();
				
				$OBJ->lib_class('subdirs');
				$OBJ->subdirs->secid = $clean['id'];
				$OBJ->subdirs->get_section_info();
				$OBJ->subdirs->new_title = $_POST['t'];
				$OBJ->subdirs->new_dir = $_POST['d'];
				$OBJ->subdirs->input_flag = $_POST['f'];
				
				load_helpers( array('output', 'romanize') );
				$folder_name = load_class('publish', TRUE, 'lib');
				
				// need to create the actual page too
				$page['object'] = 'exhibits';
				$page['title'] = $OBJ->subdirs->new_title;
				$page['udate'] = getNow();
				$page['pdate'] = getNow();
				$page['creator'] = 1;
				$page['status'] = 0;
				$page['url'] = $folder_name->urlStrip($OBJ->subdirs->section['sec_path'] . '/' . $OBJ->subdirs->new_dir . '/');
				$page['section_top'] = 0;
				$page['media_source'] = 3;
				$page['obj_ref_id'] = 0;
				$page['section_id'] = $clean['id'];
				$page['ord'] = '0';
				$page['subdir'] = 1;

				$last = $OBJ->subdirs->process_subdir_input();
				
				$page['section_sub'] = $last;
				$last = $OBJ->db->insertArray(PX.'objects', $page);
				
				echo $OBJ->subdirs->getSubs($clean['id']);
				exit;
			}
			elseif ($_POST['v'] == 'update')
			{
				$OBJ =& get_instance();
				
				//sid : secid, id : node, t : title, d : dir
				
				$OBJ->lib_class('subdirs');
				$OBJ->subdirs->secid = (int) $_POST['sid'];
				$OBJ->subdirs->get_section_info();
				$OBJ->subdirs->new_title = $_POST['t'];
				$OBJ->subdirs->new_dir = $_POST['d'];
				$OBJ->subdirs->old_dir = $_POST['old'];
				$OBJ->subdirs->new_id = (int) $_POST['id'];
				
				load_helpers( array('output', 'romanize') );
				$folder_name = load_class('publish', TRUE, 'lib');
				$folder_name->title = trim($_POST['d']);
				$clean['section'] = $folder_name->processTitle();
				
				// need to update the actual page too
				$page['title'] = $clean['section'];
				
				// update the subsection
				// title, folder
				$OBJ->db->updateArray(PX.'subsections', array('sub_title' => $OBJ->subdirs->new_title, 'sub_folder' => $clean['section']), "sub_id = '" . $OBJ->subdirs->new_id . "'");
				
				// update the actual page
				$OBJ->db->updateArray(PX.'objects', array('title' => $OBJ->subdirs->new_title), "section_id = '" . (int) $_POST['sid'] . "' AND section_sub = '" . $OBJ->subdirs->new_id . "' AND subdir = '1'");
				
				echo $OBJ->subdirs->getSub();
				exit;
			}
			elseif ($_POST['v'] == 'del')
			{
				$OBJ =& get_instance();
				
				$OBJ->lib_class('subdirs');
				$OBJ->subdirs->secid = $clean['id'];
				$OBJ->subdirs->del_dir = (int) $_POST['n'];
				//$OBJ->subdirs->old_dir = $_POST['old'];
				$OBJ->subdirs->del_subdir_input();
				
				// delete section sub for subs
				$page['section_sub'] = '';
				
				// delete the actual subdir page
				$OBJ->db->deleteArray(PX.'objects', "section_sub = '" . $OBJ->subdirs->del_dir . "' AND subdir = '1'");
				// un subsection exhibit pages too
				$OBJ->db->updateArray(PX.'objects', $page, "section_sub = '" . $OBJ->subdirs->del_dir . "' AND section_top != '1' AND subdir = '0'");

				echo $OBJ->subdirs->getSubs($OBJ->subdirs->secid);
				exit;
			}
			elseif ($_POST['v'] == 'order')
			{
				$OBJ =& get_instance();

				// make this more safe
				$vars = explode('.', $_POST['order']);

				foreach ($vars as $out)
				{
					// fix this later
					if ($out != '')
					{
						$out = preg_replace('/[^[:digit:]]/', '', $out);
						$order[] = $out;
					}
				}
				
				//print_r($order); exit;
				
				if (is_array($order))
				{
					foreach ($order as $key => $ord)
					{
						$OBJ->db->updateArray(PX.'subsections', array('sub_order' => ($key + 1)), "sub_id = '" . $ord . "'");
					}
				}

				// make this better later
				header ('Content-type: text/html; charset=utf-8');
				echo "<span class='notify'>".$this->lang->word('updated')."</span>";
				exit;
			}
			else
			{
				
			}
			break;
		case 'pdate':
			// need to validate the date
			$date = $_POST['v'] . ' ' . date('H') . ':' . date('i') . ':' . date('s');
			$clean['pdate'] = $date;
			break;
		case 'gallery-imgs':
		
			$OBJ =& get_instance();
			global $default;
		
			// we need the info
			$rs = $OBJ->db->fetchRecord("SELECT * FROM ".PX."objects 
				WHERE id='$clean[id]'");
			
			/////////////////

			$class = 'filesource' . $default['filesource'][$rs['media_source']];
			$F =& load_class($class, true, 'lib');
			$F->rs = $rs;

			// get our output
			echo $F->getExhibitImages($clean['id']);

			exit;
			break;
		case 'filesingle': 
			global $default;
			// we need the check the image filesize before we try to load it
			// if it's an image - quick check
			if (file_exists(DIRNAME . '/files/' . $_POST['f'] . '/' . $_POST['v']))
			{
				$test = explode('.', strtolower($_POST['v']));
				$mime = array_pop($test);

				// this check is only for pictures...
				$size = @filesize(DIRNAME . '/files/' . $_POST['f'] . '/' . $_POST['v']);
			}
			
			// only images need this check
			if (($size >= ($default['maxsize'] * 1000)) && (in_array($mime, $default['images'])))
			{
				echo 'The image is too large to load.';
				exit;
				break;
			}
			else
			{
				$RSZ = load_class('resize', true, 'lib');
				$RSZ->single_load_image($_POST['v'], $clean['id'], 9999, 'image', $_POST['f']);
				echo 'yes';
				exit;
				break;
			}
			
		case 'filesall':
			$RSZ = load_class('resize', true, 'lib');
			load_helper('files');
			$theFiles = getTheFiles(DIRNAME . '/files/' . $_POST['f'] . '/', array(''));
			$RSZ->folder_load_images($theFiles, $clean['id'], 9999, 'image', $_POST['f']);
			echo 'yes';
			exit;
			break;
		
		case 'ajx-status':
			if ($clean['id'] == 1) break;
			$clean['status'] = (int) $_POST['v'];
			$this->pub_status = $clean['status'];
			$this->page_id = $clean['id'];
			$this->publisher();
			break;
		case 'ajx-images':
			$clean['images'] = (int) $_POST['v'];
			break;
		case 'edtag':
			$this->edtag($clean['id']);
			break;
		case 'ajx-process':
			$clean['process'] = (int) $_POST['v'];
			break;
		case 'ajx-gallery':
			$clean['media_info'] = (int) $_POST['v'];
			break;
		case 'passwords':
			$replace = ($_POST['v'] == 'on') ? false : true;
			
			if ($replace == true)
			{
				$out = href('on', '#', "class='t-on' onclick=\"do_settings('passwords'); return false;\" title='on'");
				
				// update site vars
				$end = unserialize($this->access->settings['site_vars']);
				$end['site_passwords'] = 1;
			}
			else
			{
				$out = href('off', '#', "class='t-off' onclick=\"do_settings('passwords'); return false;\" title='off'");
				
				// update site vars
				$end = unserialize($this->access->settings['site_vars']);
				$end['site_passwords'] = 0;
			}
			
			$vars['site_vars'] = serialize($end);
			$this->db->updateArray(PX.'settings', $vars, "adm_id='1'");
			
			echo $out; exit;
			break;
			
		case 'addasset':
		
			$OBJ =& get_instance();
			$out = '';
			
			// get the info and object
			$asset = $OBJ->db->fetchRecord("SELECT * FROM ".PX."media 
				INNER JOIN ".PX."objects_prefs ON obj_id = '$_POST[od]' 
				WHERE media_id = '$_POST[id]'");
			
			// update the order of the assets
			$OBJ->db->updateRecord("UPDATE ".PX."set_assets SET set_order = set_order + 1 
				WHERE set_obj_id = '10'");
			
			// insert into the collect table and oid	
			$insert['set_obj_id'] = $_POST['od'];
			$insert['set_media_id'] = $_POST['id'];
			$insert['set_title'] = $asset['media_title'];
			$insert['set_caption'] = $asset['media_caption'];
			$OBJ->db->insertArray(PX.'set_assets', $insert);
			
			$out .= "<div id='thepic$asset[media_id]' style='float: left; width: 95px; height: 115px;'>\n";
			$out .= "<div style='width: 75px; height: 75px;'>\n";
			$out .= "<a href='#' onclick=\"delAsset($asset[media_id]); return false;\" style='background: transparent;'>";
			$out .= "<img src='" . BASEURL . GIMGS . '/sys-' . $asset['media_file'] . "' width='75' height='75' />";
			$out .= "</a>\n";
			$out .= "</div>\n";
			$out .= "</div>\n";
		
			echo $out; exit;
			break;
			
		case 'delasset':

			$OBJ =& get_instance();
			$out = '';

			// get the info and object
			$asset = $OBJ->db->fetchRecord("SELECT * FROM ".PX."media 
				INNER JOIN ".PX."objects_prefs ON obj_id = '$_POST[od]' 
				INNER JOIN ".PX."set_assets ON set_media_id = '$_POST[id]' 
				WHERE media_id = '$_POST[id]'");

			// insert into the collect table and oid
			$OBJ->db->deleteArray(PX.'set_assets', "set_media_id = '$_POST[id]'");
			
			// update the order of the assets
			$OBJ->db->updateRecord("UPDATE ".PX."set_assets SET set_order = set_order - 1 
				WHERE set_order > '$asset[set_order]'");

			$out .= "<div id='pic$asset[media_id]' style='float: left; width: 95px; height: 115px;'>\n";
			$out .= "<div style='width: 75px; height: 75px;'>\n";
			$out .= "<a href='#' onclick=\"addAsset($asset[media_id]); return false;\" style='background: transparent;'>";
			$out .= "<img src='" . BASEURL . GIMGS . '/sys-' . $asset['media_file'] . "' width='75' height='75' />";
			$out .= "</a>\n";
			$out .= "</div>\n";
			$out .= "</div>\n";

			echo $out; exit;
			break;
			
		case 'assets':
			
			$OBJ =& get_instance();
			$out = '';
			
			$assets = $OBJ->db->fetchArray("SELECT * FROM ".PX."media ORDER BY media_id DESC LIMIT 0,100");
			
			if (!$assets)
			{
				$out .= "Nothing!";
			}
			else
			{
				$no = round( count($assets) * 95 ) + 1;
				$out .= "<div id='part' style='width: {$no}px; padding: 20px 0 0 20px;'>\n";
			
				foreach ($assets as $key => $asset)
				{
					$out .= "<div id='pic$asset[media_id]' style='float: left; width: 95px; height: 75px;'>\n";
					$out .= "<div style='width: 100px; height: 75px;'>\n";
					$out .= "<a href='#' onclick=\"addAsset($asset[media_id]); return false;\" style='background: transparent;'>";
					$out .= "<img src='" . BASEURL . GIMGS . '/sys-' . $asset['media_file'] . "' width='75' height='75' />";
					$out .= "</a>\n";
					$out .= "</div>\n";
					$out .= "</div>\n";
				}
				
				$out .= "<div style='clear: left;'><!-- --></div>\n";
				$out .= "</div>\n";
			}
			
			echo $out; exit;
			break;
		
		// get flickr image
		case 'doqueue':
		
			// use some curl to get the things
			$img = $_POST['v'];
			$fullpath = basename($img);
			
			$filename = explode('/', $img);
			$filename = array_pop($filename);
			//$filename = 'flickr5.jpg';

			$ch = curl_init($img);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			$rawdata = curl_exec($ch);
			
			if (strpos($rawdata, "Not Found") === false) 
			{
				if (file_exists($fullpath)) { unlink($fullpath); }
				$fp = fopen(DIRNAME . GIMGS . '/' . $filename, 'x');
			    fwrite($fp, $rawdata);
			    fclose($fp);
			    //$this->log("success");
			
				chmod(DIRNAME . GIMGS . '/' . $filename, 0777);
			}
			else 
			{
			    //$this->log("fail");
			}
			curl_close ($ch);
			
			// add into to the database
			$OBJ =& get_instance();
			global $default;
			
			$insert['media_ref_id'] = (int) $_POST['id'];
			$insert['media_file'] = $filename;
			$insert['media_order'] = 999;
			$insert['media_mime'] = 'jpg';
			$insert['media_obj_type'] = 'exhibit';
			$insert['media_src'] = 'flickr';
			
			$OBJ->db->insertArray(PX.'media', $insert);
			
			$IMG =& load_class('media', true, 'lib');
			$IMG->path = DIRNAME . GIMGS . '/';
			// we'll query for all our defaults first...
			$rs = $OBJ->db->fetchRecord("SELECT thumbs, images, thumbs_shape   
				FROM ".PX."objects    
				WHERE id = '$_POST[id]'");
			// we need to get these from some defaults someplace
			$IMG->thumbsize = ($rs['thumbs'] != '') ? $rs['thumbs'] : 200;
			$IMG->maxsize = ($rs['images'] != '') ? $rs['images'] : 9999;
			$IMG->quality = $default['img_quality'];
			$IMG->shape = ($rs['thumbs_shape'] != '') ? $rs['thumbs_shape'] : 0;
			$IMG->makethumb	= true;
			$IMG->type = '.' . 'jpg';
			//$IMG->filename = $IMG->checkName($_POST['id'] . '_' . $new_title) . '.' . $thetype;
			$IMG->filename = $filename;
			$IMG->origname = $IMG->filename;
			$IMG->id = $_POST['id'] . '_';
			$IMG->filename = $IMG->filename;
			$IMG->image = $IMG->path . '/' . $IMG->filename;
			$IMG->uploader();
		
			echo $img; exit;
			break;
		
	case 'atags':
			$replace = ($_POST['v'] == 'on') ? false : true;

				if ($replace == true)
				{
					$out = href('on', '#', "class='t-on' onclick=\"do_settings('atags'); return false;\" title='on'");

					// update site vars
					$end = unserialize($this->access->settings['site_vars']);
					$end['site_tags'] = 1;
				}
				else
				{
					$out = href('off', '#', "class='t-off' onclick=\"do_settings('atags'); return false;\" title='off'");

					// update site vars
					$end = unserialize($this->access->settings['site_vars']);
					$end['site_tags'] = 0;
				}

				$vars['site_vars'] = serialize($end);
				$this->db->updateArray(PX.'settings', $vars, "adm_id='1'");

			echo $out; exit;
			break;
		case 'templates':
			$replace = ($_POST['v'] == 'on') ? false : true;

			if ($replace == true)
			{
				$out = href('on', '#', "class='t-on' onclick=\"do_settings('templates'); return false;\" title='on'");

				// update site vars
				$end = unserialize($this->access->settings['site_vars']);
				$end['site_templates'] = 1;
			}
			else
			{
				$out = href('off', '#', "class='t-off' onclick=\"do_settings('templates'); return false;\" title='off'");

				// update site vars
				$end = unserialize($this->access->settings['site_vars']);
				$end['site_templates'] = 0;
			}

			$vars['site_vars'] = serialize($end);
			$this->db->updateArray(PX.'settings', $vars, "adm_id='1'");

			echo $out; exit;
			break;
		case 'stats':
				$replace = ($_POST['v'] == 'on') ? false : true;

				if ($replace == true)
				{
					$out = href('on', '#', "class='t-on' onclick=\"do_settings('stats'); return false;\" title='on'");

					// update site vars
					$end = unserialize($this->access->settings['site_vars']);
					$end['site_stats'] = 1;
				}
				else
				{
					$out = href('off', '#', "class='t-off' onclick=\"do_settings('stats'); return false;\" title='off'");

					// update site vars
					$end = unserialize($this->access->settings['site_vars']);
					$end['site_stats'] = 0;
				}

				$vars['site_vars'] = serialize($end);
				$this->db->updateArray(PX.'settings', $vars, "adm_id='1'");

			echo $out; exit;
			break;
			
		case 'editor':
			$replace = ($_POST['v'] == 'on') ? false : true;

			if ($replace == true)
			{
				$out = href('on', '#', "class='t-on' onclick=\"do_settings('editor'); return false;\" title='on'");

				// update site vars
				$end = unserialize($this->access->settings['site_vars']);
				$end['site_editor'] = 1;
			}
			else
			{
				$out = href('off', '#', "class='t-off' onclick=\"do_settings('editor'); return false;\" title='off'");

				// update site vars
				$end = unserialize($this->access->settings['site_vars']);
				$end['site_editor'] = 0;
			}

			$vars['site_vars'] = serialize($end);
			$this->db->updateArray(PX.'settings', $vars, "adm_id='1'");

			echo $out; exit;
			break;
			
		case 'tagme':
			global $go;

			$tmp['tagged_id'] 		= $clean['id'];
			$tmp['tagged_obj_id'] 	= $_POST['fid'];
			$tmp['tagged_object'] 	= 'img';

			$this->db->insertArray(PX.'tagged', $tmp);
				
			echo 'Done'; exit;
			break;
				
		case 'uptag':
			global $go;
			
			$clean['tag_name'] = $_POST['v'];
			$tmp['tag_id'] = $clean['id'];
			//$clean['tag_group'] = (int) $_POST['group'];
			$clean['tag_group'] = 1;
			$method = $_POST['method'];
			unset($clean['id']);
			
			// need to clean it up...unencode...
			load_module_helper('files', $go['a']);
			$clean['tag_name'] = utf8Urldecode($clean['tag_name']);
			$clean['tag_name'] = str_replace(' ', '_', trim($clean['tag_name']));

			$this->db->updateArray(PX.'tags', $clean, "tag_id='$tmp[tag_id]'");
			
			$this->update_tag($clean['tag_name'], $tmp['tag_id']);
			
			$this->lib_class('tag');
			$this->tag->method = $method;
			$this->tag->id = (int) $_POST['p']; // this is the page id
			
			// how do we get the active group?
			$group = $this->db->fetchRecord("SELECT sec_group FROM ".PX."sections, ".PX."objects 
				WHERE id = '" . $this->tag->id . "' AND secid = section_id");

			echo $this->tag->get_active_tags2($group['sec_group']);
			exit;
			break;
			
		case 'deltag':
			global $go;
			$this->db->deleteArray(PX.'tags', "tag_id = '$clean[id]'");
			$this->db->deleteArray(PX.'objects', "obj_ref_id = '$clean[id]' AND object = 'tag'");
			
			$this->lib_class('tag');
			$this->tag->method = $_POST['method'];
			$this->tag->id = (int) $_POST['p'];
			
			if ($this->tag->method == 'exh')
			{
				$rs = $this->db->fetchRecord("SELECT tags FROM ".PX."objects WHERE id='" . $this->tag->id . "'");
				$this->tag->active_tags = $rs['tags'];
			}
			else
			{
				$rs = $this->db->fetchRecord("SELECT media_tags FROM ".PX."media WHERE media_id='" . $this->tag->id . "'");
				$this->tag->active_tags = $rs['media_tags'];
			}

			echo $this->tag->get_active_tags2();
			exit;
			break;
					
		case 'ajx-comments':
			$clean['commenting'] = (int) $_POST['v'];
			
			$clean['cdate'] = ($clean['commenting'] == 1) ? 
				$this->comment_expiration(1, $this->settings['expiration'], getNow()) :
				'0000-00-00 00:00:00';
			
			break;
		case 'ajx-hidden':
			$clean['hidden'] = (int) $_POST['v'];
			break;
		case 'ajx-tiling':
			$clean['tiling'] = (int) $_POST['v'];
			break;
		case 'color':
			$clean['color'] = $_POST['v'];
			break;
		case 'password':
			$clean['pwd'] = $_POST['v'];
			break;
		case 'year':
			$clean['year'] = $_POST['v'];
			break;
		case 'present':
			$clean['format'] = $_POST['v'];
			break;
		case 'tags':
			global $go;
	
			$this->lib_class('tag');
			$this->tag->method = $_POST['method'];
			$clean['tags'] = $_POST['v'];

			if ($this->tag->method == 'exh')
			{
				// we would need to query table for records IN
				$in = $this->db->fetchArray("SELECT tagged_id FROM ".PX."tagged  
					WHERE tagged_obj_id='$go[id]' AND tagged_object = 'exh'");
					
					//echo "SELECT tagged_id FROM ".PX."tagged  
					//	WHERE tagged_obj_id='$go[id]' AND tagged_object = 'exh'"; exit;
	
				// determine which ones are not (new and old)
				// in in array ignore...if not in array add...but what about delete?
				if ($in)
				{
					$tags = explode(',', $clean['tags']);
					foreach ($in as $t) $tagged[] = $t['tagged_id'];
					
					// everything involved - new and old
					$adjust = array_unique(array_merge($tags, $tagged));
					
					// loop through all
					foreach ($adjust as $tag)
					{
						// add to table
						if ((in_array($tag, $tags)) && (!in_array($tag, $tagged)))
						{
							// add
							$cleaned['tagged_id'] = $tag;
							$cleaned['tagged_object'] = 'exh';
							$cleaned['tagged_obj_id'] = $go['id'];

							// temp fix
							if ($cleaned['tagged_id'] > 0) $this->db->insertArray(PX.'tagged', $cleaned);
						}
						elseif ((in_array($tag, $tagged)) && (!in_array($tag, $tags)))
						{
							// delete
							$this->db->deleteArray(PX.'tagged', "tagged_id='$tag' AND tagged_obj_id='$go[id]'");
						}
						else
						{
							// do nothing
						}	
					}
				}
				else // add them all
				{
					$tags = explode(',', $clean['tags']);
					
					foreach ($tags as $tag)
					{
						// add
						$cleaned['tagged_id'] = $tag;
						$cleaned['tagged_object'] = 'exh';
						$cleaned['tagged_obj_id'] = $go['id'];
	
						$this->db->insertArray(PX.'tagged', $cleaned);
					}
				}

				$this->tag->active_tags = explode(',', $clean['tags']);
			}
			else // images
			{
				// we would need to query table for records IN
				$in = $this->db->fetchArray("SELECT tagged_id FROM ".PX."tagged  
					WHERE tagged_obj_id='$go[id]' AND tagged_object = 'img'");
	
				// determine which ones are not (new and old)
				// in in array ignore...if not in array add...but what about delete?
				if ($in)
				{
					$tags = explode(',', $clean['tags']);
					foreach ($in as $t) $tagged[] = $t['tagged_id'];
					
					// everything involved - new and old
					$adjust = array_unique(array_merge($tags, $tagged));
					
					// loop through all
					foreach ($adjust as $tag)
					{
						// add to table
						if ((in_array($tag, $tags)) && (!in_array($tag, $tagged)))
						{
							// add
							$cleaned['tagged_id'] = $tag;
							$cleaned['tagged_object'] = 'img';
							$cleaned['tagged_obj_id'] = $go['id'];

							// temp fix
							if ($cleaned['tagged_id'] > 0) $this->db->insertArray(PX.'tagged', $cleaned);
						}
						elseif ((in_array($tag, $tagged)) && (!in_array($tag, $tags)))
						{
							// delete
							$this->db->deleteArray(PX.'tagged', "tagged_id='$tag' AND tagged_obj_id='$go[id]'");
						}
						else
						{
							// do nothing
						}	
					}
				}
				else // add them all
				{
					$tags = explode(',', $clean['tags']);
					
					foreach ($tags as $tag)
					{
						// add
						$cleaned['tagged_id'] = $tag;
						$cleaned['tagged_object'] = 'img';
						$cleaned['tagged_obj_id'] = $go['id'];
	
						$this->db->insertArray(PX.'tagged', $cleaned);
					}
				}

				$this->tag->active_tags = explode(',', $clean['tags']);
			}
			
			// ouch...we need to know if this page is in an active section
			$group = $this->db->fetchRecord("SELECT sec_group FROM ".PX."objects, ".PX."sections 
				WHERE id='$go[id]' 
				AND object = 'exhibit' 
				AND section_top != '1' 
				AND section_id = secid");
			
			$this->tag->id = $go['id'];
			echo $this->tag->get_active_tags2($group['sec_group']);
			exit;
			break;
				
		case 'break':
			$clean['break'] = (int) $_POST['v'];
			break;
		case 'gallery':
			$clean['media_info'] = $_POST['v'];
			break;
		case 'extend':
			$extend = (int) $_POST['v'];
			$cdate = $_POST['date']; // validate time?
			
			$clean['cdate'] = (strftime("%Y-%m-%d %H:%M:%S", strtotime($cdate)) > getNow()) ? $cdate : getNow();
			$clean['cdate'] = $this->comment_expiration(1, $extend, $clean['cdate']);
			
			$this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");

			header ('Content-type: text/html; charset=utf-8');
			echo "<span class='grn-text'>" . $clean['cdate'] . "</span>";
			exit;
			break;
		case 'c_delete':
			$id = (int) $_POST['id'];
			$this->delete_comment($id);
			exit;
			break;
		case 'comment':
			$id = (int) $_POST['id'];
		
			if ($_POST['y'] == 'get')
			{
				$this->get_comment_editor($id);
			}
			else
			{
				load_module_helper('files', 'blog');
				$text = ($_POST['t'] == '') ? '' : utf8Urldecode($_POST['t']);
				$this->update_comment($id, $text);
			}

			exit;
			break;
		case 'title':
			if ($_POST['update_value'] == '') { echo 'Error'; exit; }
			$clean['title'] = $_POST['update_value'];
			$this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");
			
			header ('Content-type: text/html; charset=utf-8');
			echo $clean['title'];
			exit;
			break;
		case 'resize':
			$id = (int) $_POST['sz'];
			$type = ($_POST['p'] == 'ajx-images') ? 'image' : 'thumbs';
			$this->resize_images($id, $type);
			exit;
			break;
			
		case 'reset_stats':
		
			// empty the statistics tables
			$this->db->query("TRUNCATE TABLE ".PX."stats");
			$this->db->query("TRUNCATE TABLE ".PX."stats_exhibits");
			$this->db->query("TRUNCATE TABLE ".PX."stats_storage");
		
			echo 'Done';
			exit;
			break;
		}
		
		if ($clean['id'] > 0) $this->db->updateArray(PX.'objects', $clean, "id='$clean[id]'");

		echo "<span class='notify'>" . $this->lang->word('updating') . "</span>";
		exit;
	}
	
	public function publish_tag($tag, $id)
	{
		global $default;
		
		// need to get some preferences
		$rsa = $this->db->selectArray(PX . 'objects_prefs', array('obj_ref_type' => 'tag'), 'record');
		$this->settings = unserialize($rsa['obj_settings']);
		
		$rs = $this->db->selectArray(PX . 'sections', array('secid' => $this->settings['section_id']), 'record');
			
		load_helper('output');
		load_helper('romanize');
		$URL =& load_class('publish', TRUE, 'lib');

		// make the url
		$URL->title = $tag;
		$URL->section = $rs['sec_path'];
		$check_url = $URL->makeURL();
		
		// check for dupe
		$check = $this->db->fetchArray("SELECT id 
			FROM ".PX."objects 
			WHERE url = '$check_url'");
			
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
		$clean['status'] 	= 1;
		$clean['udate'] 	= getNow();
		$clean['pdate'] 	= getNow();
		$clean['url'] 		= $clean['url'];
		$clean['object'] 	= 'tag';
		$clean['obj_ref_id'] = $id;
		$clean['section_id'] = $this->settings['section_id'];
		$clean['creator']	= 1;
		$clean['title']		= $tag;
		$clean['template']	= $this->settings['template'];
		$clean['media_source'] = 4;

		$this->db->insertArray(PX.'objects', $clean);
	}
	
	
	public function update_tag($tag, $id)
	{
		$OBJ =& get_instance();
		global $default;
		
		// need to get some preferences and settings
		$settings = $OBJ->db->fetchRecord("SELECT * FROM ".PX."objects_prefs WHERE obj_ref_type = 'tag'");
		$tag_vars = unserialize($settings['obj_settings']);
		$section = $OBJ->db->fetchRecord("SELECT sec_path FROM ".PX."sections WHERE secid = '$tag_vars[section_id]'");
			
		load_helper('output');
		load_helper('romanize');
		$URL =& load_class('publish', TRUE, 'lib');

		// make the url
		$URL->title = $tag;
		$URL->section = ($section['sec_path'] == '/') ? '/' : $section['sec_path'] . '/';
		$check_url = $URL->makeURL();
		
		// check for dupe
		$check = $this->db->fetchArray("SELECT id 
			FROM ".PX."objects 
			WHERE url = '$check_url' 
			AND object = 'tag' 
			AND obj_ref_id != '$id'");
			
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
		$clean['status'] 	= 1;
		$clean['udate'] 	= getNow();
		$clean['url'] 		= $clean['url'];
		$clean['section_id'] = $tag_vars['section_id'];
		$clean['title']		= str_replace('_', ' ', $tag);
		$clean['template']	= "$tag_vars[template]";
		$clean['object']	= 'tag';

		$this->db->updateArray(PX.'objects', $clean, "obj_ref_id='$id' AND object = 'tag'");
	}
	

	public function sbmt_upd_jxtag()
	{	
		global $go;
		
		if ($_POST['x'] == 'reload')
		{
			$this->lib_class('tag');
			echo $this->tag->get_all_tags_count();
			exit;
		}

		// explode around ','
		load_module_helper('files', 'exhibits');
		$tags = explode(',', utf8Urldecode($_POST['v']));
		$clean['tag_group'] = 1;
		
		foreach ($tags as $tag)
		{
			// need to validate
			// special validation for anything beginning with #
			$clean['tag_name'] = str_replace(' ', '_', trim($tag));
			$last = $this->db->insertArray(PX.'tags', $clean);
			
			// publish the page for them
			$this->publish_tag($clean['tag_name'], $last);
		}
		
		$this->lib_class('tag');
		$this->tag->method = $_POST['method'];
		
		// get tags
		if ($this->tag->method == 'exh')
		{
			$rs = $this->db->fetchRecord("SELECT id FROM ".PX."objects
				WHERE id='$_POST[id]'");

			$this->tag->id = $rs['id'];
			$this->tag->method = 'exh';
		}
		else
		{
			$rs = $this->db->fetchRecord("SELECT media_id FROM ".PX."media 
				WHERE media_id='$_POST[id]'");

			$this->tag->id = $rs['media_id'];
			$this->tag->method = 'img';
		}
		
		// master tag list?
		if ($_POST['x'] == 'addtag')
		{
			echo $this->tag->get_active_tags2();
		}
		else
		{
			echo $this->tag->get_all_tags_count();
		}

		exit;
	}
	
	
	public function page_users_edit()
	{
		global $go, $default;
		
		$this->template->add_js('jquery.js');
		$this->template->add_module_js('system.js');
		
		$this->template->location = $this->lang->word('edit');
		
		// sub-locations
		//$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]");
		
		$b = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		
		$rs = $this->db->fetchRecord("SELECT * FROM ".PX."users WHERE ID=" . $go['id'] . "");
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$b .= $this->toggler();
		
		$b .= "<div class='bg-grey corners'>\n";
		$b .= "<div class='c1'>\n";
		
		// user info
		//$title = ($rs['user_title'] == '') ? '' : ', ' . $rs['user_title'];
		$pre_span = ($rs['user_admin'] == 1) ? 
			span($this->lang->word('admin'), "style='font-weight: normal; font-size: 11px;'") :
			span($this->lang->word('user'), "style='font-weight: normal; font-size: 11px;'");
			
		$img = ($rs['user_img'] == '') ? td("<div style='width: 45px; float: left;'><div style='width: 35px; height: 35px; border: 1px solid #ccc; border: 1px solid #ccc;'>&nbsp;</div></div>") : 
			td("<div style='width: 45px; float: left;'><div style='width: 35px; height: 35px; border: 1px solid #ccc;'><img src='" . BASEURL . "/files/$rs[user_img]' width='35' /></div></div>");
		
		$b .= "<div class='col' style='padding-bottom: 12px;'>$img <h3>$rs[user_name] $rs[user_surname] <br />$pre_span</h3></div>\n";
		$b .= "<div class='cl'><!-- --></div>\n";
		$b .= "</div>\n";
		
		$b .= "<div class='c3'>\n";
		// First column
		$b .= "<div class='col'>\n";
		$b .= ips($this->lang->word('user name'), 'input', 'user_name', $rs['user_name'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
		$b .= ips($this->lang->word('user last name'), 'input', 'user_surname', $rs['user_surname'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
		$b .= ips($this->lang->word('user email'), 'input', 'email', $rs['email'], "maxlength='100'", 'text', $this->lang->word('required'),'req');
		$b .= ips($this->lang->word('user id'), 'input', 'userid', $rs['userid'], "maxlength='50'", 'text', $this->lang->word('required'),'req');
		$b .= ips($this->lang->word('user active'), 'getGeneric', 'user_active', $rs['user_active']);
		
		if ($rs['ID'] != 1)
		{
			$b .= ips($this->lang->word('admin status'), 'getGeneric', 'user_admin', $rs['user_admin']);
		}
		
		// when the email and login are set a password can be sent
		if ($this->access->prefs['ID'] != $rs['ID'])
		{
			if (($rs['email'] != '') && ($rs['userid'] != ''))
			{
				$b .= input('sendlogin', 'button', "onclick=\"transmit($go[id]); return false;\"", $this->lang->word('transmit login info')) . ' ';
			}
		}

		$b .= input('upd_user', 'submit', null, $this->lang->word('update'));
		
		if (($this->access->prefs['ID'] != $rs['ID']) || ($rs['ID'] != 1))
		{
			$b .= input('del_user', 'button', "onclick=\"window.location.href = '?a=system&q=users&x=del&id=$go[id]'\"", $this->lang->word('delete'));
		}

		$b .= "</div>\n";
		
		// Second column
		$b .= "<div class='dcol' style='width: 550px;'>\n";
		$b .= "</div>\n";
		
		$b .= "<div class='cl'><!-- --></div>\n";
		$b .= "</div>";
		$b .= "</div>";
	
		
		$this->template->body = $b;
		$this->template->output('index');
		exit;
	}


	public function page_users_del()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		if ($go['id'] == 1) return;
		
		$this->template->location = $this->lang->word('delete');
		
		// sub-locations
		//$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]");
		
		$b = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		
		$rs = $this->db->fetchRecord("SELECT * FROM ".PX."users WHERE ID=" . $go['id'] . "");
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$b .= $this->toggler();

		$b .= "<div class='bg-grey'>\n";
		$b .= "<div class='c1'>\n";
		
		// user info
		//$title = ($rs['user_title'] == '') ? '' : ', ' . $rs['user_title'];
		$pre_span = span($this->lang->word('delete'), "class='bg-red wht-text pad3'");
		
		$b .= "<div class='col' style='padding-bottom: 12px;'><h3>$pre_span &nbsp;$rs[user_name] $rs[user_surname]</h3></div>\n";
		$b .= "<div class='cl'><!-- --></div>\n";
		$b .= "</div>\n";
		
		$b .= "<div class='c3'>\n";
		// First column
		$b .= "<div class='col'>\n";
		
		$b .= p($this->lang->word('this action is not undoable'), "class='red-text'");
		
		$b .= p("It is recommended that you make this user inactive instead of deletion. User generated content throughout the system may become unavailable.");
		
		$b .= p($this->lang->word('proceed with caution'), "class='red-text'");
		
		if ($go['id'] != 1) $b .= input('del_user', 'submit', null, $this->lang->word('delete'));

		$b .= input('cancel', 'button', "onclick=\"document.location.href='?a=$go[a]&q=users'\"", $this->lang->word('cancel'));
		$b .= "</div>\n";
		
		$b .= "<div class='cl'><!-- --></div>\n";
		$b .= "</div>";
		$b .= "</div>";
	
		
		$this->template->body = $b;
		$this->template->output('index');
		exit;
	}
	
	
	public function createRandomPassword()
	{
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		
	    srand((double)microtime()*1000000);
	    $i = 0;
	    $pass = '' ;

	    while ($i <= 7) 
		{
	        $num = rand() % 33;
	        $tmp = substr($chars, $num, 1);
	        $pass = $pass . $tmp;
	        $i++;
	    }

	    return $pass;
	}
	

	public function sbmt_sendlogin()
	{
		global $go;

		$id = (int) $_POST['id'];

		// make password
		$temp['password'] = $this->createRandomPassword();
		$clean['password'] = md5($temp['password']);

		// get user info
		$rs = $this->db->fetchRecord("SELECT * FROM ".PX."users WHERE ID='$id'");

		// update password
		if ($rs) $this->db->updateArray(PX.'users', $clean, "ID='$id'");

		#produce message in html format 
		$body = "Your password was updated.\n\n";
		$body .= "Enclosed are your login details - you may change these after you login.\n\n";
		$body .= "Login: $rs[userid]\n";
		$body .= "Password: $temp[password]\n\n";
		$body .= "URL: " . BASEURL . "/ndxzstudio/";

		$mail =& load_class('mail', true, 'lib');

		$mail->setTo($rs['email'], 'From your website');
		$mail->setSubject('Indexhibit Password Reset');
		$mail->setMessage($body);
		$mail->addMailHeader('Reply-To', 'noreply@indexhibit.org', 'indexhibit.org');
		$mail->addGenericHeader('X-Mailer', 'PHP/' . phpversion());
		$mail->addGenericHeader('Content-Type', 'text/html; charset="utf-8"');
		$mail->setWrap(100);

		$mail->send();

		if($rs) 
		{ 
		   	echo 'Message was sent. '; 
			echo "Temp password is '$temp[password]'.";
		} 
		else 
		{ 
			echo 'Message was not sent - there was an error. '; 
		}

		exit;
	}

	
	// we need a way to protect these page from outside access
	public function sbmt_add_user()
	{
		$OBJ->template->errors = TRUE;
		global $go;
		
		// can we do this better?
		$processor =& load_class('processor', TRUE, 'lib');
	
		$clean['user_name'] = $processor->process('user_name', array('notags', 'reqNotEmpty'));
		$clean['user_surname'] = $processor->process('user_surname', array('notags', 'reqNotEmpty'));

		if ($processor->check_errors())
		{
			// get our error messages
			$error_msg = $processor->get_errors();
			$this->errors = TRUE;
			$this->template->action_error = 'Error';
			$this->template->onready[] = "toggle('add-user');";
			return;
		}
		else
		{
			// add user
			$last = $this->db->insertArray(PX.'users', $clean);
			
			// send to complete
			system_redirect("?a=$go[a]&q=users&x=edit&id=$last");
		}
		
		return;
	}
	
	
	public function sbmt_del_user()
	{
		global $go, $default;
		
		// can not delete user #1 or current user in use
		if (($go['id'] != 1) || ($go['id'] == $this->access->prefs['ID'])) $this->db->deleteArray(PX.'users', "ID='$go[id]'");
		
		// send to complete
		system_redirect("?a=$go[a]&q=users");
	}
	
	
	/* not used */
	public function sbmt_deact_user()
	{
		global $go, $default;
		
		// can not delete user #1
		$clean['user_active'] = '0'; 
		
		if ($go['id'] != 1) $this->db->updateArray(PX.'users', $clean, "ID='".$go['id']."'");
		
		// send to complete
		system_redirect("?a=$go[a]&q=users");
	}
	
	
	public function sbmt_upd_user()
	{
		global $go, $default;
		
		load_module_helper('files', $go['a']);
		
		$p =& load_class('processor', TRUE, 'lib');
	
		$clean['user_name'] 	= $p->process('user_name', array('notags', 'reqNotEmpty'));
		$clean['user_surname'] 	= $p->process('user_surname', array('notags', 'reqNotEmpty'));
		$clean['email'] 		= $p->process('email', array('notags', 'reqNotEmpty'));
		$clean['userid']		= $p->process('userid', array('notags', 'reqNotEmpty'));
		
		//print_r($clean); exit;
		
		// initial user OR if user equals themself
		if (($go['id'] != 1) || ($go['id'] == $this->access->prefs['ID']))
		{
			$clean['user_admin']	= $p->process('user_admin', array('notags', 'boolean', 'reqNotEmpty'));
			$clean['user_active']	= $p->process('user_active', array('notags', 'boolean', 'reqNotEmpty'));
		}

		// deal with password stuff...

		if ($p->check_errors())
		{
			// get our error messages
			$error_msg = $p->get_errors();
			$this->errors = TRUE;
			$GLOBALS['error_msg'] = $error_msg;
			$this->template->action_error = 'There is an error.';
			return;
		}
		else
		{
			// update user
			$this->db->updateArray(PX.'users', $clean, "ID='".$go['id']."'");
			
			// send an update notice
			$this->template->onready[] = "toggle('add-user');";
			$this->template->action_update = 'updated';
		}		
	}
	
	public function sbmt_upd_prefs()
	{
		global $go, $default;
		
		$redirect = false;
		
		load_module_helper('files', $go['a']);
		
		$p =& load_class('processor', TRUE, 'lib');
	
		$clean['user_name'] 	= $p->process('user_name', array('notags', 'reqNotEmpty'));
		$clean['user_surname'] 	= $p->process('user_surname', array('notags', 'reqNotEmpty'));
		$clean['email'] 		= $p->process('email', array('notags', 'reqNotEmpty'));
		$clean['userid']		= $p->process('userid', array('notags', 'reqNotEmpty'));
		$clean['user_lang']		= $p->process('user_lang', array('notags', 'reqNotEmpty'));
		
		$tmp['password']		= $p->process('password', array('notags', 'length12'));
		$tmp['cpassword']		= $p->process('cpassword', array('notags', 'length12'));

		// need to update the password too
		if (($tmp['password'] != '') && ($tmp['password'] == $tmp['cpassword']))
		{
			$clean['password'] = md5($tmp['password']);
			setcookie('ndxz_access', $clean['password'], time()+3600*24*2, '/');
			$redirect = true;
		}

		if ($p->check_errors())
		{
			// get our error messages
			$error_msg = $p->get_errors();
			$this->errors = TRUE;
			$GLOBALS['error_msg'] = $error_msg;
			$this->template->action_error = 'There is an error.';
			return;
		}
		else
		{
			// update user
			$this->db->updateArray(PX.'users', $clean, "ID='" . $this->access->prefs['ID'] . "'");
			
			// send an update notice
			if ($redirect == true)
			{
				system_redirect("?a=$go[a]&q=$go[q]");
				exit;
			}
			else
			{
				$this->template->action_update = 'updated';
			}
		}		
	}
	
	///////////////////
	public function page_assets()
	{
		global $go, $default;
		
		if ($this->access->is_admin() == false) { system_redirect("?a=$go[a]"); }
		
		if (isset($_GET['edit'])) { $this->page_code(); }

		$this->template->location = $this->lang->word('assets');
		
		// sub-locations
		//$this->template->sub_location[] = array($this->lang->word('upload'),"#", "onclick=\"toggle('add-page'); return false;\"");
		//$this->template->sub_location[] = array($this->lang->word('main'),"?a=$go[a]");
		
		// the record
		$rs = $this->db->fetchRecord("SELECT * 
			FROM ".PX."objects_prefs 
			WHERE obj_ref_type = 'exhibit'");
			
		
		$body = (isset($this->error)) ?
			div($this->error_msg,"id='show-error'").br() : '';
		
		load_module_helper('files', $go['a']);
		load_helpers(array('editortools', 'output'));
		
		// fix this...should be everywhere all the time...
		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'settings.js';
		$this->template->module_css[] = 'settings.css';
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$body .= $this->toggler();
		
		$body .= "<div class='bg-grey corners'>\n";
		
		$body .= div(p($this->lang->word("the editor is intended")), "style='background: #fff; margin: 5px; padding: 9px; border: 1px solid #ccc; font-size: 13px;'");
		
		// add stuff from here
		$body .= "<div id='add-page' style='display: none; margin-bottom: 18px;'>\n";
		$body .= "<div class='c3'>\n";
		
		$body .= "<div class='col'>\n";
		$body .= "</div>\n";	
		
		$body .= "</div>\n";
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>\n\n";
		/////////////////////////////////
		
		$body .= "<div class='c3'>\n";
		
		// First column
		$body .= "<div class='col'>\n";
		$t = the_templates(DIRNAME . '/ndxzsite/' . $this->access->settings['obj_theme'], '', 'index.php');
		$li = '';
		$body .= p(label($this->lang->word('template set') . ' ' . span('(' . $this->access->settings['obj_theme'] . ')')));
		foreach ($t as $key => $te) 
		{
			// exclude php files
			if (substr($te, -3) != 'php')
			{
				$li .= li(href($te, "?a=$go[a]&q=assets&edit=/" . $this->access->settings['obj_theme'] . "/$te"));
			}
		}
		//$li .= li(href('error.php', "?a=$go[a]&q=assets&edit=/error.php"));
		$body .= ul($li, "class='ndxz_files'");
		$body .= "</div>\n\n";
		
		// second column
		$body .= "<div class='col'>\n";
		$t = the_templates(DIRNAME . '/ndxzsite/css', 'css', 'index.php');
		$li = '';
		$body .= p(label($this->lang->word('css')));
		foreach ($t as $key => $te) $li .= li(href($te, "?a=$go[a]&q=assets&edit=/css/$te"));
		$body .= ul($li, "class='ndxz_files'");
		//$body .= "</div>\n\n";
		
		// third column
		/*
		$body .= "<div style='margin-top: 50px;'><!-- --></div>\n";
		$t = the_templates(DIRNAME . '/ndxzsite/js', 'js', 'index.php');
		$li = '';
		$body .= p(label($this->lang->word('js')));
		foreach ($t as $key => $te) $li .= li(href($te, "?a=$go[a]&q=assets&edit=/js/$te"));
		$body .= ul($li, "class='ndxz_files'");
		$body .= "</div>\n\n";
		*/
		
		// fourth column
		/*
		$body .= "<div class='col'>\n";
		$t = the_templates(DIRNAME . '/ndxzsite/plugin', '', 'index.php');
		$li = '';
		$body .= p(label($this->lang->word('plugins')));
		foreach ($t as $key => $te) $li .= li(href($te, "?a=$go[a]&q=assets&edit=/plugin/$te"));
		$body .= ul($li, "class='ndxz_files'");
		$body .= "</div>\n\n";
		*/
		
		$body .= "<div class='cl'><!-- --></div>\n";
		$body .= "</div>";
		
		$this->template->body = $body;
		
		return;
	}
	

	
	public function page_code()
	{
		global $go, $default;
		
		$this->template->location = $this->lang->word('assets');
		
		$this->template->add_js('jquery.js');
		$this->template->module_js[] = 'system.js';
		
		// sub-locations
		//$this->template->sub_location[] = array($this->lang->word('back'), "?a=$go[a]&amp;q=assets");
		
		load_module_helper('files', $go['a']);
		
		// ++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		// figure out what we're dealing with - format?
		// validate get
		$type = explode('.', $_GET['edit']);
		$format = array_pop($type);

		if ($format != 'css') { system_redirect("?a=$go[a]&q=assets"); }
		
		// get the stuff
		$template = (file_exists(DIRNAME . '/ndxzsite' . $_GET['edit'])) ? $_GET['edit'] : false;
		
		if ($template == false) { echo 'Woops!'; exit; }
		
		// need to restrict which folders we can access
		// disallow php & js editing
		if (($format == 'css') && (is_writable(DIRNAME . '/ndxzsite' . $_GET['edit'])))
		{
			$filename = DIRNAME . '/ndxzsite' . $template;
			$fp = @fopen($filename, 'r');
			$contents = fread($fp, filesize($filename));
			fclose($fp);
		
			$b = $this->toggler();
		
			$b .= "<div class='bg-grey corners'>\n";

			$b .= "<div class='c2' style='margin-bottom: 18px;'>\n";
		
			$b .= "<div class='col'>\n";
			$b .= "<h3>" . span($format, "class='sec-title' style='text-transform: uppercase;'") . " $_GET[edit]</h3>\n";
			$b .= "</div>\n";
		
			$b .= "<div class='col' style='text-align: right;'>\n";

			$b .= "<input name='save' type='image' src='asset/img/save.gif' title='".$this->lang->word('save')."'  class='btn btn-off' onmouseover=\"this.className='btn btn-over'\" onmouseout=\"this.className='btn btn-off'\" style='margin-bottom:0;' onclick=\"updateCode('$_GET[edit]'); return false;\" />\n";

			$b .= "</div>\n";
		
			$b .= "<div class='cl'><!-- --></div>\n";
			$b .= "</div>\n";
		
			$b .= "<div class='c1'>\n";
			load_helper('output');
			$code = mb_encode_numericentity($contents, UTF8EntConvert(1), 'utf-8');
			$b .= div(textarea($code, "id='code' style='font-family: Courier, Courier New, monospace; font-size: 12px;'", 'code'), "class='code'");

			$b .= "</div>\n";
			$b .= "</div>\n";
		}
		else
		{
			// we'll just show the code...
			$filename = DIRNAME . '/ndxzsite' . $template;
			$fp = @fopen($filename, 'r');
			$contents = fread($fp, filesize($filename));
			fclose($fp);

			$b = $this->toggler();

			$b .= "<div class='bg-grey corners'>\n";

			$b .= "<div class='c2' style='margin-bottom: 18px;'>\n";

			$b .= "<div class='col'>\n";
			$b .= "<h3>" . span($format, "class='sec-title' style='text-transform: uppercase;'") . " $_GET[edit]</h3>\n";
			$b .= "</div>\n";

			$b .= "<div class='col' style='text-align: right;'>\n";

			$b .= "</div>\n";

			$b .= "<div class='cl'><!-- --></div>\n";
			$b .= "</div>\n";

			$b .= "<div class='c1'>\n";

			$b .= "<div class='content-prev' style='background: #fff; padding: 9px;'>" . tpl_process_code($contents) . "\n</div>\n\n";

			$b .= "</div>\n";
			$b .= "</div>\n";
		}
				
		$this->template->body = $b;
		$this->template->output('index');
		exit;
	}
	
	public function sbmt_upd_files()
	{
		$OBJ->template->errors = TRUE;
		global $go, $default;

		$IMG =& load_class('media', TRUE, 'lib');
			
		// +++++++++++++++++++++++++++++++++++++++++++++++++++
		
		// need to rewrite this too

		$num = count($_FILES['filename']['name']);
		$dir = DIRNAME . BASEFILES . '/';
		$types = array_merge($default['images'], $default['media'], $default['files'], $default['files'], $default['flash']);
		$IMG->path = $dir;
			
		if ($num > 0)
		{
			for ($i = 0; $i < $num; $i++)
			{
				if ($_FILES['filename']['size'][$i] < $IMG->upload_max_size)
				{
					$title 	= (isset($_POST['media_title'][$i])) ? $_POST['media_title'][$i] : '';
					
					// we need to clean the file name
					$test = explode('.', $_FILES['filename']['name'][$i]);
					$thetype = array_pop($test);
					
					load_helper('output');
					$URL =& load_class('publish', TRUE, 'lib');

					$URL->title = implode('_', $test);
					$name = $URL->processTitle();
					
					// look for dupllications
					$name = ($name == '') ? time().$i : $name;
					
					$IMG->type = '.' . $thetype;
					$IMG->filename = $IMG->checkName($name) . '.' . $thetype;
					
					if (in_array($thetype,$types))
					{
						// if uploaded we can work with it
						if (move_uploaded_file($_FILES['filename']['tmp_name'][$i], $IMG->path.'/'.$IMG->filename)) 
						{
							$clean['media_id'] 	= 'NULL';
							$clean['media_file'] = $IMG->filename;
							$clean['media_uploaded'] = getNow();
							$clean['media_udate'] = getNow();
							$clean['media_kb'] 	= str_replace('.', '', filesize($IMG->path . '/' . $IMG->filename));
							$clean['media_title'] = $title;
							$clean['media_mime'] = $thetype;
							
							// add swf to image sizery
							$img_sizes = array_merge($default['images'], array('swf'));
						
							// only images can deal with these
							if (in_array($thetype, $img_sizes))
							{
								$size = getimagesize($IMG->path . '/' . $IMG->filename);
								$clean['media_x'] = $size[0];
								$clean['media_y'] = $size[1];
							}
							
							$this->db->insertArray(PX.'media', $clean);
							
							@chmod($IMG->path . '/' . $IMG->filename, 0755);
						}
						else
						{
							// file not uploaded
						}
					}
					else
					{
					// not a valid format
					}
				}
				else
				{
					// too big
				}
			}
		}
			
		system_redirect("?a=$go[a]&q=files");
	}
}