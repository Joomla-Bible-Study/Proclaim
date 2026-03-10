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
 * YouTube Test API Field - button to test API key and channel ID
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class YoutubeTestApiField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.1.0
     */
    protected $type = 'YoutubeTestApi';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function getInput(): string
    {
        // Load the YouTube addon language file
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('jbs_addon_youtube', JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/Youtube');

        $html = [];

        // Container
        $html[] = '<div id="youtube-test-api-container" class="youtube-test-api">';

        // Test button
        $html[] = '<button type="button" class="btn btn-info" id="youtube-test-api-btn">';
        $html[] = '<span class="icon-play me-2" aria-hidden="true"></span>';
        $html[] = Text::_('JBS_ADDON_YOUTUBE_TEST_API');
        $html[] = '</button>';

        // Loading indicator
        $html[] = '<span id="youtube-test-api-loading" class="ms-3 d-none">';
        $html[] = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        $html[] = ' ' . Text::_('JBS_ADDON_YOUTUBE_TESTING');
        $html[] = '</span>';

        // Result container
        $html[] = '<div id="youtube-test-api-result" class="mt-3"></div>';

        $html[] = '</div>';

        // Add JavaScript
        $html[] = $this->getJavaScript();

        return implode("\n", $html);
    }

    /**
     * Get the JavaScript for the test button
     *
     * @return  string  JavaScript code
     *
     * @since   10.1.0
     */
    protected function getJavaScript(): string
    {
        $token   = Factory::getApplication()->getSession()->getFormToken();
        $baseUrl = Uri::base() . 'index.php?option=com_proclaim&task=cwmserver.addonAjax&addon=youtube&action=testApi&format=raw&' . $token . '=1';

        // Pre-escape all language strings for use in JavaScript
        $noApiKey     = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_NO_API_KEY'));
        $noChannelId  = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_NO_CHANNEL_ID'));
        $channelName  = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_CHANNEL_NAME'));
        $subscribers  = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_SUBSCRIBERS'));
        $videos       = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_VIDEOS'));
        $apiSuccess   = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_API_SUCCESS'));
        $apiFailed    = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_API_FAILED'));
        $apiError     = $this->escapeJs(Text::_('JBS_ADDON_YOUTUBE_API_ERROR'));

        $js = <<<JS
<script>
(function() {
    const testBtn = document.getElementById('youtube-test-api-btn');
    const loadingEl = document.getElementById('youtube-test-api-loading');
    const resultEl = document.getElementById('youtube-test-api-result');
    const baseUrl = '{$baseUrl}';

    function getFieldValue(fieldName) {
        // Try different field name patterns
        const selectors = [
            'input[name="jform[params][' + fieldName + ']"]',
            'input[name="params[' + fieldName + ']"]',
            '#jform_params_' + fieldName,
            'input[name*="[' + fieldName + ']"]'
        ];

        for (const selector of selectors) {
            const el = document.querySelector(selector);
            if (el) {
                return el.value;
            }
        }
        return '';
    }

    function showResult(success, message, details) {
        let html = '<div class="alert ' + (success ? 'alert-success' : 'alert-danger') + '">';
        html += '<strong>' + (success ? '✓ ' : '✗ ') + '</strong>';
        html += message;

        if (details) {
            html += '<div class="mt-2 small">' + details + '</div>';
        }

        html += '</div>';
        resultEl.innerHTML = html;
    }

    testBtn.addEventListener('click', function() {
        const apiKey = getFieldValue('api_key');
        const channelId = getFieldValue('channel_id');

        if (!apiKey) {
            showResult(false, '{$noApiKey}');
            return;
        }

        if (!channelId) {
            showResult(false, '{$noChannelId}');
            return;
        }

        // Show loading
        loadingEl.classList.remove('d-none');
        testBtn.disabled = true;
        resultEl.innerHTML = '';

        // Make request
        const url = baseUrl + '&api_key=' + encodeURIComponent(apiKey) + '&channel_id=' + encodeURIComponent(channelId);

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingEl.classList.add('d-none');
            testBtn.disabled = false;

            if (data.success) {
                let details = '';
                if (data.channel) {
                    details = '<strong>{$channelName}:</strong> ' + data.channel.title;
                    if (data.channel.subscriberCount) {
                        details += '<br><strong>{$subscribers}:</strong> ' + Number(data.channel.subscriberCount).toLocaleString();
                    }
                    if (data.channel.videoCount) {
                        details += '<br><strong>{$videos}:</strong> ' + Number(data.channel.videoCount).toLocaleString();
                    }
                }
                showResult(true, data.message || '{$apiSuccess}', details);
            } else {
                showResult(false, data.error || '{$apiFailed}');
            }
        })
        .catch(error => {
            loadingEl.classList.add('d-none');
            testBtn.disabled = false;
            showResult(false, '{$apiError}' + ': ' + error.message);
        });
    });
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
     * @since   10.1.0
     */
    protected function escapeJs(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
