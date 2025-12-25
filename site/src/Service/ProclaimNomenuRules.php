<?php

/**
 * @package        Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
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
        if (empty($segments)) {
            return;
        }

        $views = $this->router->getViews();

        if (isset($views[$segments[0]])) {
            $viewName = array_shift($segments);
            $view     = $views[$viewName];

            // Check if this is a parent view and the next segment could be a child item
            if (!empty($segments[0]) && !$view->key) {
                $childView = $this->findChildViewForSegment($views, $viewName, $segments[0]);

                if ($childView) {
                    $vars['view'] = $childView->name;
                    $segment      = array_shift($segments);

                    if (\is_callable([$this->router, 'get' . ucfirst($childView->name) . 'Id'])) {
                        $result = \call_user_func(
                            [$this->router, 'get' . ucfirst($childView->name) . 'Id'],
                            $segment,
                            $vars
                        );
                        $vars[$childView->key] = preg_replace('/-/', ':', $result, 1);
                    } else {
                        $vars[$childView->key] = preg_replace('/-/', ':', $segment, 1);
                    }

                    return;
                }
            }

            // Standard view processing
            $vars['view'] = $viewName;

            if (isset($view->key) && isset($segments[0])) {
                if (\is_callable([$this->router, 'get' . ucfirst($view->name) . 'Id'])) {
                    $input = $this->router->app->getInput();

                    if ($view->parent_key && $input->get($view->parent_key)) {
                        $vars[$view->parent->key] = $input->get($view->parent_key);
                        $vars[$view->parent_key]  = $input->get($view->parent_key);
                    }

                    if ($view->nestable) {
                        $vars[$view->key] = 0;

                        while ($segments) {
                            $segment = array_shift($segments);
                            $result  = \call_user_func(
                                [$this->router, 'get' . ucfirst($view->name) . 'Id'],
                                $segment,
                                $vars
                            );

                            if (!$result) {
                                array_unshift($segments, $segment);
                                break;
                            }

                            $vars[$view->key] = preg_replace('/-/', ':', $result, 1);
                        }
                    } else {
                        $segment = array_shift($segments);
                        $result  = \call_user_func(
                            [$this->router, 'get' . ucfirst($view->name) . 'Id'],
                            $segment,
                            $vars
                        );

                        $vars[$view->key] = preg_replace('/-/', ':', $result, 1);
                    }
                } else {
                    $vars[$view->key] = preg_replace('/-/', ':', array_shift($segments), 1);
                }
            }
        }
    }

    /**
     * Find a child view that can handle the given segment
     *
     * @param   array   $views       All registered views
     * @param   string  $parentName  The parent view name
     * @param   string  $segment     The URL segment to check
     *
     * @return  object|null  The child view configuration or null
     *
     * @since   10.0.0
     */
    private function findChildViewForSegment(array $views, string $parentName, string $segment): ?object
    {
        foreach ($views as $view) {
            // Check if this view has the specified parent
            if (isset($view->parent) && $view->parent->name === $parentName && $view->key) {
                // Try to resolve the segment using this child view's ID method
                if (\is_callable([$this->router, 'get' . ucfirst($view->name) . 'Id'])) {
                    $result = \call_user_func(
                        [$this->router, 'get' . ucfirst($view->name) . 'Id'],
                        $segment,
                        []
                    );

                    if ($result) {
                        return $view;
                    }
                }
            }
        }

        return null;
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
        if (isset($query['view'])) {
            $views = $this->router->getViews();

            if (isset($views[$query['view']])) {
                $view = $views[$query['view']];

                // Use parent view name if available for cleaner URLs
                // e.g., /cwmsermons/sermon-alias instead of /cwmsermon/sermon-alias
                if (isset($view->parent) && isset($view->parent->name)) {
                    $segments[] = $view->parent->name;
                } else {
                    $segments[] = $query['view'];
                }

                if ($view->key && isset($query[$view->key])) {
                    if (\is_callable([$this->router, 'get' . ucfirst($view->name) . 'Segment'])) {
                        $result = \call_user_func(
                            [$this->router, 'get' . ucfirst($view->name) . 'Segment'],
                            $query[$view->key],
                            $query
                        );

                        if ($view->nestable) {
                            array_pop($result);

                            while ($result) {
                                $segments[] = str_replace(':', '-', array_pop($result));
                            }
                        } else {
                            $segments[] = str_replace(':', '-', array_pop($result));
                        }
                    } else {
                        $segments[] = str_replace(':', '-', $query[$view->key]);
                    }

                    unset($query[$views[$query['view']]->key]);
                }

                unset($query['view']);
            }
        }

        if (isset($query['t'])) {
            // Remove do to we don't want to display it but will use it in a hidden format.
            unset($query['t']);
        }
    }
}
