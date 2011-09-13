<?php defined('_JEXEC') or die ('Restricted Access');


function biblestudyBuildRoute(&$query)
{
	$segments = array();

	if(isset($query['view'])) 
	{
		if(empty($query['Itemid'])) {
			$segments[] = $query['view'];
		}
		else { 
		$segments[] = $query['view'];
		}
		unset($query['view']);
	};
	
	if(isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	};
	
	
	return $segments;
}

function biblestudyParseRoute($segments)
{
	$vars = array();

	//Get the active menu item
	$menu =& JSite::getMenu();
	$item =& $menu->getActive();

	// Count route segments
	//$count = count($segments);
	
	//Standard routing for articles
	if(!isset($item)) 
	{
		$vars['view'] = $segments[0];
		
		$vars['id'] = $segments[1];
		
		

		return $vars;
	}

	//Handle View and Identifier
	switch($item->query['view'])
	{
		case 'studydetails'   :
		{
			
			
			$vars['id']  = $segments[1];
			$vars['view'] = 'studydetails';

		} break;
		case 'studieslist' :
		{
			
			$vars['id']   = $segments[1];
			$vars['view'] = 'studieslist';
		} break;
	}

	return $vars;
}