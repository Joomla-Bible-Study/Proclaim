<?php

/**
 * Controller for Locations
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

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * Locations list controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmlocationsController extends AdminController
{
    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return    void
     *
     * @throws \Exception
     * @since   7.0.0
     */
    public function saveOrderAjax(): void
    {
        // Get the input
        $pks   = $this->input->post->get('cid', [], 'array');
        $order = $this->input->post->get('order', [], 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        Factory::getApplication()->close();
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Cwmlocation', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Merge selected locations into a target location.
     *
     * Reassigns all content from the selected locations to the target,
     * then deletes the source locations.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.3.0
     */
    public function merge(): void
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $app      = Factory::getApplication();
        $cid      = $this->input->post->get('cid', [], 'array');
        $jform    = $this->input->post->get('jform', [], 'array');
        $targetId = (int) ($jform['merge_target'] ?? 0);

        ArrayHelper::toInteger($cid);

        $redirect = Route::_('index.php?option=com_proclaim&view=cwmlocations', false);

        if (empty($cid)) {
            $app->enqueueMessage(Text::_('JGLOBAL_NO_ITEM_SELECTED'), 'warning');
            $this->setRedirect($redirect);

            return;
        }

        if ($targetId === 0) {
            $app->enqueueMessage(Text::_('JBS_LOC_MERGE_TARGET_REQUIRED'), 'warning');
            $this->setRedirect($redirect);

            return;
        }

        // Check permissions
        $user = $app->getIdentity();

        if (!$user->authorise('core.delete', 'com_proclaim') || !$user->authorise('core.edit', 'com_proclaim')) {
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
            $this->setRedirect($redirect);

            return;
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmlocationModel $model */
        $model  = $this->getModel();
        $errors = 0;

        foreach ($cid as $sourceId) {
            $sourceId = (int) $sourceId;

            if ($sourceId === $targetId) {
                $app->enqueueMessage(Text::_('JBS_LOC_MERGE_SAME_ERROR'), 'warning');
                $errors++;

                continue;
            }

            try {
                $count = $model->merge($sourceId, $targetId);

                // Load source name for the success message (already deleted, use ID)
                $targetTable = $model->getTable();
                $targetTable->load($targetId);

                $app->enqueueMessage(
                    Text::sprintf('JBS_LOC_MERGE_SUCCESS', $sourceId, $targetTable->location_text, $count),
                    'success'
                );
            } catch (\RuntimeException $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
                $errors++;
            }
        }

        $this->setRedirect($redirect);
    }

    /**
     * Method to get the JSON-encoded counts for Locations
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function getQuickIconLocations(): void
    {
        CwmcountHelper::sendQuickIconResponse('#__bsms_locations', 'COM_PROCLAIM_N_QUICKICON_LOCATIONS');
    }
}
