<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Component\Postinstall\Administrator\Model\MessagesModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JModel class for Cpanel
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmcpanelModel extends BaseModel
{
    /**
     * Get Data
     *
     * @return object
     *
     * @since 7.0
     */
    public function getData()
    {
        // Get version information
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $return = new \stdClass();
        $query  = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions');
        $query->where('element = "com_proclaim" and type = "component"');
        $db->setQuery($query);

        try {
            $data = $db->loadObject();

            // Convert parameter fields to objects.
            $registry = new Registry();
            $registry->loadString($data->manifest_cache);

            if ($data) {
                $return->version     = $registry->get('version');
                $return->versiondate = $registry->get('creationDate');
            }
        } catch (\Exception $e) {
            $return = null;
        }

        return $return;
    }

    /**
     * Returns true if we are installed in Joomla! 3.2 or later and we have post-installation messages for our component
     * which must be showed to the user.
     *
     * Returns null if the com_postinstall component is broken because the user screwed up his Joomla! site following
     * some idiot's advice. Apparently there's no shortage of idiots giving terribly bad advice to Joomla! users.
     *
     * @return boolean
     *
     * @since 7.0
     */
    public function hasPostInstallMessages(): bool
    {
        // Make sure we have Joomla! 3.2.0 or later
        if (!version_compare(JVERSION, '3.2.0', 'ge')) {
            return false;
        }

        // Get the extension ID
        // Get the extension ID for our component
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('extension_id')
            ->from('#__extensions')
            ->where($db->qn('element') . ' = ' . $db->q('com_proclaim'));
        $db->setQuery($query);

        try {
            $ids = $db->loadColumn();
        } catch (\Exception $exc) {
            return false;
        }

        if (empty($ids)) {
            return false;
        }

        $extension_id = array_shift($ids);

        $this->setState('extension_id', $extension_id);

        // Do I have messages?
        try {
            $pimModel = new MessagesModel();
            $pimModel->setState('eid', $extension_id);

            $list   = $pimModel->getitems();
            $result = count($list) >= 1;
        } catch (\Exception $e) {
            $result = true;
        }

        return $result;
    }
}
