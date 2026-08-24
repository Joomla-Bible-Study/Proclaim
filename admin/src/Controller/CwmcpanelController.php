<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Controller for the cPanel
 *
 * Display controller, plus the one task the dashboard itself owns: hiding its
 * own Simple Mode notice.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmcpanelController extends BaseController
{
    /**
     * Hide the Simple Mode notice on the dashboard.
     *
     * Writes the same `simple_mode_display` setting that Administrative
     * Parameters exposes, so this is a shortcut to it rather than a second
     * piece of state. Simple Mode itself is untouched — the notice goes, the
     * hidden features stay hidden.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function hideSimpleNotice(): void
    {
        // A state change reached by a link still needs its token, whatever the
        // method.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        if (!$this->app->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_admin'))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query, 0, 1);

        $params = new Registry($db->loadResult() ?: '{}');
        $params->set('simple_mode_display', 0);

        $query = $db->createQuery()
            ->update($db->quoteName('#__bsms_admin'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);
        $db->execute();

        $this->app->enqueueMessage(Text::_('JBS_CPANEL_SIMPLE_MODE_HIDDEN_DONE'), 'message');
        $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel');
    }
}
