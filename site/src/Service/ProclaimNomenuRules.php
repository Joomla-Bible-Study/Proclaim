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
	public function parse(&$segments, &$vars): void
	{
		$views = $this->router->getViews();

		if (isset($views[$segments[0]]))
		{
			$vars['view'] = array_shift($segments);
			$view         = $views[$vars['view']];

			if (isset($view->key) && isset($segments[0]))
			{
				if (\is_callable([$this->router, 'get' . ucfirst($view->name) . 'Id']))
				{
					$input = $this->router->app->getInput();

					if ($view->parent_key && $input->get($view->parent_key))
					{
						$vars[$view->parent->key] = $input->get($view->parent_key);
						$vars[$view->parent_key]  = $input->get($view->parent_key);
					}

					if ($view->nestable)
					{
						$vars[$view->key] = 0;

						while (count($segments))
						{
							$segment = array_shift($segments);
							$result  = call_user_func([$this->router, 'get' . ucfirst($view->name) . 'Id'], $segment, $vars);

							if (!$result)
							{
								array_unshift($segments, $segment);
								break;
							}

							$vars[$view->key] = preg_replace('/-/', ':', $result, 1);
						}
					}
					else
					{
						$segment = array_shift($segments);
						$result  = call_user_func([$this->router, 'get' . ucfirst($view->name) . 'Id'], $segment, $vars);

						$vars[$view->key] = preg_replace('/-/', ':', $result, 1);
					}
				}
				else
				{
					$vars[$view->key] = preg_replace('/-/', ':', array_shift($segments), 1);
				}
			}
		}
	}

	/**
	 * Build a menu-less URL
	 *
	 * @param   array  $query     The vars that should be converted
	 * @param   array  $segments  The URL segments to create
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function build(&$query, &$segments): void
	{
		if (isset($query['view']))
		{
			$views = $this->router->getViews();

			if (isset($views[$query['view']]))
			{
				$view       = $views[$query['view']];
				$segments[] = $query['view'];

				if ($view->key && isset($query[$view->key]))
				{
					if (\is_callable([$this->router, 'get' . ucfirst($view->name) . 'Segment']))
					{
						$result = \call_user_func_array([$this->router, 'get' . ucfirst($view->name) . 'Segment'], [$query[$view->key], $query]);

						if ($view->nestable)
						{
							array_pop($result);

							while (count($result))
							{
								$segments[] = str_replace(':', '-', array_pop($result));
							}
						}
						else
						{
							$segments[] = str_replace(':', '-', array_pop($result));
						}
					}
					else
					{
						$segments[] = str_replace(':', '-', $query[$view->key]);
					}

					unset($query[$views[$query['view']]->key]);
				}

				unset($query['view']);
			}
		}

		if (isset($query['t']))
		{
			// Remove do to we don't want to display it but will use it in a hidden format.
			unset($query['t']);
		}
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
