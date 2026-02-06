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

use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmImageCleanup;
use CWM\Component\Proclaim\Administrator\Helper\CwmImageMigration;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Administrator\Lib\CwmpIconvert;
use CWM\Component\Proclaim\Administrator\Lib\Cwmrestore;
use CWM\Component\Proclaim\Administrator\Lib\Cwmssconvert;
use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
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
                $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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

        $db   = Factory::getContainer()->get('DatabaseDriver');
        $msg  = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        $post = $this->input->post->get('jform', [], 'array');
        $reg  = new Registry();
        $reg->loadArray($post['params']);
        $from = $reg->get('from', 'x');
        $to   = $reg->get('to', 'x');

        if ($from !== 'x' && $to !== 'x') {
            $query = $db->getQuery(true);
            $query->select('id, params')
                ->from('#__bsms_mediafiles');
            $db->setQuery($query);

            foreach ($db->loadObjectList() as $media) {
                $reg = new Registry();
                $reg->loadString($media->params);

                if ($reg->get('player', 0) == $from) {
                    $reg->set('player', $to);

                    $query = $db->getQuery(true);
                    $query->update('#__bsms_mediafiles')
                        ->set('params = ' . $db->q($reg->toString()))
                        ->where('id = ' . (int)$media->id);
                    $db->setQuery($query);

                    if (!$db->execute()) {
                        $msg = Text::_('JBS_ADM_ERROR_OCCURED');
                        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
                    }
                }
            }
        } else {
            $msg = Text::_('JBS_ADM_ERROR_OCCURED') . ': Missed setting the From or To';
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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

        $db   = Factory::getContainer()->get('DatabaseDriver');
        $post = $this->input->post->get('jform', [], 'array');
        $reg  = new Registry();
        $reg->loadArray($post['params']);
        $from  = $reg->get('pFrom', 'x');
        $form2 = '';
        $to    = $reg->get('pTo', 'x');
        $msg   = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        $query = $db->getQuery(true);
        $query->select('id, params')
            ->from('#__bsms_mediafiles');
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
                $query->update('#__bsms_mediafiles')
                    ->set('params = ' . $db->q($reg->toString()))
                    ->where('id = ' . (int)$media->id);
                $db->setQuery($query);

                if (!$db->execute()) {
                    $msg = Text::_('JBS_ADM_ERROR_OCCURED');
                    $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
                }
            }
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $query   = $db->getQuery(true);
        $query->select('id, params')
            ->from('#__bsms_mediafiles');
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
                            $query->update('#__bsms_mediafiles')
                                ->set('params = ' . $db->q($reg->toString()))
                                ->where('id = ' . (int)$media->id);
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
                            $query->update('#__bsms_mediafiles')
                                ->set('params = ' . $db->q($reg->toString()))
                                ->where('id = ' . (int)$media->id);
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
                            $query->update('#__bsms_mediafiles')
                                ->set('params = ' . $db->q($reg->toString()))
                                ->where('id = ' . (int)$media->id);
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
                            $query->update('#__bsms_mediafiles')
                                ->set('params = ' . $db->q($reg->toString()))
                                ->where('id = ' . (int)$media->id);
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

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $msg   = null;
        $query = $db->getQuery(true);
        $query->update('#__bsms_mediafiles')
            ->set('hits = ' . 0)
            ->where('hits != 0');
        $db->setQuery($query);

        if (!$db->execute()) {
            $msg = Text::_('JBS_ADM_ERROR_OCCURED');
        } else {
            $msg = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update('#__bsms_mediafiles')
            ->set('downloads = ' . 0)
            ->where('downloads != 0');
        $db->setQuery($query);

        if (!$db->execute()) {
            $msg = Text::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS');
        } else {
            $updated = $db->getAffectedRows();
            $msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update('#__bsms_mediafiles')
            ->set('plays = ' . 0)
            ->where('plays != 0');
        $db->setQuery($query);

        if (!$db->execute()) {
            $msg = Text::_('JBS_CMN_ERROR_RESETTING_PLAYS');
        } else {
            $updated = $db->getAffectedRows();
            $msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
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
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1');
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
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $ssconversion);
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
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $piconversion);
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
        $user = Factory::getApplication()->getSession()->get('user');

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
        $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', false));
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
        $db     = Factory::getContainer()->get('DatabaseDriver');
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
        $document     = $app->getDocument();
        $input        = $app->getInput();
        $images_paths = [];

        $document->setMimeEncoding('application/json');

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
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

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
     * @since version
     */
    public function submit(?int $key = null, ?string $urlVar = null): bool
    {
        $this->checkToken();

        $app   = Factory::getApplication();
        $model = $this->getModel('form');
        $form  = $model->getForm('', false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        // Name of an array 'jform' must match 'control' => 'jform' line in the model code
        $data = $this->input->post->get('jform', [], 'array');

        // This is validate() from the FormModel class, not the Form class
        // FormModel::validate() calls both Form::filter() and Form::validate() methods
        $validData = $model->validate($form, $data);

        if ($validData === false) {
            $errors = $model->getErrors();

            foreach ($errors as $error) {
                if ($error instanceof \Exception) {
                    $app->enqueueMessage($error->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($error, 'warning');
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
        $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmadmin&layout=edit', false));

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
        $document = $app->getDocument();

        $document->setMimeEncoding('application/json');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $counts = CwmImageMigration::getMigrationCounts();

        echo json_encode($counts, JSON_THROW_ON_ERROR);

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
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type  = $input->get('type', 'studies', 'string');
        $limit = $input->get('limit', 10, 'int');

        $batch = CwmImageMigration::getBatch($type, $limit);

        echo json_encode($batch, JSON_THROW_ON_ERROR);

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
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $type    = $input->get('type', '', 'string');
        $id      = $input->get('id', 0, 'int');
        $title   = $input->get('title', '', 'string');
        $oldPath = $input->get('old_path', '', 'string');

        if (empty($type) || empty($id) || empty($oldPath)) {
            echo json_encode([
                'success' => false,
                'error'   => 'Missing required parameters',
            ], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $result = CwmImageMigration::migrateRecord($type, $id, $title, $oldPath);

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
        $document = $app->getDocument();

        $document->setMimeEncoding('application/json');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $orphans = CwmImageCleanup::findOrphanedFolders();
        $totals  = CwmImageCleanup::getTotals($orphans);

        echo json_encode([
            'orphans' => $orphans,
            'totals'  => $totals,
        ], JSON_THROW_ON_ERROR);

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
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

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
        $document = $app->getDocument();

        $document->setMimeEncoding('application/json');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

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
        $document = $app->getDocument();

        $document->setMimeEncoding('application/json');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => Text::_('JINVALID_TOKEN')], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

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
        $document = $app->getDocument();

        $document->setMimeEncoding('application/json');

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
        $document = $app->getDocument();

        $document->setMimeEncoding('application/json');

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
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

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
            $db = Factory::getContainer()->get('DatabaseDriver');

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
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

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
            $db = Factory::getContainer()->get('DatabaseDriver');

            // Handle legacy value mapping (100 = 0, etc.)
            $searchFrom = $from;
            $searchFrom2 = null;

            if ($from === '100') {
                $searchFrom = '0';
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
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

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
            $db = Factory::getContainer()->get('DatabaseDriver');

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
}
