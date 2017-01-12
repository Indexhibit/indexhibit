<?php define('SITE', 'Bonjour!');

/**
* Installation
* 
* @version 2.0
* @author Vaska
*/

	require_once '../ndxzsite/config/options.php';
	require_once 'defaults.php';
	require_once 'common.php';
	require_once './helper/entrance.php';
	require_once './helper/html.php';
	require_once './helper/time.php';
	require_once './lang/index.php';
	
	$page = getURI('page', 0, 'alnum', 1);
	
	// set cookie
	if (isset($_POST['submitLang']) && ($_POST['user_lang'] != ''))
	{
		setcookie('install', $_POST['user_lang'], time()+3600);
		header("location:install.php?page=1");
	}
	
	// look for the cookie here
	$picked = (isset($_COOKIE['install'])) ? $_COOKIE['install'] : 'en-us';
	
	$lang = new Lang;
	$lang->setlang($picked);
	
	function install_db()
	{
		global $c, $picked;
		
		require_once '../ndxzsite/config/config.php';
		
		$link = @mysqli_connect($indx['host'], $indx['user'], $indx['pass']);
		$ver = mysqli_ver($link);
		if (is_numeric($ver) && $ver <= 4)
		{
			$isam = 'TYPE=MyISAM';
		}
		else // it's 5
		{
			$isam = 'ENGINE=MyISAM DEFAULT CHARSET=utf8';
		}
		
		$sql = array();

		$sql[] = "CREATE TABLE IF NOT EXISTS `iptocountry` (
		  `ip_from` double NOT NULL DEFAULT '0',
		  `ip_to` double NOT NULL DEFAULT '0',
		  `country_code2` char(2) NOT NULL,
		  `country_code3` char(3) NOT NULL,
		  `country_name` varchar(50) NOT NULL,
		  KEY `ip_from_to_idx` (`ip_from`,`ip_to`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."abstracts` (
		  `ab_id` int(11) NOT NULL AUTO_INCREMENT,
		  `ab_obj` varchar(32) NOT NULL,
		  `ab_obj_id` int(11) NOT NULL,
		  `ab_var` varchar(255) NOT NULL,
		  `abstract` text,
		  PRIMARY KEY (`ab_id`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."media` (
		  `media_id` int(11) NOT NULL AUTO_INCREMENT,
		  `media_ref_id` smallint(6) NOT NULL DEFAULT '0',
		  `media_obj_type` varchar(15) NOT NULL,
		  `media_mime` varchar(15) NOT NULL,
		  `media_tags` varchar(255) NOT NULL DEFAULT '0',
		  `media_file` varchar(255) NOT NULL,
		  `media_thumb` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `media_file_replace` varchar(255) NOT NULL,
		  `media_title` varchar(255) NOT NULL,
		  `media_caption` text NOT NULL,
		  `media_x` varchar(5) NOT NULL,
		  `media_y` varchar(5) NOT NULL,
		  `media_xr` smallint(4) NOT NULL,
		  `media_yr` smallint(4) NOT NULL,
		  `media_kb` mediumint(9) NOT NULL DEFAULT '0',
		  `media_udate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `media_uploaded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `media_order` smallint(3) NOT NULL DEFAULT '999',
		  `media_hide` tinyint(1) NOT NULL DEFAULT '0',
		  `media_dir` varchar(255) NOT NULL,
		  `media_src` varchar(25) NOT NULL,
		  PRIMARY KEY (`media_id`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."objects` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `object` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `obj_ref_id` int(4) NOT NULL DEFAULT '0',
		  `title` varchar(100) NOT NULL,
		  `content` mediumtext NOT NULL,
		  `home` tinyint(1) NOT NULL DEFAULT '0',
		  `link` varchar(255) NOT NULL,
		  `target` tinyint(1) NOT NULL DEFAULT '0',
		  `iframe` tinyint(1) NOT NULL DEFAULT '0',
		  `new` tinyint(1) NOT NULL DEFAULT '0',
		  `tags` varchar(250) CHARACTER SET latin1 NOT NULL DEFAULT '0',
		  `header` text NOT NULL,
		  `udate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `pdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `creator` tinyint(3) NOT NULL DEFAULT '0',
		  `status` tinyint(1) NOT NULL DEFAULT '0',
		  `process` tinyint(1) NOT NULL DEFAULT '1',
		  `page_cache` tinyint(1) NOT NULL DEFAULT '0',
		  `section_id` tinyint(3) NOT NULL DEFAULT '0',
		  `section_top` tinyint(1) NOT NULL DEFAULT '0',
		  `section_sub` varchar(255) NOT NULL,
		  `subdir` tinyint(1) NOT NULL DEFAULT '0',
		  `url` varchar(250) NOT NULL,
		  `ord` smallint(3) NOT NULL DEFAULT '999',
		  `color` varchar(7) CHARACTER SET latin1 NOT NULL DEFAULT 'ffffff',
		  `bgimg` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `hidden` tinyint(1) NOT NULL DEFAULT '0',
		  `current` tinyint(1) NOT NULL DEFAULT '0',
		  `perm` tinyint(1) NOT NULL DEFAULT '0',
		  `media_source` tinyint(3) NOT NULL DEFAULT '0',
		  `media_source_detail` varchar(255) NOT NULL,
		  `images` smallint(4) NOT NULL DEFAULT '9999',
		  `thumbs_shape` tinyint(1) NOT NULL DEFAULT '0',
		  `thumbs` smallint(4) NOT NULL DEFAULT '200',
		  `format` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT 'visual_index',
		  `thumbs_format` tinyint(1) NOT NULL DEFAULT '0',
		  `operand` tinyint(4) NOT NULL,
		  `titling` tinyint(1) NOT NULL DEFAULT '0',
		  `break` smallint(2) NOT NULL DEFAULT '0',
		  `tiling` tinyint(1) NOT NULL DEFAULT '1',
		  `year` varchar(4) CHARACTER SET latin1 NOT NULL DEFAULT '2010',
		  `report` tinyint(1) NOT NULL DEFAULT '0',
		  `pwd` varchar(100) NOT NULL,
		  `placement` tinyint(1) NOT NULL DEFAULT '0',
		  `template` varchar(25) CHARACTER SET latin1 NOT NULL DEFAULT 'index.php',
		  `ling` varchar(7) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'en',
		  `ling_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `serial` longtext NOT NULL,
		  `extra1` varchar(255) NOT NULL,
		  `extra2` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."objects_prefs` (
		  `obj_id` int(11) NOT NULL AUTO_INCREMENT,
		  `obj_ref_type` varchar(255) NOT NULL,
		  `obj_active` tinyint(1) NOT NULL DEFAULT '1',
		  `obj_title` varchar(255) NOT NULL,
		  `obj_section` smallint(3) NOT NULL DEFAULT '1',
		  `obj_template` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `obj_members` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `obj_img` varchar(255) NOT NULL,
		  `obj_settings` longtext CHARACTER SET latin1 NOT NULL,
		  `obj_group` varchar(255) NOT NULL,
		  PRIMARY KEY (`obj_id`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."plugins` (
		  `pl_id` int(4) NOT NULL AUTO_INCREMENT,
		  `pl_primary` tinyint(1) NOT NULL DEFAULT '0',
		  `pl_type` varchar(15) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `pl_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `pl_uri` varchar(255) NOT NULL,
		  `pl_version` varchar(20) NOT NULL,
		  `pl_file` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `pl_function` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `pl_hook` varchar(255) NOT NULL,
		  `pl_space` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `pl_creator` varchar(50) NOT NULL,
		  `pl_www` varchar(255) NOT NULL,
		  `pl_desc` text NOT NULL,
		  `pl_options` text NOT NULL,
		  `pl_options_build` text NOT NULL,
		  `pl_usage` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `pl_usage_desc` varchar(255) NOT NULL,
		  `pl_order` smallint(3) NOT NULL DEFAULT '100',
		  PRIMARY KEY (`pl_id`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."sections` (
		  `secid` tinyint(3) NOT NULL AUTO_INCREMENT,
		  `section` varchar(60) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `sec_obj` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT 'exhibit',
		  `sec_ord` tinyint(4) NOT NULL DEFAULT '0',
		  `sec_disp` tinyint(3) NOT NULL DEFAULT '1',
		  `sec_hide` tinyint(1) NOT NULL DEFAULT '0',
		  `sec_pwd` varchar(32) NOT NULL,
		  `sec_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `sec_path` varchar(250) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `sec_subs` varchar(100) NOT NULL,
		  `sec_desc` varchar(100) NOT NULL,
		  `sec_proj` tinyint(4) NOT NULL DEFAULT '0',
		  `sec_group` tinyint(4) NOT NULL,
		  `sec_report` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`secid`),
		  UNIQUE KEY `sec_path` (`sec_path`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."settings` (
		  `adm_id` tinyint(3) NOT NULL AUTO_INCREMENT,
		  `site_name` varchar(40) NOT NULL DEFAULT '',
		  `installdate` varchar(20) NOT NULL DEFAULT '',
		  `version` varchar(25) NOT NULL DEFAULT '',
		  `curr_time` tinyint(3) NOT NULL DEFAULT '0',
		  `site_lang` varchar(8) NOT NULL DEFAULT 'en-us',
		  `time_format` varchar(25) NOT NULL DEFAULT '',
		  `tagging` tinyint(1) NOT NULL DEFAULT '1',
		  `help` tinyint(1) NOT NULL DEFAULT '0',
		  `caching` tinyint(1) NOT NULL DEFAULT '0',
		  `hibernate` varchar(255) CHARACTER SET utf8 NOT NULL,
		  `obj_name` varchar(255) NOT NULL DEFAULT '',
		  `obj_theme` varchar(50) NOT NULL DEFAULT '',
		  `obj_itop` text NOT NULL,
		  `obj_ibot` text NOT NULL,
		  `obj_org` tinyint(1) NOT NULL DEFAULT '1',
		  `obj_apikey` varchar(32) NOT NULL DEFAULT '',
		  `site_format` varchar(30) NOT NULL DEFAULT '%d %B %Y',
		  `site_offset` tinyint(3) NOT NULL DEFAULT '0',
		  `site_vars` longtext NOT NULL,
		  PRIMARY KEY (`adm_id`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."stats` (
		  `hit_id` int(14) NOT NULL AUTO_INCREMENT,
		  `hit_addr` varchar(16) NOT NULL DEFAULT '',
		  `hit_country` varchar(30) NOT NULL DEFAULT '',
		  `hit_lang` varchar(10) NOT NULL DEFAULT '',
		  `hit_domain` varchar(100) NOT NULL DEFAULT '',
		  `hit_referrer` varchar(100) NOT NULL DEFAULT '',
		  `hit_page` varchar(100) NOT NULL DEFAULT '',
		  `hit_agent` varchar(250) NOT NULL DEFAULT '',
		  `hit_keyword` varchar(250) NOT NULL DEFAULT '',
		  `hit_os` varchar(20) NOT NULL DEFAULT '',
		  `hit_browser` varchar(20) NOT NULL DEFAULT '',
		  `hit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `hit_month` varchar(7) CHARACTER SET utf8 NOT NULL,
		  `hit_day` date NOT NULL,
		  PRIMARY KEY (`hit_id`)
		) $isam ;";
		
		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."stats_exhibits` (
		  `stor_url` varchar(255) NOT NULL DEFAULT '',
		  `stor_count` smallint(6) NOT NULL DEFAULT '0'
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."stats_storage` (
		  `stor_date` varchar(7) NOT NULL DEFAULT '0000-00',
		  `stor_hits` int(11) NOT NULL DEFAULT '0',
		  `stor_unique` int(11) NOT NULL DEFAULT '0',
		  `stor_referrer` int(11) NOT NULL DEFAULT '0'
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."subsections` (
		  `sub_id` tinyint(3) NOT NULL AUTO_INCREMENT,
		  `sub_sec_id` tinyint(3) NOT NULL,
		  `sub_title` varchar(255) NOT NULL,
		  `sub_folder` varchar(255) NOT NULL,
		  `sub_order` tinyint(3) NOT NULL,
		  `sub_hide` tinyint(1) NOT NULL,
		  PRIMARY KEY (`sub_id`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."tagged` (
		  `tagged_id` smallint(6) NOT NULL,
		  `tagged_object` varchar(3) CHARACTER SET utf8 NOT NULL,
		  `tagged_obj_id` smallint(6) NOT NULL
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."tags` (
		  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
		  `tag_name` varchar(255) NOT NULL,
		  `tag_group` smallint(3) NOT NULL DEFAULT '1',
		  `tag_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `tag_icon` varchar(255) NOT NULL,
		  PRIMARY KEY (`tag_id`),
		  UNIQUE KEY `tag_name` (`tag_name`)
		) $isam ;";

		$sql[] = "CREATE TABLE IF NOT EXISTS `".PX."users` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `userid` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `password` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `email` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `threads` tinyint(3) NOT NULL DEFAULT '10',
		  `writing` tinyint(1) NOT NULL DEFAULT '0',
		  `user_offset` tinyint(3) NOT NULL DEFAULT '0',
		  `user_format` varchar(30) CHARACTER SET latin1 NOT NULL DEFAULT '%d %B %Y',
		  `user_lang` varchar(8) CHARACTER SET latin1 NOT NULL DEFAULT 'en-us',
		  `user_hash` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
		  `user_help` tinyint(1) NOT NULL DEFAULT '0',
		  `user_mode` tinyint(1) NOT NULL DEFAULT '0',
		  `user_name` varchar(35) NOT NULL,
		  `user_surname` varchar(35) NOT NULL,
		  `user_admin` tinyint(1) NOT NULL DEFAULT '0',
		  `user_active` tinyint(1) NOT NULL DEFAULT '1',
		  `user_client` tinyint(1) NOT NULL DEFAULT '0',
		  `user_img` varchar(255) NOT NULL,
		  PRIMARY KEY (`ID`),
		  UNIQUE KEY `userid` (`userid`),
		  KEY `ID` (`ID`)
		) $isam ;";
	
		$sql[] = "INSERT INTO `".PX."objects` (`id`, `object`, `obj_ref_id`, `title`, `content`, `home`, `link`, `target`, `iframe`, `new`, `tags`, `header`, `udate`, `pdate`, `creator`, `status`, `process`, `page_cache`, `section_id`, `section_top`, `url`, `ord`, `color`, `bgimg`, `hidden`, `current`, `perm`, `media_source`, `media_source_detail`, `images`, `thumbs_shape`, `thumbs`, `format`, `thumbs_format`, `operand`, `titling`, `break`, `tiling`, `year`, `report`, `pwd`, `placement`, `template`, `ling`, `ling_id`, `serial`, `extra1`, `extra2`) VALUES
		(1, 'exhibits', 1, 'Main', '', 1, '', 0, 0, 0, '2', '', '".getNow()."', '".getNow()."', 1, 1, 1, 0, 1, 1, '/', 0, 'ffffff', '', 0, 0, 0, 0, '', 600, 0, 200, 'visual_index', 0, 2, 1, 0, 1, '2011', 0, '', 0, 'index.php', 'en', '', '', '', ''),
		(2, 'xml', 0, 'RSS', '<plugin:xml:rss />', 0, '', 0, 0, 0, '0', '', '".getNow()."', '".getNow()."', 1, 1, 0, 0, 1, 0, '/xml/', 22, 'ffffff', '', 1, 0, 0, 0, '', 9999, 0, 200, 'visual_index', 0, 0, 0, 0, 1, '2011', 0, '', 0, 'index.php', 'en', '', '', '', ''),
		(4, 'exhibits', 2, 'Project', '', 0, '', 0, 0, 0, '0', '', '".getNow()."', '".getNow()."', 1, 1, 1, 0, 2, 1, '/project/', 0, 'ffffff', '', 0, 0, 0, 2, '', 9999, 0, 200, 'visual_index', 0, 0, 1, 0, 1, '2011', 0, '', 1, 'index.php', 'en', '', '', '', ''),
		(5, 'exhibits', 3, 'Tags', '', 0, '', 0, 0, 0, '0', '', '".getNow()."', '".getNow()."', 1, 0, 1, 0, 3, 1, '/tag/', 0, 'ffffff', '', 0, 0, 0, 0, '', 9999, 0, 200, 'visual_index', 0, 0, 0, 0, 1, '2011', 0, '', 0, 'index.php', 'en', '', '', '', '');";
		
		$sql[] = "INSERT INTO `".PX."objects_prefs` (`obj_id`, `obj_ref_type`, `obj_active`, `obj_title`, `obj_section`, `obj_template`, `obj_members`, `obj_img`, `obj_settings`, `obj_group`) VALUES
		(1, 'exhibits', 1, '', 1, '', '', '', '', ''),
		(2, 'xml', 1, '', 1, '', '', '', '', ''),
		(3, 'tag', 1, '', 1, '', '', '', 'a:7:{s:10:\"section_id\";s:1:\"3\";s:8:\"template\";s:7:\"tag\.php\";s:6:\"format\";s:12:\"visual_index\";s:6:\"thumbs\";s:3:\"200\";s:12:\"thumbs_shape\";s:1:\"0\";s:5:\"break\";s:1:\"0\";s:7:\"titling\";s:1:\"0\";}', '');";

		$sql[] = "INSERT INTO `".PX."sections` (`secid`, `section`, `sec_obj`, `sec_ord`, `sec_disp`, `sec_hide`, `sec_pwd`, `sec_date`, `sec_path`, `sec_desc`, `sec_proj`, `sec_group`, `sec_report`) VALUES
		(1, 'root', 'exhibits', 2, 1, 0, '', '2006-12-20 17:01:31', '/', 'Main', 0, 0, 0),
		(2, 'project', 'exhibits', 1, 1, 0, '', '2010-03-03 23:48:44', '/project', 'Projects', 0, 0, 0),
		(3, 'tag', 'exhibits', 3, 1, 1, '', '2010-03-04 05:51:22', '/tag', 'Tags', 3, 0, 0);";

		$sql[] = "INSERT INTO `".PX."settings` (`adm_id`, `site_name`, `installdate`, `version`, `curr_time`, `site_lang`, `time_format`, `tagging`, `help`, `hibernate`, `obj_name`, `obj_theme`, `obj_itop`, `obj_ibot`, `obj_org`, `obj_apikey`, `site_format`, `site_offset`, `site_vars`) VALUES
		(1, '".addslashes($c['n_site'])."', '".getNow()."', '" . VERSION . "', 1, 'en-us', '%d %B %Y', 1, 0, '', '".addslashes($c['n_site'])."', 'default', \"<h1><a href='/' title='{{obj_name}}'>{{obj_name}}</a></h1>\", \"<p><a href='http:\/\/www\.indexhibit\.org\/'>Built with Indexhibit</a></p>\", 2, 'asdfsafasfadsfdfs', '%d %B %Y', 0, 'a:3:{s:9:\"passwords\";s:1:\"1\";s:9:\"templates\";s:1:\"0\";s:4:\"tags\";s:1:\"1\";}');";

		$sql[] = "INSERT INTO `".PX."users` (`ID`, `userid`, `password`, `email`, `threads`, `writing`, `user_offset`, `user_format`, `user_lang`, `user_hash`, `user_help`, `user_mode`, `user_name`, `user_surname`, `user_admin`, `user_active`, `user_client`) VALUES
		(1, 'index1', '22645ed8b5f5fa4b597d0fe61bed6a96', '".addslashes($c['n_email'])."', 15, 0, 0, '%d %B %Y', '$picked', '5f8bfb51cc5c437a603abe3766d004d8', 0, 1, '".addslashes($c['n_fname'])."', '".addslashes($c['n_lname'])."', 1, 1, 0);";

		if (mysqli_select_db($link, $indx['db']))
		{
			foreach ($sql as $install)
			{
				$result = mysqli_query($link, $install);
				if (!$result) {
				    die(mysqli_error($link));
                }
			}
		}
	}


	function mysqli_ver($link)
	{
		$ver = mysqli_get_client_info($link);
		$num = explode('.', $ver);
		return $num[0];
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

		$rs = $lang->lang_options();

		if ($default == '')
		{
			$s .= option('', $lang->word('make selection'), 0, 0);
		}

		foreach ($rs as $key => $a) 
		{
			$language = array_pop($a);
			
			//echo DIRNAME . BASENAME . '/' . LANGPATH . '/' . $key . " //////// ";

			// check to see if the lang folder exists
			if (is_dir(DIRNAME . BASENAME . '/' . LANGPATH . '/' . $key))
			{
				($default == $a) ? $sl = "selected ": $sl = "";
				$s .= option($key, $lang->word($language), $default, $key);
			}
		}
		clearstatcache();

		return select($name, attr($attr), $s);
	}
	
	
	// try to connect & install
	if (isset($_POST['n_submit']))
	{
		// check the vars...clean...
		$c['n_host']	= getPOST('n_host', '', 'connect', 100);
		$c['n_name']	= getPOST('n_name', '', 'connect', 65);
		$c['n_user']	= getPOST('n_user', '', 'connect', 32);
		$c['n_pwd']		= getPOST('n_pwd', '', 'connect', 32);
		$c['n_site']	= getPOST('n_site', '', 'none', 35);
		$c['n_appnd']	= getPOST('n_appnd', '', 'none', 10);

        define("PX", $c['n_appnd']);
		
		// these need to be inserted into the database...
		$c['n_fname']	= getPOST('n_fname', '', 'none', 35);
		$c['n_lname']	= getPOST('n_lname', '', 'none', 35);
		$c['n_email']	= getPOST('n_email', '', 'none', 100);
		
		$GLOBALS['c'] = $c;
		
		// check connection - tables exist?
		$link = mysqli_connect($c['n_host'], $c['n_user'], $c['n_pwd']);
	
		if (mysqli_select_db($link, $c['n_name']) && (writeConfig() == TRUE))
		{	
			$result = mysqli_query($link, "SELECT * FROM ".PX."settings WHERE adm_id = 1");
		
			if ($result)
			{
				//header("location:install.php?page=3&s=success");
				setcookie('ndxz_hash', '5f8bfb51cc5c437a603abe3766d004d8', time()+3600*24*2, '/');
				setcookie('ndxz_access', md5('exhibit'), time()+3600*24*2, '/');
				header('location:' . BASEURL . BASENAME . '/?a=system&q=preferences&flag=true');
				exit;
			}
			else
			{	
				// this is where we try to install
				install_db();
			
				// let's check
				$result = mysqli_query($link, "SELECT * FROM ".PX."settings WHERE adm_id = 1");
			
				if ($result)
				{
					//header("location:install.php?page=3&s=success");
					setcookie('ndxz_hash', '5f8bfb51cc5c437a603abe3766d004d8', time()+3600*24*2, '/');
					setcookie('ndxz_access', md5('exhibit'), time()+3600*24*2, '/');
					header('location:' . BASEURL . BASENAME . '/?a=system&q=preferences&flag=true');
					exit;
				}
				else
				{
					$s = "<p><span class='ok-not'>XX</span> " . $lang->word('cannot install') . "</p><br />";
					$s .= "<p><small>" . $lang->word('goto forum') . "</small></p><br />";
				}
			}
		}
		else
		{
			$s = "<p><span class='ok-not'>XX</span> " . $lang->word('check config') . "</p><br />";
			$s .= "<p><small>" . $lang->word('goto forum') . "</small></p><br />";
		}
	}
	else
	{
		// make error note
	}
	
	function makeEdition()
	{
		$rest = 'http://api.indexhibit.org/?method=edition&url=' . urlencode(BASEURL);
		$edition = @file_get_contents($rest);
		
		return;
	}
	
header ('Content-type: text/html; charset=utf-8');
?>
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
<?php
	if ($page == 0)
	{
		if (file_exists(DIRNAME . '/ndxzsite/config/config.php'))
		{
			echo p($lang->word('you are already installed'));
		}
		else
		{
			echo ips($lang->word('language'), 'getLanguage', 'user_lang', NULL, NULL, 'text');
			echo "<input type='submit' name='submitLang' value='NEXT &raquo;' />\n";
		}
	}
	elseif ($page == 1)
	{
		if (file_exists(DIRNAME . '/.htaccess'))
		{
			$flagA = true;
			echo "<p><span class='ok'>OK</span> " . $lang->word('htaccess ok') . "</p>";
		}
		else
		{
			echo "<p><span class='ok-not'>??</span> " . $lang->word('htaccess not ok') . "</p>";
		}
		
		if ((is_dir(DIRNAME . '/files')) && (is_writable(DIRNAME . '/files')))
		{
			$flagB = true;
			echo "<p><span class='ok'>OK</span> " . $lang->word('files ok') . "</p>\n";
		}
		else
		{
			echo "<p><span class='ok-not'>XX</span> " . $lang->word('files not ok') . "</p>";
		}
		
		if ((is_dir(DIRNAME . '/files/gimgs')) && (is_writable(DIRNAME . '/files/gimgs')))
		{
			$flagC = true;
			echo "<p><span class='ok'>OK</span> " . $lang->word('filesgimgs ok') . "</p>";
		}
		else
		{
			echo "<p><span class='ok-not'>XX</span> " . $lang->word('filesgimgs not ok') . "</p>";
		}
		
		if ((is_dir(DIRNAME . '/files/dimgs')) && (is_writable(DIRNAME . '/files/dimgs')))
		{
			$flagE = true;
			echo "<p><span class='ok'>OK</span> " . $lang->word('filesdimgs ok') . "</p>";
		}
		else
		{
			echo "<p><span class='ok-not'>XX</span> " . $lang->word('filesdimgs not ok') . "</p>";
		}
		
		if ((is_dir(DIRNAME . '/ndxzsite/config')) && (is_writable(DIRNAME . '/ndxzsite/config')))
		{
			$flagD = true;
			echo "<p><span class='ok'>OK</span> " . $lang->word('config ok') . "</p>\n";
		}
		else
		{
			echo "<p><span class='ok-not'>XX</span> " . $lang->word('config not ok') . "</p>";
		}
		
		if (($flagB == true) && ($flagC == true) && ($flagD == true) && ($flagE == true))
		{
			echo "<br /><p><strong>" . $lang->word('try db setup now') . "</strong></p>";
			echo "<br /><p><a href='?page=2'>" . $lang->word('continue') . "</a></p><br />";
		}
		else
		{
			echo "<br /><p><strong>" . $lang->word('please correct errors') . "<strong></p><br />";
			echo "<p><strong>" . $lang->word('refresh page') . "</strong></p><br />";
			echo "<p><small>" . $lang->word('goto forum') . "</small></p><br />";
		}
		
		// we need gd library and mbstring and php4+ and mysql 3.23+
	}
	elseif ($page == 2)
	{
		echo "<div id='log-form'>\n";
		echo "<form name='iform' method='post'>\n";
		
		// build the form here
		echo "<label>" . $lang->word('site name') . "</label><br />\n";
		echo "<input type='text' name='n_site' value='".showPosted('n_site')."' maxlength='35' />\n";
		
		echo "<label>" . $lang->word('user name') . "</label><br />\n";
		echo "<input type='text' name='n_fname' value='".showPosted('n_fname')."' maxlength='35' />\n";
		
		echo "<label>" . $lang->word('user last name') . "</label><br />\n";
		echo "<input type='text' name='n_lname' value='".showPosted('n_lname')."' maxlength='35' />\n";
		
		echo "<label>" . $lang->word('user email address') . "</label><br />\n";
		echo "<input type='text' name='n_email' value='".showPosted('n_email')."' maxlength='100' />\n";
		
		echo "<div style='width: 250px; margin: 24px 0 12px 0;'><hr /></div>";
		
		echo "<label>" . $lang->word('database server') . "</label><br />\n";
		echo "<input type='text' name='n_host' value='".showPosted('n_host')."' maxlength='50' />\n";
		
		echo "<label>" . $lang->word('database name') . "</label><br />\n";
		echo "<input type='text' name='n_name' value='".showPosted('n_name')."' maxlength='50' />\n";
		
		echo "<label>" . $lang->word('database username') . "</label><br />\n";
		echo "<input type='text' name='n_user' value='".showPosted('n_user')."' maxlength='35' />\n";
		
		echo "<label>" . $lang->word('database password') . "</label><br />\n";
		echo "<input type='text' name='n_pwd' value='".showPosted('n_pwd')."' maxlength='35' />\n";
		
		echo "<label>" . $lang->word('database append') . "</label><br />\n";
		echo "<input type='text' name='n_appnd' value='ndxz_' maxlength='10' />\n";
		
		//echo "</legend>";

		echo "<input type='submit' name='n_submit' value='" . $lang->word('submit') . "' maxlength='50' /><br />\n";
		
		echo "</form>\n";
		
		if (isset($s)) echo $s;
		
		echo "</div>\n";
	}
	elseif ($page == 3)
	{
		if ($_GET['s'] == 'success')
		{
			// need to set some cookies and goto the admin page with a flag for a message...
			setcookie('ndxz_hash', '5f8bfb51cc5c437a603abe3766d004d8', time()+3600*24*2, '/');
			setcookie('ndxz_access', md5('exhibit'), time()+3600*24*2, '/');

			// let's get an edition number
			makeEdition();
			
			// to the preferences page
			header('location:' . BASEURL . BASENAME . '/?a=system&q=preferences&flag=true');
			exit;
		}
		else
		{
			echo "<p><span class='ok-not'>XX</span> " . $lang->word('cannot install') . "</p><br />";
			echo "<p><small>" . $lang->word('goto forum') . "</small></p><br />";
		}
	}
	else
	{
		echo "<p class='red'>" . $lang->word('freak out') . "</p><br />";
		echo "<p><small>" . $lang->word('goto forum') . "</small></p><br />";
	}

?>
</form>
<div class='cl'><!-- --></div>
</div>
<div id='footer' class='c2'>
	<div class='col'><a href='<?php echo BASEURL.BASENAME ?>/license.txt'>License</a></div>
	<div class='cl'><!-- --></div>
</div>	
</div>
</body>
</html>