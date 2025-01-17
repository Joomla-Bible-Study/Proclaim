<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Administrator\Lib\CwmpIconvert;
use CWM\Component\Proclaim\Administrator\Lib\Cwmrestore;
use CWM\Component\Proclaim\Administrator\Lib\Cwmssconvert;
use CWM\Component\Proclaim\Administrator\Model\CwmarchiveModel;
use Exception;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

/**
 * Controller for Admin
 *
 * @since  7.0.0
 */
class CwmadminController extends FormController
{
    /**
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $view_list = 'cwmcpanel';

    /**
     * Tools to change player or popup
     *
     * @return void
     *
     * @throws  Exception
     * @since   7.0.0
     */
    public function tools(): void
    {
        $tool = Factory::getApplication()->input->get('tooltype', '', 'post');

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
     * @since 7.0.0
     */
    public function changePlayers(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $db   = Factory::getContainer()->get('DatabaseDriver');
        $msg  = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        $post = $_POST['jform'];
        $reg  = new Registry();
        $reg->loadArray($post['params']);
        $from = $reg->get('from', 'x');
        $to   = $reg->get('to', 'x');

        if ($from != 'x' && $to != 'x') {
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
            $msg = Text::_('JBS_ADM_ERROR_OCCURED') . ': Missed setting the From or Two';
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $msg);
    }

    /**
     * Change Media Popup
     *
     * @return void
     *
     * @since 7.0.0
     */
    public function changePopup(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $db   = Factory::getContainer()->get('DatabaseDriver');
        $post = $_POST['jform'];
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
     * Change media images from a digital file to css
     *
     * @return void
     *
     * @throws Exception
     * @since 7.0.0
     */
    public function mediaimages(): void
    {
        $post    = $_POST['jform'];
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
                        $query->update('#__bsms_mediafiles')
                            ->set('params = ' . $db->q($reg->toString()))
                            ->where('id = ' . (int)$media->id);

                        try {
                            $db->setQuery($query);
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
                        $query->update('#__bsms_mediafiles')
                            ->set('params = ' . $db->q($reg->toString()))
                            ->where('id = ' . (int)$media->id);

                        try {
                            $db->setQuery($query);
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
                        $query->update('#__bsms_mediafiles')
                            ->set('params = ' . $db->q($reg->toString()))
                            ->where('id = ' . (int)$media->id);
                        $db->setQuery($query);

                        try {
                            $db->setQuery($query);
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
     * @since 7.0.0
     */
    public function resetHits(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
     * @since 7.0.0
     */
    public function resetDownloads(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
     * @since 7.0.0
     */
    public function resetPlays(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
     * @since 7.0.0
     */
    public function convertSermonSpeaker(): void
    {
        // Check for request forgeries.
        Session::checkToken('get') || Session::checkToken() || jexit(Text::_('JINVALID_TOKEN'));

        $convert      = new Cwmssconvert();
        $ssconversion = $convert->convertSS();
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $ssconversion);
    }

    /**
     * Convert PreachIt to BibleStudy
     *
     * @return void
     *
     * @since 7.0.0
     */
    public function convertPreachIt(): void
    {
        // Check for request forgeries.
        Session::checkToken('get') || Session::checkToken() || jexit(Text::_('JINVALID_TOKEN'));

        $convert      = new CwmpIconvert();
        $piconversion = $convert->convertPI();
        $this->setRedirect('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', $piconversion);
    }

    /**
     * Reset Db to install
     *
     * @return void
     *
     * @throws  Exception
     * @since   7.1.0
     */
    public function dbReset(): void
    {
        $user = Factory::getApplication()->getSession()->get('user');

        if (array_key_exists(8, $user->groups)) {
            CwmdbHelper::resetdb();
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_proclaim&view=cwmassats&task=cwmassets.browse&' . Session::getFormToken(
                    ) . '=1',
                    false
                )
            );
        } else {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'eroor');
            $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmcpanel', false));
        }
    }

    /**
     * Alias Updates
     *
     * @return void
     *
     * @since 7.1.0
     */
    public function aliasUpdate(): void
    {
        // Check for request forgeries.
        Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

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
     * @throws  Exception
     * @since   7.0.0
     */
    public function doimport(bool $parent = true): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $alt         = '';

        // This should be where the form administrator/form_migrate comes to with either the file select box or the tmp folder input field
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $input->set('view', $input->get('view', 'administrator', 'cmd'));

        // Add commands to move tables from old prefix to new
        $oldprefix = $input->get('oldprefix', '', 'string');

        if ($oldprefix) {
            if (!($this->copyTables($oldprefix))) {
                $app->enqueueMessage(Text::_('JBS_CMN_DATABASE_NOT_COPIED'), 'worning');
            }
        } else {
            $import = new Cwmrestore();
            $import->importdb($parent);
            $alt    = '&cwmalt=1';
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwminstall&scanstate=start&cwmimport=1' . $alt);
    }

    /**
     * Copy Old Tables to new Joomla! Tables
     *
     * @param   string  $oldprefix  Old table Prefix
     *
     * @return bool
     *
     * @throws  Exception
     * @since   7.0.0
     */
    public function copyTables(string $oldprefix): bool
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Create table tablename_new like tablename; -> this will copy the structure...
        // Insert into tablename_new select * from tablename; -> this would copy all the data
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();

        foreach ($tables as $table) {
            $isjbs = substr_count($table, $oldprefix . 'bsms');

            if ($isjbs) {
                $oldlength       = strlen($oldprefix);
                $newsubtablename = substr($table, $oldlength);
                $newtablename    = $prefix . $newsubtablename;
                $query           = 'DROP TABLE IF EXISTS ' . $newtablename;

                if (!CwmdbHelper::performDB($query)) {
                    return false;
                }

                $query = 'CREATE TABLE ' . $newtablename . ' LIKE ' . $table;

                if (!CwmdbHelper::performDB($query)) {
                    return false;
                }

                $query = 'INSERT INTO ' . $newtablename . ' SELECT * FROM ' . $table;

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
     * @throws  Exception
     * @since   7.1.0
     */
    public function import(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
     * @throws Exception
     * @since 7.0.0
     */
    public function export(): void
    {
        // Check for request forgeries.
        Session::checkToken('get') || Session::checkToken() || jexit(Text::_('JINVALID_TOKEN'));

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
     * @throws Exception
     *
     * @since 9.0.0
     */
    public function getThumbnailListXHR(): void
    {
        $app          = Factory::getApplication();
        $document     = $app->getDocument();
        $input        = $app->getInput();
        $images_paths = array();

        $document->setMimeEncoding('application/json');

        $image_types = $input->get('images', null, 'array');
        $count       = 0;

        foreach ($image_types as $image_type) {
            $images = Folder::files(JPATH_ROOT . '/images/biblestudy/' . $image_type, 'original_', true, true);

            if ($images) {
                $count += count($images);
            }

            $images_paths[] = array(array('type' => $image_type, 'images' => $images));
        }

        echo json_encode(array('total' => $count, 'paths' => $images_paths), JSON_THROW_ON_ERROR);

        $app->close();
    }

    /**
     * Create Thumbnail XHR
     *
     * @return void
     *
     * @throws Exception
     *
     * @since 9.0.0
     */
    public function createThumbnailXHR(): void
    {
        $app      = Factory::getApplication();
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

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
     * @since 9.0.1
     */
    public function doArchive(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $model = new CwmarchiveModel();
        try {
            $msg = $model->doArchive();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
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
     * @throws Exception
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
        $data = $this->input->post->get('jform', array(), 'array');

        // This is validate() from the FormModel class, not the Form class
        // FormModel::validate() calls both Form::filter() and Form::validate() methods
        $validData = $model->validate($form, $data);

        if ($validData === false) {
            $errors = $model->getErrors();

            foreach ($errors as $error) {
                if ($error instanceof Exception) {
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
}
