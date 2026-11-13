<?php

/**
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Component Controller
 *
 * @since  1.5
 */
class DisplayController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * @param   array                 $config   An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'default_task', 'model_path', and
     *                                          'view_path' (this list is not meant to be comprehensive).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    The Input object for the request
     *
     * @throws \Exception
     * @since   3.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // Get input early to check view before parent construction.
        $appInput = $input ?? Factory::getApplication()->getInput();
        $view     = $appInput->get('view');

        if (($view === 'cwmlandingpage' || $view === 'cwmsermons') && $appInput->get('layout') === 'modal') {
            $config['base_path'] = JPATH_ADMINISTRATOR . '/components';
        }

        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Method to display a view.
     *
     * @param   bool   $cachable   If true, the view output will be cached
     * @param   array  $urlparams  An array of safe URL parameters and their variable types.
     *
     * @return  static  This object to support chaining.
     *
     * @throws \Exception
     * @see    \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @since   3.0
     */
    public function display($cachable = true, $urlparams = []): static
    {
        /*
        Set the default view name and format from the Request.
        Note, we are using a_id to avoid collisions with the router and the return page.
        Frontend is a bit messier than the backend.
        */
        $input = $this->input;
        $id    = $input->getInt('a_id');
        $vName = $input->getCmd('view', 'cwmlandingpage');
        $input->set('view', $vName);

        $user = $this->app->getIdentity();

        if (
            $vName === 'cwmpopup'
            || $user->id
            || ($input->getMethod() === 'POST'
                && str_contains($vName, 'form'))
        ) {
            $cachable = false;
        }

        // Disable Joomla view caching when filters are active (e.g. landing page links)
        if ($cachable && $vName === 'cwmsermons') {
            $filterParams = ['sendingview', 'filter_series', 'filter_teacher', 'filter_book',
                'filter_topic', 'filter_year', 'filter_messagetype', 'filter_location'];

            foreach ($filterParams as $param) {
                if ($input->getString($param, '')) {
                    $cachable = false;
                    break;
                }
            }
        }

        // Attempt to change MySQL for an error in a large select
        $t = $input->get('t', '1', 'int');

        $input->set('t', $t);

        $safeurlparams = [
            'id'               => 'INT',
            'year'             => 'INT',
            'month'            => 'INT',
            'limit'            => 'INT',
            'limitstart'       => 'INT',
            'showall'          => 'INT',
            'return'           => 'BASE64',
            'filter'           => 'STRING',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'filter-search'    => 'STRING',
            'print'            => 'BOOLEAN',
            'lang'             => 'CMD',
            'Itemid'           => 'INT',
        ];

        // Check for an edit form.
        if ($vName === 'form' && !$this->checkEditId('com_proclaim.edit.message', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
        }

        parent::display($cachable, $safeurlparams);

        // A not-found page sets its 404 inside the view, which a view-cache hit
        // never runs, so every later request would get the stored copy as a 200.
        if ($cachable && $this->isNotFoundResponse()) {
            $this->forgetCachedView($vName);
        }

        return $this;
    }

    /**
     * Whether the view just rendered answered 404.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isNotFoundResponse(): bool
    {
        foreach ($this->app->getHeaders() as $header) {
            if (strtolower($header['name']) === 'status' && (int) $header['value'] === 404) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the view-cache entry stored for a view during this request.
     *
     * @param   string  $viewName  The view name
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function forgetCachedView(string $viewName): void
    {
        $view = $this->getView($viewName, $this->app->getDocument()->getType());

        // From the container: BaseController only gained a cache factory
        // accessor in Joomla 6.2.
        Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('view', ['defaultgroup' => $this->input->get('option')])
            ->remove(self::viewCacheId($view));
    }

    /**
     * The key Joomla's view cache stores a view's output under, as built by
     * the protected ViewController::_makeId() for the display method.
     *
     * @param   object  $view  The view
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function viewCacheId(object $view): string
    {
        return md5(serialize([Cache::makeId(), \get_class($view), 'display']));
    }
}
