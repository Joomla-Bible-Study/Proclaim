<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Service;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
	 * Maps Proclaim Views 9.x to Proclaim Views 10x.
	 *
	 * @const array
	 * @since 5.0.0
	 */
	private const VIEW_CASE_MAP = [
		'cwmsermons'        => 'CWMSermons',
		'cwmsermon'         => 'CWMSermon',
		'cwmteachers'       => 'CWMTeachers',
		'cwmteacher'        => 'CWMTeacher',
		'cwmseriesdisplay'  => 'CWMSeriesDisplay',
		'cwmseriesdisplays' => 'CWMSeriesDisplays',
		'cwmcommentform'    => 'CWMCommentForm',
		'cwmcommentlist'    => 'CWMCommentList',
		'cwmlandingpage'    => 'CWMLandingPage',
		'cwmlatest'         => 'CWMLatest',
		'cwmmediafileform'  => 'CWMMediaFileForm',
		'cwmmediafilelist'  => 'CWMMediaFileList',
		'cwmmessageform'    => 'CWMMessageForm',
		'cwmmessagelist'    => 'CWMMessageList',
		'cwmpodcastdisplay' => 'CWMPodcastDisplay',
		'cwmpopup'          => 'CWMPopUp',
		'cwmsqueezebox'     => 'CWMSqueezebox',
		'cwmterms'          => 'CWMTerms',
	];

	/**
	 * Router this rule belongs to
	 *
	 * @var RouterView
	 * @since 3.4
	 */
	protected RouterView $router;

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
	 * Dummy method to fulfil the interface requirements
	 *
	 * @param   array  $query  The query array to process
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function preprocess(&$query): void
	{
	}

	/**
	 * Parse a menu-less URL
	 *
	 * @param   array  $segments  The URL segments to parse
	 * @param   array  $vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function parse(&$segments = array(), &$vars = array()): void
	{
		// Count route segments
		$count = (int) count($segments);

		if ($count === 6)
		{
			$vars['sendingview'] = $segments[1];
			$vars['view']        = $segments[2];
			$id                  = explode('/', $segments[3]);
			$vars['id']          = (int) $id[0];
			$vars['t']           = $segments[$count - 2];
			$vars['Itemid']      = $segments[$count - 1];
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);

			return;
		}

		if ($count === 5)
		{
			$vars['view']   = $segments[1];
			$id             = explode('/', $segments[2]);
			$vars['id']     = (int) $id[0];
			$vars['t']      = $segments[$count - 2];
			$vars['Itemid'] = $segments[$count - 1];
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);

			return;
		}

		if ($count === 4)
		{
			$vars['view']   = $segments[1];
			$id             = explode('/', $segments[2]);
			$vars['id']     = (int) $id[0];
			$vars['t']      = $segments[$count - 2];
			$vars['Itemid'] = $segments[$count - 1];
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);

			return;
		}

		if ($count === 3)
		{
			$vars['view'] = $segments[0];
			$id           = explode('/', $segments[1]);
			$vars['id']   = (int) $id[0];
			$vars['t']    = $segments[$count - 1];
			array_shift($segments);
			array_shift($segments);
			array_shift($segments);

			return;
		}

		if ($count === 2)
		{
			if ($segments[1] === 'cwmpodcastdisplay')
			{
				$vars['view'] = $segments[1];
				$id           = explode('/', $segments[2]);
				$vars['id']   = (int) $id[0];
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

		$vars['view'] = $this->translateOldViewName($vars['view']);
		$vars['id']   = substr($segments[0], strpos($segments[0], '-') + 1);
		$vars['t']    = substr($segments[0], strpos($segments[0], '-') + 1);
		array_shift($segments);
		array_shift($segments);
	}

	/**
	 * Build a menu-less URL
	 *
	 * @param   array  $query     The vars that should be converted
	 * @param   array  $segments  The URL segments to create
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function build(&$query, &$segments): array
	{
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
			// Remove do we are not using it uet.
			unset($query['Itemid']);
		}

		foreach ($segments as $i => $iValue)
		{
			$segments[$i] = str_replace(':', '-', $iValue);
		}

		return $segments;
	}

	/**
	 * Translates view names from older versions of the component to the ones currently in use.
	 *
	 * @param   string  $oldViewName  Old view name
	 *
	 * @return  string
	 * @since   5.0.0
	 */
	private function translateOldViewName(string $oldViewName): string
	{
		$oldViewName = strtolower($oldViewName);

		return self::VIEW_CASE_MAP[$oldViewName] ?? $oldViewName;
	}
}
