<?php if (!defined('SITE')) exit('No direct script access allowed');


// a collection of things related to server setup

// returns a user friendly value - don't use this for an actual uploading limit
function getLimit()
{
	$upload_max_filesize = ini_get('upload_max_filesize');
	$upload_max_filesize = preg_replace('/M/', '', $upload_max_filesize);
	
	$post_max_size = ini_get('post_max_size');
	$post_max_size = preg_replace('/M/', '', $post_max_size);
	
	return ($post_max_size >= $upload_max_filesize) ? $upload_max_filesize . ' MB' : $post_max_size . ' MB';
}