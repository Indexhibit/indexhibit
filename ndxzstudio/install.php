<?php define('SITE', 'Bonjour!');

/**
* Installation
* 
* @version 2.1
* @author Vaska
*/

// annoying date setting thing
if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
{
	@date_default_timezone_set(@date_default_timezone_get());
}

class Installation
{
	var $html;
	var $lang;
	var $db;
	var $charset_collate;
	var $charset = 'utf8';
	var $collate = 'utf8_unicode_ci';

	public function __construct()
	{
		require_once '../ndxzsite/config/options.php';
		require_once 'defaults.php';
		require_once 'common.php';
		require_once './helper/entrance.php';
		require_once './helper/html.php';
		require_once './helper/time.php';
		require_once './lang/index.php';

		// look for the cookie here
		$picked = (isset($_COOKIE['install'])) ? $_COOKIE['install'] : 'en-us';
		
		$this->lang = new Lang;
		$this->lang->setlang($picked);
				
		switch( $this->get_page() ) 
		{
			case 0:
				$this->page_zero();
			break;
		
			case 1:
				$this->page_one();
			break;
		
			case 2:
				$this->page_two();
			break;
		
			case 3:
				$this->page_three();
			break;
					
			case 4:
				$this->page_four();
			break;
		}
	}
	
	function page_zero()
	{
		$user_lang = getPOST('user_lang', 'en-us', 'iso', 5);

		// set cookie
		if (isset($_POST['submitLang']) && ($user_lang != ''))
		{
			setcookie('install', $user_lang, time()+3600);
			header("location:install.php?p=1");
			exit;
		}
		
		// PHP version check here
		if (version_compare(PHP_VERSION, '5.6.0', '>='))
		{
			if (file_exists(DIRNAME . '/ndxzsite/config/config.php'))
			{
				$this->html = p($this->lang->word('you are already installed'));
			}
			else
			{
				$this->html = "<p><label>" . $this->lang->word('language') . "</label><br />\n";
				$this->html .= $this->getLanguage(null, 'user_lang') . "</p>\n";
				$this->html .= "<input type='submit' name='submitLang' value='NEXT &raquo;' />\n";
			}
		}
		else
		{
			$this->html = "<p>" . $this->lang->word('you need php 5.6 or greater') . "<p>\n";
		}
	}
	
	function page_one()
	{
		$flagB = false;
		$flagC = false;
		$flagD = false;
		$flagE = false;

		if ((is_dir(DIRNAME . '/files')) && (is_writable(DIRNAME . '/files')))
		{
			$flagB = true;
			$this->html = "<p><span class='ok'>OK</span> " . $this->lang->word('files ok') . "</p>\n";
		}
		else
		{
			$this->html .= "<p><span class='ok-not'>XX</span> " . $this->lang->word('files not ok') . "</p>";
		}
		
		if ((is_dir(DIRNAME . '/files/gimgs')) && (is_writable(DIRNAME . '/files/gimgs')))
		{
			$flagC = true;
			$this->html .= "<p><span class='ok'>OK</span> " . $this->lang->word('filesgimgs ok') . "</p>";
		}
		else
		{
			$this->html .= "<p><span class='ok-not'>XX</span> " . $this->lang->word('filesgimgs not ok') . "</p>";
		}
		
		if ((is_dir(DIRNAME . '/files/dimgs')) && (is_writable(DIRNAME . '/files/dimgs')))
		{
			$flagE = true;
			$this->html .= "<p><span class='ok'>OK</span> " . $this->lang->word('filesdimgs ok') . "</p>";
		}
		else
		{
			$this->html .= "<p><span class='ok-not'>XX</span> " . $this->lang->word('filesdimgs not ok') . "</p>";
		}
		
		if ((is_dir(DIRNAME . '/ndxzsite/config')) && (is_writable(DIRNAME . '/ndxzsite/config')))
		{
			$flagD = true;
			$this->html .= "<p><span class='ok'>OK</span> " . $this->lang->word('config ok') . "</p>\n";
		}
		else
		{
			$this->html .= "<p><span class='ok-not'>XX</span> " . $this->lang->word('config not ok') . "</p>";
		}
		
		if (($flagB == true) && ($flagC == true) && ($flagD == true) && ($flagE == true))
		{
			$this->html .= "<br /><p><strong>" . $this->lang->word('try db setup now') . "</strong></p>";
			$this->html .= "<br /><p><a href='?p=2'>" . $this->lang->word('continue') . "</a></p><br />";
		}
		else
		{
			$this->html .= "<br /><p><strong>" . $this->lang->word('please correct errors') . "<strong></p><br />";
			$this->html .= "<p><strong>" . $this->lang->word('refresh page') . "</strong></p><br />";
			$this->html .= "<p><small>" . $this->lang->word('goto forum') . "</small></p><br />";
		}
	}
	
