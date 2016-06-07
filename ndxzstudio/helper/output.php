<?php if (!defined('SITE')) exit('No direct script access allowed');


// functions used when getting data out of database

// for nicer placement in our textareas - but are we using this really?
function stripForForm($text='', $process='')
{
	if (($process == 0) || ($process == '')) 
	{
		// have we checked this yet
		if (function_exists('mb_decode_numericentity'))
		{
			return mb_decode_numericentity($text, UTF8EntConvert('1'), 'utf-8');
		}
		else
		{
			$text = htmlspecialchars($text);
			return str_replace(array("&gt;","&lt;"), array(">","<"), $text);
		}
	}
	
	if ($text) 
	{
		$out = str_replace("<p>", "", $text);
		$out = str_replace(array("<br />","<br>"),array("",""), $out);
		$out = str_replace("</p>", "", $out);
		
		if (function_exists('mb_decode_numericentity'))
		{
			$out = mb_decode_numericentity($out, UTF8EntConvert('1'), 'utf-8');
		}
		else
		{
			$out = htmlspecialchars($out);
			$out = str_replace(array("&gt;","&lt;"), array(">","<"), $out);
		}
		
		return $out;
	} 
	else 
	{
		return '';
	}
}


// this does allow ", &, < and > so be sure to be aware of them
// http://php.belnet.be/manual/en/function.mb-encode-numericentity.php
function UTF8EntConvert($out='')
{
	$f = 0xffff;

	$convmap = array(

		// %HTMLlat1;
		160,  255, 0, $f,

		// %HTMLsymbol;
		402,  402, 0, $f,  913,  929, 0, $f,  931,  937, 0, $f,
		945,  969, 0, $f,  977,  978, 0, $f,  982,  982, 0, $f,
		8226, 8226, 0, $f, 8230, 8230, 0, $f, 8242, 8243, 0, $f,
		8254, 8254, 0, $f, 8260, 8260, 0, $f, 8465, 8465, 0, $f,
		8472, 8472, 0, $f, 8476, 8476, 0, $f, 8482, 8482, 0, $f,
		8501, 8501, 0, $f, 8592, 8596, 0, $f, 8629, 8629, 0, $f,
		8656, 8660, 0, $f, 8704, 8704, 0, $f, 8706, 8707, 0, $f,
		8709, 8709, 0, $f, 8711, 8713, 0, $f, 8715, 8715, 0, $f,
		8719, 8719, 0, $f, 8721, 8722, 0, $f, 8727, 8727, 0, $f,
		8730, 8730, 0, $f, 8733, 8734, 0, $f, 8736, 8736, 0, $f,
		8743, 8747, 0, $f, 8756, 8756, 0, $f, 8764, 8764, 0, $f,
		8773, 8773, 0, $f, 8776, 8776, 0, $f, 8800, 8801, 0, $f,
		8804, 8805, 0, $f, 8834, 8836, 0, $f, 8838, 8839, 0, $f,
		8853, 8853, 0, $f, 8855, 8855, 0, $f, 8869, 8869, 0, $f,
		8901, 8901, 0, $f, 8968, 8971, 0, $f, 9001, 9002, 0, $f,
		9674, 9674, 0, $f, 9824, 9824, 0, $f, 9827, 9827, 0, $f,
		9829, 9830, 0, $f,

		// %HTMLspecial;
		// These ones are excluded to enable HTML: 34, 38, 60, 62
		// but we enable 38, 60, 62 when displaying in textarea (see below)
		338,  339, 0, $f,  352,  353, 0, $f,  376,  376, 0, $f,
		710,  710, 0, $f,  732,  732, 0, $f, 8194, 8195, 0, $f,
		8201, 8201, 0, $f, 8204, 8207, 0, $f, 8211, 8212, 0, $f,
		8216, 8218, 0, $f, 8218, 8218, 0, $f, 8220, 8222, 0, $f,
		8224, 8225, 0, $f, 8240, 8240, 0, $f, 8249, 8250, 0, $f,
		8364, 8364, 0, $f,

		// basic foreign chars

		// other symbols
		191, 191, 0, $f
		);

	if ($out == '1') 
	{
		$insert = array(38, 38, 0, $f, 60, 60, 0, $f, 62, 62, 0, $f);
		return $convmap = array_merge($insert,$convmap);
	} 
	else 
	{
		return $convmap;
	}
}


