<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * VTT Upload Field — URL input with file upload button for caption/subtitle files.
 *
 * Renders a text input for the VTT/SRT URL alongside an upload button.
 * Uploaded files are stored in media/com_proclaim/captions/ via AJAX.
 * Users can also paste an external URL directly.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class VttUploadField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'VttUpload';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since 10.2.0
     */
    protected function getInput(): string
    {
        $this->registerAssets();

        $value    = htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8');
        $id       = $this->id;
        $name     = $this->name;
        $disabled = $this->disabled ? ' disabled' : '';
        $required = $this->required ? ' required' : '';

        $uploadLabel  = Text::_('JBS_MED_VTT_UPLOAD');
        $browseLabel  = Text::_('JBS_MED_VTT_BROWSE');
        $acceptedExts = '.vtt,.srt,.sbv';

        // Wrapper div for DOM traversal in the external JS
        $html = '<div class="cwm-vtt-field">';
        $html .= '<div class="input-group">';

        // URL text input
        $html .= \sprintf(
            '<input type="url" name="%s" id="%s" value="%s" class="form-control cwm-vtt-url"'
            . ' placeholder="%s"%s%s />',
            $name,
            $id,
            $value,
            htmlspecialchars('https://example.com/subtitles.vtt', ENT_QUOTES, 'UTF-8'),
            $disabled,
            $required
        );

        // Upload button
        $html .= \sprintf(
            '<button type="button" class="btn btn-outline-secondary cwm-vtt-upload-btn"'
            . ' title="%s"%s>'
            . '<span class="icon-upload" aria-hidden="true"></span> %s</button>',
            htmlspecialchars($uploadLabel, ENT_QUOTES, 'UTF-8'),
            $disabled,
            htmlspecialchars($browseLabel, ENT_QUOTES, 'UTF-8')
        );

        $html .= '</div>';

        // Hidden file input inside the wrapper
        $html .= \sprintf(
            '<input type="file" accept="%s" class="d-none cwm-vtt-file-input" />',
            $acceptedExts
        );

        // Current file indicator + download-format dropdown
        if (!empty($this->value)) {
            $filename     = basename((string) $this->value);
            $downloadable = (bool) preg_match('/^caption_\d+_[A-Za-z0-9_-]+\.vtt$/', $filename);

            $html .= '<small class="text-muted mt-1 d-block cwm-vtt-current">'
                . '<span class="icon-file" aria-hidden="true"></span> '
                . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');

            if ($downloadable) {
                $html .= ' &mdash; '
                    . htmlspecialchars(Text::_('JBS_MED_CAPTION_DOWNLOAD'), ENT_QUOTES, 'UTF-8')
                    . ' <select class="form-select form-select-sm d-inline-block w-auto cwm-vtt-download-format"'
                    . ' data-filename="' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '">'
                    . '<option value="">&hellip;</option>'
                    . '<option value="vtt">' . htmlspecialchars(Text::_('JBS_MED_CAPTION_DOWNLOAD_VTT'), ENT_QUOTES, 'UTF-8') . '</option>'
                    . '<option value="srt">' . htmlspecialchars(Text::_('JBS_MED_CAPTION_DOWNLOAD_SRT'), ENT_QUOTES, 'UTF-8') . '</option>'
                    . '<option value="sbv">' . htmlspecialchars(Text::_('JBS_MED_CAPTION_DOWNLOAD_SBV'), ENT_QUOTES, 'UTF-8') . '</option>'
                    . '</select>';
            }

            $html .= '</small>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Register the external JS asset and pass config via script options.
     *
     * @return  void
     *
     * @since 10.2.0
     */
    protected function registerAssets(): void
    {
        static $registered = false;

        if ($registered) {
            return;
        }
        $registered = true;

        $app = Factory::getApplication();
        $doc = $app->getDocument();
        $wa  = $doc->getWebAssetManager();

        $wa->useScript('com_proclaim.cwm-vtt-upload');

        $token = Session::getFormToken();

        $uploadUrl = Uri::base()
            . 'index.php?option=com_proclaim&task=cwmmediafile.uploadVttXHR&'
            . $token . '=1';

        $downloadUrl = Uri::base()
            . 'index.php?option=com_proclaim&task=cwmmediafile.downloadCaption&'
            . $token . '=1';

        $doc->addScriptOptions('com_proclaim.vttUpload', [
            'uploadUrl'   => $uploadUrl,
            'downloadUrl' => $downloadUrl,
        ]);

        // Register the language keys this field's JS consumes via
        // Joomla.Text._(). Joomla.Text._() returns the raw key string when
        // a key is unregistered (truthy), so a `Joomla.Text._('K') || 'fallback'`
        // pattern in the JS would silently print the raw key instead of the
        // fallback. Register here rather than relying on the host template
        // calling CwmlangHelper::registerAllForJs() — this field is self-contained.
        $existing                              = $doc->getScriptOptions('joomla.jtext') ?: [];
        $existing['JBS_MED_VTT_UPLOADING']     = Text::_('JBS_MED_VTT_UPLOADING');
        $existing['JBS_MED_VTT_UPLOAD_FAILED'] = Text::_('JBS_MED_VTT_UPLOAD_FAILED');
        $doc->addScriptOptions('joomla.jtext', $existing, false);
    }
}
