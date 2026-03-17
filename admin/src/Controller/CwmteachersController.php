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

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Teachers list controller class.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmteachersController extends AdminController
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
        $pks   = $this->input->post->get('cid', [], 'array');
        $order = $this->input->post->get('order', [], 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        // Get the model
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
    public function getModel($name = 'Cwmteacher', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Reset ordering for all teachers alphabetically by name.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.3.0
     */
    public function resetOrdering(): void
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Get all teacher IDs ordered alphabetically
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_teachers'))
            ->order($db->quoteName('teachername') . ' ASC');
        $db->setQuery($query);
        $ids = $db->loadColumn();

        // Update each teacher's ordering sequentially
        foreach ($ids as $index => $id) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_teachers'))
                ->set($db->quoteName('ordering') . ' = ' . ($index + 1))
                ->where($db->quoteName('id') . ' = ' . (int) $id);
            $db->setQuery($query);
            $db->execute();
        }

        $this->setMessage(Text::sprintf('JBS_TCH_ORDERING_RESET_SUCCESS', \count($ids)));
        $this->setRedirect('index.php?option=com_proclaim&view=cwmteachers');
    }

    /**
     * Method to get the JSON-encoded counts for Teachers
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function getQuickIconTeachers(): void
    {
        CwmcountHelper::sendQuickIconResponse('#__bsms_teachers', 'COM_PROCLAIM_N_QUICKICON_TEACHERS', 'access');
    }
}
