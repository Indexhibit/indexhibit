<?php if (!defined('SITE')) exit('No direct script access allowed');


class xml
{
	var $type;
	var $limit = 50;
	var $output;
	var $xml;
	
	public function __construct()
	{
		
	}
	
	// /xml/
	function rss()
	{
		$OBJ =& get_instance();

		$rs = $OBJ->db->fetchArray("SELECT * FROM " . PX . "objects 
			WHERE status = '1' 
			AND hidden != '1' 
			ORDER BY pdate DESC
			LIMIT " . $this->limit);

		if ($rs) 
		{
			foreach ($rs as $row)
			{
				// let's query for photos too (only photos)
				$photos = $this->get_photos($row['id']);

				$this->xml .= "\n<item>
<title>" . strip_tags(htmlentities($row['title'])) . "</title>\n
<description>\n<![CDATA[\n" .  strip_tags($row['content'], '<p><br><a><img>') . $photos . "\n]]>\n</description>\n
<link>" . BASEURL . $row['url'] . "</link>\n
<pubDate>" . $row['pdate'] . "</pubDate>\n
</item>\n";
			}
		}

		$this->output();
	}
	
	function output()
	{
		$OBJ =& get_instance();
		
		// echo
		$this->output .= "<?xml version='1.0' encoding='utf-8' ?>\n";
		$this->output .= "<rss version='2.0'>
<channel>
<title>" . $OBJ->vars->exhibit['obj_name'] . "</title>
<link>" . BASEURL . "</link>\n
<language>" . $OBJ->vars->exhibit['site_lang'] . "</language>\n
<generator>Indexhibit</generator>\n";

		$this->output .= $this->xml;

		$this->output .= '</channel>
</rss>';

		// we should add a caching mechanism here

		header("Content-Type: application/xml; charset=utf-8");
		echo $this->output;
		exit;
	}
	
	// /xml/api/
	function api()
	{
		
	}

	
	function get_photos($id=0)
	{
		$OBJ =& get_instance();
		$photos = '';

		$rs = $OBJ->db->fetchArray("SELECT media_file, media_title, media_caption, media_dir 
			FROM " . PX . "media 
			WHERE media_obj_type != '' 
			AND media_ref_id = '$id'
			AND media_mime IN ('jpg', 'gif', 'png', 'jpeg') 
			AND media_hide != '1' 
			ORDER BY media_order ASC 
			limit 0,10");

		if ($rs) 
		{
			foreach ($rs as $row)
			{
				$dir = ($row['media_dir'] == '') ? "gimgs/" : $row['media_dir'] . '/';

				$photos .= "<p><img src='" . BASEURL . '/files/' . $dir . htmlentities($row['media_file']) . "' /></p>\n";
			}
		}
		
		return $photos;
	}


	function get_sitemap()
	{
		$OBJ =& get_instance();

		$rs = $OBJ->db->fetchArray("SELECT * FROM " . PX . "objects 
			WHERE status = '1' 
			AND hidden != '1' 
			ORDER BY pdate DESC
			LIMIT " . $this->limit);

		$items = '';

		if ($rs) 
		{
			foreach ($rs as $row)
			{
				$items .= "\n<item>
<title>" . strip_tags(htmlentities($row['title'])) . "</title>
<link>" . BASEURL . $row['url'] . "</link>
<description>\n<![CDATA[\n" .  strip_tags($row['content'], '<p><a><img>') . "\n]]>\n</description>
</item>\n";
			}
		}

		$items .= '</channel>
	</rss>';

		return $items;
	}
}