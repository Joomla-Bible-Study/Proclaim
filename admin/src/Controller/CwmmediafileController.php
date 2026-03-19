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
use CWM\Component\Proclaim\Administrator\Table\CwmmediafileTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
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
        $query = $db->getQuery(true)
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

        $update = $db->getQuery(true)
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

        // Validate file extension
        $allowedExt = ['vtt', 'srt'];
        $ext        = strtolower(pathinfo($userfile['name'], PATHINFO_EXTENSION));

        if (!\in_array($ext, $allowedExt, true)) {
            echo json_encode([
                'success' => false,
                'error'   => Text::sprintf('JBS_MED_VTT_INVALID_TYPE', implode(', ', $allowedExt)),
            ]);
            Factory::getApplication()->close();

            return;
        }

        // Max 2 MB for caption files
        $maxSize = 2 * 1024 * 1024;

        if ($userfile['size'] > $maxSize) {
            echo json_encode([
                'success' => false,
                'error'   => Text::sprintf('JBS_MED_VTT_FILE_TOO_LARGE', '2 MB'),
            ]);
            Factory::getApplication()->close();

            return;
        }

        // Validate content — must start with WEBVTT header or SRT numeric cue index
        $head = file_get_contents($userfile['tmp_name'], false, null, 0, 64);

        if ($head === false) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_VTT_UPLOAD_FAILED')]);
            Factory::getApplication()->close();

            return;
        }

        // Strip BOM if present
        $head  = ltrim($head, "\xEF\xBB\xBF");
        $isVtt = str_starts_with(trim($head), 'WEBVTT');
        $isSrt = (bool) preg_match('/^\d+\s*\r?\n\d{2}:\d{2}/', trim($head));

        if (!$isVtt && !$isSrt) {
            echo json_encode([
                'success' => false,
                'error'   => Text::_('JBS_MED_VTT_INVALID_CONTENT'),
            ]);
            Factory::getApplication()->close();

            return;
        }

        // Ensure destination directory exists
        $destDir = JPATH_ROOT . '/media/com_proclaim/captions';

        if (!is_dir($destDir)) {
            Folder::create($destDir);
        }

        // Build safe filename: caption_{timestamp}_{sanitised-original}.ext
        $baseName = File::makeSafe(pathinfo($userfile['name'], PATHINFO_FILENAME));
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', $baseName) ?: 'caption';
        $fileName = 'caption_' . time() . '_' . mb_substr($baseName, 0, 50) . '.' . $ext;

        $destPath = $destDir . '/' . $fileName;

        if (!File::upload($userfile['tmp_name'], $destPath, false, true)) {
            echo json_encode(['success' => false, 'error' => Text::_('JBS_MED_VTT_UPLOAD_FAILED')]);
            Factory::getApplication()->close();

            return;
        }

        // Return the public URL
        $url = Uri::root() . 'media/com_proclaim/captions/' . $fileName;

        echo json_encode(['success' => true, 'url' => $url, 'filename' => $fileName]);
        Factory::getApplication()->close();
    }
}
