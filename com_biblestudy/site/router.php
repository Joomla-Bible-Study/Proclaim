<?php defined('_JEXEC') or die ('Restricted Access');


function biblestudyBuildRoute(&$query)
{
	$segments = array();

        // get a menu item based on Itemid or currently active
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();
	$params		= JComponentHelper::getParams('com_biblestudy');
	$advanced	= $params->get('sef_advanced_link', 0);

    
//print_r($query);

	if(isset($query['view']))
	{
	
		$segments[] = $query['view'];
		unset($query['view']);
	}

	if(isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	}

    if (isset($query['t']))
    {
        $segments[] = $query['t'];
        unset($query['t']);
    }
//print_r ($segments);
	return $segments;
}

function biblestudyParseRoute($segments)
{
	$vars = array();
//print_r($segments);
        //Get the active menu item.
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();
	$item	= $menu->getActive();
	$params = JComponentHelper::getParams('com_biblestudy');
	$advanced = $params->get('sef_advanced_link', 0);


	// Count route segments
	$count = count($segments);
    
    
    
    if ($count == 3)
    {
        $vars['view']	= $segments[0];
		$vars['id']	= (int)$segments[$count - 2];
        $vars['t'] = $segments[$count -1];
        return $vars;
    }
    elseif ($count == 2)
    {
        $vars['view']	= $segments[0];
        $vars['t'] = $segments[$count -1];
        return $vars;
    }
    else
    {
        $vars['view']	= $segments[0];
        return $vars;
    }

}