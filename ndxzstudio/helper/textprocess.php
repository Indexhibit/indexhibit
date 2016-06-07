<?php if (!defined('SITE')) exit('No direct script access allowed');


// this file contains functions for text processing and

// ---------------------------------------------------
//  All the best parts of this are remnants from TextPattern
//  Dean Allen <http://www.textpattern.com/
//  & <http://www.textdrive.com/
// ---------------------------------------------------

function textProcess($text='', $override)
{
	if ($text != '')
	{
		return ($override == 1) ? block($text) : $text;
	}	
	else
	{
		return;
	}
}


function cleanWhiteSpace($text)
{
	$out = str_replace(array("\r\n", "\t"), array("\n", ''), $text);
	$out = preg_replace("/(\n){2,}/", "\n\n", $out);
	$out = preg_replace("/\n *\n/", "\n\n", $out);
	return preg_replace('/"$/', "\" ", $out);
}
	

function cleaningOutput($text)
{
	$out = str_replace("<br>", "<br />", $text);
	$out = str_replace("<br />", "\n", $out);

	if (preg_match("/<(code.*|pre.*|table.*|ol.*|ul.*)>/i",$out)) 
	{
		return preg_replace("/(\n)/", "\n", $out);
	} 
	else 
	{
		return preg_replace("/(\n)/", "<br />\n", $out);
	}
}


function block($text)
{
	// REVIEW THIS APPROACH
	$text = preg_replace("/&(?![#a-z0-9]+;)/i", "x%x%", $text);
	$text = str_replace("x%x%", "&#38;", $text);
	$text = cleanWhiteSpace($text);

	$that = array();
	$that = explode("\n\n",$text);
	
	foreach ($that as $key => $value) 
	{
		$value = cleaningOutput($value);
		$value = glyphs($value);
		
		// to make it work with 'plug:'
		//if (preg_match("/<(h[12345]|code.*|h[12345].*|p.*|plug.*|style.*|div.*|\/div)>/i", $value)) 
		if (preg_match("/<(h[12345]|code.*|h[12345].*|blockquote.*|plug.*|object.*|p.*|style.*|!--.*|div.*|\/div|\/p)>/i", $value)) 
		{
			$s[] = $value;
		} 
		else 
		{			
			$s[] = '<p>'.$value.'</p>';
		}
	}

	$out = implode("\n\n",$s);

	return $out;
}


function glyphs($text) 
{
	$text = preg_replace('/"\z/', "\" ", $text);
	$codepre = false;
		
	if (!preg_match("/<.*>/", $text)) 
	{
		return $text;
	} 
	else 
	{
		$text = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

		foreach($text as $line) 
		{
			$offtags = ('code|pre');

			if (preg_match('/<(' . $offtags . ')>/i', $line)) $codepre = true;
			if (preg_match('/<\/(' . $offtags . ')>/i', $line)) $codepre = false;

			if ($codepre == true) 
			{
				$line = htmlspecialchars($line, ENT_NOQUOTES, "UTF-8");
				$line = preg_replace('/&lt;(\/?' . $offtags . ')&gt;/', "<$1>", $line);
			}
				
			$glyph_out[] = $line;
		}
			
		return join('', $glyph_out);
	}
}


// text_reduction($go['content'], "&nbsp;", '<p><br>' 50);
function text_reduction($string='', $repl, $allow='', $limit=40)
{
    if (($string == '') || ($string == null)) return;
 
    $limit = @strpos(strip_tags($string, $allow), " ", $limit);
    
    if ($limit)
    {
        return substr_replace(strip_tags($string, $allow), $repl, $limit);
    }
    else
    {
        return $string;
    }
}