	function page_two()
	{
		global $indx;
		$s = '';

		if (isset($_POST['n_submit']))
		{
			if (!file_exists(DIRNAME . '/ndxzsite/config/config.php'))
			{
				// we go to page 3 after installation
				$s = $this->do_installation();
			}
		}
		
		// if the previous installation can be found...
		if (file_exists(DIRNAME . '/ndxz-studio/config/config.php'))
		{
			if (file_exists(DIRNAME . '/ndxzsite/config/config.php'))
			{
				// let's check if we have already upgraded
				require_once '../ndxzsite/config/config.php';
				require_once './db/db.mysql.php';
				
				$GLOBALS['indx'] = $indx;
				$this->db = new Db();
				
				// get old settings
				$objects = $this->db->fetchArray("SELECT ID FROM ".PX."objects");

				// fresh install only adds three 'objects'
				if (count($objects) < 4)
				{
					// let's try to get the information from an older version
					$this->html = $this->previous_install_connection();
				}
				else
				{
					$this->html = p($this->lang->word('you are already installed'));
				}
			}
			else
			{
				$this->html = $this->previous_install_connection();
			}
		}
		else
		{
			if (!file_exists(DIRNAME . '/ndxzsite/config/config.php'))
			{
				// do not display if we have already upgraded
				$this->html = "<div id='log-form'>\n";
				$this->html .= "<form name='iform' method='post'>\n";
			
				// build the form here
				$this->html .= "<label>" . $this->lang->word('site name') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_site' value='".$this->showPosted('n_site')."' maxlength='35' />\n";
			
				$this->html .= "<label>" . $this->lang->word('user name') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_fname' value='".$this->showPosted('n_fname')."' maxlength='35' />\n";
			
				$this->html .= "<label>" . $this->lang->word('user last name') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_lname' value='".$this->showPosted('n_lname')."' maxlength='35' />\n";
			
				$this->html .= "<label>" . $this->lang->word('user email address') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_email' value='".$this->showPosted('n_email')."' maxlength='100' />\n";
			
				$this->html .= "<div style='width: 250px; margin: 24px 0 12px 0;'><hr /></div>";
			
				$this->html .= "<label>" . $this->lang->word('database server') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_host' value='".$this->showPosted('n_host')."' maxlength='50' />\n";
			
				$this->html .= "<label>" . $this->lang->word('database name') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_name' value='".$this->showPosted('n_name')."' maxlength='50' />\n";
			
				$this->html .= "<label>" . $this->lang->word('database username') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_user' value='".$this->showPosted('n_user')."' maxlength='35' />\n";
			
				$this->html .= "<label>" . $this->lang->word('database password') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_pwd' value='".$this->showPosted('n_pwd')."' maxlength='35' />\n";
			
				$this->html .= "<label>" . $this->lang->word('database append') . "</label><br />\n";
				$this->html .= "<input type='text' name='n_appnd' value='ndxzbt_' maxlength='20' />\n";
			
				$this->html .= "<input type='submit' name='n_submit' value='" . $this->lang->word('submit') . "' maxlength='50' /><br />\n";
			
				$this->html .= "</form>\n";
				$this->html .= $s;
				$this->html .= "</div>\n";
			}
			else
			{
				$this->html = p($this->lang->word('you are already installed'));
			}
		}
	}
	
	function page_three()
	{
		global $indx;

		// by this point we are installed
		$objects = 0;

		// we are upgraded if we are on this page
		require_once '../ndxzsite/config/config.php';
		require_once './db/db.mysql.php';
		
		$GLOBALS['indx'] = $indx;
		$this->db = new Db();

		if (file_exists(DIRNAME . '/ndxzsite/config/config.php'))
		{
			// get old settings
			$objects = $this->db->fetchArray("SELECT ID FROM ".PX."objects");
		}

		if (isset($_POST['upgrade']))
		{
			if (count($objects) < 4)
			{
				$this->do_the_upgrade();
			}
			else
			{
				header('location:' . BASEURL . BASENAME . '/?a=system&q=preferences&flag=true');
				exit;
			}
		}
		elseif ((file_exists(DIRNAME . '/ndxz-studio/config/config.php')) && (count($objects) < 4))
		{
			$this->html = "<p>Do you want to import the data from your previous website?</p>";
				
			$this->html .= "<p><input type='submit' name='upgrade' value='" . $this->lang->word('import') . "' /></p>\n";
					
			$this->html .= "<p>Otherwise, you can <a href='" . BASEURL . BASENAME . "/?a=system&q=preferences&flag=true'>login</a> now.</p>";
		}
		else // they are installed or they are just mucking with the installer (whhich should be deleted)
		{
			header('location:' . BASEURL . BASENAME . '/?a=system&q=preferences&flag=true');
			exit;
		}
	}
	
	function page_four()
	{
		if (isset($_GET['s']))
		{
			// to the preferences page
			header('location:' . BASEURL . BASENAME . '/?a=system&q=preferences&flag=true');
			exit;
		}
		else
		{
			$this->html = "<p><span class='ok-not'>XX</span> " . $this->lang->word('cannot install') . "</p><br />";
			$this->html .= "<p><small>" . $this->lang->word('goto forum') . "</small></p><br />";
		}
	}
	
	function output()
	{
		echo $this->html;
	}
	
	function get_page()
	{
		return (isset($_GET['p'])) ? (int) $_GET['p'] : 0;
	}
	
	
	function set_charset_collation()
	{
		$version = preg_replace('/[^0-9.].*/', '', mysqli_get_server_info($this->db->link));
		
		if (version_compare($version, '4.1', '>='))
		{
			$this->charset_collate = 'DEFAULT CHARACTER SET ' . $this->charset;
			$this->charset_collate .= ' COLLATE ' . $this->collate;
		}
		
		$ver = $this->mysqli_ver($this->db->link);
		
		return ((is_numeric($ver) && $ver <= 4)) ? 'TYPE=MyISAM' : 'ENGINE=MyISAM DEFAULT CHARSET=utf8';
	}
	
	
	function install_db()
	{
		global $c, $picked, $indx;
		
		require_once '../ndxzsite/config/config.php';
		require_once './db/db.mysql.php';
		
		$GLOBALS['indx'] = $indx;
		$this->db = new Db();

		$isam = $this->set_charset_collation();
		
		$sql = array();

		$sql[] = "CREATE TABLE IF NOT EXISTS iptocountry (
		  ip_from double NOT NULL DEFAULT '0',
		  ip_to double NOT NULL DEFAULT '0',
		  country_code2 char(2) NOT NULL DEFAULT '',
		  country_code3 char(3) NOT NULL DEFAULT '',
		  country_name varchar(50) NOT NULL DEFAULT '',
		  KEY ip_from_to_idx (ip_from,ip_to)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."abstracts (
		  ab_id int(11) NOT NULL AUTO_INCREMENT,
		  ab_obj varchar(32) NOT NULL DEFAULT '',
		  ab_obj_id int(11) NOT NULL DEFAULT '0',
		  ab_var varchar(255) NOT NULL DEFAULT '',
		  abstract text,
		  PRIMARY KEY (ab_id)
		) $isam ;";
		
