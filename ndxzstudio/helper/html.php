<?php if (!defined('SITE')) exit('No direct script access allowed');


// rudimentary functions for making html with less handling

// widget function
// works for select menus and some input fields
function ips($title, $function, $name, $value='', $attr='', $type='', $subtitle='', $req='', $tag='', $tag_attr='', $extra='', $afterwards='')
{
	$OBJ =& get_instance();
	
	global $error_msg, $go;
	
	// set a default
	// we might want div later
	if (!$tag) $tag = 'label';
	($tag_attr) ? $tag_attr = "$tag_attr" : $tag_attr = '';
	$help = (isset($OBJ->access->prefs['user_help'])) ? showHelp($title) : null;
	$afterwards = ($afterwards) ? $afterwards : '';
	
	if (isset($error_msg[$name]))
	{
		$msg = span($error_msg[$name], "class='error'");
	} 
	else 
	{
		$msg = NULL;
	}
	
	($subtitle) ? $subtitle = span($subtitle,"class='small-txt'") : $subtitle = '';
	($title) ? $title = label($title . ' ' . $subtitle . ' ' . $help . $msg) : $title = '';
	
	
	($req) ? $req = showerror($msg) : $req = '';
	$value = showvalue($name,$value);
	
	($extra) ? $add = $extra : $add = '';

	
	if ($function == 'input') 
	{
		$function = input($name, $type, $attr, $value) . $afterwards;
	} 
	else 
	{
		($function) ? $function = $function($value, $name, $attr, $add) . $afterwards:
		$function = NULL;
	}
	
	return $title ."\n" . $function;
}


function get_tag_groups($state, $name, $attr='')
{
	$OBJ =& get_instance();
	global $default;
	
	$s = '';
	
	if ($state == '') $state = 1;
	
	for ($i = 1; $i <= $default['tag_groups']; $i++)
	{
		$s .= option($i, $i, $state, $i);
	}
	
	return select($name, $attr, $s);	
}


function row_color($style)
{
	static $color = FALSE;
	
	if ($color == FALSE)
	{
		$color = TRUE;
		return;
	} 
	else
	{
		$color = FALSE;
		return $style;
	}
}


function attr($attr='')
{
	return ($attr) ? $attr = ' '.$attr: $attr = '';
}


function href($insert, $url, $attr='')
{
	$s = "<a href=\"$url\"";
	$s .= attr($attr).">$insert</a>";
	
	return $s;
}


function popup($insert, $url, $size='')
{
	$s = "<a href=\"$url\" onClick=\"OpenWindow(this.href,'popup',$size,'yes');return false;\"";
	$s .= ">$insert</a>";
	
	return $s;
}	


function div($insert='', $attr='')
{
	return tag($insert,'div',$attr);
}


function label($insert='', $attr='')
{	
	return softag($insert,'label',$attr);
}


function p($insert='', $attr='')
{	
	return ptag($insert,'p',$attr);
}


function ul($insert='', $attr='')
{	
	return ptag($insert,'ul',$attr);
}


function ol($insert='', $attr='')
{	
	return tag($insert,'ol',$attr);
}


function li($insert='', $attr='')
{	
	return xtag($insert,'li',$attr);
}


function code($insert='', $attr='')
{	
	return softag($insert,'code',$attr);
}


function table($insert='', $attr='')
{	
	return xtag($insert,'table',$attr);
}


function th($insert='', $attr='')
{	
	return xtag($insert,'th',$attr);
}


function tr($insert='', $attr='')
{	
	return xtag($insert,'tr',$attr);
}


function td($insert='', $attr='')
{	
	return xtag($insert,'td',$attr);
}


function span($insert, $attr='') 
{	
	return softag($insert,'span',$attr);
}


function strong($insert='', $attr='') 
{	
	return softag($insert,'strong',$attr);
}


function em($insert, $attr='') 
{	
	return softag($insert,'em',$attr);
}


function form($insert, $attr='') 
{	
	return tag($insert,'form',$attr);
}

function fieldset($insert, $attr='') 
{	
	return tag($insert, 'fieldset' ,$attr);
}


function legend($insert, $attr='') 
{	
	return tag($insert, 'legend' ,$attr);
}


function textarea($insert='', $attr='', $name='') 
{
	return softag($insert,'textarea',"name='$name'".attr($attr));
}


function radio($name, $attr='', $value, $check='') 
{
	($value == $check) ? $checked = ' checked ': $checked = '';
	return input($name,'radio',attr($attr).$checked,$value);
}


function button($name, $type, $attr='', $value) 
{
	//return '<button type="' . $type . '" name="' . $name . '" ' . attr($attr) . '>' . inputHelper($value) . '"</button>\n";
	
	return "<button type='$type' name='$name' " . attr($attr) . ">" . inputHelper($value) . "</button>";
}


function input($name, $type, $attr='', $value) 
{
	return '<input type="'.$type.'" name="'.$name.'" value="'.inputHelper($value).'" '.attr($attr)." />\n";
}


// deals with double quotes in input/text
function inputHelper($str)
{
	if (!$str) return;
	return str_replace('"','&#34;',$str);
}


function checkbox($name, $attr='', $value, $checked) 
{	
	($checked == '1') ? $check = ' checked' : $check = '';
	return "<input type='checkbox' name='$name' value='$value'" . attr($attr) . "$check />\n";
}


function option($value='', $insert, $s1='', $s2='') 
{
	($s1 == $s2) ? $sl = " selected" : $sl = '';
	return "<option value=\"$value\" $sl>$insert</option>\n";
}


function select($name, $attr, $insert) 
{
	$s = "<select name='$name'" . attr($attr) . ">\n";
	$s .= $insert;
	$s .= "</select>\n";
	return $s;
}


function br($repeat='1', $attr='') 
{
	$br = '';
	
	for ($i = 1; $i <= $repeat; $i++) $br .= "<br" . attr($attr) . " />\n";
	return $br;
}


function tag($insert='', $tag, $attr='') 
{
	return "<$tag".attr($attr).">\n$insert\n</$tag>\n";
}


function ptag($insert='', $tag, $attr='') 
{
	return "<$tag".attr($attr).">$insert</$tag>\n\n";
}


function xtag($insert, $tag, $attr='') 
{
	return "<$tag".attr($attr).">$insert</$tag>\n";
}


// for things like <span>
function softag($insert, $tag, $attr='') 
{
	return "<$tag".attr($attr).">$insert</$tag>";
}


function showerror($string='')
{
	if ($string)
	{
		return " <span class='error'>$string</span>";
	} 
	else 
	{
		return '';
	}
}


function errorAlert($err='')
{
	if ($err) 
	{
		return div('There was an error - please check your coordinates', "class='alert'");
	}
}


function showvalue($value, $name='')
{
	global $error_msg;	
	
	if (isset($error_msg)) 
	{
		// review
		return stripslashes($_POST[$value]);
	} 
	elseif ($name) 
	{
		return $name;
	} 
	else 
	{
		return NULL;
	}
}


// not being used, yet
function helpurl($subject)
{
	global $go;
		
	return "?a=$go[a]&q=help&lookup=" . htmlspecialchars($subject);
}


// not being used
function showHelp($title='')
{
	$OBJ =& get_instance();
	
	if ($OBJ->access->prefs['user_help'] == 1) 
	{
		return span(href('?', helpurl($title), "alt='Help: $title' title='Help: $title' onClick=\"OpenWindow(this.href,'popup','375','425','yes');return false;\""),"class='help'");
	} 
	else 
	{
		return;
	}
}