/**
 * Romanize a non-latin string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8Romanize($string)
{
	if (utf8_isASCII($string)) return $string; //nothing to do

	$romanize = romanizeFile(NULL);

 	return strtr($string,$romanize);
}


/**
 * Checks if a string contains 7bit ASCII only
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8_isASCII($str)
{
	for ($i=0; $i<strlen($str); $i++) 
	{
		if (ord($str{$i}) >127) return false;
	}

	return true;
}

	
/**
 * Replace accented UTF-8 characters by unaccented ASCII-7 equivalents
 *
 * Use the optional parameter to just deaccent lower ($case = -1) or upper ($case = 1)
 * letters. Default is to deaccent both cases ($case = 0)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8Deaccent($string, $case=0)
{
	$accents = accentsFile();

	if ($case <= 0) 
	{
		$string = str_replace(
			array_keys($accents['lower']),
			array_values($accents['lower']),
			$string);
	}

	if ($case >= 0)
	{
		$string = str_replace(
			array_keys($accents['upper']),
			array_values($accents['upper']),
			$string);
	}

	return $string;
}


function accentsFile()
{
	$UTF8['lower'] = array(
	  'à' => 'a', 'ô' => 'o', 'ď' => 'd', 'ḟ' => 'f', 'ë' => 'e', 'š' => 's', 'ơ' => 'o', 
	  'ß' => 'ss', 'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a', 'ķ' => 'k', 
	  'ŝ' => 's', 'ỳ' => 'y', 'ņ' => 'n', 'ĺ' => 'l', 'ħ' => 'h', 'ṗ' => 'p', 'ó' => 'o', 
	  'ú' => 'u', 'ě' => 'e', 'é' => 'e', 'ç' => 'c', 'ẁ' => 'w', 'ċ' => 'c', 'õ' => 'o', 
	  'ṡ' => 's', 'ø' => 'o', 'ģ' => 'g', 'ŧ' => 't', 'ș' => 's', 'ė' => 'e', 'ĉ' => 'c', 
	  'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c', 'ę' => 'e', 'ŵ' => 'w', 'ṫ' => 't', 
	  'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e', 'ŷ' => 'y', 'ą' => 'a', 'ł' => 'l', 
	  'ų' => 'u', 'ů' => 'u', 'ş' => 's', 'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z', 
	  'ẃ' => 'w', 'ḃ' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', 'ḋ' => 'd', 'ť' => 't', 
	  'ŗ' => 'r', 'ä' => 'ae', 'í' => 'i', 'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o', 
	  'ē' => 'e', 'ñ' => 'n', 'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j', 
	  'ÿ' => 'y', 'ũ' => 'u', 'ŭ' => 'u', 'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o', 
	  'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z', 'ī' => 'i', 'ã' => 'a', 'ġ' => 'g', 
	  'ṁ' => 'm', 'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i', 'ź' => 'z', 'á' => 'a', 
	  'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u'
	);

	$UTF8['upper'] = array(
	  'À' => 'A', 'Ô' => 'O', 'Ď' => 'D', 'Ḟ' => 'F', 'Ë' => 'E', 'Š' => 'S', 'Ơ' => 'O', 
	  'Ă' => 'A', 'Ř' => 'R', 'Ț' => 'T', 'Ň' => 'N', 'Ā' => 'A', 'Ķ' => 'K', 
	  'Ŝ' => 'S', 'Ỳ' => 'Y', 'Ņ' => 'N', 'Ĺ' => 'L', 'Ħ' => 'H', 'Ṗ' => 'P', 'Ó' => 'O', 
	  'Ú' => 'U', 'Ě' => 'E', 'É' => 'E', 'Ç' => 'C', 'Ẁ' => 'W', 'Ċ' => 'C', 'Õ' => 'O', 
	  'Ṡ' => 'S', 'Ø' => 'O', 'Ģ' => 'G', 'Ŧ' => 'T', 'Ș' => 'S', 'Ė' => 'E', 'Ĉ' => 'C', 
	  'Ś' => 'S', 'Î' => 'I', 'Ű' => 'U', 'Ć' => 'C', 'Ę' => 'E', 'Ŵ' => 'W', 'Ṫ' => 'T', 
	  'Ū' => 'U', 'Č' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Ŷ' => 'Y', 'Ą' => 'A', 'Ł' => 'L', 
	  'Ų' => 'U', 'Ů' => 'U', 'Ş' => 'S', 'Ğ' => 'G', 'Ļ' => 'L', 'Ƒ' => 'F', 'Ž' => 'Z', 
	  'Ẃ' => 'W', 'Ḃ' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', 'Ḋ' => 'D', 'Ť' => 'T', 
	  'Ŗ' => 'R', 'Ä' => 'Ae', 'Í' => 'I', 'Ŕ' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O', 
	  'Ē' => 'E', 'Ñ' => 'N', 'Ń' => 'N', 'Ĥ' => 'H', 'Ĝ' => 'G', 'Đ' => 'D', 'Ĵ' => 'J', 
	  'Ÿ' => 'Y', 'Ũ' => 'U', 'Ŭ' => 'U', 'Ư' => 'U', 'Ţ' => 'T', 'Ý' => 'Y', 'Ő' => 'O', 
	  'Â' => 'A', 'Ľ' => 'L', 'Ẅ' => 'W', 'Ż' => 'Z', 'Ī' => 'I', 'Ã' => 'A', 'Ġ' => 'G', 
	  'Ṁ' => 'M', 'Ō' => 'O', 'Ĩ' => 'I', 'Ù' => 'U', 'Į' => 'I', 'Ź' => 'Z', 'Á' => 'A', 
	  'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae'
	);

	return $UTF8;
}