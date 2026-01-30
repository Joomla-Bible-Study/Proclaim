<?php

/**
 * @package         Proclaim
 * @subpackage      mod_proclaim_podcast
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

namespace CWM\Module\ProclaimPodcast\Site\Dispatcher;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcastsubscribe;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

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
     * @since   10.0.0
     */
    #[\Override]
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();


        // Always load Proclaim API if it exists.
        if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
            $apiPath = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

            if (file_exists($apiPath)) {
                require_once $apiPath;
            }
        }

        if (!ComponentHelper::isEnabled('com_proclaim')) {
            $this->app->enqueueMessage("Extension Proclaim not present or enabled", 'error');

            return $data;
        }

        $podcast = new Cwmpodcastsubscribe();

        try {
            $data['list'] = $podcast->buildSubscribeTable($data['params']->get('subscribeintro', 'Our Podcasts'));
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $data['list'] = '';
        }

        $data['app'] = $this->getApplication();

        // Display the module
        return $data;
    }
}
