<?php if (!defined('SITE')) exit('No direct script access allowed');

/**
* Abstracts class
* 
* @version 1.0
* @author Vaska 
*/
class Abstracts
{
	public $abstract = false;
	public $temp;
	public $input = array();
	
	public function __construct()
	{
		
	}
	
	/**
	* Returns loaded abstracts object or false
	* Only for exhibits/pages - images will be separate
	*
	* @param void
	* @return array
	*/
	// REVIEW THIS LATER!!!!!!
	public function front_abstracts()
	{
		$OBJ =& get_instance();

		$this->temp = $OBJ->db->fetchArray("SELECT * FROM ".PX."abstracts 
			WHERE (ab_obj_id = '" . $OBJ->vars->exhibit['id'] . "' AND ab_obj = '" . $OBJ->vars->exhibit['object'] . "') 
			OR ab_obj = 'system'");
		
		if ($this->temp) $this->abstract_variables_production();
	}
	
	// get_exhibit_abstracts ???
	// REVIEW THIS LATER!!!!!!
	public function get_system_abstracts($id=0)
	{
		$OBJ =& get_instance();
		
		// if a = 'collect'...
		if ($OBJ->vars->route['a'] == 'collect')
		{
			$this->temp = $OBJ->db->fetchArray("SELECT * FROM ".PX."abstracts 
				WHERE (ab_obj_id = '" . $OBJ->vars->route['id'] . "' 
				AND ab_obj LIKE 'collect%') 
				OR ab_obj = 'system'");
		}
		else
		{
			$this->temp = $OBJ->db->fetchArray("SELECT * FROM ".PX."abstracts 
				WHERE (ab_obj_id = '" . $OBJ->vars->route['id'] . "' 
				AND ab_obj = '" . $OBJ->vars->route['a'] . "') 
				OR ab_obj = 'system'");
		}
			
		if ($this->temp) $this->abstract_variables_production();
	}
	
	public function abstract_variables_production()
	{
		foreach ($this->temp as $temp)
		{
			$this->abstract[$temp['ab_var']] = $temp['abstract'];
		}
	}
	
	public function abstract_clean()
	{
		$this->input = array();
	}
	
	// create abstract
	public function abstract_check($abstract, $ab_var, $ab_obj, $ab_obj_id)
	{
		$OBJ =& get_instance();
		
		// get the ide
		$check = $OBJ->db->fetchRecord("SELECT ab_id 
			FROM ".PX."abstracts 
			WHERE ab_obj_id = '$ab_obj_id' 
			AND ab_var = '$ab_var'");
		
		if ($check)
		{
			// we want to delete the abstract - or zero?
			// we'll consider 0 a default
			if (($abstract == '') || ($abstract == 0))
			{
				$OBJ->abstracts->abstract_delete($ab_var, $ab_obj, $ab_obj_id, null);
			}
			else
			{
				$this->input['abstract'] = $abstract;
				$this->input['ab_var'] = $ab_var;
				$this->input['ab_obj'] = $ab_obj;
				//if ($ab_obj_id !=  null) $this->input['ab_obj_id'] = $ab_obj_id;
				//if ($ab_id !=  null) $this->input['ab_id'] = $ab_id;

				//if ($debug == true) { print_r($this->input); exit; }

				// need to do this better...really...feeling tired right now...
				$specify = ($ab_obj_id != null) ? "ab_obj_id = '$ab_obj_id' " : '';
				$specify .= ($ab_id != null) ? "ab_id = '$ab_id'" : '';

				if ($specify == null) return;

				$OBJ->db->updateArray(PX.'abstracts', $this->input, "$specify AND ab_var = '$ab_var'");
			}
		}
		else
		{
			$this->input['abstract'] = $abstract;
			$this->input['ab_var'] = $ab_var;
			$this->input['ab_obj'] = $ab_obj;
			$this->input['ab_obj_id'] = $ab_obj_id;

			$OBJ->db->insertArray(PX.'abstracts', $this->input);
		}
		
		// clean out the array
		$this->abstract_clean();
	}
	
	// create abstract
	public function abstract_create($abstract, $ab_var, $ab_obj, $ab_obj_id)
	{
		$OBJ =& get_instance();

		$this->input['abstract'] = $abstract;
		$this->input['ab_var'] = $ab_var;
		$this->input['ab_obj'] = $ab_obj;
		$this->input['ab_obj_id'] = $ab_obj_id;

		$OBJ->db->insertArray(PX.'abstracts', $this->input);
		
		// clean out the array
		$this->abstract_clean();
	}
	
	
	// update abstract
	public function abstract_update($abstract, $ab_var, $ab_obj, $ab_obj_id = null, $ab_id = null, $debug=false)
	{
		$OBJ =& get_instance();

		$this->input['abstract'] = $abstract;
		$this->input['ab_var'] = $ab_var;
		$this->input['ab_obj'] = $ab_obj;
		//if ($ab_obj_id !=  null) $this->input['ab_obj_id'] = $ab_obj_id;
		//if ($ab_id !=  null) $this->input['ab_id'] = $ab_id;
		
		if ($debug == true) { print_r($this->input); exit; }
		
		// need to do this better...really...feeling tired right now...
		$specify = ($ab_obj_id != null) ? "ab_obj_id = '$ab_obj_id' " : '';
		$specify .= ($ab_id != null) ? "ab_id = '$ab_id'" : '';
		
		if ($specify == null) return;

		$OBJ->db->updateArray(PX.'abstracts', $this->input, "$specify AND ab_var = '$ab_var'");
		
		// clean out the array
		$this->abstract_clean();
	}
	
	
	// delete abstract
	public function abstract_delete($ab_var, $ab_obj, $ab_obj_id = null, $ab_id = null)
	{
		$OBJ =& get_instance();
		
		// need to do this better...really...feeling tired right now...
		$specify = ($ab_obj_id != null) ? "ab_obj_id = '$ab_obj_id'" : '';
		$specify .= ($ab_id != null) ? "ab_id = '$ab_id'" : '';
		
		if ($specify == null) return;

		$OBJ->db->deleteArray(PX.'abstracts', "ab_var = '$ab_var' AND $specify AND ab_obj = '$ab_obj'");
		
		// clean out the array
		$this->abstract_clean();
	}
	
	
	// deletes all abstracts for a particular id and object
	public function abstract_delete_all($ab_obj, $ab_obj_id)
	{
		$OBJ =& get_instance();

		$OBJ->db->deleteArray(PX.'abstracts', "ab_obj_id = '$ab_obj_id' AND ab_obj = '$ab_obj'");
		
		// clean out the array
		$this->abstract_clean();
	}
}