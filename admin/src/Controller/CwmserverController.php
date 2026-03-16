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

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use CWM\Component\Proclaim\Administrator\Model\CwmserverModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for Server
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserverController extends FormController
{
    /**
     * Method to add a new record.
     *
     * @return  bool  True if the record can be added, an error object if not.
     *
     * @throws \Exception
     * @since   7.0.0
     */
    public function add(): bool
    {
        $app = Factory::getApplication();

        if (parent::add()) {
            $app->setUserState('com_proclaim.edit.cwmserver.server_name', null);
            $app->setUserState('com_proclaim.edit.cwmserver.type', null);

            return true;
        }

        return false;
    }

    /**
     * Resets the User state for the server type. Needed to allow the value from the DB to be used
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key
     *                           (sometimes required to avoid router collisions).
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   9.0.0
     */
    public function edit($key = null, $urlVar = null): bool
    {
        $app    = Factory::getApplication();
        $result = parent::edit();

        if ($result) {
            $app->setUserState('com_proclaim.edit.cwmserver.server_name', null);
            $app->setUserState('com_proclaim.edit.cwmserver.type', null);
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
        $recordId = (int) ($data[$key] ?? 0);
        $user     = Factory::getApplication()->getIdentity();

        // Non-admin users must have access to the item's view level
        if (!$user->authorise('core.admin') && $recordId > 0) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('access'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = :rid')
                ->bind(':rid', $recordId, ParameterType::INTEGER);
            $db->setQuery($query);
            $access = (int) $db->loadResult();

            if ($access && !\in_array($access, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        return parent::allowEdit($data, $key);
    }

    /**
     * Sets the type of endpoint currently being configured.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   9.0.0
     */
    public function setType(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $data  = $input->get('jform', [], 'post');
        $sname = $data['server_name'] ?? '';
        $type  = json_decode(base64_decode($data['type'] ?? ''), true, 512, JSON_THROW_ON_ERROR);

        $recordId = $type['id'] ?? 0;

        // Save the endpoint in the session
        $app->setUserState('com_proclaim.edit.cwmserver.type', $type['name'] ?? '');
        $app->setUserState('com_proclaim.edit.cwmserver.server_name', $sname);

        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item .
                $this->getRedirectToItemAppend((int)$recordId),
                false
            )
        );
    }

    /**
     * Generic AJAX handler for addon actions
     *
     * This method provides a single entry point for all addon AJAX requests.
     * It routes requests to the appropriate addon based on the 'addon' parameter
     * and dispatches to the specified action.
     *
     * URL format: index.php?option=com_proclaim&task=cwmserver.addonAjax&addon=youtube&action=testApi
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.0.0
     */
    public function addonAjax(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken()) {
            CWMAddon::outputJson(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);

            return;
        }

        $app       = Factory::getApplication();
        $addonType = $app->getInput()->getString('addon', '');
        $action    = $app->getInput()->getString('action', '');

        if (empty($addonType)) {
            CWMAddon::outputJson(['success' => false, 'error' => 'No addon type specified']);

            return;
        }

        if (empty($action)) {
            CWMAddon::outputJson(['success' => false, 'error' => 'No action specified']);

            return;
        }

        CWMAddon::handleAjaxRequest($addonType, $action);
    }

    /**
     * Method to run batch operations.
     *
     * @param   CwmserverModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmservers' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($this->getModel());
    }

    /**
     * Initiate YouTube OAuth 2.0 authorization flow.
     *
     * Generates a state token, stores it in session, creates the Google
     * authorization URL and redirects to Google's consent screen.
     *
     * URL: index.php?option=com_proclaim&task=cwmserver.youtubeOAuth&server_id=X
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.2.0
     */
    public function youtubeOAuth(): void
    {
        $app      = Factory::getApplication();
        $serverId = $app->getInput()->getInt('server_id', 0);

        if (!$serverId) {
            $app->enqueueMessage(Text::_('JBS_ADDON_YOUTUBE_OAUTH_NO_SERVER'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmservers', false));

            return;
        }

        // Build callback URL
        $callbackUrl = rtrim(Uri::base(), '/')
            . '/index.php?option=com_proclaim&task=cwmserver.youtubeOAuthCallback';

        // Create Google OAuth client via addon
        $addon  = new CWMAddonYoutube();
        $client = $addon->createOAuthClient($serverId, $callbackUrl);

        if (!$client) {
            $app->enqueueMessage(Text::_('JBS_ADDON_YOUTUBE_OAUTH_NO_CREDENTIALS'), 'error');
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_proclaim&view=cwmserver&layout=edit&id=' . $serverId,
                    false
                )
            );

            return;
        }

        // Generate random state token and store in session
        $state = bin2hex(random_bytes(16));
        $app->getSession()->set('youtube_oauth_state', $state);
        $app->getSession()->set('youtube_oauth_server_id', $serverId);

        $client->setState($state);
        $authUrl = $client->createAuthUrl();

        $app->redirect($authUrl);
    }

    /**
     * Handle Google OAuth 2.0 callback after user grants consent.
     *
     * Validates the state parameter against the session, exchanges the
     * authorization code for tokens, saves them, and closes the popup.
     *
     * URL: index.php?option=com_proclaim&task=cwmserver.youtubeOAuthCallback
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.2.0
     */
    public function youtubeOAuthCallback(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Validate state parameter against session (CSRF protection for OAuth)
        $state         = $input->getString('state', '');
        $sessionState  = $app->getSession()->get('youtube_oauth_state', '');
        $serverId      = (int) $app->getSession()->get('youtube_oauth_server_id', 0);

        // Clear session state immediately
        $app->getSession()->clear('youtube_oauth_state');
        $app->getSession()->clear('youtube_oauth_server_id');

        if (empty($state) || !hash_equals($sessionState, $state)) {
            $this->renderPopupClose(Text::_('JBS_ADDON_YOUTUBE_OAUTH_STATE_MISMATCH'), false);

            return;
        }

        if (!$serverId) {
            $this->renderPopupClose(Text::_('JBS_ADDON_YOUTUBE_OAUTH_NO_SERVER'), false);

            return;
        }

        // Check for error from Google
        $error = $input->getString('error', '');

        if (!empty($error)) {
            $errorDesc = $input->getString('error_description', $error);
            $this->renderPopupClose(htmlspecialchars($errorDesc, ENT_QUOTES, 'UTF-8'), false);

            return;
        }

        // Exchange authorization code for tokens
        $code = $input->getString('code', '');

        if (empty($code)) {
            $this->renderPopupClose(Text::_('JBS_ADDON_YOUTUBE_OAUTH_NO_CODE'), false);

            return;
        }

        $callbackUrl = rtrim(Uri::base(), '/')
            . '/index.php?option=com_proclaim&task=cwmserver.youtubeOAuthCallback';

        $addon  = new CWMAddonYoutube();
        $client = $addon->createOAuthClient($serverId, $callbackUrl);

        if (!$client) {
            $this->renderPopupClose(Text::_('JBS_ADDON_YOUTUBE_OAUTH_NO_CREDENTIALS'), false);

            return;
        }

        try {
            $tokenData = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($tokenData['error'])) {
                $this->renderPopupClose(
                    htmlspecialchars($tokenData['error_description'] ?? $tokenData['error'], ENT_QUOTES, 'UTF-8'),
                    false
                );

                return;
            }

            // saveOAuthTokens also sets the access_token flag for the status field
            $addon->saveOAuthTokens($serverId, $tokenData);

            $this->renderPopupClose(Text::_('JBS_ADDON_YOUTUBE_OAUTH_SUCCESS'), true);
        } catch (\Exception $e) {
            $this->renderPopupClose(
                htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
                false
            );
        }
    }

    /**
     * Render a minimal HTML page that shows a message and closes the popup.
     *
     * @param   string  $message  Message to display briefly
     * @param   bool    $success  Whether the operation succeeded
     *
     * @return  void
     *
     * @since   10.2.0
     */
    private function renderPopupClose(string $message, bool $success): void
    {
        $bgColor = $success ? '#d4edda' : '#f8d7da';
        $color   = $success ? '#155724' : '#721c24';
        $icon    = $success ? '&#10004;' : '&#10006;';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>YouTube OAuth</title></head>
<body style="font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:{$bgColor}">
<div style="text-align:center;color:{$color};padding:2rem">
<p style="font-size:3rem;margin:0">{$icon}</p>
<p style="font-size:1.2rem">{$message}</p>
<p style="font-size:0.9rem;opacity:0.7">This window will close automatically...</p>
</div>
<script>setTimeout(function(){window.close()},2000);</script>
</body>
</html>
HTML;

        echo $html;

        Factory::getApplication()->close();
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
    /**
     * Method to cancel an edit — redirects to modalreturn when in modal layout.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  bool  True if access level checks pass, false otherwise.
     *
     * @since   10.2.0
     */
    #[\Override]
    public function cancel($key = null): bool
    {
        $result = parent::cancel($key);

        // When editing in modal then redirect to modalreturn layout
        if ($result && $this->input->get('layout') === 'modal') {
            $id     = $this->input->get('id');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                . '&layout=modalreturn&from-task=cancel';

            $this->setRedirect(Route::_($return, false));
        }

        return $result;
    }

    protected function postSaveHook(BaseDatabaseModel $model, $validData = []): void
    {
        $id    = (int) $model->getState('cwmserver.id');
        $isNew = empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_SERVER_ADDED' : 'COM_PROCLAIM_ACTION_LOG_SERVER_UPDATED';
        $title = $validData['server_name'] ?? '';

        CwmactionlogHelper::log($key, $title, 'server', $id);

        // Modal layout: redirect to modalreturn or stay in modal
        if ($this->input->get('layout') === 'modal') {
            if ($this->task === 'save') {
                $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend($id) . '&layout=modalreturn&from-task=save';
                $this->setRedirect(Route::_($return, false));
            } elseif ($this->task === 'apply') {
                $return = 'index.php?option=' . $this->option . '&task=' . $this->view_item . '.edit'
                    . $this->getRedirectToItemAppend($id) . '&layout=modal&tmpl=component';
                $this->setRedirect(Route::_($return, false));
            }
        }
    }
}
