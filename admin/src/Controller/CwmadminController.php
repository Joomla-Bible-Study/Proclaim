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

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Bible\BibleImporter;
use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Administrator\Helper\CwmcsvimportHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmdescriptionHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmImageCleanup;
use CWM\Component\Proclaim\Administrator\Helper\CwmImageMigration;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Helper\CwmupgradeHelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Administrator\Lib\CwmpIconvert;
use CWM\Component\Proclaim\Administrator\Lib\Cwmrestore;
use CWM\Component\Proclaim\Administrator\Lib\Cwmssconvert;
use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

/**
 * Controller for Admin
 *
 * @since  7.0.0
 */
class CwmadminController extends FormController
{
    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $view_list = 'cwmcpanel';

    /**
     * Gate ALL admin center actions behind core.admin.
     *
     * Campus editors/viewers only have core.manage — they must not reach
     * any admin center task (tools, backup, restore, conversions, etc.).
     *
     * @param   string  $task  The task to execute.
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    #[\Override]
    public function execute($task): mixed
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.admin')) {
            $this->setRedirect(
                Route::_('index.php?option=com_proclaim&view=cwmcpanel', false),
                Text::_('JERROR_ALERTNOAUTHOR'),
                'warning'
            );

            return null;
        }

        return parent::execute($task);
    }

    /**
     * Tools to change the player or pop-up
     *
     * @return void
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    public function tools(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $tool = Factory::getApplication()->getInput()->get('tooltype', '', 'post');

        $model = $this->getModel();

        switch ($tool) {
            case 'players':
                $this->changePlayers();
                break;

            case 'popups':
                $this->changePopup();
                break;

            case 'playerbymediatype':
                $msg = $model->playerByMediaType();
                $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
                break;
        }
    }

    /**
     * Change Player Modes
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function changePlayers(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $msg  = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        $post = $this->input->post->get('jform', [], 'array');
        $reg  = new Registry();
        $reg->loadArray($post['params']);
        $from = $reg->get('from', 'x');
        $to   = $reg->get('to', 'x');

        if ($from !== 'x' && $to !== 'x') {
            $query = $db->getQuery(true);
            $query->select($db->quoteName(['id', 'params']))
                ->from($db->quoteName('#__bsms_mediafiles'));
            $db->setQuery($query);

            foreach ($db->loadObjectList() as $media) {
                $reg = new Registry();
                $reg->loadString($media->params);

                if ($reg->get('player', 0) == $from) {
                    $reg->set('player', $to);

                    $query = $db->getQuery(true);
                    $query->update($db->quoteName('#__bsms_mediafiles'))
                        ->set($db->quoteName('params') . ' = ' . $db->q($reg->toString()))
                        ->where($db->quoteName('id') . ' = ' . (int)$media->id);
                    $db->setQuery($query);

                    if (!$db->execute()) {
                        $msg = Text::_('JBS_ADM_ERROR_OCCURED');
                        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
                    }
                }
            }
        } else {
            $msg = Text::_('JBS_ADM_ERROR_OCCURED') . ': Missed setting the From or To';
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
    }

    /**
     * Change Media Popup
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function changePopup(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $post = $this->input->post->get('jform', [], 'array');
        $reg  = new Registry();
        $reg->loadArray($post['params']);
        $from  = $reg->get('pFrom', 'x');
        $form2 = '';
        $to    = $reg->get('pTo', 'x');
        $msg   = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['id', 'params']))
            ->from($db->quoteName('#__bsms_mediafiles'));
        $db->setQuery($query);

        foreach ($db->loadObjectList() as $media) {
            $reg = new Registry();
            $reg->loadString($media->params);

            if ($from == '100') {
                $from  = '0';
                $form2 = '100';
            } elseif ($to == '100') {
                $to = '';
            }

            if ($reg->get('popup', 0) == $from || $reg->get('popup', 0) == $form2) {
                $reg->set('popup', $to);

                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__bsms_mediafiles'))
                    ->set($db->quoteName('params') . ' = ' . $db->q($reg->toString()))
                    ->where($db->quoteName('id') . ' = ' . (int)$media->id);
                $db->setQuery($query);

                if (!$db->execute()) {
                    $msg = Text::_('JBS_ADM_ERROR_OCCURED');
                    $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
                }
            }
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
    }

    /**
     * Change media images from a digital file to CSS
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function mediaimages(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $post    = $this->input->post->get('jform', [], 'raw');
        $decoded = json_decode($post['mediaimage'], true, 512, JSON_THROW_ON_ERROR);
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->getQuery(true);
        $query->select($db->quoteName(['id', 'params']))
            ->from($db->quoteName('#__bsms_mediafiles'));
        $db->setQuery($query);
        $images    = $db->loadObjectList();
        $error     = 0;
        $added     = 0;
        $errortext = '';
        $msg       = Text::_('JBS_RESULTS') . ': ';

        switch ($decoded->media_use_button_icon) {
            case 1:
                // Button only
                $buttontype = $decoded->media_button_type;
                $buttontext = $decoded->media_button_text;

                if (!isset($post['media_icon_type'])) {
                    $post['media_icon_type'] = 0;
                }

                foreach ($images as $media) {
                    $reg = new Registry();
                    $reg->loadString($media->params);

                    if (
                        $reg->get('media_button_type') == $buttontype && $reg->get(
                            'media_button_text'
                        ) == $buttontext
                    ) {
                        $query = $db->getQuery(true);
                        $reg->set('media_button_color', $post['media_button_color']);
                        $reg->set('media_button_text', $post['media_button_text']);
                        $reg->set('media_button_type', $post['media_button_type']);
                        $reg->set('media_custom_icon', $post['media_custom_icon']);
                        $reg->set('media_icon_text_size', $post['media_icon_text_size']);
                        $reg->set('media_icon_type', $post['media_icon_type']);
                        $reg->set('media_image', $post['media_image']);
                        $reg->set('media_use_button_icon', $post['media_use_button_icon']);
                        $db->setQuery($query);

                        try {
                            $query->update($db->quoteName('#__bsms_mediafiles'))
                                ->set($db->quoteName('params') . ' = ' . $db->q($reg->toString()))
                                ->where($db->quoteName('id') . ' = ' . (int)$media->id);
                            $db->execute();
                            $rows  = $db->getAffectedRows();
                            $added = $added + $rows;
                        } catch (\RuntimeException $e) {
                            $errortext .= $e->getMessage() . '<br />';
                            $error++;
                        }
                    }
                }

                $msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_(
                    'JBS_RESULTS'
                ) .
                    ': ' . $added . ' ' . Text::_('JBS_SUCCESS');
                break;
            case 2:
                $buttontype = $decoded->media_button_type;
                $icontype   = $decoded->media_icon_type;

                foreach ($images as $media) {
                    $reg = new Registry();
                    $reg->loadString($media->params);

                    if ($reg->get('media_button_type') == $buttontype && $reg->get('media_icon_type') == $icontype) {
                        $query = $db->getQuery(true);
                        $reg->set('media_button_color', $post['media_button_color']);
                        $reg->set('media_button_text', $post['media_button_text']);
                        $reg->set('media_button_type', $post['media_button_type']);
                        $reg->set('media_custom_icon', $post['media_custom_icon']);
                        $reg->set('media_icon_text_size', $post['media_icon_text_size']);
                        $reg->set('media_icon_type', $post['media_icon_type']);
                        $reg->set('media_image', $post['media_image']);
                        $reg->set('media_use_button_icon', $post['media_use_button_icon']);
                        $db->setQuery($query);

                        try {
                            $query->update($db->quoteName('#__bsms_mediafiles'))
                                ->set($db->quoteName('params') . ' = ' . $db->q($reg->toString()))
                                ->where($db->quoteName('id') . ' = ' . (int)$media->id);
                            $db->execute();
                            $rows  = $db->getAffectedRows();
                            $added = $added + $rows;
                        } catch (\RuntimeException $e) {
                            $errortext .= $e->getMessage() . '<br />';
                            $error++;
                        }
                    }
                }

                $msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_(
                    'JBS_RESULTS'
                ) .
                    ': ' . $added . ' ' . Text::_('JBS_SUCCESS');
                break;
            case 3:
                // Icon only
                $icontype = $decoded->media_icon_type;

                if (!isset($post['media_button_type'])) {
                    $post['media_button_type'] = 0;
                }

                foreach ($images as $media) {
                    $reg = new Registry();
                    $reg->loadString($media->params);

                    if ($reg->get('media_icon_type') == $icontype) {
                        $query = $db->getQuery(true);
                        $reg->set('media_button_color', $post['media_button_color']);
                        $reg->set('media_button_text', $post['media_button_text']);
                        $reg->set('media_button_type', $post['media_button_type']);
                        $reg->set('media_custom_icon', $post['media_custom_icon']);
                        $reg->set('media_icon_text_size', $post['media_icon_text_size']);
                        $reg->set('media_icon_type', $post['media_icon_type']);
                        $reg->set('media_image', $post['media_image']);
                        $reg->set('media_use_button_icon', $post['media_use_button_icon']);
                        $db->setQuery($query);

                        try {
                            $query->update($db->quoteName('#__bsms_mediafiles'))
                                ->set($db->quoteName('params') . ' = ' . $db->q($reg->toString()))
                                ->where($db->quoteName('id') . ' = ' . (int)$media->id);
                            $db->execute();
                            $rows  = $db->getAffectedRows();
                            $added = $added + $rows;
                        } catch (\RuntimeException $e) {
                            $errortext .= $e->getMessage() . '<br />';
                            $error++;
                        }
                    }
                }

                $msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_(
                    'JBS_RESULTS'
                ) .
                    ': ' . $added . ' ' . Text::_('JBS_SUCCESS');
                break;
            case 0:
                // It's an image
                $mediaimage = $decoded->media_image;

                if (!isset($post['media_icon_type'])) {
                    $post['media_icon_type'] = 0;
                }

                if (!isset($post['media_button_type'])) {
                    $post['media_button_type'] = 0;
                }

                foreach ($images as $media) {
                    $reg = new Registry();
                    $reg->loadString($media->params);

                    if ($reg->get('media_image') == $mediaimage) {
                        $query = $db->getQuery(true);
                        $reg->set('media_button_color', $post['media_button_color']);
                        $reg->set('media_button_text', $post['media_button_text']);
                        $reg->set('media_button_type', $post['media_button_type']);
                        $reg->set('media_custom_icon', $post['media_custom_icon']);
                        $reg->set('media_icon_text_size', $post['media_icon_text_size']);
                        $reg->set('media_icon_type', $post['media_icon_type']);
                        $reg->set('media_image', $post['media_image']);
                        $reg->set('media_use_button_icon', $post['media_use_button_icon']);

                        try {
                            $db->setQuery($query);
                            $query->update($db->quoteName('#__bsms_mediafiles'))
                                ->set($db->quoteName('params') . ' = ' . $db->q($reg->toString()))
                                ->where($db->quoteName('id') . ' = ' . (int)$media->id);
                            $db->execute();
                            $rows  = $db->getAffectedRows();
                            $added += $rows;
                        } catch (\RuntimeException $e) {
                            $errortext .= $e->getMessage() . '<br />';
                            $error++;
                        }
                    }
                }

                $msg .= Text::_('JBS_ERROR') . ': ' . $error . '<br />' . $errortext . '<br />' . Text::_(
                    'JBS_RESULTS'
                ) .
                    ': ' . $added . ' ' . Text::_('JBS_SUCCESS');
                break;
            default:
                $msg = Text::_('JBS_NOTHING_MATCHED');
                break;
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
    }

    /**
     * Reset Hits
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function resetHits(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $msg   = null;
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('hits') . ' = 0')
            ->where($db->quoteName('hits') . ' != 0');
        $db->setQuery($query);

        if (!$db->execute()) {
            $msg = Text::_('JBS_ADM_ERROR_OCCURED');
        } else {
            $msg = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
    }

    /**
     * Reset Downloads
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function resetDownloads(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $msg   = null;
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('downloads') . ' = 0')
            ->where($db->quoteName('downloads') . ' != 0');
        $db->setQuery($query);

        if (!$db->execute()) {
            $msg = Text::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS');
        } else {
            $updated = $db->getAffectedRows();
            $msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
    }

    /**
     * Reset Players
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function resetPlays(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $msg   = null;
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('plays') . ' = 0')
            ->where($db->quoteName('plays') . ' != 0');
        $db->setQuery($query);

        if (!$db->execute()) {
            $msg = Text::_('JBS_CMN_ERROR_RESETTING_PLAYS');
        } else {
            $updated = $db->getAffectedRows();
            $msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $msg);
    }

    /**
     * Reset All Hits — AJAX endpoint
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function resetHitsXHR(): void
    {
        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            $this->app->close();

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('hits') . ' = 0')
            ->where($db->quoteName('hits') . ' != 0');
        $db->setQuery($query);

        if ($db->execute()) {
            echo json_encode(['success' => true, 'updated' => $db->getAffectedRows()]);
        } else {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_CMN_ERROR_RESETTING_HITS')]);
        }

        $this->app->close();
    }

    /**
     * Reset All Downloads — AJAX endpoint
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function resetDownloadsXHR(): void
    {
        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            $this->app->close();

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('downloads') . ' = 0')
            ->where($db->quoteName('downloads') . ' != 0');
        $db->setQuery($query);

        if ($db->execute()) {
            echo json_encode(['success' => true, 'updated' => $db->getAffectedRows()]);
        } else {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS')]);
        }

        $this->app->close();
    }

    /**
     * Reset All Plays — AJAX endpoint
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function resetPlaysXHR(): void
    {
        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            $this->app->close();

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('plays') . ' = 0')
            ->where($db->quoteName('plays') . ' != 0');
        $db->setQuery($query);

        if ($db->execute()) {
            echo json_encode(['success' => true, 'updated' => $db->getAffectedRows()]);
        } else {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_CMN_ERROR_RESETTING_PLAYS')]);
        }

        $this->app->close();
    }

    /**
     * Get formatted video description for a study — AJAX endpoint.
     *
     * Returns the description text for preview/copy operations.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function getVideoDescriptionXHR(): void
    {
        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            $this->app->close();

            return;
        }

        // Release session lock for concurrent requests
        $this->app->getSession()->close();

        $studyId = $this->input->getInt('study_id', 0);

        if (!$studyId) {
            echo json_encode(['success' => false, 'error' => 'No study ID provided']);
            $this->app->close();

            return;
        }

        try {
            $description = CwmdescriptionHelper::buildVideoDescription($studyId);

            echo json_encode(['success' => true, 'description' => $description]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

        $this->app->close();
    }

    /**
     * Push description to a video platform — AJAX endpoint.
     *
     * Accepts study_id and media_id, builds the description, and pushes via addon API.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function syncVideoDescriptionXHR(): void
    {
        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            $this->app->close();

            return;
        }

        // Release session lock for concurrent requests
        $this->app->getSession()->close();

        $studyId = $this->input->getInt('study_id', 0);
        $mediaId = $this->input->getInt('media_id', 0);

        if (!$studyId || !$mediaId) {
            echo json_encode(['success' => false, 'error' => 'Missing study_id or media_id']);
            $this->app->close();

            return;
        }

        try {
            $description = $this->input->getString('description', '');

            if (empty($description)) {
                $description = CwmdescriptionHelper::buildVideoDescription($studyId);
            }

            // Look up the server type for this media file
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('sv.type'))
                ->from($db->quoteName('#__bsms_mediafiles', 'm'))
                ->leftJoin(
                    $db->quoteName('#__bsms_servers', 'sv') .
                    ' ON ' . $db->quoteName('sv.id') . ' = ' . $db->quoteName('m.server_id')
                )
                ->where($db->quoteName('m.id') . ' = ' . (int) $mediaId);
            $db->setQuery($query);
            $serverType = $db->loadResult();

            if (empty($serverType)) {
                echo json_encode(['success' => false, 'error' => 'Could not determine server type']);
                $this->app->close();

                return;
            }

            $addon = CWMAddon::getInstance($serverType);

            if (!$addon->supportsDescriptionSync()) {
                echo json_encode(['success' => false, 'error' => 'This platform does not support description sync']);
                $this->app->close();

                return;
            }

            $result = $addon->syncDescription($mediaId, $description);

            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

        $this->app->close();
    }

    /**
     * Return to c-panel
     *
     * @return void
     *
     * @since 7.0.0
     */
    public function back(): void
    {
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin');
    }

