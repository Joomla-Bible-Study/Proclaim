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

use CWM\Component\Proclaim\Administrator\Controller\Trait\MultiCampusAccessTrait;
use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastIndexHelper;
use CWM\Component\Proclaim\Administrator\Model\CwmpodcastModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Podcast form class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmpodcastController extends FormController
{
    use MultiCampusAccessTrait;

    /**
     * The database table for access level checks.
     *
     * @var    string
     * @since  10.3.0
     */
    protected string $accessTable = '#__bsms_podcast';

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
     * Method to run batch operations.
     *
     * @param   CwmpodcastModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmpodcasts' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($this->getModel());
    }

    /**
     * Method to run after a successful save.
     *
     * @param   BaseDatabaseModel  $model      The model.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = []): void
    {
        $id    = (int) $model->getState('cwmpodcast.id');
        $isNew = empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_PODCAST_ADDED' : 'COM_PROCLAIM_ACTION_LOG_PODCAST_UPDATED';
        $title = $validData['title'] ?? '';

        CwmactionlogHelper::log($key, $title, 'podcast', $id);
    }

    /**
     * Submit the podcast feed to Podcast Index for directory listing.
     *
     * Called via AJAX from the admin edit form's Directory Submission card.
     * Requires podcastindex_api_key and podcastindex_api_secret in component options.
     *
     * @return  void  Sends JSON response
     *
     * @since   10.1.0
     */
    public function submitToIndex(): void
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $id  = $this->input->getInt('id', 0);

        try {
            if ($id <= 0) {
                throw new \InvalidArgumentException(Text::_('JBS_PDC_SUBMIT_ERROR_NO_ID'));
            }

            $params    = ComponentHelper::getParams('com_proclaim');
            $apiKey    = $params->get('podcastindex_api_key', '');
            $apiSecret = $params->get('podcastindex_api_secret', '');

            if (empty($apiKey) || empty($apiSecret)) {
                throw new \RuntimeException(Text::_('JBS_PDC_SUBMIT_ERROR_NO_API_KEYS'));
            }

            // Load the podcast to get its filename (RSS feed)
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'filename', 'title']))
                ->from($db->quoteName('#__bsms_podcast'))
                ->where($db->quoteName('id') . ' = :pid')
                ->bind(':pid', $id, ParameterType::INTEGER);
            $db->setQuery($query);
            $podcast = $db->loadObject();

            if (!$podcast || empty($podcast->filename)) {
                throw new \RuntimeException(Text::_('JBS_PDC_SUBMIT_ERROR_NO_FEED'));
            }

            $feedUrl = Uri::root() . $podcast->filename;
            $helper  = new CwmpodcastIndexHelper($apiKey, $apiSecret);
            $result  = $helper->submitFeed($feedUrl);

            $app->setHeader('Content-Type', 'application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => Text::sprintf('JBS_PDC_SUBMIT_SUCCESS', $podcast->title),
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            $app->setHeader('Content-Type', 'application/json; charset=utf-8');
            $app->setHeader('Status', '500');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        $app->close();
    }
}
