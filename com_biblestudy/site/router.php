<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
defined('_JEXEC') or die;

/**
 * BibleStudy Build Route
 *
 * @param   array  &$query  Info to Query
 *
 * @return string
 *
 * @since 7.0
 */
function biblestudyBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['view']))
	{
		if ($query['view'] == 'mediafile')
		{
			return $segments;
		}

		if ($query['view'] == 'comment')
		{
			return $segments;
		}

		if ($query['view'] == 'comments')
		{
			return $segments;
		}

		$segments[] = $query['view'];
		unset($query['view']);
	}

	if (isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	}

	/*if (isset($query['mid']))
	{
		$segments[] = $query['mid'];
		unset($query['mid']);
	}*/

	if (isset($query['t']))
	{
		$segments[] = $query['t'];
		unset($query['t']);
	}

	return $segments;
}

/**
 * BibleStudy Parse Route
 *
 * @param   array  $segments  Parse Route Info
 *
 * @return array
 *
 * @since 7.0
 */
function biblestudyParseRoute($segments)
{
	$vars = array();

	// Count route segments
	$count = count($segments);

	if ($count == 3)
	{
		$vars['view'] = $segments[0];
		$vars['id']   = (int) $segments[$count - 2];
		$vars['t']    = $segments[$count - 1];

		return $vars;
	}
	elseif ($count == 2)
	{
		$vars['view'] = $segments[0];
		$vars['t']    = $segments[$count - 1];

		return $vars;
	}
	else
	{
		$vars['view'] = $segments[0];

		return $vars;
	}
}