    /**
     * Convert SermonSpeaker to Proclaim
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function convertSermonSpeaker(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $convert      = new Cwmssconvert();
        $ssconversion = $convert->convertSS();
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $ssconversion);
    }

    /**
     * Convert PreachIt to Proclaim
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function convertPreachIt(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $convert      = new CwmpIconvert();
        $piconversion = $convert->convertPI();
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin', $piconversion);
    }

    /**
     * Reset the DB to install
     *
     * @return void
     *
     * @throws  \Exception
     * @since   7.1.0
     */
    public function dbReset(): void
    {
        $user = Factory::getApplication()->getIdentity();

        if ($user->authorise('core.admin')) {
            CwmdbHelper::resetdb();
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_proclaim&view=cwmassats&task=cwmassets.browse&' . Session::getFormToken(
                    ) . '=1',
                    false
                )
            );
        } else {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmcpanel', false));
        }
    }

    /**
     * Alias Updates
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.1.0
     */
    public function aliasUpdate(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get')) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $update = Cwmalias::updateAlias();
        $this->setMessage(Text::_('JBS_ADM_ALIAS_ROWS') . $update);
        $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmadmin', false));
    }

    /**
     * Do the import
     *
     * @param   bool  $parent  Source of info
     *
     * @return void
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    public function doimport(bool $parent = true): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        // This should be where the form administrator/form_migrate comes to with either the file select box or the tmp folder input field
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $input->set('view', $input->get('view', 'administrator', 'cmd'));

        // Add commands to move tables from old prefix to new
        $oldprefix = $input->get('oldprefix', '', 'string');

        if ($oldprefix) {
            if (!($this->copyTables($oldprefix))) {
                $app->enqueueMessage(Text::_('JBS_CMN_DATABASE_NOT_COPIED'), 'warning');
                $this->setRedirect('index.php?option=com_proclaim&view=cwmbackup');

                return;
            }

            $app->enqueueMessage(Text::_('JBS_CMN_OPERATION_SUCCESSFUL'), 'success');
        } else {
            $import = new Cwmrestore();
            $result = $import->importdb($parent);

            if ($result === true) {
                $app->enqueueMessage(Text::_('JBS_CMN_OPERATION_SUCCESSFUL'), 'success');
            } elseif ($result === false) {
                // Error messages already enqueued by importdb()
            } else {
                $app->enqueueMessage((string) $result, 'warning');
            }
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmbackup');
    }

    /**
     * Copy Old Tables to new Joomla! Tables
     *
     * @param   string  $oldprefix  Old table Prefix
     *
     * @return bool
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    public function copyTables(string $oldprefix): bool
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return false;
        }

        // Create table tablename_new like tablename; -> this will copy the structure...
        // Insert into tablename_new select * from tablename; -> this would copy all the data
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();

        foreach ($tables as $table) {
            $isjbs = substr_count($table, $oldprefix . 'bsms');

            if ($isjbs) {
                $oldlength       = \strlen($oldprefix);
                $newsubtablename = substr($table, $oldlength);
                $newtablename    = $prefix . $newsubtablename;
                $query           = 'DROP TABLE IF EXISTS ' . $db->quoteName($newtablename);

                if (!CwmdbHelper::performDB($query)) {
                    return false;
                }

                $query = 'CREATE TABLE ' . $db->quoteName($newtablename) . ' LIKE ' . $db->quoteName($table);

                if (!CwmdbHelper::performDB($query)) {
                    return false;
                }

                $query = 'INSERT INTO ' . $db->quoteName($newtablename) . ' SELECT * FROM ' . $db->quoteName($table);

                if (!CwmdbHelper::performDB($query)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Import function from the backup page
     *
     * @return void
     *
     * @throws  \Exception
     * @since   7.1.0
     */
    public function import(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $application = Factory::getApplication();
        $import      = new Cwmrestore();
        $result      = $import->importdb(false);

        if ($result === true) {
            $application->enqueueMessage(Text::_('JBS_CMN_OPERATION_SUCCESSFUL'));
        } elseif ($result === false) {
            // Do nothing
        } else {
            $application->enqueueMessage($result);
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmbackup');
    }

    /**
     * Export Db
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function export(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken('get') && !Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $input  = Factory::getApplication()->getInput();
        $run    = (int)$input->get('run', '', 'int');
        $export = new Cwmbackup();

        if (!$result = $export->exportdb($run)) {
            $msg = Text::_('JBS_CMN_OPERATION_FAILED');
            $this->setRedirect('index.php?option=com_proclaim&view=cwmbackup', $msg);
        } elseif ($run === 2) {
            if (!$result) {
                $msg = $result;
            } else {
                $msg = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
            }

            $this->setRedirect('index.php?option=com_proclaim&view=cwmbackup', $msg);
        }
    }

    /**
     * Get Thumbnail List XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 9.0.0
     */
    public function getThumbnailListXHR(): void
    {
        $app          = Factory::getApplication();
        $input        = $app->getInput();
        $images_paths = [];

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $image_types = $input->get('images', null, 'array');
        $count       = 0;

        foreach ($image_types as $image_type) {
            $images = Folder::files(JPATH_ROOT . '/images/biblestudy/' . $image_type, 'original_', true, true);

            if ($images) {
                $count += \count($images);
            }

            $images_paths[] = [['type' => $image_type, 'images' => $images]];
        }

        echo json_encode(['total' => $count, 'paths' => $images_paths], JSON_THROW_ON_ERROR);

        $app->close();
    }

    /**
     * Create Thumbnail XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 9.0.0
     */
    public function createThumbnailXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $image_path = $input->get('image_path', null, 'string');
        $new_size   = $input->get('new_size', null, 'integer');

        Cwmthumbnail::resize($image_path, $new_size);

        $app->close();
    }

    /**
     * Archive Old Message and Media
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.0.1
     */
    public function doArchive(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmarchiveModel $model */
        $model = $this->getModel('Cwmarchive');
        $msg   = $model->doArchive();
        $this->setRedirect('index.php?option=com_proclaim&view=cwmcpanel', $msg);
    }

    /**
     * Submit function
     *
     * @param   ?int     $key     ID
     * @param   ?string  $urlVar  URL variable
     *
     * @return bool
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function submit(?int $key = null, ?string $urlVar = null): bool
    {
        $this->checkToken();

        $app   = Factory::getApplication();
        $model = $this->getModel('form');
        $form  = $model->getForm('', false);

        if (!$form) {
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_FORM_LOAD'), 'error');

            return false;
        }

        // Name of an array 'jform' must match 'control' => 'jform' line in the model code
        $data = $this->input->post->get('jform', [], 'array');

        // This is validate() from the FormModel class, not the Form class
        // FormModel::validate() calls both Form::filter() and Form::validate() methods
        $validData = $model->validate($form, $data);

        if ($validData === false) {
            // Get validation errors from the form directly (Joomla 6 compatible)
            $form   = $model->getForm();
            $errors = $form ? $form->getErrors() : [];

            foreach ($errors as $error) {
                if ($error instanceof \Exception) {
                    $app->enqueueMessage($error->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage((string) $error, 'warning');
                }
            }

            // Save the form data in the session, using a unique identifier
            $app->setUserState('com_proclaim.cwmadmin', $data);
        } else {
            $app->enqueueMessage("Data successfully validated", 'notice');

            // Clear the form data in the session
            $app->setUserState('com_proclaim.cwmadmin', null);
        }

        // Redirect back to the form in all cases
        $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmadmin', false));

        return true;
    }

    /**
     * Get migration counts XHR - returns count of records needing migration
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.2.0
     */
    public function getMigrationCountsXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            $counts = CwmImageMigration::getMigrationCounts();
            echo json_encode($counts, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'studies' => 0, 'teachers' => 0, 'series' => 0, 'total' => 0,
                'error'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Get migration batch XHR - returns batch of records to migrate
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.2.0
     */
    public function getMigrationBatchXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type    = $input->get('type', 'studies', 'string');
        $limit   = $input->get('limit', 10, 'int');
        $exclude = $input->get('exclude', '', 'string');

        // Parse comma-separated exclude IDs (records that already failed)
        $excludeIds = !empty($exclude) ? array_map('intval', explode(',', $exclude)) : [];

        try {
            $batch = CwmImageMigration::getBatch($type, $limit, $excludeIds);
            echo json_encode($batch, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'records' => [], 'remaining' => 0,
                'error'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Migrate single record XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.2.0
     */
    public function migrateRecordXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type = $input->get('type', '', 'string');
        $id   = $input->get('id', 0, 'int');

        if (empty($type) || empty($id)) {
            echo json_encode([
                'success' => false,
                'error'   => 'Missing required parameters (type and id)',
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Look up the record from the DB — avoids URL encoding issues with image paths
        try {
            $result = CwmImageMigration::migrateRecordById($type, $id);
        } catch (\Throwable $e) {
            $result = ['success' => false, 'newPath' => null, 'error' => $e->getMessage()];
        }

        echo json_encode($result, JSON_THROW_ON_ERROR);

        $app->close();
    }

    /**
     * Get orphaned folders XHR - scans for orphaned image folders
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.2.0
     */
    public function getOrphanedFoldersXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $orphans = CwmImageCleanup::findOrphanedFolders();
            $totals  = CwmImageCleanup::getTotals($orphans);
            echo json_encode(['orphans' => $orphans, 'totals' => $totals], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'orphans' => [], 'totals' => ['folders' => 0, 'size' => 0, 'size_formatted' => '0 B'],
                'error'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Delete orphaned folders XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.2.0
     */
    public function deleteOrphanedFoldersXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $paths = $input->get('paths', [], 'array');

        if (empty($paths)) {
            echo json_encode([
                'deleted' => 0,
                'errors'  => ['No paths provided'],
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $result = CwmImageCleanup::deleteOrphans($paths);

        echo json_encode($result, JSON_THROW_ON_ERROR);

        $app->close();
    }

    /**
     * Get legacy folder report XHR - scans old image folders for leftover files
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function getLegacyFolderReportXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $report = CwmImageMigration::getLegacyFolderReport();
            echo json_encode($report, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'folders' => [], 'total_files' => 0, 'total_size' => 0,
                'error'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Download the cleared images log CSV
     *
     * Sends the proclaim_cleared_images.csv file as a download so admins
     * can review which image values were cleared during migration.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function downloadClearedLogXHR(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('get')) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        $logFile = CwmImageMigration::getClearedLogPath();

        if (!is_file($logFile)) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => 'No cleared images log found.'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $app->setHeader('Content-Type', 'text/csv');
        $app->setHeader('Content-Disposition', 'attachment; filename="proclaim_cleared_images.csv"');
        $app->setHeader('Content-Length', (string) filesize($logFile));
        $app->sendHeaders();

        readfile($logFile);

        $app->close();
    }

    /**
     * Get count of unresolvable image records XHR
     *
     * Returns a preview of how many records have image paths pointing to
     * files that cannot be found on disk.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function getUnresolvableCountXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $result = CwmImageMigration::getUnresolvableRecords();
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode(['records' => [], 'count' => 0, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Clear all unresolvable image fields XHR
     *
     * Clears DB image fields for records whose source files cannot be found
     * and logs the cleared values to a CSV file for manual recovery.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function clearUnresolvableXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $result = CwmImageMigration::clearUnresolvableImages();
            echo json_encode(['success' => true, 'cleared' => $result['cleared']], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'cleared' => 0, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Get WebP migration counts XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function getWebPCountsXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['error' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            $counts = CwmImageMigration::getWebPMigrationCounts();
            echo json_encode($counts, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'studies' => 0, 'teachers' => 0, 'series' => 0, 'total' => 0,
                'error'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Run a batch of WebP conversions XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function migrateToWebPXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['error' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type  = $input->get('type', 'studies', 'string');
        $limit = $input->getInt('limit', 10);

        try {
            $result = CwmImageMigration::migrateToWebP($type, $limit);
        } catch (\Throwable $e) {
            $result = ['converted' => 0, 'errors' => 0, 'remaining' => 0, 'error' => $e->getMessage()];
        }

        echo json_encode($result, JSON_THROW_ON_ERROR);

        $app->close();
    }

    /**
     * Get thumbnail regeneration count XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function getThumbRegenCountXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['error' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            $counts = CwmImageMigration::getThumbRegenerationCounts();
            echo json_encode($counts, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode(['total' => 0, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Regenerate thumbnails batch XHR
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function regenerateThumbsXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['error' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type         = $input->getCmd('type', 'studies');
        $limit        = $input->getInt('limit', 10);
        $offset       = $input->getInt('offset', 0);
        $sizeOverride = $input->getInt('size', 0);

        try {
            $result = CwmImageMigration::regenerateThumbnails($type, $limit, $offset, $sizeOverride);
        } catch (\Throwable $e) {
            $result = ['processed' => 0, 'errors' => 0, 'remaining' => 0, 'error' => $e->getMessage()];
        }

        echo json_encode($result, JSON_THROW_ON_ERROR);

        $app->close();
    }

    /**
     * Get count of recoverable bare-ID image folders XHR
     *
     * Returns per-type count of numeric-only folders in images/biblestudy/{type}/
     * that contain image files but whose DB records have empty image fields.
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function getRecoveryCountsXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            $counts = CwmImageMigration::getRecoveryCounts();
            echo json_encode($counts, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'studies' => 0, 'teachers' => 0, 'series' => 0, 'total' => 0,
                'error'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Recover bare-ID image folders XHR - processes a batch per type
     *
     * Migrates images from numeric-only folders to proper alias-ID folders,
     * updates DB columns, and cleans up the old folders.
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function recoverBareIdFoldersXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type  = $input->getCmd('type', 'studies');
        $limit = $input->getInt('limit', 10);

        try {
            $result = CwmImageMigration::recoverBareIdFolders($type, $limit);
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'recovered'    => 0,
                'skipped'      => 0,
                'errors'       => 0,
                'remaining'    => 0,
                'errorDetails' => [$e->getMessage()],
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Get count of records with broken or empty image references that can be relinked XHR
     *
     * Returns per-type count of records where the DB image columns are empty or
     * point to missing files, but whose alias-ID folder has files on disk.
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function getRelinkCountsXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            $counts = CwmImageMigration::getRelinkCounts();
            echo json_encode($counts, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'studies' => 0, 'teachers' => 0, 'series' => 0, 'total' => 0,
                'error'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Relink a batch of records to their existing image files XHR
     *
     * Updates DB columns for records with broken or empty image references
     * where the expected alias-ID folder already contains image files.
     * No files are moved or copied — only DB references are updated.
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function relinkBatchXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type  = $input->getCmd('type', 'studies');
        $limit = $input->getInt('limit', 10);

        try {
            $result = CwmImageMigration::relinkBatch($type, $limit);
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'relinked'     => 0,
                'skipped'      => 0,
                'errors'       => 0,
                'remaining'    => 0,
                'errorDetails' => [$e->getMessage()],
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Delete legacy image folders/files XHR
     *
     * Accepts an array of relative folder paths and deletes the image
     * files within them (not entire directory trees).
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function deleteLegacyFoldersXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken() && !Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $paths = $input->get('paths', [], 'array');

        if (empty($paths)) {
            echo json_encode([
                'deleted' => 0,
                'errors'  => ['No paths provided'],
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $result = CwmImageMigration::deleteLegacyFiles($paths);
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'deleted' => 0,
                'errors'  => [$e->getMessage()],
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Get player statistics XHR - returns player stats HTML as JSON for lazy loading
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function getPlayerStatsXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            $html = Cwmstats::getPlayers();

            echo json_encode([
                'success' => true,
                'data'    => ['html' => $html],
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Get popup statistics XHR - returns popup stats HTML as JSON for lazy loading
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function getPopupStatsXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            $html = Cwmstats::getPopups();

            echo json_encode([
                'success' => true,
                'data'    => ['html' => $html],
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Archive Old Messages and Media XHR - AJAX version with JSON response
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function doArchiveXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        // Check for request forgeries
        if (!Session::checkToken('get')) {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JINVALID_TOKEN'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmarchiveModel $model */
            $model = $this->getModel('Cwmarchive');
            $msg   = $model->doArchive();

            echo json_encode([
                'success' => true,
                'message' => $msg,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Alias Update XHR - AJAX version with JSON response
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function aliasUpdateXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        // Check for request forgeries
        if (!Session::checkToken('get')) {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JINVALID_TOKEN'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $count = Cwmalias::updateAlias();

            echo json_encode([
                'success' => true,
                'count'   => $count,
                'message' => Text::_('JBS_ADM_ALIAS_ROWS') . $count,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Change Players XHR - AJAX version with optimized batch update
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function changePlayersXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        // Check for request forgeries
        if (!Session::checkToken('get')) {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JINVALID_TOKEN'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $from = $input->getCmd('from', 'x');
        $to   = $input->getCmd('to', 'x');

        if ($from === 'x' || $to === 'x') {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JBS_ADM_ERROR_OCCURED') . ': ' . Text::_('JBS_ADM_SELECT_FROM_TO'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Use optimized batch update with JSON functions
            // This replaces the N+1 query pattern with a single UPDATE
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_mediafiles'))
                ->set($db->quoteName('params') . ' = REPLACE(' . $db->quoteName('params') . ', '
                    . $db->quote('"player":"' . $from . '"') . ', '
                    . $db->quote('"player":"' . $to . '"') . ')')
                ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%"player":"' . $from . '"%'));

            $db->setQuery($query);
            $db->execute();
            $count = $db->getAffectedRows();

            echo json_encode([
                'success' => true,
                'count'   => $count,
                'message' => Text::sprintf('JBS_ADM_PLAYER_CHANGED', $count),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Change Popup XHR - AJAX version with optimized batch update
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function changePopupXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        // Check for request forgeries
        if (!Session::checkToken('get')) {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JINVALID_TOKEN'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $from = $input->getCmd('from', 'x');
        $to   = $input->getCmd('to', 'x');

        if ($from === 'x' || $to === 'x') {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JBS_ADM_ERROR_OCCURED') . ': ' . Text::_('JBS_ADM_SELECT_FROM_TO'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Handle legacy value mapping (100 = 0, etc.)
            $searchFrom  = $from;
            $searchFrom2 = null;

            if ($from === '100') {
                $searchFrom  = '0';
                $searchFrom2 = '100';
            }

            $replaceTo = $to === '100' ? '' : $to;

            // Use optimized batch update
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_mediafiles'))
                ->set($db->quoteName('params') . ' = REPLACE(' . $db->quoteName('params') . ', '
                    . $db->quote('"popup":"' . $searchFrom . '"') . ', '
                    . $db->quote('"popup":"' . $replaceTo . '"') . ')')
                ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%"popup":"' . $searchFrom . '"%'));

            $db->setQuery($query);
            $db->execute();
            $count = $db->getAffectedRows();

            // If there's a secondary search pattern (for legacy 100 value)
            if ($searchFrom2 !== null) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_mediafiles'))
                    ->set($db->quoteName('params') . ' = REPLACE(' . $db->quoteName('params') . ', '
                        . $db->quote('"popup":"' . $searchFrom2 . '"') . ', '
                        . $db->quote('"popup":"' . $replaceTo . '"') . ')')
                    ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%"popup":"' . $searchFrom2 . '"%'));

                $db->setQuery($query);
                $db->execute();
                $count += $db->getAffectedRows();
            }

            echo json_encode([
                'success' => true,
                'count'   => $count,
                'message' => Text::sprintf('JBS_ADM_POPUP_CHANGED', $count),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Change Player by Media Type XHR - AJAX version with optimized batch update
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function changePlayerByMediaTypeXHR(): void
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();

        header('Content-Type: application/json; charset=utf-8');

        // Check for request forgeries
        if (!Session::checkToken('get')) {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JINVALID_TOKEN'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $mediaType = $input->getInt('mediatype', 0);
        $player    = $input->getCmd('player', 'x');

        if ($mediaType === 0 || $player === 'x') {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JBS_ADM_ERROR_OCCURED') . ': ' . Text::_('JBS_ADM_SELECT_MEDIATYPE_PLAYER'),
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Get all media files with matching media type
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('params')])
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('media_image') . ' = ' . $mediaType);

            $db->setQuery($query);
            $mediaFiles = $db->loadObjectList();

            $count = 0;

            foreach ($mediaFiles as $media) {
                $reg = new Registry();
                $reg->loadString($media->params);
                $reg->set('player', $player);

                $updateQuery = $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_mediafiles'))
                    ->set($db->quoteName('params') . ' = ' . $db->quote($reg->toString()))
                    ->where($db->quoteName('id') . ' = ' . (int) $media->id);

                $db->setQuery($updateQuery);
                $db->execute();
                $count++;
            }

            echo json_encode([
                'success' => true,
                'count'   => $count,
                'message' => Text::sprintf('JBS_ADM_PLAYER_BY_MEDIATYPE_CHANGED', $count),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * Get scripture provider status XHR - returns count of locally installed translations
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 10.1.0
     */
    public function getScriptureStatusXHR(): void
    {
        $app      = Factory::getApplication();

        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls (e.g. getTranslationsXHR
        // firing at the same time) don't serialise behind each other.
        session_write_close();

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->where($db->quoteName('installed') . ' = 1');
            $db->setQuery($query);
            $localCount = (int) $db->loadResult();

            echo json_encode([
                'success'     => true,
                'local_count' => $localCount,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success'     => true,
                'local_count' => 0,
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Get list of available translations with install status.
     *
     * @return  void
     *
     * @since 10.1.0
     */
    public function getTranslationsXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Release session lock so concurrent AJAX calls don't serialise.
        session_write_close();

        try {
            // Auto-seed GetBible catalog if provider is enabled but catalog is depleted
            try {
                $admin       = Cwmparams::getAdmin();
                $adminParams = $admin->params ?? new Registry();
            } catch (\Exception $e) {
                $adminParams = new Registry();
            }

            $getbibleEnabled = (int) $adminParams->get('provider_getbible', 1) === 1
                && (int) $adminParams->get('gdpr_mode', 0) !== 1;

            if ($getbibleEnabled) {
                BibleImporter::seedGetBibleCatalog();
            }

            $db    = Factory::getContainer()->get(DatabaseInterface::class);

            // data_size is a cached column added in 10.1.0 — may not exist yet
            // if the migration hasn't run.  Detect once and fall back gracefully.
            $hasDataSize = !empty(
                $db->setQuery(
                    'SHOW COLUMNS FROM ' . $db->quoteName('#__bsms_bible_translations')
                    . ' LIKE ' . $db->quote('data_size')
                )->loadObjectList()
            );

            $cols = ['t.abbreviation', 't.name', 't.language', 't.installed', 't.verse_count', 't.source', 't.bundled', 't.estimated_size'];

            if ($hasDataSize) {
                $cols[] = 't.data_size';
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName($cols))
                ->from($db->quoteName('#__bsms_bible_translations', 't'))
                ->order($db->quoteName('t.name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList();

            // Build usage counts from studies table (separate query, fail-safe)
            $usageCounts = [];

            try {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('bible_version') . ' AS ' . $db->quoteName('abbr'))
                    ->select('COUNT(*) AS ' . $db->quoteName('cnt'))
                    ->from($db->quoteName('#__bsms_studies'))
                    ->where($db->quoteName('bible_version') . ' IS NOT NULL')
                    ->where($db->quoteName('bible_version') . ' != ' . $db->quote(''))
                    ->group($db->quoteName('bible_version'));
                $db->setQuery($query);

                foreach ($db->loadObjectList() as $row) {
                    $usageCounts[$row->abbr] = (int) $row->cnt;
                }

                $query = $db->getQuery(true)
                    ->select($db->quoteName('bible_version2') . ' AS ' . $db->quoteName('abbr'))
                    ->select('COUNT(*) AS ' . $db->quoteName('cnt'))
                    ->from($db->quoteName('#__bsms_studies'))
                    ->where($db->quoteName('bible_version2') . ' IS NOT NULL')
                    ->where($db->quoteName('bible_version2') . ' != ' . $db->quote(''))
                    ->group($db->quoteName('bible_version2'));
                $db->setQuery($query);

                foreach ($db->loadObjectList() as $row) {
                    $usageCounts[$row->abbr] = ($usageCounts[$row->abbr] ?? 0) + (int) $row->cnt;
                }
            } catch (\Exception) {
                // bible_version columns may not exist yet — usage counts stay empty
            }

            // Sum total installed size from the cached column; attach usage counts
            $totalSize = 0;

            foreach ($translations as $t) {
                $totalSize += (int) ($t->data_size ?? 0);
                $t->usage_count  = $usageCounts[$t->abbreviation] ?? 0;
            }

            echo json_encode([
                'success'      => true,
                'translations' => $translations,
                'total_size'   => $totalSize,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Download and install a Bible translation locally.
     *
     * @return  void
     *
     * @since 10.1.0
     */
    public function downloadTranslationXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        session_write_close();

        $abbreviation = $app->getInput()->getCmd('abbreviation', '');

        if (empty($abbreviation)) {
            echo json_encode(['success' => false, 'message' => 'No abbreviation provided'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            // Downloading 66 books can take a while
            @set_time_limit(600);

            $count = BibleImporter::downloadAndImport($abbreviation);

            if ($count < 0) {
                echo json_encode([
                    'success' => false,
                    'message' => Text::sprintf('JBS_ADM_BIBLE_DOWNLOAD_FAILED', strtoupper($abbreviation)),
                ], JSON_THROW_ON_ERROR);
            } else {
                echo json_encode([
                    'success'     => true,
                    'verse_count' => $count,
                    'message'     => Text::sprintf('JBS_ADM_BIBLE_DOWNLOAD_SUCCESS', strtoupper($abbreviation), $count),
                ], JSON_THROW_ON_ERROR);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Remove a locally installed Bible translation.
     *
     * @return  void
     *
     * @since 10.1.0
     */
    public function removeTranslationXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        session_write_close();

        $abbreviation = $app->getInput()->getCmd('abbreviation', '');

        if (empty($abbreviation)) {
            echo json_encode(['success' => false, 'message' => 'No abbreviation provided'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            BibleImporter::removeTranslation($abbreviation);

            echo json_encode([
                'success' => true,
                'message' => Text::sprintf('JBS_ADM_BIBLE_REMOVED', strtoupper($abbreviation)),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Remove all installed translations and their verses.
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public function removeAllTranslationsXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        session_write_close();

        try {
            $count = BibleImporter::removeAllTranslations();

            echo json_encode([
                'success' => true,
                'message' => Text::sprintf('JBS_ADM_BIBLE_REMOVED_ALL', $count),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Sync translations from API.Bible using the configured API key.
     *
     * Fetches available Bibles from the API.Bible endpoint and upserts them
     * into the bible_translations table with source='api_bible'.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since  10.1.0
     */
    public function syncApiBibleTranslationsXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        session_write_close();

        try {
            $admin  = Cwmparams::getAdmin();
            $params = $admin->params;
            $apiKey = (string) $params->get('api_bible_api_key', '');

            if (empty($apiKey)) {
                echo json_encode([
                    'success' => false,
                    'message' => Text::_('JBS_ADM_API_BIBLE_KEY_DESC'),
                ], JSON_THROW_ON_ERROR);
                $app->close();

                return;
            }

            // Fetch available Bibles from API.Bible
            $http     = HttpFactory::getHttp();
            $response = $http->get(
                'https://rest.api.bible/v1/bibles',
                ['api-key' => $apiKey],
                30
            );

            if ($response->code !== 200) {
                // Parse API error message if available
                $apiError = '';
                $decoded  = json_decode($response->body ?? '', true);

                if (\is_array($decoded) && isset($decoded['message'])) {
                    $apiError = $decoded['message'];
                } elseif (\is_array($decoded) && isset($decoded['error'])) {
                    $apiError = $decoded['error'];
                }

                $detail = $apiError
                    ? Text::sprintf('JBS_ADM_SYNC_FAILED_DETAIL', $response->code, $apiError)
                    : Text::sprintf('JBS_ADM_SYNC_FAILED_CODE', $response->code);

                echo json_encode([
                    'success' => false,
                    'message' => $detail,
                ], JSON_THROW_ON_ERROR);
                $app->close();

                return;
            }

            $data = json_decode($response->body, true);

            if (!\is_array($data) || !isset($data['data'])) {
                // Log the actual response for debugging
                $snippet = substr($response->body ?? '', 0, 200);

                echo json_encode([
                    'success' => false,
                    'message' => Text::sprintf(
                        'JBS_ADM_SYNC_FAILED_DETAIL',
                        $response->code,
                        'Unexpected response format: ' . $snippet
                    ),
                ], JSON_THROW_ON_ERROR);
                $app->close();

                return;
            }

            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $count = 0;

            foreach ($data['data'] as $bible) {
                $bibleId     = $bible['id'] ?? '';
                $name        = $bible['name'] ?? ($bible['nameLocal'] ?? '');
                $abbr        = strtolower($bible['abbreviation'] ?? $bible['abbreviationLocal'] ?? '');
                $language    = $bible['language']['id'] ?? 'en';

                if (empty($bibleId) || empty($abbr) || empty($name)) {
                    continue;
                }

                // Truncate abbreviation to fit VARCHAR(20) column
                $abbr = substr($abbr, 0, 20);

                // Check if this abbreviation already exists with a different source
                $query = $db->getQuery(true)
                    ->select($db->quoteName(['id', 'source']))
                    ->from($db->quoteName('#__bsms_bible_translations'))
                    ->where($db->quoteName('abbreviation') . ' = :abbr')
                    ->bind(':abbr', $abbr);
                $db->setQuery($query);
                $existing = $db->loadObject();

                if ($existing && $existing->source !== 'api_bible') {
                    // Don't overwrite local/getbible entries
                    continue;
                }

                if ($existing) {
                    // Update existing api_bible entry
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__bsms_bible_translations'))
                        ->set($db->quoteName('name') . ' = :name')
                        ->set($db->quoteName('language') . ' = :lang')
                        ->set($db->quoteName('provider_id') . ' = :pid')
                        ->where($db->quoteName('id') . ' = ' . (int) $existing->id)
                        ->bind(':name', $name)
                        ->bind(':lang', $language)
                        ->bind(':pid', $bibleId);
                    $db->setQuery($query);
                    $db->execute();
                } else {
                    // Insert new entry
                    $source = 'api_bible';
                    $query  = $db->getQuery(true)
                        ->insert($db->quoteName('#__bsms_bible_translations'))
                        ->columns($db->quoteName(['abbreviation', 'name', 'language', 'source', 'provider_id']))
                        ->values(':abbr2, :name2, :lang2, :source2, :pid2')
                        ->bind(':abbr2', $abbr)
                        ->bind(':name2', $name)
                        ->bind(':lang2', $language)
                        ->bind(':source2', $source)
                        ->bind(':pid2', $bibleId);
                    $db->setQuery($query);
                    $db->execute();
                }

                $count++;
            }

            echo json_encode([
                'success' => true,
                'count'   => $count,
                'message' => Text::sprintf('JBS_ADM_SYNC_COMPLETE', $count),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => Text::sprintf(
                    'JBS_ADM_SYNC_FAILED_DETAIL',
                    0,
                    $e->getMessage()
                ),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Remove non-installed translation records from a provider.
     *
     * Called when a provider is disabled to clean up synced entries that
     * were never downloaded locally.
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public function cleanupProviderXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $source = $app->getInput()->getCmd('source', '');

        if (empty($source)) {
            echo json_encode(['success' => false, 'message' => 'No source provided'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $count = BibleImporter::removeProviderEntries($source);

            echo json_encode([
                'success' => true,
                'count'   => $count,
                'message' => Text::sprintf('JBS_ADM_PROVIDER_CLEANUP_DONE', $count),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Process a batch of CSV rows for import.
     *
     * Expects POST JSON with 'rows' (array of arrays), 'mappings' (column index => field),
     * and 'settings' (auto_create, default_published, duplicate_handling).
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since  10.1.0
     */
    public function csvImportBatchXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get') && !Session::checkToken()) {
            echo json_encode(['imported' => 0, 'skipped' => 0, 'errors' => [['row' => 0, 'field' => '', 'message' => Text::_('JINVALID_TOKEN')]]], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        // Read raw POST body (JSON)
        $rawInput = file_get_contents('php://input');
        $data     = json_decode($rawInput, true);

        if (!\is_array($data) || empty($data['rows'])) {
            echo json_encode(['imported' => 0, 'skipped' => 0, 'errors' => [['row' => 0, 'field' => '', 'message' => 'No rows provided']]], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $rows     = $data['rows'];
        $mappings = $data['mappings'] ?? [];
        $settings = $data['settings'] ?? [];

        try {
            $result = CwmcsvimportHelper::processBatch($rows, $mappings, $settings);
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'imported'     => 0,
                'skipped'      => 0,
                'errors'       => [['row' => 0, 'field' => '', 'message' => $e->getMessage()]],
                'auto_created' => [],
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Stream a CSV import template for download.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since  10.1.0
     */
    public function csvTemplateXHR(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('get')) {
            $app->setHeader('Content-Type', 'application/json');
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $csv = CwmcsvimportHelper::generateTemplate();

        $app->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $app->setHeader('Content-Disposition', 'attachment; filename="proclaim-import-template.csv"');
        $app->setHeader('Content-Length', (string) \strlen($csv));
        $app->sendHeaders();

        echo $csv;

        $app->close();
    }

    /**
     * AJAX: Scan legacy servers and classify media files by detected platform.
     *
     * Returns scan results + existing core servers for the configuration step.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function serverMigrationScanXHR(): void
    {
        session_write_close();
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $servers  = CwmserverMigrationHelper::scanLegacyServers();
            $existing = CwmserverMigrationHelper::getExistingServersByType();

            echo json_encode([
                'success'  => true,
                'servers'  => $servers,
                'existing' => $existing,
                'labels'   => CwmserverMigrationHelper::TYPE_LABELS,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Migrate a batch of media files from a legacy server to a target server.
     *
     * Expects POST JSON with: legacyServerId, detectedType, targetServerId,
     * targetType, offset, limit, legacyServerParams.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function serverMigrationBatchXHR(): void
    {
        session_write_close();
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get') && !Session::checkToken()) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $rawInput = file_get_contents('php://input');
        $data     = json_decode($rawInput, true);

        if (!\is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request body'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $legacyServerId     = (int) ($data['legacyServerId'] ?? 0);
        $detectedType       = $data['detectedType'] ?? '';
        $targetServerId     = (int) ($data['targetServerId'] ?? 0);
        $targetType         = $data['targetType'] ?? '';
        $offset             = (int) ($data['offset'] ?? 0);
        $limit              = (int) ($data['limit'] ?? 25);
        $legacyServerParams = $data['legacyServerParams'] ?? [];

        try {
            $ids    = CwmserverMigrationHelper::getLegacyMediaFileIds($legacyServerId, $detectedType, $offset, $limit);
            $result = CwmserverMigrationHelper::migrateMediaBatch($ids, $targetServerId, $targetType, $legacyServerParams);

            echo json_encode([
                'success'  => true,
                'migrated' => $result['migrated'],
                'errors'   => $result['errors'],
                'fetched'  => \count($ids),
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Create a new core server of a given type.
     *
     * Expects POST JSON with: type, name, locationId (optional).
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function serverMigrationCreateServerXHR(): void
    {
        session_write_close();
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get') && !Session::checkToken()) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $rawInput = file_get_contents('php://input');
        $data     = json_decode($rawInput, true);

        if (!\is_array($data) || empty($data['type']) || empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'Missing type or name'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $serverId = CwmserverMigrationHelper::createServerForType(
                $data['type'],
                $data['name'],
                isset($data['locationId']) ? (int) $data['locationId'] : null
            );

            echo json_encode([
                'success'  => true,
                'serverId' => $serverId,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Unpublish empty legacy servers after migration.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function serverMigrationCleanupXHR(): void
    {
        session_write_close();
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $result = CwmserverMigrationHelper::unpublishEmptyLegacyServers();

            echo json_encode([
                'success'     => true,
                'unpublished' => $result['unpublished'],
                'skipped'     => $result['skipped'],
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Detect whether a 9.x schema exists and return version + record counts.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function detectUpgradeXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $detection = CwmupgradeHelper::detect9xSchema();

            if ($detection['detected']) {
                $detection['meets_minimum'] = CwmupgradeHelper::meetsMinimumVersion($detection['version']);
                $detection['record_counts'] = CwmupgradeHelper::get9xInfo();
            }

            echo json_encode($detection, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'detected' => false,
                'error'    => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Create a safety backup before the upgrade.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function upgradeBackupXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $backup = new Cwmbackup();
            $result = $backup->exportdb(2);

            echo json_encode([
                'success'  => (bool) $result,
                'filename' => $result ? 'backup created' : '',
                'message'  => $result ? 'Backup completed' : 'Backup failed',
            ], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Convert INI parameters to JSON format.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function upgradeParamsXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $result            = CwmupgradeHelper::convertIniToJson();
            $result['success'] = true;
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'success'   => false,
                'converted' => 0,
                'message'   => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Reset schema version and run SQL migrations.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function upgradeSchemaXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $resetResult = CwmupgradeHelper::resetSchemaVersion();

            if (!$resetResult) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Could not reset schema version',
                ], JSON_THROW_ON_ERROR);
                $app->close();

                return;
            }

            $migrationResult = CwmupgradeHelper::runSchemaMigration();
            echo json_encode($migrationResult, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Run all data migration fixes.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function upgradeDataXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $result            = CwmupgradeHelper::runDataFixes();
            $result['success'] = empty($result['errors']);
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'steps'   => [],
                'errors'  => [$e->getMessage()],
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Rebuild ACL assets.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function upgradeAssetsXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $result = CwmupgradeHelper::rebuildAssets();
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX: Verify upgrade and clean up 9.x artifacts.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   10.1.0
     */
    public function upgradeVerifyXHR(): void
    {
        $app      = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $verifyResult = CwmupgradeHelper::verify();
            $dropped      = CwmupgradeHelper::cleanup9xArtifacts();

            $verifyResult['artifacts_dropped'] = $dropped;
            echo json_encode($verifyResult, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }
}
