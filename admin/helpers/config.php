<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormProConfig
{
	protected $config;
	protected $db;
	
	public function __construct() {
		$this->db = JFactory::getDbo();
		$this->load();
	}
	
	public function get($key, $default=false, $explode=false) {
		if (isset($this->config->$key)) {
			return $explode ? $this->explode($this->config->$key) : $this->config->$key;
		}
		
		return $default;
	}
	
	public function getKeys() {
		return array_keys((array) $this->config);
	}
	
	public function getData() {
		return $this->config;
	}
	
	public function reload() {
		$this->load();
	}
	
	protected function load() {
		// reset the values
		$this->config = new stdClass();
		
		// prepare the query
		$query 	= $this->db->getQuery(true);
		$query->select('*')->from('#__rsform_config');
		$this->db->setQuery($query);
		
		// run the query
		if ($results = $this->db->loadObjectList()) {
			foreach ($results as $result) {
				$this->config->{$result->SettingName} = $result->SettingValue;
			}
		}
	}
	
	protected function explode($string) {
		$string = str_replace(array("\r\n", "\r"), "\n", $string);
		return explode("\n", $string);
	}
	
	protected function implode($string) {
		return implode("\n", $string);
	}
	
	protected function convert($key, &$value) {
		if (is_array($value)) {
			$value = implode("\n", $value);
		}
	}
	
	public function set($key, $value, $updateDb = true) {
		if (isset($this->config->{$key})) {
			// convert values to appropriate type
			$this->convert($key, $value);
			
			// refresh our value
			$this->config->{$key} = $value;

			if ($updateDb)
			{
				// prepare the query
				$query = $this->db->getQuery(true);
				$query->update('#__rsform_config')
					->set($this->db->qn('SettingValue').'='.$this->db->q($value))
					->where($this->db->qn('SettingName').'='.$this->db->q($key));
				$this->db->setQuery($query);

				// run the query
				return $this->db->execute();
			}

			return true;
		}
		
		return false;
	}
	
	public static function getInstance() {
		static $inst;
		if (!$inst) {
			$inst = new RSFormProConfig();
		}
		
		return $inst;
	}
}