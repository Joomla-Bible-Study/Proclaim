<?php

/**
 * @package         Proclaim
 * @subpackage      mod_proclaim_podcast
 * @copyright   (C) 2007 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

namespace CWM\Module\ProclaimPodcast\Site\Dispatcher;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcastsubscribe;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles_latest
 *
 * @since  4.2.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @throws \Exception
     * @since   4.2.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();


        // Always load Proclaim API if it exists.
        $api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

        if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
            require_once $api;
        }

        if (!ComponentHelper::isEnabled('com_proclaim')) {
            throw new \Exception("Extension Proclaim not present or enabled");
        }

        $podcast   = new Cwmpodcastsubscribe();
        $data['list'] = $podcast->buildSubscribeTable($data['params']->get('subscribeintro', 'Our Podcasts'));

        // Display the module
        return $data;
    }
}
