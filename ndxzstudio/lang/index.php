<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* Language class
*
* When languages are added be sure to update lang_options() accordingly
* 
* @version 1.0
* @author Vaska 
*/
class Lang
{
	public $words 	= array();
	public $locale;
	public $lang;
	public $output;
	
	/**
	* Return array of our langauge
	*
	* @param string $lang
	* @return array
	*/
	public function setlang($lang='')
	{
		$words = '';
		
		$this->lang = ($lang == '') ? LANGUAGE : $lang;
		
		if (file_exists(LANGPATH . '/' . $this->lang . '/index.php'))
			require_once(LANGPATH . '/' . $this->lang . '/index.php');
			
		// let's setlocale too
		$this->getlocale($this->lang);
		
		return $this->words = $words;
	}
	
	
	/**
	* Returns the word
	*
	* @param string $string
	* @return string
	*/
	public function word($string)
	{
		return (isset($this->words[strtolower($string)])) ? $this->words[strtolower($string)] : $string;
	}

	
	
	/**
	* Returns merged array
	*
	* @param array $array
	* @return array
	*/
	public function add_words($array)
	{
		if (is_array($array))
			$this->words = array_merge($this->words, $array);
	}
	
	
	/**
	* Returns array of translated languages
	*
	* @param void
	* @return array
	*/
	public function lang_options()
	{
		return array(
			'en-us' => array('en_US.UTF-8', 'en_US', 'english-us', 'en_US.ISO_8859-1', 'English'),
			'es-es' => array('es_ES.UTF-8', 'es_ES', 'esp', 'spanish', 'es_ES.ISO_8859-1', 'Español'),
			'eu-es' => array('eu_ES.UTF-8', 'eu_ES', 'eus', 'basque', 'eu_ES.ISO_8859-1', 'Euskara'),
			'pt-br' => array('pt_BR.UTF-8', 'pt_BR', 'br', 'portuguese', 'pt_BR.ISO_8859-1', 'Portuguese (Brazilian)'),
			'ca-es' => array('ca_ES.UTF-8', 'ca_ES', 'ca', 'catalan', 'ca_ES.ISO_8859-1', 'Català'),
			'nl-nl' => array('nl_NL.UTF-8', 'nl_NL', 'dut', 'nla', 'nl', 'nld', 'dutch', 'nl_NL.ISO_8859-1', 'Dutch'),
			'fi-fi' => array('fi_FI.UTF-8', 'fi_FI', 'fin', 'fi', 'finnish', 'fi_FI.ISO_8859-1', 'Finnish'),
			'fr-fr' => array('fr_FR.UTF-8', 'fr_FR', 'fra', 'fre', 'fr', 'french', 'fr_FR.ISO_8859-1', 'Français'),
			'ja-jp' => array('ja_JP.UTF-8', 'ja_JP', 'ja', 'jpn', 'japanese', 'ja_JP.ISO_8859-1', '日本語'),
			'pt-pt' => array('pt_PT.UTF-8', 'pt_PT', 'por', 'portuguese', 'pt_PT.ISO_8859-1', 'Portuguese'),
			'ru-ru' => array('ru_RU.UTF-8', 'ru_RU', 'ru', 'rus', 'russian', 'ru_RU.ISO8859-5', 'Русский'),
			'de-de' => array('de_DE.UTF-8', 'de_DE', 'de', 'deu', 'german', 'de_DE.ISO_8859-1', 'Deutsch'),
			'zh-cn' => array('zh_CN.UTF-8', 'zh_CN', 'zh', '简体中文'),
			'zh-tw' => array('zh_TW.UTF-8', 'zh_TW', 'zh', '繁體中文'),
			'el-gr' => array('el_GR.UTF-8', 'el_GR', 'el', 'gr', 'gr_GR', 'el_GRE', 'gr_GRE', 'greek', 'el_GR.ISO_8859-1','Greek'),
			'it-it' => array('it_IT.UTF-8', 'it_IT', 'it', 'ita', 'italian', 'it_IT.ISO_8859-1', 'Italiano'),
			'cs-cz' => array('cs_CZ.UTF-8', 'cs_CZ', 'cze', 'cs', 'csy', 'ces', 'czech', 'cs_CZ.ISO_8859-2', 'Czech'),
			'se-se' => array('se_SE.UTF-8', 'se_SE', 'se', 'swe', 'swedish', 'se_SE.ISO_8859-1', 'Svenska'),
			'no-no' => array('no_NO.UTF-8', 'no_NO', 'no', 'nor', 'norwegian', 'no_NO.ISO_8859-1', 'Norsk')
		);
	}

	
	
	/**
	* Returns (hopefully) our language
	*
	* @param string $lang
	* @return string
	*/
	public function getlocale($lang='')
	{
		$this->lang = ($lang == '') ? 'en-us' : $lang;

		$select = $this->lang_options();
		
		if (!empty($select[$lang]))
		{
			// delete the last element as it carries the language name
			array_pop($select[$lang]);
			
			$find = @setlocale(LC_TIME, $select[$lang]);
			if ($find !== false) $this->locale = $find;
		}
		
		@setlocale(LC_TIME, $this->locale);

		return $this->locale;
	}
}