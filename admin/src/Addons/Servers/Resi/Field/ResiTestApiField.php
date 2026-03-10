<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Resi\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Resi Test API Field - button to test OAuth credentials
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class ResiTestApiField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.1.0
     */
    protected $type = 'ResiTestApi';

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
        // Load the Resi addon language file
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('jbs_addon_resi', JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/Resi');

        $html = [];

        // Container
        $html[] = '<div id="resi-test-api-container" class="resi-test-api">';

        // Test button
        $html[] = '<button type="button" class="btn btn-info" id="resi-test-api-btn">';
        $html[] = '<span class="icon-play me-2" aria-hidden="true"></span>';
        $html[] = Text::_('JBS_ADDON_RESI_TEST_API');
        $html[] = '</button>';

        // Loading indicator
        $html[] = '<span id="resi-test-api-loading" class="ms-3 d-none">';
        $html[] = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        $html[] = ' ' . Text::_('JBS_ADDON_RESI_TESTING');
        $html[] = '</span>';

        // Result container
        $html[] = '<div id="resi-test-api-result" class="mt-3"></div>';

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
        $baseUrl = Uri::base() . 'index.php?option=com_proclaim&task=cwmserver.addonAjax&addon=resi&action=testApi&format=raw&' . $token . '=1';

        // Pre-escape all language strings for use in JavaScript
        $noClientId     = $this->escapeJs(Text::_('JBS_ADDON_RESI_NO_CLIENT_ID'));
        $noClientSecret = $this->escapeJs(Text::_('JBS_ADDON_RESI_NO_CLIENT_SECRET'));
        $apiSuccess     = $this->escapeJs(Text::_('JBS_ADDON_RESI_API_SUCCESS'));
        $apiFailed      = $this->escapeJs(Text::_('JBS_ADDON_RESI_API_FAILED'));
        $apiError       = $this->escapeJs(Text::_('JBS_ADDON_RESI_API_ERROR'));

        $js = <<<JS
<script>
(function() {
    const testBtn = document.getElementById('resi-test-api-btn');
    const loadingEl = document.getElementById('resi-test-api-loading');
    const resultEl = document.getElementById('resi-test-api-result');
    const baseUrl = '{$baseUrl}';

    function getFieldValue(fieldName) {
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

    function getServerId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || '0';
    }

    function showResult(success, message, details) {
        let html = '<div class="alert ' + (success ? 'alert-success' : 'alert-danger') + '">';
        html += '<strong>' + (success ? '\u2713 ' : '\u2717 ') + '</strong>';
        html += message;

        if (details) {
            html += '<div class="mt-2 small">' + details + '</div>';
        }

        html += '</div>';
        resultEl.innerHTML = html;
    }

    testBtn.addEventListener('click', function() {
        const clientId = getFieldValue('client_id');
        const clientSecret = getFieldValue('client_secret');

        if (!clientId) {
            showResult(false, '{$noClientId}');
            return;
        }

        if (!clientSecret) {
            showResult(false, '{$noClientSecret}');
            return;
        }

        loadingEl.classList.remove('d-none');
        testBtn.disabled = true;
        resultEl.innerHTML = '';

        const serverId = getServerId();
        const url = baseUrl
            + '&server_id=' + encodeURIComponent(serverId)
            + '&client_id=' + encodeURIComponent(clientId)
            + '&client_secret=' + encodeURIComponent(clientSecret);

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
                showResult(true, data.message || '{$apiSuccess}');
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
