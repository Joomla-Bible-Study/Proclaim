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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * MediaFiles list controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafilesController extends AdminController
{
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
    public function getModel($name = 'Cwmmediafile', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the JSON-encoded counts for Media Files
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function getQuickIconMediaFiles(): void
    {
        CwmcountHelper::sendQuickIconResponse('#__bsms_mediafiles', 'COM_PROCLAIM_N_QUICKICON_MEDIAFILES');
    }

    /**
     * AJAX: check whether selected media files have physical files on delete-enabled servers.
     *
     * Returns JSON: {success: bool, hasFiles: bool, files: [{id, filename, message, server}]}
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function checkDeleteFiles(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('get')) {
            $app->setHeader('Content-Type', 'application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Invalid token']);
            $app->close();

            return;
        }

        $ids = $this->input->get('cid', [], 'array');
        ArrayHelper::toInteger($ids);
        $ids = array_filter($ids);

        if (empty($ids)) {
            $app->setHeader('Content-Type', 'application/json; charset=utf-8');
            echo json_encode(['success' => true, 'hasFiles' => false, 'files' => []]);
            $app->close();

            return;
        }

        $db    = $this->getModel()->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mf.id'),
                $db->quoteName('mf.params', 'mf_params'),
                $db->quoteName('sv.server_name'),
                $db->quoteName('sv.params', 'sv_params'),
                $db->quoteName('st.studytitle'),
            ])
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->join('INNER', $db->quoteName('#__bsms_servers', 'sv') . ' ON ' . $db->quoteName('sv.id') . ' = ' . $db->quoteName('mf.server_id'))
            ->join('LEFT', $db->quoteName('#__bsms_studies', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->whereIn($db->quoteName('mf.id'), $ids)
            ->where($db->quoteName('mf.server_id') . ' > 0');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $files = [];

        foreach ($rows as $row) {
            $svParams = new Registry($row->sv_params ?: '{}');

            if (!(int) $svParams->get('delete_files', 0)) {
                continue;
            }

            $mfParams = new Registry($row->mf_params ?: '{}');
            $filename = $mfParams->get('filename', '');

            if ($filename === '') {
                continue;
            }

            $files[] = [
                'id'       => (int) $row->id,
                'filename' => $filename,
                'message'  => $row->studytitle ?: '',
                'server'   => $row->server_name ?: '',
            ];
        }

        $app->setHeader('Content-Type', 'application/json; charset=utf-8');
        echo json_encode([
            'success'  => true,
            'hasFiles' => !empty($files),
            'files'    => $files,
        ]);
        $app->close();
    }
}
