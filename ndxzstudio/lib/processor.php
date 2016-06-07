<?php if (!defined('SITE')) exit('No direct script access allowed');


/**
* Validation class
* 
* @version 1.0
* @author Vaska 
*/
class Processor 
{	
	public $error = array();
	public $variable = "";
	public $descriptor = "";
	public $tests = "";
	public $flag = false;
	public $required;
	public $rs;
	public $extra = false;

	
	/**
	* Returns validated string
	*
	* @param string $descrip
	* @param array $test
	* @param string $extra
	* @return string
	*/
	public function process($descrip, $tests, $extra='')
	{ 
		$this->descriptor = $descrip;
	
		if (isset($extra)) $this->extra = $extra;
		
		if (!isset($_POST[$this->descriptor])) 
		{
			$this->variable = NULL;
		} 
		else 
		{
			$this->variable = $_POST[$this->descriptor];
		}
		
		foreach ($tests as $test) 
		{
			$this->variable = $this->$test();
		}
			
		return $this->variable;
	}
	
	
	/**
	* Returns boolean
	*
	* @param void
	* @return boolean
	*/
	public function check_errors()
	{
		return $this->flag;
	} 

	/**
	* Returns array of errors
	*
	* @param void
	* @return array
	*/
	public function get_errors()
	{
		return $this->error;
	}

	/**
	* Return force error boolean
	*
	* @param void
	* @return boolean
	*/
	// a way to make sure we return errors
	public function force_error()
	{
		$this->error['error'] = TRUE;
	}


	/**
	* Return string
	* (checks string lenght)
	*
	* @param void
	* @return string
	*/
	public function reqNotEmpty()
	{
		if (strlen($this->variable) == "0") 
		{
			$this->error[$this->descriptor] = " (Required)";
			$this->flag[$this->descriptor] = true;
			return '';
		} 
		else 
		{
			return $this->variable;	
		}
	}
	

	/**
	* Return string
	* (using strip tags function)
	*
	* @param void
	* @return string
	*/
	public function notags()
	{
		if ($this->variable) 
		{
			$out = strip_tags($this->variable);
			return $out;
		} 
		else 
		{
			return NULL;
		}
	}
	
	
	/**
	* Returns string
	* (strips php tags)
	*
	* @param void
	* @return string
	*/
	public function nophp()
	{
		if ($this->variable) 
		{
			$out = preg_replace('|<\?[php]?(.)*\?>|sUi', '', $this->variable);
			return $out;
		} 
		else 
		{
			return NULL;
		}
	}
	
	
	/**
	* Returns boolean
	* (force boolean)
	*
	* @param void
	* @return boolean
	*/
	public function boolean()
	{
		if ($this->variable == 1) 
		{
			return 1;
		} 
		else 
		{
			return 0;
		}
	}
	
	
	/**
	* Return string
	* (letters only, no spaces)
	*
	* @param void
	* @return string
	*/
	public function alpha()
	{
		if ($this->variable) 
		{
			$out = preg_replace('/[^a-z0-9-]/i', '', $this->variable);
			return $out;
		} 
		else 
		{
			return NULL;
		}
	}
	
	
	/**
	* Returns string
	* (numbers only, no spaces)
	*
	* @param void
	* @return string
	*/
	public function digit()
	{
		if ($this->variable) 
		{
			return ((int) $this->variable) ? (int) $this->variable : NULL;
		} 
		else 
		{
			return NULL;
		}
	}
	
	
	/**
	* Returns string
	* (this should not be used for titles as it allows characters)
	*
	* @param void
	* @return string
	*/
	public function alphanum()
	{
		if ($this->variable) 
		{
			$out = preg_replace('/[^[:alnum:]|[:blank:]]/', '', $this->variable);
			return $out;
		} 
		else 
		{
			return NULL;
		}
	}
	
	
	/**
	* Returns string
	* (specific to login/passwords)
	*
	* @param void
	* @return string
	*/
	public function length12()
	{
		if ((strlen($this->variable) > 12) && (strlen($this->variable) < 6))
		{
			$this->error[$this->descriptor] = " (Too many characters)";
			$this->flag[$this->descriptor] = true;
			return '';
		} 
		else 
		{
			return $this->variable;	
		}
	}
	
	
	/**
	* Return false on error
	* (letters/numbers only, no spaces)
	*
	* @param void
	* @return mixed
	*/
	public function pchars()
	{
		// FIX LATER
		$OBJ =& get_instance();
		
		if ($this->variable) 
		{
			if (preg_match('/^[a-zA-Z0-9]+$/', $this->variable)) 
			{
				return $this->variable;
			}
			else
			{
				$this->force_error();
				// FIX LATER
				$OBJ->template->action_error = 'invalid input';
				
				$this->error[$this->descriptor] = " (Invalid Input)";
				$this->flag[$this->descriptor] = true;
				return NULL;
			}
		} 
		else 
		{
			return NULL;
		}
	}
	
	
	/**
	* Return string
	* Tries to prevent xss attacks
	*
	* @param void
	* @return mixed
	*/
	public function xss() 
	{ 
		if ($this->variable) 
		{
			$val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $this->variable); 

			$search = 'abcdefghijklmnopqrstuvwxyz'; 
			$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
			$search .= '1234567890!@#$%^&*()'; 
			$search .= '~`";:?+/={}[]-_|\'\\';
			
			for ($i = 0; $i < strlen($search); $i++) 
			{ 
				$val = preg_replace('/(&#[x|X]0{0,8}' . dechex(ord($search[$i])).';?)/i', $search[$i], $val); 	
				$val = preg_replace('/(&#0{0,8}' . ord($search[$i]).';?)/', $search[$i], $val); 
			} 

	   		$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'); 
	   		$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	
	   		$ra = array_merge($ra1, $ra2); 

			$found = true;
			
			while ($found == true) 
			{ 
				$val_before = $val; 
				for ($i = 0; $i < sizeof($ra); $i++) 
				{ 
					$pattern = '/'; 
					for ($j = 0; $j < strlen($ra[$i]); $j++) 
					{ 
						if ($j > 0) 
						{ 
							$pattern .= '('; 
							$pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?'; 
							$pattern .= '|(&#0{0,8}([9][10][13]);?)?'; 
							$pattern .= ')?'; 
						}
						
						$pattern .= $ra[$i][$j]; 
					}
					
					$pattern .= '/i'; 
					$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
					//$replacement = '';
					$val = preg_replace($pattern, $replacement, $val);
					
					if ($val_before == $val) 
					{ 
						$found = false; 
					} 
				} 
			}
			
			return $val;
		}
		else
		{
			return null;
		}
	}
}


?>
