<?php


class OptionsBuilder
{
	public $options	= array();
	public $build 		= array();
	public $key;
	public $html;
	public $plugin		= array();
	
	public function builder($options = array(), $build = array(), $key)
	{
		$this->options = $options;
		$this->build = $build; // we may need to deal with unset variables below
		$this->key = $key;
		
		// what kind are we?
		$kind = 'build_' . $this->build[0];
		
		$this->$kind();
		
		return $this->html;
	}
	
	public function build_text()
	{
		$OBJ =& get_instance();
		
		$this->html = ips($OBJ->lang->word($this->build[2]), 'input', 
			"option[" . $this->build[3] . "]", $this->options[$this->build[3]], null, 'text');
	}
	
	// we should make this automatic and then grab the parts...
	public function build_callback()
	{
		if (file_exists(DIRNAME . '/ndxzsite/plugin/' . $this->plugin['pl_file']))
		{
			require_once(DIRNAME . '/ndxzsite/plugin/' . $this->plugin['pl_file']);
			
			// the need to be classes - the class is implied here, no reason to specity
			$arr = explode(':', $this->plugin['pl_function']);
			
			$TMP = new $arr[0];
			//echo $TMP->picker(); exit;
			
			if (method_exists($TMP, $this->build[4]))
			{
				// not pretty - review later
				$M = $this->build[4];
				
				$this->html = call_user_func(array($TMP, $M), array($this->options, null));
				
				// need call_user_func_array();
				//$this->html = $TMP->$M();
			}
			/*
			if (function_exists($this->build[4]))
			{
				$f = $this->build[4];
				
				// passing $this - has most everything we need
				$this->html = call_user_func($f, &$this);
			}
			*/
		}
	}
	
	public function build_list()
	{
		$OBJ =& get_instance();
		
		if (!is_array($this->build[4])) return;
		
		$this->html = "<label>" . $OBJ->lang->word($this->build[2]) . "</label>\n";
		$this->html .= "<select name='option[" . $this->build[3] . "]'>\n";
		
		foreach ($this->build[4] as $key => $do)
		{
			$this->html .= option($key, $do, $key, $this->options[$this->build[3]]);
		}
		
		$this->html .= "</select>\n";
	}
	
	public function build_password()
	{
		$OBJ =& get_instance();
		
		$this->html = ips($OBJ->lang->word($this->build[2]), 'input', 
			"option[" . $this->build[3] . "]", $this->options[$this->build[3]], null, 'password');
	}
	
	public function build_()
	{
		// empty, something went wrong
	}
}