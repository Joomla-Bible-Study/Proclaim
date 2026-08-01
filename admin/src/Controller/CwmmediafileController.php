<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Controller\Trait\MultiCampusAccessTrait;
use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmcaptionValidator;
use CWM\Component\Proclaim\Administrator\Table\CwmmediafileTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Controller For MediaFile
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafileController extends FormController
{
    use MultiCampusAccessTrait;

    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var string
     * @since 7.0
     */
    protected $view_list = 'cwmmediafiles';

    /**
     * The URL option for the component.
     *
     * @var    string
     * @since  7.0.0
     */
    protected $option = 'com_proclaim';

    /**
     * The database table for access level checks.
     *
     * @var    string
     * @since  10.3.0
     */
    protected string $accessTable = '#__bsms_mediafiles';

    /**
     * Method to add a new record.
     *
     * @return  bool  True, if the record can be added, a error object if not.
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    public function add(): bool
    {
        $app = Factory::getApplication();

        if (parent::add()) {
            $app->setUserState('com_proclaim.edit.mediafile.createdate', null);
            $app->setUserState('com_proclaim.edit.mediafile.study_id', null);
            $app->setUserState('com_proclaim.edit.mediafile.server_id', null);

            return true;
        }

        return false;
    }

    /**
     * Resets the User state for the server type. Needed to allow the value from the DB to be used
     *
     * @param   int     $key     ?
     * @param   string  $urlVar  ?
     *
     * @return  bool
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function edit($key = null, $urlVar = null): bool
    {
        $app    = Factory::getApplication();
        $result = parent::edit();

        if ($result) {
            $app->setUserState('com_proclaim.edit.mediafile.createdate', null);
            $app->setUserState('com_proclaim.edit.mediafile.study_id', null);
            $app->setUserState('com_proclaim.edit.mediafile.server_id', null);
        }

        return $result;
    }

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
        $denied = $this->checkRecordAccessLevel((int) ($data[$key] ?? 0));
        if ($denied === false) {
            return false;
        }

        return parent::allowEdit($data, $key);
    }

    /**
     * Handles XHR requests (i.e. File uploads)
     *
     * @return void
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function xhr(): void
    {
        if (!Session::checkToken('get')) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmediafiles', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }
        $input = Factory::getApplication()->getInput();

        $addonType = $input->get('type', 'Legacy', 'string');
        $handler   = $input->get('handler');

        // Load the addon
        $addon = CWMAddon::getInstance($addonType);

        if (method_exists($addon, $handler)) {
            echo json_encode($addon->$handler($input), JSON_THROW_ON_ERROR);

            $app = Factory::getApplication();
            $app->close();
        } else {
            throw new \RuntimeException(Text::sprintf('Handler: "%s" does not exist!', htmlspecialchars($handler, ENT_QUOTES, 'UTF-8')), 404);
        }
    }

    /**
     * Method to run batch operations.
     *
     * @param   CwmmediafileModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        $this->checkToken();

        if (!$model) {
            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel $model */
            $model = $this->getModel('Cwmmediafile', 'Administrator', []);
        }

        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmmediafiles' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($model);
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  bool  True if access level checks pass, false otherwise.
     *
     * @throws \Exception
     * @since   7.0.0
     */
    public function cancel($key = null): bool
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmediafiles', Text::_('JINVALID_TOKEN'), 'error');

            return false;
        }

        $app   = Factory::getApplication();
        $model = $this->getModel();
        /** @type CwmmediafileTable $table */
        $table   = $model->getTable();
        $checkin = property_exists($table, 'checked_out');

        if (empty($key)) {
            $key = (string)$table->getKeyName();
        }

        $recordId = $app->getInput()->getInt($key);

        // Attempt to check in the current record.
        if ($recordId) {
            if ($checkin) {
                if ($model->checkin($recordId) === false) {
                    // Check-in failed, go back to the record and display a notice.
                    $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', ''), 'error');

                    $this->setRedirect(
                        Route::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend($recordId, $key),
                            false
                        )
                    );

                    return false;
                }
            }
        }

        $return = $this->input->getCmd('return');

        if ($return && parent::cancel($key)) {
            $decoded = base64_decode($return);

            if ($decoded && Uri::isInternal($decoded)) {
                $this->setRedirect($decoded);
            }

            return true;
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

        return false;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   int     $recordId  The primary key ID for the item.
     * @param   string  $urlVar    The name of the URL variable for the ID.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   7.0.0
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id'): string
    {
        $tmpl    = $this->input->get('tmpl');
        $layout  = $this->input->get('layout', 'edit', 'string');
        $return  = $this->input->getCmd('return');
        $options = $this->input->get('options');
        $append  = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($recordId) {
            $append .= '&' . $urlVar . '=' . $recordId;
        }

        if ($options) {
            $append .= '&options=' . $options;
        }

        if ($return) {
            $append .= '&return=' . $return;
        }

        return $append;
    }

    /**
     * Return addon HTML fragments via AJAX for a given server_id.
     *
     * Called via GET with token validation. Returns JSON with generalHtml
     * and optionsHtml for the selected server's addon.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function getAddonHtml(): void
    {
        CWMAddon::prepareAjaxEnvironment();

        try {
            if (!Session::checkToken('get')) {
                CWMAddon::outputJson(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            }

            $app      = Factory::getApplication();
            $serverId = $app->getInput()->getInt('server_id', 0);

            if (empty($serverId)) {
                CWMAddon::outputJson(['success' => false, 'error' => 'No server_id provided']);
            }

            // Set server_id in user state so the model picks it up via populateState()
            $app->setUserState('com_proclaim.edit.mediafile.server_id', $serverId);

            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel $model */
            $model = $this->getModel('Cwmmediafile', 'Administrator', []);

            // getItem() populates model->data including server_id from state
            $model->getItem();

            // getMediaForm() loads form paths, language, and returns the Joomla Form
            $mediaForm = $model->getMediaForm();

            if (empty($mediaForm)) {
                CWMAddon::outputJson(['success' => false, 'error' => 'Could not load media form']);
            }

            $serverType = $model->getState('type');
            $sParams    = $model->getState('s_params', []);

            // Wrap form with server params (same pattern as HtmlView::display)
            $wrappedForm = new class ($mediaForm, $sParams) {
                private $form;
                public array $s_params;

                public function __construct($form, array $s_params)
                {
                    $this->form     = $form;
                    $this->s_params = $s_params;
                }

                public function __call(string $name, array $args): mixed
                {
                    return $this->form->$name(...$args);
                }
            };

            // Bind server defaults for new items
            $mediaForm->bind(['params' => $sParams]);

            // Instantiate addon and render HTML
            $addon       = CWMAddon::getInstance($serverType);
            $generalHtml = $addon->renderGeneral($wrappedForm, true);
            $optionsHtml = $addon->renderOptionsFields($wrappedForm, true);

            CWMAddon::outputJson([
                'success'     => true,
                'generalHtml' => $generalHtml,
                'optionsHtml' => $optionsHtml,
                'serverType'  => $serverType,
                // The playlist picker lives outside the swapped containers, so the
                // capability has to travel with the response for the client to
                // show or hide it on a server change. See #1392.
                'supportsPlaylists' => $addon->supportsPlaylists(),
            ]);
        } catch (\Exception $e) {
            CWMAddon::outputJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Sets the server for this media record
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function setServer(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmediafiles', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $app   = Factory::getApplication();
        $input = $app->getInput();

        $data      = $input->get('jform', [], 'post', 'array');
        $cdate     = $data['createdate'];
        $study_id  = $data['study_id'];
        $server_id = $data['server_id'];

        // Save server in the session
        $app->setUserState('com_proclaim.edit.mediafile.createdate', $cdate);
        $app->setUserState('com_proclaim.edit.mediafile.study_id', $study_id);
        $app->setUserState('com_proclaim.edit.mediafile.server_id', $server_id);

        $redirect = $this->getRedirectToItemAppend($data['id']);
        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $redirect, false)
        );
    }

    /**
     * Save chapters to a media file's params via AJAX.
     *
     * Called from the message edit page when applying AI-suggested or
     * YouTube-imported chapters to a media file.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.2.0
     */
    public function saveChapters(): void
    {
        CWMAddon::prepareAjaxEnvironment();

        if (!Session::checkToken('get') && !Session::checkToken()) {
            CWMAddon::outputJson(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);

            return;
        }

        $app     = Factory::getApplication();
        $mediaId = $app->getInput()->getInt('media_id', 0);

        if (!$mediaId) {
            CWMAddon::outputJson(['success' => false, 'error' => 'No media_id provided']);

            return;
        }

        // Parse chapters from POST body (JSON)
        $rawBody = file_get_contents('php://input');

        try {
            $payload  = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            $chapters = $payload['chapters'] ?? [];
        } catch (\JsonException) {
            CWMAddon::outputJson(['success' => false, 'error' => 'Invalid JSON body']);

            return;
        }

        if (empty($chapters) || !\is_array($chapters)) {
            CWMAddon::outputJson(['success' => false, 'error' => 'No chapters provided']);

            return;
        }

        // Sanitize and compute seconds for each chapter
        $clean = [];

        foreach ($chapters as $ch) {
            $ch    = (array) $ch;
            $time  = preg_replace('/[^\d:]/', '', $ch['time'] ?? '0:00');
            $label = trim(strip_tags($ch['label'] ?? ''));

            if (empty($time) || empty($label)) {
                continue;
            }

            $clean[] = [
                'time'    => $time,
                'seconds' => \CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel::timeToSeconds($time),
                'label'   => $label,
            ];
        }

        if (empty($clean)) {
            CWMAddon::outputJson(['success' => false, 'error' => 'No valid chapters after sanitization']);

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('id') . ' = ' . (int) $mediaId);
        $db->setQuery($query);
        $paramsJson = $db->loadResult();

        if ($paramsJson === null) {
            CWMAddon::outputJson(['success' => false, 'error' => 'Media file not found']);

            return;
        }

        $params = new \Joomla\Registry\Registry($paramsJson ?: '{}');
        $params->set('chapters', $clean);

        $update = $db->createQuery()
            ->update($db->quoteName('#__bsms_mediafiles'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('id') . ' = ' . (int) $mediaId);
        $db->setQuery($update);
        $db->execute();

        CWMAddon::outputJson(['success' => true, 'count' => \count($clean)]);
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param   BaseModel  $model      The data model object.
     * @param   array      $validData  The validated data.
     *
     * @return    void
     *
     * @throws   \Exception
     * @since    3.1
     */
    protected function postSaveHook($model, $validData = []): void
    {
        $mediaId = (int) $model->getState('cwmmediafile.id');
        $isNew   = empty($validData['id']);
        $key     = $isNew ? 'COM_PROCLAIM_ACTION_LOG_ITEM_ADDED' : 'COM_PROCLAIM_ACTION_LOG_ITEM_UPDATED';
        $title   = $validData['params']['filename'] ?? ('#' . $mediaId);

        CwmactionlogHelper::log($key, $title, 'mediafile', $mediaId);

        $return = $this->input->getCmd('return');
        $task   = $this->input->get('task');

        if ($return && $task !== 'apply') {
            $decoded = base64_decode($return);

            if ($decoded && Uri::isInternal($decoded)) {
                Factory::getApplication()->enqueueMessage(Text::_('JBS_MED_SAVE'), 'message');
                $this->setRedirect($decoded);
            }
        }
    }

    /**
     * AJAX endpoint: upload a VTT/SRT caption file.
     *
     * Accepts a single file via multipart POST, validates the extension,
     * and stores it in media/com_proclaim/captions/. Returns the public
     * URL to the uploaded file.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.2.0
     */
    public function uploadVttXHR(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get') && !Session::checkToken()) {
            echo json_encode(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            Factory::getApplication()->close();

            return;
        }

        $input    = Factory::getApplication()->getInput();
        $userfile = $input->files->get('vttfile', null, 'raw');

        if (!\is_array($userfile) || $userfile['error'] || $userfile['size'] < 1) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_VTT_UPLOAD_FAILED')]);
            Factory::getApplication()->close();

            return;
        }

        $validator = new CwmcaptionValidator();
        $ext       = strtolower(pathinfo($userfile['name'], PATHINFO_EXTENSION));

        // Plain-text transcripts belong on the Message entity's transcript
        // field, not in the captions directory. Return a redirect hint so
        // the JS can scroll the user to the right field.
        if ($validator->isTranscriptExtension($ext)) {
            echo json_encode([
                'success'  => false,
                'error'    => Text::_('JBS_MED_VTT_USE_TRANSCRIPT_FIELD'),
                'redirect' => 'transcript',
            ]);
            Factory::getApplication()->close();

            return;
        }

        if (!$validator->isAllowedExtension($ext)) {
            echo json_encode([
                'success' => false,
                'error'   => Text::sprintf('JBS_MED_VTT_INVALID_TYPE', implode(', ', CwmcaptionValidator::ALLOWED_EXTENSIONS)),
            ]);
            Factory::getApplication()->close();

            return;
        }

        if (!$validator->isAllowedSize((int) $userfile['size'])) {
            echo json_encode([
                'success' => false,
                'error'   => Text::sprintf('JBS_MED_VTT_FILE_TOO_LARGE', '2 MB'),
            ]);
            Factory::getApplication()->close();

            return;
        }

        // Sniff the first 64 bytes to confirm WEBVTT, SBV, or SRT magic —
        // defends against a renamed binary slipping past the extension whitelist.
        $head     = file_get_contents($userfile['tmp_name'], false, null, 0, 64);
        $detected = $head === false ? null : $validator->detectFormat($head);

        if ($detected === null) {
            echo json_encode([
                'success' => false,
                'error'   => Text::_($head === false ? 'JBS_MED_VTT_UPLOAD_FAILED' : 'JBS_MED_VTT_INVALID_CONTENT'),
            ]);
            Factory::getApplication()->close();

            return;
        }

        $destDir = JPATH_ROOT . '/media/com_proclaim/captions';

        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_VTT_UPLOAD_FAILED')]);
            Factory::getApplication()->close();

            return;
        }

        // Every stored caption ends in `.vtt` — VTT files are moved in place
        // (zero-copy), SBV and SRT are converted to WebVTT on store so the
        // on-site player consumes one canonical format without a runtime shim.
        $fileName = $validator->buildFilename($userfile['name'], 'vtt');
        $destPath = $destDir . '/' . $fileName;

        $stored = match ($detected) {
            'vtt' => move_uploaded_file($userfile['tmp_name'], $destPath),
            'srt' => ($body = file_get_contents($userfile['tmp_name'])) !== false
                && file_put_contents($destPath, $validator->convertSrtToVtt($body)) !== false,
            'sbv' => ($body = file_get_contents($userfile['tmp_name'])) !== false
                && file_put_contents($destPath, $validator->convertSbvToVtt($body)) !== false,
        };

        if (!$stored) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_VTT_UPLOAD_FAILED')]);
            Factory::getApplication()->close();

            return;
        }

        $url = Uri::root() . 'media/com_proclaim/captions/' . $fileName;

        echo json_encode(['success' => true, 'url' => $url, 'filename' => $fileName]);
        Factory::getApplication()->close();
    }

    /**
     * Store a WebVTT body in the captions directory and return the
     * publicly-accessible URL and stored filename.
     *
     * Mirrors the storage half of {@see uploadVttXHR()} so other endpoints
     * (e.g. {@see generateCaptionsFromTranscript()}) can land caption
     * bytes through the same canonical pipeline without re-implementing
     * the directory + filename + write dance.
     *
     * @param   string  $vttBody       Already-converted WebVTT bytes.
     * @param   string  $originalName  Filename hint for sanitization;
     *                                 used only for the slug portion of
     *                                 the stored name.
     *
     * @return  array{url: string, filename: string}
     *
     * @throws  \RuntimeException  When the destination directory cannot be
     *                             created or the file write fails.
     *
     * @since   10.3.3
     */
    private function storeVttBody(string $vttBody, string $originalName): array
    {
        $destDir = JPATH_ROOT . '/media/com_proclaim/captions';

        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new \RuntimeException(Text::_('JBS_MED_VTT_UPLOAD_FAILED'));
        }

        $validator = new CwmcaptionValidator();
        $fileName  = $validator->buildFilename($originalName, 'vtt');
        $destPath  = $destDir . '/' . $fileName;

        if (file_put_contents($destPath, $vttBody) === false) {
            throw new \RuntimeException(Text::_('JBS_MED_VTT_UPLOAD_FAILED'));
        }

        return [
            'url'      => Uri::root() . 'media/com_proclaim/captions/' . $fileName,
            'filename' => $fileName,
        ];
    }

    /**
     * Stream a stored caption back to the browser in the requested format.
     *
     * Reads a stored `.vtt` caption file and converts it on the fly to VTT,
     * SRT, or SBV. Defense against path traversal is layered: the filename
     * input is matched against the same whitelist regex used at upload
     * (`/^caption_\d+_[A-Za-z0-9_-]+\.vtt$/`) and then `realpath()` is
     * confined to the captions directory before any file read.
     *
     * Query string:
     *  - `filename`  required, must match the upload-time filename shape
     *  - `format`    required, one of `vtt|srt|sbv`
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function downloadCaption(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $input    = Factory::getApplication()->getInput();
        $filename = (string) $input->getString('filename', '');
        $format   = strtolower((string) $input->getCmd('format', ''));

        if (!preg_match('/^caption_\d+_[A-Za-z0-9_-]+\.vtt$/', $filename)) {
            throw new \RuntimeException(Text::_('JBS_MED_CAPTION_NOT_FOUND'), 404);
        }

        if (!\in_array($format, ['vtt', 'srt', 'sbv'], true)) {
            throw new \RuntimeException(Text::_('JBS_MED_CAPTION_INVALID_FORMAT'), 400);
        }

        $captionsDir = JPATH_ROOT . '/media/com_proclaim/captions';
        $dirReal     = realpath($captionsDir);
        $fileReal    = realpath($captionsDir . '/' . $filename);

        if ($dirReal === false || $fileReal === false || !str_starts_with($fileReal, $dirReal . \DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException(Text::_('JBS_MED_CAPTION_NOT_FOUND'), 404);
        }

        $vtt = file_get_contents($fileReal);

        if ($vtt === false) {
            throw new \RuntimeException(Text::_('JBS_MED_CAPTION_NOT_FOUND'), 404);
        }

        $validator = new CwmcaptionValidator();

        $body = match ($format) {
            'vtt' => $vtt,
            'srt' => $validator->convertVttToSrt($vtt),
            'sbv' => $validator->convertVttToSbv($vtt),
        };

        $mime = match ($format) {
            'vtt' => 'text/vtt; charset=utf-8',
            'srt' => 'application/x-subrip; charset=utf-8',
            'sbv' => 'text/plain; charset=utf-8',
        };

        $base       = pathinfo($filename, PATHINFO_FILENAME);
        $outputName = $base . '.' . $format;

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $outputName . '"');
        header('Content-Length: ' . \strlen($body));

        echo $body;
        Factory::getApplication()->close();
    }

    /**
     * Generate captions for a YouTube-hosted media file from its parent
     * Message's transcript field.
     *
     * Pipeline: load media file → load linked study → fetch server's OAuth
     * client → upload transcript with sync=true → poll captions.list → download
     * the time-coded VTT → store via {@see storeVttBody()}. Returns the same
     * JSON response shape as {@see uploadVttXHR()} so the caption upload UI
     * can render the result without distinguishing source.
     *
     * Synchronous because the user explicitly clicked a "this may take a
     * minute" button — Joomla's request thread is acceptable for the
     * 30–90 second wait. If sermon length pushes past 90s, the addon
     * throws a TIMEOUT message pointing the user at YouTube Studio.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function generateCaptionsFromTranscript(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get') && !Session::checkToken()) {
            echo json_encode(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            Factory::getApplication()->close();

            return;
        }

        $input         = Factory::getApplication()->getInput();
        $mediaFileId   = (int) $input->getInt('id', 0);
        $language      = (string) $input->getCmd('language', 'en');

        if ($mediaFileId <= 0) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_GENERATE_CAPTIONS_NO_TRANSCRIPT')]);
            Factory::getApplication()->close();

            return;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Load the media file alongside its parent study's transcript in one
        // query — the join keeps us from fetching the whole study row twice.
        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.server_id'),
                $db->quoteName('m.params', 'media_params'),
                $db->quoteName('s.transcript'),
            ])
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->join('LEFT', $db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id'))
            ->where($db->quoteName('m.id') . ' = ' . $mediaFileId);
        $db->setQuery($query);

        $row = $db->loadAssoc();

        if (!$row) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_CAPTION_NOT_FOUND')]);
            Factory::getApplication()->close();

            return;
        }

        $transcript = trim((string) ($row['transcript'] ?? ''));

        if ($transcript === '') {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_GENERATE_CAPTIONS_NO_TRANSCRIPT')]);
            Factory::getApplication()->close();

            return;
        }

        // Extract the YouTube video id from the media file's stored params.
        // The addon owns the parsing rules so multiple URL shapes are handled.
        $mediaParams = new \Joomla\Registry\Registry((string) ($row['media_params'] ?? '{}'));
        $videoUrl    = (string) $mediaParams->get('filename', '');
        $videoId     = \CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube::extractYoutubeVideoId($videoUrl);

        if ($videoId === null) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_GENERATE_CAPTIONS_NOT_OWNED')]);
            Factory::getApplication()->close();

            return;
        }

        $serverId = (int) ($row['server_id'] ?? 0);

        try {
            /** @var \CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube $addon */
            $addon  = CWMAddon::getInstance('Youtube');
            $vtt    = $addon->generateCaptionsFromTranscript($serverId, $videoId, $transcript, $language);
            $stored = $this->storeVttBody($vtt, 'youtube-' . $videoId . '.vtt');
        } catch (\RuntimeException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            Factory::getApplication()->close();

            return;
        }

        echo json_encode([
            'success'  => true,
            'url'      => $stored['url'],
            'filename' => $stored['filename'],
        ]);
        Factory::getApplication()->close();
    }
}
