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

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Template Code controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtemplatecodeController extends FormController
{
    /**
     * Protect the view
     *
     * @var string
     *
     * @since 1.5
     */
    protected $view_list = 'cwmtemplatecodes';

    /**
     * The URL option for the component.
     *
     * @var    string
     * @since  7.0.0
     */
    protected $option = 'com_proclaim';

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function allowEdit($data = [], $key = 'id'): bool
    {
        $recordId = (int) ($data[$key] ?? 0);
        $user     = Factory::getApplication()->getIdentity();
        $isAdmin  = $user->authorise('core.admin');

        if (!$isAdmin && $recordId > 0) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select([$db->quoteName('access'), $db->quoteName('location_id')])
                ->from($db->quoteName('#__bsms_templatecode'))
                ->where($db->quoteName('id') . ' = :rid')
                ->bind(':rid', $recordId, ParameterType::INTEGER);
            $db->setQuery($query);
            $row = $db->loadObject();

            if (!$row) {
                return false;
            }

            // View-level access check
            $access = (int) $row->access;

            if ($access && !\in_array($access, $user->getAuthorisedViewLevels())) {
                return false;
            }

            // Location-based access check: non-admins can only edit template
            // codes assigned to their campus. Global codes are read-only.
            if (CwmlocationHelper::isEnabled()) {
                $locationId = (int) ($row->location_id ?? 0);

                if ($locationId === 0) {
                    return false;
                }

                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible) && !\in_array($locationId, $accessible, true)) {
                    return false;
                }
            }
        }

        return parent::allowEdit($data, $key);
    }
}
