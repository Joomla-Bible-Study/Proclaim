<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Table\CwmmediafileTable;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * MediaFile model class
 *
 * @property mixed _id
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafileModel extends AdminModel
{
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_proclaim.mediafile';
    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'com_proclaim';
    /**
     * Data
     *
     * @var object
     * @since   9.0.0
     */
    private object $data;


    /**
     * Allowed batch commands
     *
     * @var array
     * @since 10.0.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'player'        => 'batchPlayer',
        'linkType'      => 'batchLinkType',
        'mimetype'      => 'batchMimetype',
        'mediaType'     => 'batchMediatype',
        'popup'         => 'batchPopup',
    ];

    /**
     * Method to move a mediafile listing
     *
     * @param   string  $direction  ?
     *
     * @access    public
     * @return    bool    True on success
     *
     * @throws \Exception
     * @since     1.5
     */
    public function move(string $direction): bool
    {
        $row = $this->getTable();

        if (!$row->load($this->_id)) {
            return false;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        if (!$row->move($direction, $db->qn('study_id') . ' = ' . (int)$row->study_id . ' AND ' . $db->qn('published') . ' >= 0')) {
            return false;
        }

        return true;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  \Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmmediafile', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Overrides the AdminModel save routine to implode the podcast_id
     *
     * @param   array  $data  The form data.
     *
     * @return  bool True on successful save
     *
     * @since   7.0
     */
    public function save($data): bool
    {
        if ($data) {
            // Implode only if they selected at least one podcast. Otherwise, just clear the podcast_id field
            $data['podcast_id'] = empty($data['podcast_id']) ? '' : implode(",", $data['podcast_id']);

            $params = new Registry();
            $params->loadArray($data['params']);

            $table = Factory::getApplication()->bootComponent('com_proclaim')
                ->getMVCFactory()->createTable('Cwmserver', 'Administrator');
            $table->load($data['server_id']);

            $path = new Registry();
            $path->loadString($table->params);
            $set_path = '';

            if ($path->get('path')) {
                $set_path = $path->get('path') . '/';
            }

            // Auto-detect missing metadata (size, MIME type, duration)
            $this->autoDetectMetadata($params, $table, $set_path, $path);

            $data['params'] = $params->toArray();

            if (parent::save($data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Auto-detect missing metadata for a media file.
     * Detects file size, MIME type, and duration when not already set.
     *
     * @param   Registry  $params    Media file params (modified in place)
     * @param   object    $server    Server table object
     * @param   string    $set_path  Server path prefix
     * @param   Registry  $path      Server params
     *
     * @return  void
     *
     * @since   10.2.0
     */
    protected function autoDetectMetadata(Registry $params, object $server, string $set_path, Registry $path): void
    {
        $filename = $params->get('filename');
        if (empty($filename)) {
            return;
        }

        $jbspodcast = new Cwmpodcast();

        // Check what needs to be detected
        $needsSize     = empty($params->get('size', 0)) || (int) $params->get('size', 0) < 1000;
        $needsMimeType = empty($params->get('mime_type'));
        $hours         = $params->get('media_hours', '00');
        $minutes       = $params->get('media_minutes', '00');
        $seconds       = $params->get('media_seconds', '00');
        $needsDuration = ($hours === '00' && $minutes === '00' && $seconds === '00');

        // Nothing to detect
        if (!$needsSize && !$needsMimeType && !$needsDuration) {
            return;
        }

        // Check if this is a YouTube URL
        if ($jbspodcast->isYouTubeUrl($filename)) {
            $this->detectYouTubeMetadata($params, $filename, $jbspodcast, $needsDuration);
            return;
        }

        // Determine if file is local or remote
        $isLocalFilename = str_starts_with($filename, '/')
            || preg_match('/^[a-z]:\\\\/i', $filename)
            || preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $filename)
            || !preg_match('/^(www\.)?[a-z0-9-]+\.[a-z]{2,}/i', $filename);

        if (!$isLocalFilename && Cwmmedia::isExternal($filename)) {
            // Remote file - try to get metadata via HTTP headers and FFprobe
            $this->detectRemoteMetadata($params, $filename, $jbspodcast, $needsSize, $needsMimeType, $needsDuration);
            return;
        }

        // Build local file path
        $path_server = Cwmhelper::mediaBuildUrl($set_path, $filename, $params, false, false, true);
        $prefix      = Uri::root();
        $nohttp      = $jbspodcast->removeHttp($prefix);
        $siteinfo    = strpos($path_server, $nohttp);

        if ($siteinfo !== false) {
            $localPath = JPATH_SITE . '/' . substr($path_server, \strlen($nohttp));
        } else {
            $localPath = $path_server;
        }

        // Check if the constructed path looks like a remote URL
        $isLocalPath = preg_match('/^(images|media|files|modules|components|administrator|tmp|cache|logs)\//i', $localPath)
            || str_starts_with($localPath, '/')
            || str_starts_with($localPath, JPATH_SITE)
            || preg_match('/^[a-z]:\\\\/i', $localPath);

        if (!$isLocalPath && preg_match('/^(www\.)?[a-z0-9]([a-z0-9-]*[a-z0-9])?\.[a-z]{2,}(\/|$)/i', $localPath)) {
            // Path resolved to a remote URL
            $this->detectRemoteMetadata($params, $localPath, $jbspodcast, $needsSize, $needsMimeType, $needsDuration);
            return;
        }

        // Local file - detect metadata directly
        $this->detectLocalMetadata($params, $localPath, $jbspodcast, $needsSize, $needsMimeType, $needsDuration);
    }

    /**
     * Detect metadata for a YouTube video.
     *
     * @param   Registry    $params       Media params (modified in place)
     * @param   string      $filename     YouTube URL
     * @param   Cwmpodcast  $jbspodcast   Podcast helper
     * @param   bool        $needsDuration Whether duration needs detection
     *
     * @return  void
     *
     * @since   10.2.0
     */
    protected function detectYouTubeMetadata(Registry $params, string $filename, Cwmpodcast $jbspodcast, bool $needsDuration): void
    {
        // Set default MIME type for YouTube
        if (empty($params->get('mime_type'))) {
            $params->set('mime_type', 'video/mp4');
        }

        // Get duration via YouTube API if needed
        if ($needsDuration) {
            $videoId = $jbspodcast->extractYouTubeVideoId($filename);
            $apiKey  = $jbspodcast->getYouTubeApiKey();

            if ($videoId && $apiKey) {
                $durationSeconds = $jbspodcast->getYouTubeDuration($videoId, $apiKey);

                if ($durationSeconds > 0) {
                    $duration = $jbspodcast->formatTime($durationSeconds);
                    $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                    $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                    $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
                }
            }
        }
    }

    /**
     * Detect metadata for a remote file via HTTP headers and FFprobe.
     *
     * @param   Registry    $params         Media params (modified in place)
     * @param   string      $path           File URL/path
     * @param   Cwmpodcast  $jbspodcast     Podcast helper
     * @param   bool        $needsSize      Whether size needs detection
     * @param   bool        $needsMimeType  Whether MIME type needs detection
     * @param   bool        $needsDuration  Whether duration needs detection
     *
     * @return  void
     *
     * @since   10.2.0
     */
    protected function detectRemoteMetadata(Registry $params, string $path, Cwmpodcast $jbspodcast, bool $needsSize, bool $needsMimeType, bool $needsDuration): void
    {
        // Build full URL
        $remoteUrl = $path;
        if (!str_contains($remoteUrl, '://')) {
            $remoteUrl = 'https://' . ltrim($remoteUrl, '/');
        }

        // Get HTTP headers for size and MIME type
        if ($needsSize || $needsMimeType) {
            $size = Cwmhelper::getRemoteFileSize($remoteUrl);
            if ($needsSize && $size > 0) {
                $params->set('size', $size);
            }

            // Try to get MIME type from extension if still needed
            if ($needsMimeType) {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'mp3'  => 'audio/mpeg',
                    'mp4'  => 'video/mp4',
                    'm4a'  => 'audio/mp4',
                    'm4v'  => 'video/mp4',
                    'ogg'  => 'audio/ogg',
                    'oga'  => 'audio/ogg',
                    'ogv'  => 'video/ogg',
                    'wav'  => 'audio/wav',
                    'webm' => 'video/webm',
                    'flac' => 'audio/flac',
                    'aac'  => 'audio/aac',
                ];
                if (isset($mimeTypes[$extension])) {
                    $params->set('mime_type', $mimeTypes[$extension]);
                }
            }
        }

        // Try FFprobe for duration
        if ($needsDuration) {
            $durationSeconds = $jbspodcast->getDurationWithFFprobe($remoteUrl);
            if ($durationSeconds > 0) {
                $duration = $jbspodcast->formatTime($durationSeconds);
                $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
            }
        }
    }

    /**
     * Detect metadata for a local file.
     *
     * @param   Registry    $params         Media params (modified in place)
     * @param   string      $localPath      Local file path
     * @param   Cwmpodcast  $jbspodcast     Podcast helper
     * @param   bool        $needsSize      Whether size needs detection
     * @param   bool        $needsMimeType  Whether MIME type needs detection
     * @param   bool        $needsDuration  Whether duration needs detection
     *
     * @return  void
     *
     * @since   10.2.0
     */
    protected function detectLocalMetadata(Registry $params, string $localPath, Cwmpodcast $jbspodcast, bool $needsSize, bool $needsMimeType, bool $needsDuration): void
    {
        // Check if file exists
        if (!is_file($localPath)) {
            return;
        }

        // Get file size
        if ($needsSize) {
            $size = filesize($localPath);
            if ($size !== false && $size > 0) {
                $params->set('size', $size);
            }
        }

        // Get MIME type
        if ($needsMimeType) {
            $mimeType = null;

            // Try mime_content_type
            if (\function_exists('mime_content_type')) {
                $mimeType = mime_content_type($localPath);
                if ($mimeType === 'application/octet-stream') {
                    $mimeType = null;
                }
            }

            // Try finfo
            if (!$mimeType && class_exists('finfo')) {
                $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($localPath);
                if ($mimeType === 'application/octet-stream') {
                    $mimeType = null;
                }
            }

            // Fall back to extension
            if (!$mimeType) {
                $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'mp3'  => 'audio/mpeg',
                    'mp4'  => 'video/mp4',
                    'm4a'  => 'audio/mp4',
                    'm4v'  => 'video/mp4',
                    'ogg'  => 'audio/ogg',
                    'oga'  => 'audio/ogg',
                    'ogv'  => 'video/ogg',
                    'wav'  => 'audio/wav',
                    'webm' => 'video/webm',
                    'flac' => 'audio/flac',
                    'aac'  => 'audio/aac',
                    'pdf'  => 'application/pdf',
                ];
                $mimeType = $mimeTypes[$extension] ?? null;
            }

            if ($mimeType) {
                $params->set('mime_type', $mimeType);
            }
        }

        // Get duration
        if ($needsDuration) {
            $durationSeconds = $jbspodcast->getMediaDuration($localPath);
            if ($durationSeconds > 0) {
                $duration = $jbspodcast->formatTime($durationSeconds);
                $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
                $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
                $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
            }
        }
    }

    /**
     * Get the media form
     *
     * @return bool|mixed
     *
     * @throws \Exception
     *
     * @since   9.0.0
     */
    public function getMediaForm(): mixed
    {
        // server_id is now set by getItem() (including admin default for new items)
        $server_id = $this->data->server_id;

        // No server selected yet — nothing to load
        if (empty($server_id)) {
            return null;
        }

        // Reverse lookup server_id to server type
        /** @var CwmserverModel $model */
        $model       = Factory::getApplication()->bootComponent('com_proclaim')
            ->getMVCFactory()->createModel('Cwmserver', 'Administrator');
        $s_item      = $model->getItem($server_id);
        $server_type = $s_item->type;

        if (empty($server_type)) {
            return null;
        }

        // Load server params (stored as JSON string in database)
        $reg = new Registry();

        if (\is_string($s_item->params)) {
            $reg->loadString($s_item->params);
        } elseif (\is_array($s_item->params)) {
            $reg->loadArray($s_item->params);
        }

        // Load server media defaults (already converted to array by CwmserverModel::getItem)
        $reg1 = new Registry();

        if (\is_array($s_item->media)) {
            $reg1->loadArray($s_item->media);
        } elseif (\is_string($s_item->media)) {
            $reg1->loadString($s_item->media);
        }

        $reg1->merge($reg);

        $path = Path::clean(
            JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst($server_type)
        );

        Form::addFormPath($path);
        Form::addFieldPath($path . '/Field');

        // Add language files
        $lang = Factory::getApplication()->getLanguage();
        $path = \Joomla\Filesystem\Path::clean(
            JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst($server_type)
        );

        if (!$lang->load('jbs_addon_' . strtolower($server_type), $path)) {
            Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_ERROR_ADDON_LANGUAGE_NOT_LOADED'), 'error');
        }

        $form = $this->loadForm(
            'com_proclaim.mediafile.media',
            "media",
            ['control' => 'jform', 'load_data' => true],
            true,
            "/media"
        );

        if (empty($form)) {
            return null;
        }

        // Pass server params through state for use by view/addons when setting defaults
        $this->setState('s_params', $reg1->toArray());
        $this->setState('type', $server_type);

        return $form;
    }

    /**
     * Method to get media item
     *
     * @param   int  $pk  int
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function getItem($pk = null): mixed
    {
        $jinput = Factory::getApplication()->getInput();

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
        if ($jinput->get('a_id')) {
            $pk = $jinput->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $pk = $jinput->get('id', 0);
        }

        if (!empty($this->data)) {
            return $this->data;
        }

        $this->data = parent::getItem($pk);

        if (!empty($this->data)) {
            // Make PodCast Id to be array for view
            if (!empty($this->data->podcast_id)) {
                $this->data->podcast_id = explode(',', $this->data->podcast_id);
            }

            // Convert metadata field to array if not null
            if ($this->data->metadata !== null) {
                $registry             = new Registry($this->data->metadata);
                $this->data->metadata = $registry->toArray();
            }

            // Set the server_id from session if available or fall back on the db value
            $server_id             = $this->getState('mediafile.server_id');
            $this->data->server_id = empty($server_id) ? $this->data->server_id : $server_id;

            // For new items with no server, apply admin default
            if (empty($this->data->server_id) && empty($this->data->id)) {
                $defaultServer = Cwmparams::getAdmin()->params->get('server');

                if ($defaultServer !== null && $defaultServer !== '-1' && $defaultServer !== '') {
                    $this->data->server_id = (int) $defaultServer;
                }
            }

            $study_id             = $this->getState('mediafile.study_id');
            $this->data->study_id = empty($study_id) ? $this->data->study_id : $study_id;

            $createdate             = $this->getState('mediafile.createdate');
            $this->data->createdate = empty($createdate) ? $this->data->createdate : $createdate;
        }

        return $this->data;
    }

    /**
     * Get the form data
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return bool|object
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): object|bool
    {
        if (empty($data)) {
            $this->getItem();
        }

        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.mediafile',
            'mediafile',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if ($form === null) {
            return false;
        }

        $input = Factory::getApplication()->getInput();

        // The front end calls this model and uses a_id to avoid id clashes, so we need to check for that first.
        if ($input->get('a_id')) {
            $id = $input->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $id = $input->get('id', 0);
        }

        $user = Factory::getApplication()->getIdentity();

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if (
            ($id !== 0 && (!$user->authorise('core.edit.state', 'com_proclaim.mediafile.' . (int)$id)))
            || ($id === 0 && !$user->authorise('core.edit.state', 'com_proclaim'))
        ) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Batch Player changes for a group of mediafiles.
     *
     * @param   string  $value     The new value matching a player.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchPlayer($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getIdentity();
        /** @type CwmmediafileTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);

                // Todo Need to move to params BCC
                $table->player = (int)$value;

                if (!$table->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Custom clean the cache of the com_proclaim and Proclaim modules
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since 7.0.0
     */
    protected function cleanCache($group = null, $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }

    /**
     * Batch pop-up changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchLinkType($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getIdentity();
        /** @type CwmmediafileTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('link_type', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchMimetype($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getIdentity();
        /** @type CwmmediafileTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('mime_type', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch pop-up changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchMediatype($value, $pks, $contexts): bool
    {
        // Set the variables
        $user  = Factory::getApplication()->getIdentity();
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('media_image', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchPopup($value, $pks, $contexts): bool
    {
        // Set the variables
        $user  = Factory::getApplication()->getIdentity();
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('popup', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return    bool    True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function canDelete($record): bool
    {
        if (!empty($record->id)) {
            if ($record->published !== -2) {
                return false;
            }

            return Factory::getApplication()->getIdentity()->authorise(
                'core.delete',
                'com_proclaim.mediafile.' . (int)$record->id
            );
        }

        return false;
    }

    /**
     * Load Form Data
     *
     * @return object
     *
     * @throws  \Exception
     * @since   7.0
     */
    protected function loadFormData(): object
    {
        $session = Factory::getApplication()->getUserState('com_proclaim.mediafile.edit.data', []);

        return empty($session) ? $this->data : $session;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   CwmmediafileTable  $table  A reference to a Table object.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.0.0
     */
    protected function prepareTable($table): void
    {
        $date = new Date();
        $user = Factory::getApplication()->getIdentity();

        // Always ensure created date is set (handles empty string from form)
        if (empty($table->created) || $table->created === '') {
            $table->created = $date->toSql();
        }

        if (empty($table->id)) {
            // Set the values for a new record
            if (empty($table->created_by)) {
                $table->created_by = $user->get('id');
            }

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->select('MAX(' . $db->qn('ordering') . ')')->from($db->qn('#__bsms_mediafiles'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->get('id');
        }
    }

    /**
     * Auto-populate the model state
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    protected function populateState(): void
    {
        $app   = Factory::getApplication('administrator');
        $input = $app->input;

        // Load the Admin settings
        $admin    = Cwmparams::getAdmin();
        $registry = new Registry();
        $registry->loadString($admin->params);
        $this->setState('administrator', $registry);

        $pk = $input->get('id', null, 'INTEGER');
        $this->setState('mediafile.id', $pk);

        $server_id = $app->getUserState('com_proclaim.edit.mediafile.server_id');
        $this->setState('mediafile.server_id', $server_id);

        $study_id = $app->getUserState('com_proclaim.edit.mediafile.study_id');
        $this->setState('mediafile.study_id', $study_id);

        $createdate = $app->getUserState('com_proclaim.edit.mediafile.createdate');
        $this->setState('mediafile.createdate', $createdate);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return    array    An array of conditions to add to ordering queries.
     *
     * @since    1.6
     */
    protected function getReorderConditions($table): array
    {
        $db          = Factory::getContainer()->get('DatabaseDriver');
        $condition   = [];
        $condition[] = $db->qn('study_id') . ' = ' . (int)$table->study_id;

        return $condition;
    }
}