		$sql[] = "CREATE TABLE IF NOT EXISTS".PX."profile (
			pr_id int(11) NOT NULL AUTO_INCREMENT,
 			pr_apikey varchar(32) NOT NULL,
			pr_sitekey varchar(32) NOT NULL,
			pr_name varchar(250) NOT NULL,
			pr_title varchar(250) NOT NULL,
			pr_image varchar(1000) NOT NULL,
			pr_location varchar(250) NOT NULL,
			pr_freelance varchar(1) NOT NULL,
			PRIMARY KEY (pr_id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."media (
		  media_id int(11) NOT NULL AUTO_INCREMENT,
		  media_ref_id smallint(6) NOT NULL DEFAULT '0',
		  media_obj_type varchar(15) NOT NULL DEFAULT '',
		  media_mime varchar(15) NOT NULL DEFAULT '',
		  media_tags varchar(255) NOT NULL DEFAULT '0',
		  media_file varchar(255) NOT NULL DEFAULT '',
		  media_thumb varchar(255) NOT NULL DEFAULT '',
		  media_file_replace varchar(255) NOT NULL DEFAULT '',
		  media_title varchar(255) NOT NULL DEFAULT '',
		  media_caption text NOT NULL,
		  media_x varchar(5) NOT NULL DEFAULT '',
		  media_y varchar(5) NOT NULL DEFAULT '',
		  media_xr smallint(4) NOT NULL DEFAULT '0',
		  media_yr smallint(4) NOT NULL DEFAULT '0',
		  media_kb mediumint(9) NOT NULL DEFAULT '0',
		  media_udate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  media_uploaded datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  media_order smallint(3) NOT NULL DEFAULT '999',
		  media_hide tinyint(1) NOT NULL DEFAULT '0',
		  media_dir varchar(255) NOT NULL DEFAULT '',
		  media_src varchar(25) NOT NULL DEFAULT '',
		  PRIMARY KEY (media_id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."objects (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  object varchar(100) NOT NULL DEFAULT '',
		  obj_ref_id int(4) NOT NULL DEFAULT '0',
		  title varchar(100) NOT NULL DEFAULT '',
		  content mediumtext NOT NULL,
		  home tinyint(1) NOT NULL DEFAULT '0',
		  link varchar(255) NOT NULL DEFAULT '',
		  target tinyint(1) NOT NULL DEFAULT '0',
		  iframe tinyint(1) NOT NULL DEFAULT '0',
		  new tinyint(1) NOT NULL DEFAULT '0',
		  tags varchar(250) NOT NULL DEFAULT '0',
		  header text NOT NULL,
		  udate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  pdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  creator tinyint(3) NOT NULL DEFAULT '0',
		  status tinyint(1) NOT NULL DEFAULT '0',
		  process tinyint(1) NOT NULL DEFAULT '1',
		  page_cache tinyint(1) NOT NULL DEFAULT '0',
		  section_id tinyint(3) NOT NULL DEFAULT '0',
		  section_top tinyint(1) NOT NULL DEFAULT '0',
		  section_sub varchar(255) NOT NULL DEFAULT '',
		  subdir tinyint(1) NOT NULL DEFAULT '0',
		  url varchar(250) NOT NULL DEFAULT '',
		  ord smallint(3) NOT NULL DEFAULT '999',
		  color varchar(7) NOT NULL DEFAULT 'ffffff',
		  bgimg varchar(255) NOT NULL DEFAULT '',
		  hidden tinyint(1) NOT NULL DEFAULT '0',
		  current tinyint(1) NOT NULL DEFAULT '0',
		  perm tinyint(1) NOT NULL DEFAULT '0',
		  media_source tinyint(3) NOT NULL DEFAULT '0',
		  media_source_detail varchar(255) NOT NULL,
		  images smallint(4) NOT NULL DEFAULT '9999',
		  thumbs_shape tinyint(1) NOT NULL DEFAULT '0',
		  thumbs smallint(4) NOT NULL DEFAULT '200',
		  format varchar(100) NOT NULL DEFAULT 'visual_index',
		  thumbs_format tinyint(1) NOT NULL DEFAULT '0',
		  operand tinyint(4) NOT NULL DEFAULT '0',
		  titling tinyint(1) NOT NULL DEFAULT '0',
		  break smallint(2) NOT NULL DEFAULT '0',
		  tiling tinyint(1) NOT NULL DEFAULT '1',
		  year varchar(4) NOT NULL DEFAULT '2010',
		  report tinyint(1) NOT NULL DEFAULT '0',
		  pwd varchar(100) NOT NULL DEFAULT '',
		  placement tinyint(1) NOT NULL DEFAULT '0',
		  template varchar(25) NOT NULL DEFAULT 'index.php',
		  ling varchar(7) NOT NULL DEFAULT 'en',
		  ling_id varchar(32) NOT NULL DEFAULT '',
		  serial longtext NOT NULL,
		  extra1 varchar(255) NOT NULL DEFAULT '',
		  extra2 varchar(255) NOT NULL DEFAULT '',
		  PRIMARY KEY (id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."objects_prefs (
		  obj_id int(11) NOT NULL AUTO_INCREMENT,
		  obj_ref_type varchar(255) NOT NULL DEFAULT '',
		  obj_active tinyint(1) NOT NULL DEFAULT '1',
		  obj_title varchar(255) NOT NULL DEFAULT '',
		  obj_section smallint(3) NOT NULL DEFAULT '1',
		  obj_template varchar(50) NOT NULL DEFAULT '',
		  obj_members varchar(255) NOT NULL DEFAULT '',
		  obj_img varchar(255) NOT NULL DEFAULT '',
		  obj_settings longtext NOT NULL,
		  obj_group varchar(255) NOT NULL DEFAULT '',
		  PRIMARY KEY (obj_id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."plugins (
		  pl_id int(4) NOT NULL AUTO_INCREMENT,
		  pl_primary tinyint(1) NOT NULL DEFAULT '0',
		  pl_type varchar(15) NOT NULL DEFAULT '',
		  pl_name varchar(255) NOT NULL DEFAULT '',
		  pl_uri varchar(255) NOT NULL DEFAULT '',
		  pl_version varchar(20) NOT NULL DEFAULT '',
		  pl_file varchar(255) NOT NULL DEFAULT '',
		  pl_function varchar(255) NOT NULL DEFAULT '',
		  pl_hook varchar(255) NOT NULL DEFAULT '',
		  pl_space varchar(100) NOT NULL DEFAULT '',
		  pl_creator varchar(50) NOT NULL DEFAULT '',
		  pl_www varchar(255) NOT NULL DEFAULT '',
		  pl_desc text NOT NULL,
		  pl_options text NOT NULL,
		  pl_options_build text NOT NULL,
		  pl_usage varchar(255) NOT NULL DEFAULT '',
		  pl_usage_desc varchar(255) NOT NULL DEFAULT '',
		  pl_order smallint(3) NOT NULL DEFAULT '100',
		  PRIMARY KEY (pl_id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."sections (
		  secid tinyint(3) NOT NULL AUTO_INCREMENT,
		  section varchar(60) NOT NULL DEFAULT '',
		  sec_obj varchar(50) NOT NULL DEFAULT 'exhibits',
		  sec_ord tinyint(4) NOT NULL DEFAULT '0',
		  sec_disp tinyint(3) NOT NULL DEFAULT '1',
		  sec_hide tinyint(1) NOT NULL DEFAULT '0',
		  sec_pwd varchar(32) NOT NULL DEFAULT '',
		  sec_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  sec_path varchar(250) NOT NULL DEFAULT '',
		  sec_subs varchar(100) NOT NULL DEFAULT '',
		  sec_desc varchar(100) NOT NULL DEFAULT '',
		  sec_proj tinyint(4) NOT NULL DEFAULT '0',
		  sec_group tinyint(4) NOT NULL DEFAULT 0,
		  sec_report tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (secid)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."settings (
		  adm_id tinyint(3) NOT NULL AUTO_INCREMENT,
		  site_name varchar(40) NOT NULL DEFAULT '',
		  installdate varchar(20) NOT NULL DEFAULT '',
		  version varchar(25) NOT NULL DEFAULT '',
		  curr_time tinyint(3) NOT NULL DEFAULT '0',
		  site_lang varchar(8) NOT NULL DEFAULT 'en-us',
		  time_format varchar(25) NOT NULL DEFAULT '',
		  tagging tinyint(1) NOT NULL DEFAULT '1',
		  help tinyint(1) NOT NULL DEFAULT '0',
		  caching tinyint(1) NOT NULL DEFAULT '0',
		  hibernate varchar(255) NOT NULL DEFAULT '',
		  obj_name varchar(255) NOT NULL DEFAULT '',
		  obj_theme varchar(50) NOT NULL DEFAULT '',
		  obj_itop text NOT NULL,
		  obj_ibot text NOT NULL,
		  obj_org tinyint(1) NOT NULL DEFAULT '1',
		  obj_apikey varchar(32) NOT NULL DEFAULT '',
		  site_format varchar(30) NOT NULL DEFAULT '%d %B %Y',
		  site_offset tinyint(3) NOT NULL DEFAULT '0',
		  site_vars longtext NOT NULL,
		  PRIMARY KEY (adm_id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."stats (
		  hit_id int(14) NOT NULL AUTO_INCREMENT,
		  hit_addr varchar(16) NOT NULL DEFAULT '',
		  hit_country varchar(30) NOT NULL DEFAULT '',
		  hit_lang varchar(10) NOT NULL DEFAULT '',
		  hit_domain varchar(100) NOT NULL DEFAULT '',
		  hit_referrer varchar(100) NOT NULL DEFAULT '',
		  hit_page varchar(100) NOT NULL DEFAULT '',
		  hit_agent varchar(250) NOT NULL DEFAULT '',
		  hit_keyword varchar(250) NOT NULL DEFAULT '',
		  hit_os varchar(20) NOT NULL DEFAULT '',
		  hit_browser varchar(20) NOT NULL DEFAULT '',
		  hit_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  hit_month varchar(7) NOT NULL DEFAULT '',
		  hit_day date NOT NULL DEFAULT '0000-00-00',
		  PRIMARY KEY (hit_id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."stats_exhibits (
		  stor_url varchar(255) NOT NULL DEFAULT '',
		  stor_count smallint(6) NOT NULL DEFAULT '0'
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."stats_storage (
		  stor_date varchar(7) NOT NULL DEFAULT '0000-00',
		  stor_hits int(11) NOT NULL DEFAULT '0',
		  stor_unique int(11) NOT NULL DEFAULT '0',
		  stor_referrer int(11) NOT NULL DEFAULT '0'
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."subsections (
		  sub_id tinyint(3) NOT NULL AUTO_INCREMENT,
		  sub_sec_id tinyint(3) NOT NULL DEFAULT 0,
		  sub_title varchar(255) NOT NULL DEFAULT '',
		  sub_folder varchar(255) NOT NULL DEFAULT '',
		  sub_order tinyint(3) NOT NULL DEFAULT 0,
		  sub_hide tinyint(1) NOT NULL DEFAULT 0,
		  PRIMARY KEY (sub_id)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."tagged (
		  tagged_id smallint(6) NOT NULL DEFAULT 0,
		  tagged_object varchar(3) NOT NULL DEFAULT '',
		  tagged_obj_id smallint(6) NOT NULL DEFAULT 0
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."tags (
		  tag_id int(11) NOT NULL AUTO_INCREMENT,
		  tag_name varchar(255) NOT NULL DEFAULT '',
		  tag_group smallint(3) NOT NULL DEFAULT '1',
		  tag_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  tag_icon varchar(255) NOT NULL DEFAULT '',
		  PRIMARY KEY (tag_id),
		  UNIQUE KEY tag_name (tag_name)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS ".PX."users (
		  ID int(11) NOT NULL AUTO_INCREMENT,
		  userid varchar(100) NOT NULL DEFAULT '',
		  password varchar(32) NOT NULL DEFAULT '',
		  email varchar(100) NOT NULL DEFAULT '',
		  threads tinyint(3) NOT NULL DEFAULT '10',
		  writing tinyint(1) NOT NULL DEFAULT '0',
		  user_offset tinyint(3) NOT NULL DEFAULT '0',
		  user_format varchar(30) NOT NULL DEFAULT '%d %B %Y',
		  user_lang varchar(8) NOT NULL DEFAULT 'en-us',
		  user_hash varchar(32) NOT NULL DEFAULT '',
		  user_help tinyint(1) NOT NULL DEFAULT '0',
		  user_mode tinyint(1) NOT NULL DEFAULT '0',
		  user_name varchar(35) NOT NULL DEFAULT '',
		  user_surname varchar(35) NOT NULL DEFAULT '',
		  user_admin tinyint(1) NOT NULL DEFAULT '0',
		  user_active tinyint(1) NOT NULL DEFAULT '1',
		  user_client tinyint(1) NOT NULL DEFAULT '0',
		  user_img varchar(255) NOT NULL DEFAULT '',
		  PRIMARY KEY (ID),
		  UNIQUE KEY userid (userid),
		  KEY ID (ID)
		) $isam ;";
	
		$sql[] = "INSERT INTO `".PX."objects` (`id`, `object`, `obj_ref_id`, `title`, `content`, `home`, `link`, `target`, `iframe`, `new`, `tags`, `header`, `udate`, `pdate`, `creator`, `status`, `process`, `page_cache`, `section_id`, `section_top`, `url`, `ord`, `color`, `bgimg`, `hidden`, `current`, `perm`, `media_source`, `media_source_detail`, `images`, `thumbs_shape`, `thumbs`, `format`, `thumbs_format`, `operand`, `titling`, `break`, `tiling`, `year`, `report`, `pwd`, `placement`, `template`, `ling`, `ling_id`, `serial`, `extra1`, `extra2`) VALUES
		(1, 'exhibits', 1, 'Main', '', 1, '', 0, 0, 0, '2', '', '".getNow()."', '".getNow()."', 1, 1, 1, 0, 1, 1, '/', 0, 'ffffff', '', 0, 0, 0, 0, '', 600, 0, 200, 'visual_index', 0, 2, 1, 0, 1, '2011', 0, '', 0, 'index.php', 'en', '', '', '', ''),
		(2, 'exhibits', 2, 'Project', '', 0, '', 0, 0, 0, '0', '', '".getNow()."', '".getNow()."', 1, 1, 1, 0, 2, 1, '/project/', 0, 'ffffff', '', 0, 0, 0, 2, '', 9999, 0, 200, 'visual_index', 0, 0, 1, 0, 1, '2011', 0, '', 1, 'index.php', 'en', '', '', '', ''),
		(3, 'exhibits', 3, 'Tags', '', 0, '', 0, 0, 0, '0', '', '".getNow()."', '".getNow()."', 1, 0, 1, 0, 3, 1, '/tag/', 0, 'ffffff', '', 0, 0, 0, 0, '', 9999, 0, 200, 'visual_index', 0, 0, 0, 0, 1, '2011', 0, '', 0, 'index.php', 'en', '', '', '', '');";
		
		$sql[] = "INSERT INTO `".PX."objects_prefs` (`obj_id`, `obj_ref_type`, `obj_active`, `obj_title`, `obj_section`, `obj_template`, `obj_members`, `obj_img`, `obj_settings`, `obj_group`) VALUES
		(1, 'exhibits', 1, '', 1, '', '', '', '', ''),
		(2, 'xml', 1, '', 1, '', '', '', '', ''),
		(3, 'tag', 1, '', 1, '', '', '', 'a:7:{s:10:\"section_id\";s:1:\"3\";s:8:\"template\";s:7:\"tag\.php\";s:6:\"format\";s:12:\"visual_index\";s:6:\"thumbs\";s:3:\"200\";s:12:\"thumbs_shape\";s:1:\"0\";s:5:\"break\";s:1:\"0\";s:7:\"titling\";s:1:\"0\";}', '');";

		$sql[] = "INSERT INTO `".PX."sections` (`secid`, `section`, `sec_obj`, `sec_ord`, `sec_disp`, `sec_hide`, `sec_pwd`, `sec_date`, `sec_path`, `sec_desc`, `sec_proj`, `sec_group`, `sec_report`) VALUES
		(1, 'root', 'exhibits', 2, 1, 0, '', '2006-12-20 17:01:31', '/', 'Main', 0, 0, 0),
		(2, 'project', 'exhibits', 1, 1, 0, '', '2010-03-03 23:48:44', '/project', 'Projects', 0, 0, 0),
		(3, 'tag', 'exhibits', 3, 1, 1, '', '2010-03-04 05:51:22', '/tag', 'Tags', 3, 0, 0);";
		
		// we need to deal with the inputs here better
		$sql[] = "INSERT INTO `".PX."settings` (`adm_id`, `site_name`, `installdate`, `version`, `curr_time`, `site_lang`, `time_format`, `tagging`, `help`, `hibernate`, `obj_name`, `obj_theme`, `obj_itop`, `obj_ibot`, `obj_org`, `obj_apikey`, `site_format`, `site_offset`, `site_vars`) VALUES
		(1, ".$this->db->escape($c['n_site']).", '" . getNow() . "', '" . VERSION . "', 1, 'en-us', '%d %B %Y', 1, 0, '', ".$this->db->escape($c['n_site']).", 'default', '<h1><a href=\"/\" title=\"{{obj_name}}\">{{obj_name}}</a></h1>', '<p>Copyright 2007-2017<br />\n<a href=\"http://www.indexhibit.org/\">Built with Indexhibit</a></p>', 2, 'asdfsafasfadsfdfs', '%d %B %Y', 0, 'a:3:{s:9:\"passwords\";s:1:\"1\";s:9:\"templates\";s:1:\"0\";s:4:\"tags\";s:1:\"1\";}');";
		
		// user cookie saved language selection
		$the_lang = (isset($_COOKIE['user_lang'])) ? $_COOKIE['user_lang'] : 'en-us';

		$sql[] = "INSERT INTO `".PX."users` (`ID`, `userid`, `password`, `email`, `threads`, `writing`, `user_offset`, `user_format`, `user_lang`, `user_hash`, `user_help`, `user_mode`, `user_name`, `user_surname`, `user_admin`, `user_active`, `user_client`) VALUES
		(1, 'index1', '22645ed8b5f5fa4b597d0fe61bed6a96', ".$this->db->escape($c['n_email']).", 15, 0, 0, '%d %B %Y', ".$this->db->escape($the_lang).", '5f8bfb51cc5c437a603abe3766d004d8', 0, 1, ".$this->db->escape($c['n_fname']).", ".$this->db->escape($c['n_lname']).", 1, 1, 0);";
		
		foreach ($sql as $install)
		{
			$this->db->query($install);
		}
	}
	
	
	/**
	* Returns string
	*
	* @param string $str
	* @return string
	*/
	function escape($str)
	{	
		switch (gettype($str))
		{
			case 'string'	:	$str = "'" . $this->escape_str($str) . "'";
				break;
			case 'boolean'	:	$str = ($str === FALSE) ? 0 : 1;
				break;
			default			:	$str = (($str == NULL) || ($str == ''))  ? "''" : "'" . $this->escape_str($str) . "'";
				break;
		}		

		return $str;
	}
	
	
	/**
	* Returns string
	*
	* @param string $str
	* @return string
	*/
	function escape_str($str)	
	{	
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}

		if (function_exists('mysqli_real_escape_string'))
		{
			return mysqli_real_escape_string($this->link, $str);
		}
		elseif (function_exists('mysqli_escape_string'))
		{
			return mysqli_escape_string($this->link, $str);
		}
		else
		{
			return addslashes($str);
		}
	}


	function mysqli_ver()
	{
		$ver = mysqli_get_client_version();

		// we only need first number
		$ver = substr($ver, 0, 1);
		return $ver;
	}
	
	function showPosted($var)
	{
		global $c;
		
		if ((!isset($c['n_host']) && ($var == 'n_host'))) return 'localhost';
		
		if (isset($c[$var]))
		{
			if ($var == 'n_host')
			{
				return ($c['n_host'] == 'localhost') ? 'localhost' : $c['n_host'];
			}
			
			return $c[$var];
		}
	}
	
	function writeConfig()
	{
		global $c;
		
		if (!is_array($c)) exit;
		
		$path = DIRNAME . '/ndxzsite/config';
		$filename = $path . '/config.php';
		
		$somecontent = "<?php  if (!defined('SITE')) exit('No direct script access allowed');

\$indx['db'] 		= '$c[n_name]';
\$indx['user'] 		= '$c[n_user]';
\$indx['pass'] 		= '$c[n_pwd]';
\$indx['host'] 		= '$c[n_host]';
\$indx['sql']		= 'mysql';

if (!defined('PX')) { define('PX', '$c[n_appnd]'); }";

		if (is_writable($path)) 
		{
			if (!$handle = fopen($filename, 'w')) 
			{
				return FALSE;
			}

			if (fwrite($handle, $somecontent) === FALSE) 
			{
				return FALSE;
			}

			fclose($handle);
			return TRUE;
		}

		return FALSE;
	}
	
	function getLanguage($default='', $name, $attr='')
	{
		global $lang;

		$s = '';

		$rs = $this->lang->lang_options();

		if ($default == '')
		{
			$s .= option('', $this->lang->word('make selection'), 0, 0);
		}

		foreach ($rs as $key => $a) 
		{
			$this->language = array_pop($a);
			
			// check to see if the lang folder exists
			if (is_dir(DIRNAME . BASENAME . '/' . LANGPATH . '/' . $key))
			{
				($default == $a) ? $sl = "selected ": $sl = "";
				$s .= option($key, $this->lang->word($this->language), $default, $key);
			}
		}
		clearstatcache();

		return select($name, attr($attr), $s);
	}
	
	
	function get_old_prefix()
	{
		// let's try to get the other prefix
		$tmp = file_get_contents(DIRNAME . '/ndxz-studio/defaults.php');

		if (preg_match_all("/define\('PX',(.*)\);/i", $tmp, $match))
		{
			$prefix = str_replace(array("'", '"'), array('', ''), $match[1][0]);
			return trim($prefix);
		}
		else
		{
			return false;
		}
	}
	
	
	function new_prefix($prefix)
	{
		$tmp = str_replace(array('_'), array(''), $prefix);
		$tmp2 = str_replace(array('ndxz'), array(''), $tmp);
		$tmp3 = (int) $tmp2; // it's mostly likely zero
		$prefix = 'ndxzbt' . ($tmp3 + 1) . '_';
		return $prefix;
	}
	
	
	function previous_install_connection()
	{
		global $lang;

		// let's try to get the old connection strings
		if (file_exists(DIRNAME . '/ndxz-studio/config/config.php'))
		{
			require_once DIRNAME . '/ndxz-studio/config/config.php';
			
			$prefix = $this->get_old_prefix();
			$prefix = ($prefix == 'false') ? 'ndxzbt_' : $this->new_prefix($prefix);
		}

		$html = "<div id='log-form'>\n";
		$html .= "<form name='iform' method='post'>\n";

		// build the form here
		$html .= "<label>" . $this->lang->word('site name') . "</label><br />\n";
		$html .= "<input type='text' name='n_site' value='".$this->showPosted('n_site')."' maxlength='35' />\n";

		$html .= "<label>" . $this->lang->word('user name') . "</label><br />\n";
		$html .= "<input type='text' name='n_fname' value='".$this->showPosted('n_fname')."' maxlength='35' />\n";

		$html .= "<label>" . $this->lang->word('user last name') . "</label><br />\n";
		$html .= "<input type='text' name='n_lname' value='".$this->showPosted('n_lname')."' maxlength='35' />\n";

		$html .= "<label>" . $this->lang->word('user email address') . "</label><br />\n";
		$html .= "<input type='text' name='n_email' value='".$this->showPosted('n_email')."' maxlength='100' />\n";

		$html .= "<div style='width: 250px; margin: 24px 0 12px 0;'><hr /></div>";

		$html .= "<label>" . $this->lang->word('database server') . "</label><br />\n";
		$html .= "<input type='text' name='n_host' value='".$indx['host']."' maxlength='50' />\n";

		$html .= "<label>" . $this->lang->word('database name') . "</label><br />\n";
		$html .= "<input type='text' name='n_name' value='".$indx['db']."' maxlength='50' />\n";

		$html .= "<label>" . $this->lang->word('database username') . "</label><br />\n";
		$html .= "<input type='text' name='n_user' value='".$indx['user']."' maxlength='35' />\n";

		$html .= "<label>" . $this->lang->word('database password') . "</label><br />\n";
		$html .= "<input type='password' name='n_pwd' value='".$indx['pass']."' maxlength='35' />\n";

		$html .= "<label>" . $this->lang->word('database append') . "</label><br />\n";
		$html .= "<input type='text' name='n_appnd' value='$prefix' maxlength='20' />\n";

		$html .= "<input type='submit' name='n_submit' value='" . $this->lang->word('submit') . "' maxlength='50' /><br />\n";

		$html .= "</form>\n";
		if (isset($s)) $html .= $s;
		$html .= "</div>\n";
		
		return $html;
	}
	
	
	function do_installation()
	{
		// try to connect & install
		if (isset($_POST['n_submit']))
		{
			// check the vars...clean...
			$c['n_host']	= getPOST('n_host', '', 'connect', 100);
			$c['n_name']	= getPOST('n_name', '', 'connect', 65);
			$c['n_user']	= getPOST('n_user', '', 'connect', 32);
			$c['n_pwd']		= getPOST('n_pwd', '', 'connect', 32);
			$c['n_site']	= getPOST('n_site', '', 'none', 35);
			$c['n_appnd']	= getPOST('n_appnd', '', 'none', 20);
		
			// these need to be inserted into the database...
			$c['n_fname']	= getPOST('n_fname', '', 'none', 35);
			$c['n_lname']	= getPOST('n_lname', '', 'none', 35);
			$c['n_email']	= getPOST('n_email', '', 'none', 100);
		
			$GLOBALS['c'] = $c;
		
			// check connection - tables exist?
			$link = @mysqli_connect($c['n_host'], $c['n_user'], $c['n_pwd']);
			$_GLOBALS['link'] = $link;
	
			if (@mysqli_select_db($link, $c['n_name']) && ($this->writeConfig() == TRUE))
			{	
				// prevents installing over itself
				$result = @mysqli_query($link, "SELECT * FROM ".PX."settings WHERE adm_id = 1");
		
				if ($result)
				{
					setcookie('ndxz_hash', '5f8bfb51cc5c437a603abe3766d004d8', time()+3600*24*2, '/');
					setcookie('ndxz_access', md5('exhibit'), time()+3600*24*2, '/');
					header('location:' . BASEURL . BASENAME . '/install.php?p=3&s=success');
					exit;
				}
				else
				{
					// this is where we try to install
					$this->install_db();
			
					// let's check
					$result = @mysqli_query($link, "SELECT * FROM ".PX."settings WHERE adm_id = 1");
			
					if ($result)
					{
						setcookie('ndxz_hash', '5f8bfb51cc5c437a603abe3766d004d8', time()+3600*24*2, '/');
						setcookie('ndxz_access', md5('exhibit'), time()+3600*24*2, '/');
						header('location:' . BASEURL . BASENAME . '/install.php?p=3&s=success');
						exit;
					}
					else
					{
						$s = "<p><span class='ok-not'>XX</span> " . $this->lang->word('cannot install') . "</p><br />";
						$s .= "<p><small>" . $this->lang->word('goto forum') . "</small></p><br />";
					}
				}
			}
			else
			{
				$s = "<p><span class='ok-not'>XX</span> " . $this->lang->word('check config') . "</p><br />";
				$s .= "<p><small>" . $this->lang->word('goto forum') . "</small></p><br />";
			}
		}
		else
		{
			// make error note
			$s = "<p><span class='ok-not'>XX</span> " . $this->lang->word('check config') . "</p><br />";
			$s .= "<p><small>" . $this->lang->word('goto forum') . "</small></p><br />";
		}
		
		return $s;
	}
	
	
	function do_the_upgrade()
	{
		global $go, $default, $indx;

		require_once '../ndxzsite/config/config.php';
		require_once './db/db.mysql.php';
		
		$GLOBALS['indx'] = $indx;
		$this->db = new Db();
		
		// get the old prefix
		$old_prefix = $this->get_old_prefix();
		
		// get old settings
		$settings = $this->db->fetchRecord("SELECT installdate, curr_time, time_format FROM {$old_prefix}settings");
		
		// also need old objects_prefs
		$objects_prefs = $this->db->fetchRecord("SELECT obj_itop, obj_ibot, obj_name FROM {$old_prefix}objects_prefs 
			WHERE obj_id = '1'");
		
		// transform settings
		$search = array('<%', '%>', '<plug:');
		$replace = array('{{', '}}', '<plugin:');

		if ($objects_prefs['obj_itop'] != '') { $settings['obj_itop'] = str_replace($search, $replace, $objects_prefs['obj_itop']); }
		if ($objects_prefs['obj_ibot'] != '') { $settings['obj_ibot'] = str_replace($search, $replace, $objects_prefs['obj_ibot']); }
		
		$settings['obj_theme'] 	= 'default';
		$settings['version']	= VERSION;
		
		// need to update the settings table
		$this->db->updateArray(PX.'settings', $settings, "adm_id = '1'");
		
		// we don't need to update the user
		// default login will be index1/exhibit again
		// we already got their email address at install

		// get old media
		$medias = $this->db->fetchArray("SELECT * FROM {$old_prefix}media");
		
		if ($medias)
		{
			// we need to empty the new table before we begin
			$this->db->query("TRUNCATE TABLE ".PX."media");

			foreach ($medias as $media)
			{
				$media['media_obj_type'] = ($media['media_obj_type'] == 'exhibit') ? 'exhibits' : '';
				$media['media_udate'] = ($media['media_udate'] == '0000-00-00 00:00') ? getNow() : $media['media_udate'];
				$media['media_uploaded'] = ($media['media_uploaded'] == '0000-00-00 00:00') ? getNow() : $media['media_uploaded'];
				$this->db->insertArray(PX.'media', $media);
			}
		}
		
		// objects
		$objects = $this->db->fetchArray("SELECT * FROM {$old_prefix}objects");
		
		if ($objects)
		{
			// we need to empty the new table before we begin
			$this->db->query("TRUNCATE TABLE ".PX."objects");

			foreach ($objects as $object)
			{
				// exceptions
				$object['object'] = 'exhibits';
				$object['format'] = ($object['format'] == 'grow') ? 'visual_index' : $object['format'];
				$this->db->insertArray(PX.'objects', $object);
			}
		}
		
		// remake sections
		$this->remake_sections();
		
		header('location:' . BASEURL . BASENAME . '/install.php?p=4&s=success');
		exit;
	}
	
	
	function remake_sections()
	{
		global $go, $default, $indx;
		
		// get the old prefix
		$old_prefix = $this->get_old_prefix();
		
		$sections = $this->db->fetchArray("SELECT * FROM {$old_prefix}sections");
		
		// add to new database
		if ($sections)
		{
			// we need to empty the new table before we begin
			$this->db->query("TRUNCATE TABLE ".PX."sections");

			foreach ($sections as $section)
			{
				$section['sec_obj']	= 'exhibits';				
				$this->db->insertArray(PX.'sections', $section);	
			}
			
			// add the tag section here
			$tag_section['section'] 	= 'tag';
			$tag_section['sec_ord'] 	= '127';
			$tag_section['sec_disp'] 	= '1';
			$tag_section['sec_date'] 	= getNow();
			$tag_section['sec_path'] 	= '/tag';
			$tag_section['sec_desc'] 	= 'Tags';
			
			$last = $this->db->insertArray(PX.'sections', $tag_section);
		}
		
		//include DIRNAME . '/ndxz-studio/helper/time.php';
		include DIRNAME . '/ndxz-studio/helper/output.php';
		include DIRNAME . '/ndxz-studio/helper/romanize.php';
		include DIRNAME . '/ndxz-studio/lib/publish.php';
		
		$PUB = new Publish();
		
		$now = date("Y-m-d", time());
		
		foreach ($sections as $section)
		{
			// don't touch the main page
			if ($section['secid'] != 1)
			{
				$now = date("Y-m-d", time());

				$page = [];

				// a few more things
				$clean['sec_date'] 	= $now;
				//$clean['sec_ord']	= $section['sec_ord'];
			
				// we need to romanize the path based upon 'section'
				$PUB->title = trim($section['section']);
				$clean['section'] = $PUB->processTitle();
			
				// we need to clean up the path thing
				$clean['sec_path'] = $PUB->urlStrip('/' . $section['section']);
			
				$final_path = $PUB->urlStrip($clean['sec_path'] . '/');
				$final_path = ($final_path == '/root/') ? '/' : $final_path; 
			
				// unpublish section sections
				$this->db->updateArray(PX.'objects', array('status' => '0'), "url = '$final_path' AND status = '1'");
			
				// need to create the actual page too
				$page['object'] = 'exhibits';
				$page['title'] = $section['sec_desc'];
				$page['udate'] = $now;
				$page['pdate'] = $now;
				$page['creator'] = 1;
				$page['status'] = 1;
				$page['url'] = $final_path;
				$page['section_top'] = 1;
				$page['obj_ref_id'] = $section['secid'];
				$page['section_id'] = $section['secid'];
				$page['ord'] = '0';
			
				$this->db->insertArray(PX.'objects', $page);
			}
			else // adjust the main page
			{
				$page = [];

				$page['home'] = 1;
				$page['section_top'] = 1;
				$page['obj_ref_id'] = 1;
				
				$this->db->updateArray(PX.'objects', $page, "id = '1'");
			}
		}

		// need to create the actual page too
		$tag['object'] = 'exhibits';
		$tag['title'] = 'Tags';
		$tag['udate'] = getNow();
		$tag['pdate'] = getNow();
		$tag['creator'] = 1;
		$tag['status'] = 1;
		$tag['url'] = '/tag';
		$tag['section_top'] = 1;
		$tag['obj_ref_id'] = $last;
		$tag['section_id'] = $last;
		$tag['ord'] = '0';
		
		$this->db->insertArray(PX.'objects', $tag);
	}
}
$install = new Installation;
header ('Content-type: text/html; charset=utf-8'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Install : Indexhibit</title>
<link type="text/css" rel='stylesheet'  href="asset/css/style.css" />
<style type='text/css'>
body {  }
h1, h2 { margin: 6px 0 0 3px; }
h2 { margin-bottom: 6px; }
p { margin: 0 0 6px 3px; font-size: 12px; width: 300px; }
p.red { color: #c00; }
code { margin: 18px 0; font-size: 12px; }
.ok { color: #0c0; padding-right: 9px; }
.ok-not { color: #f00; padding-right: 9px; }
#footer { border-top: none; }
#log-form { margin-left: 3px; }
</style>
</head>
<body>
<div id='all'>
<h1>Indexhibit</h1>
<div id='main'>
<form action='' method='post'>
<?php echo $install->output(); ?>
</form>
<div class='cl'><!-- --></div>
</div>
<div id='footer' class='c2'>
	<div class='col'><a href='<?php echo BASEURL . BASENAME ?>/license.txt'>License</a></div>
	<div class='cl'><!-- --></div>
</div>	
</div>
</body>
</html>