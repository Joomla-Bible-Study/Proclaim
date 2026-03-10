<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * YouTube OAuth Status Field — shows connection state and Connect/Disconnect buttons.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class YoutubeOAuthStatusField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'YoutubeOAuthStatus';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @throws \Exception
     * @since   10.2.0
     */
    protected function getInput(): string
    {
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('jbs_addon_youtube', JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/Youtube');

        $html   = [];
        $html[] = '<div id="youtube-oauth-status-container">';

        // Check if we have an access token in the form data
        $form           = $this->form;
        $hasToken       = !empty($form->getValue('access_token', 'params'));
        $hasCredentials = !empty($form->getValue('client_id', 'params'))
            && !empty($form->getValue('client_secret', 'params'));

        if (!$hasCredentials) {
            $html[] = '<div class="alert alert-info mb-0">';
            $html[] = '<span class="icon-info-circle me-2" aria-hidden="true"></span>';
            $html[] = Text::_('JBS_ADDON_YOUTUBE_OAUTH_NO_CREDENTIALS');
            $html[] = '</div>';
        } elseif ($hasToken) {
            $html[] = '<div class="d-flex align-items-center gap-3">';
            $html[] = '<span class="badge bg-success fs-6">';
            $html[] = '<span class="icon-check me-1" aria-hidden="true"></span>';
            $html[] = Text::_('JBS_ADDON_YOUTUBE_OAUTH_CONNECTED');
            $html[] = '</span>';
            $html[] = '<button type="button" class="btn btn-danger btn-sm" id="youtube-oauth-disconnect-btn">';
            $html[] = '<span class="icon-times me-1" aria-hidden="true"></span>';
            $html[] = Text::_('JBS_ADDON_YOUTUBE_OAUTH_DISCONNECT');
            $html[] = '</button>';
            $html[] = '</div>';
        } else {
            $html[] = '<div class="d-flex align-items-center gap-3">';
            $html[] = '<span class="badge bg-secondary fs-6">';
            $html[] = Text::_('JBS_ADDON_YOUTUBE_OAUTH_NOT_CONNECTED');
            $html[] = '</span>';
            $html[] = '<button type="button" class="btn btn-primary btn-sm" id="youtube-oauth-connect-btn">';
            $html[] = '<span class="icon-link me-1" aria-hidden="true"></span>';
            $html[] = Text::_('JBS_ADDON_YOUTUBE_OAUTH_CONNECT');
            $html[] = '</button>';
            $html[] = '</div>';
            $html[] = '<div class="form-text mt-2">';
            $html[] = Text::_('JBS_ADDON_YOUTUBE_OAUTH_CONNECT_DESC');
            $html[] = '</div>';
        }

        // Result container for messages
        $html[] = '<div id="youtube-oauth-result" class="mt-2"></div>';
        $html[] = '</div>';

        // Add JavaScript
        $html[] = $this->getJavaScript($hasToken, $hasCredentials);

        return implode("\n", $html);
    }

    /**
     * Get the JavaScript for OAuth connect/disconnect buttons.
     *
     * @param   bool  $hasToken        Whether an OAuth token exists
     * @param   bool  $hasCredentials  Whether OAuth credentials are configured
     *
     * @return  string  JavaScript code
     *
     * @since   10.2.0
     */
    protected function getJavaScript(bool $hasToken, bool $hasCredentials): string
    {
        $token    = Factory::getApplication()->getSession()->getFormToken();
        $baseUrl  = Uri::base();
        $serverId = (int) Factory::getApplication()->getInput()->getInt('id', 0);

        $oauthUrl     = $baseUrl . 'index.php?option=com_proclaim&task=cwmserver.youtubeOAuth&server_id='
            . $serverId . '&' . $token . '=1';
        $disconnectUrl = $baseUrl . 'index.php?option=com_proclaim&task=cwmserver.addonAjax&addon=youtube'
            . '&action=disconnectOAuth&server_id=' . $serverId . '&format=raw&' . $token . '=1';

        $saveFirst    = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_OAUTH_SAVE_FIRST'));
        $disconnectOk = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_OAUTH_DISCONNECTED'));
        $disconnectQ  = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_OAUTH_DISCONNECT_CONFIRM'));
        $errorMsg     = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_OAUTH_ERROR'));

        $js = <<<JS
<script>
(function() {
    'use strict';

    const serverId = {$serverId};
    const resultEl = document.getElementById('youtube-oauth-result');

    function showMessage(success, message) {
        resultEl.innerHTML = '<div class="alert ' + (success ? 'alert-success' : 'alert-danger') + ' mt-2">'
            + message + '</div>';
    }

    // Connect button
    const connectBtn = document.getElementById('youtube-oauth-connect-btn');
    if (connectBtn) {
        connectBtn.addEventListener('click', function() {
            if (!serverId) {
                showMessage(false, '{$saveFirst}');
                return;
            }

            // Open OAuth popup
            const popup = window.open(
                '{$oauthUrl}',
                'youtubeOAuth',
                'width=600,height=700,scrollbars=yes,resizable=yes'
            );

            // Listen for popup close and reload
            const timer = setInterval(function() {
                if (popup && popup.closed) {
                    clearInterval(timer);
                    window.location.reload();
                }
            }, 500);
        });
    }

    // Disconnect button
    const disconnectBtn = document.getElementById('youtube-oauth-disconnect-btn');
    if (disconnectBtn) {
        disconnectBtn.addEventListener('click', function() {
            if (!confirm('{$disconnectQ}')) {
                return;
            }

            disconnectBtn.disabled = true;

            fetch('{$disconnectUrl}', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage(true, '{$disconnectOk}');
                    setTimeout(function() { window.location.reload(); }, 1000);
                } else {
                    disconnectBtn.disabled = false;
                    showMessage(false, data.error || '{$errorMsg}');
                }
            })
            .catch(function(err) {
                disconnectBtn.disabled = false;
                showMessage(false, '{$errorMsg}');
            });
        });
    }
})();
</script>
JS;

        return $js;
    }

    /**
     * Escape a string for use in JavaScript
     *
     * @param   string  $string  The string to escape
     *
     * @return  string  The escaped string
     *
     * @since   10.2.0
     */
    protected function escapeJs(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
