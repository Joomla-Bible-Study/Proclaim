<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Service;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\RulesInterface;

/**
 * Rule to process URLs without a menu item
 *
 * @since  3.4
 */
class ProclaimNomenuRules implements RulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var RouterView
	 * @since 3.4
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param   RouterView  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(RouterView $router)
	{
		$this->router = $router;
	}

	/**
	 * Dummymethod to fullfill the interface requirements
	 *
	 * @param   array  &$query  The query array to process
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @codeCoverageIgnore
	 */
	public function preprocess(&$query)
	{
		$test = 'Test';
	}


	/**
	 * Parse a menu-less URL
	 *
	 * @param   array  &$segments  The URL segments to parse
	 * @param   array  &$vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function parse(&$segments, &$vars)
	{

        $vars = array();
		// Count route segments
		$count = count($segments);

        if ($count == 6) {
            //$vars['option'] = $segments[0];
            $vars['sendingview'] = $segments[1];
            $vars['view'] = $segments[2];
            $id = explode('/', $segments[3]);
            $vars['id'] = (int)$id[0];
            $vars['t'] = $segments[$count - 2];
            $vars['Itemid'] = $segments[$count - 1];
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);

            return;
        }
        if ($count == 5) {
            //$vars['option'] = $segments[0];
            $vars['view'] = $segments[1];
            $id = explode('/', $segments[2]);
            $vars['id'] = (int)$id[0];
            $vars['t'] = $segments[$count - 2];
            $vars['Itemid'] = $segments[$count - 1];
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);

            return;
        }
        if ($count == 4)
        {
            $vars['view'] = $segments[1];
            //$vars['id']   = (int) $segments[$count - 2];
            $id = explode('/', $segments[2]);
            $vars['id'] = (int) $id[0];
            $vars['t']    = $segments[$count - 2];
            $vars['Itemid'] = $segments[$count -1];
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);

            return;
        }
		if ($count == 3)
		{
			$vars['view'] = $segments[0];
			//$vars['id']   = (int) $segments[$count - 2];
		    $id = explode('/', $segments[1]);
            $vars['id'] = (int) $id[0];
			$vars['t']    = $segments[$count - 1];
            array_shift($segments);
            array_shift($segments);
            array_shift($segments);

			return;
		}
		if ($count == 2)
		{
			if ($segments[1] === 'CWMPodcastdisplay')
			{
				$vars['view'] = $segments[1];
				//$vars['id']   = (int) $segments[1];
		        $id = explode('/', $segments[2]);
				$vars['id'] = (int) $id[0];
			}
			else
			{
				$vars['view'] = $segments[0];
				$vars['t']    = $segments[$count - 1];
			}
            array_shift($segments);
            array_shift($segments);

			return;
		}
		else
		{
			$vars['view'] = $segments[0];
            array_shift($segments);
		}

		return;

		/*
		//with this url: http://localhost/j4x/my-walks/mywalk-n/walk-title.html
		// segments: [[0] => mywalk-n, [1] => walk-title]
		// vars: [[option] => com_mywalks, [view] => mywalks, [id] => 0]

		$vars['view'] = 'CWMSermons';
		$vars['id'] = substr($segments[0], strpos($segments[0], '-') + 1);
		$vars['t'] = substr($segments[0], strpos($segments[0], '-') + 1);
		array_shift($segments);
		array_shift($segments);
		return; */
	}

	/**
	 * Build a menu-less URL
	 *
	 * @param   array  &$query     The vars that should be converted
	 * @param   array  &$segments  The URL segments to create
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function build(&$query, &$segments)
	{
		// content of $query ($segments is empty or [[0] => mywalk-3])
		// when called by the menu: [[option] => com_mywalks, [Itemid] => 126]
		// when called by the component: [[option] => com_mywalks, [view] => mywalk, [id] => 1, [Itemid] => 126]
		// when called from a module: [[option] => com_mywalks, [view] => mywalks, [format] => html, [Itemid] => 126]
		// when called from breadcrumbs: [[option] => com_mywalks, [view] => mywalks, [Itemid] => 126]

		// the url should look like this: /site-root/mywalks/walk-n/walk-title.html

		// if the view is not mywalk - the single walk view
		/*if (!isset($query['view']) || (isset($query['view']) && $query['view'] !== 'CWMSermon') || isset($query['format']))
		{
			return;
		}
		$segments[] = $query['view'] . '-' . $query['id'] . '-' . $query['t'];
		// the last part of the url may be missing
		if (isset($query['slug'])) {
			$segments[] = $query['slug'];
			unset($query['slug']);
		}
		unset($query['view']);
		unset($query['id']);
		unset($query['t']);
*/
		//$segments = array();

		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}
		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}
		if (isset($query['t']))
		{
			$segments[] = $query['t'];
			unset($query['t']);
		}
        if (isset($query['Itemid']))
        {
            //$segments[] = $query['Itemid'];
            unset($query['Itemid']);
        }
        $total = \count($segments);

        for ($i = 0; $i < $total; $i++)
        {
            $segments[$i] = str_replace(':', '-', $segments[$i]);
        }
		return $segments;
	}
}