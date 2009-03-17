<?php defined('_JEXEC') or die();
class scripture
{
	
	function __construct()
	{
		
	parent::__construct();
	$this->_getScripture();
	
	}
/**
* Retrieves the hello message
*
* @param array $params An object containing the module parameters
* @access public
*/

function getScripture( $params )
	{
		$script = 'Hello, World!';
		return $script;
	}
}
?>