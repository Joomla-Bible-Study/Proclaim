<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Direct;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\Cwmuploadscript;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Class CWMAddonDirect
 *
 * @package  Proclaim.Admin
 * @since 10.1.0
 */
class CWMAddonDirect extends CWMAddon
{
    /**
     * Name of Add-on
     *
     * @var     string
     * @since 10.1.0
     */
    protected $name = 'Direct Link';

    /**
     * Description of add-on
     *
     * @var     string
     * @since 10.1.0
     */
    protected $description = 'Direct link server for URL-based media files.';

    /**
     * Upload
     *
     * @param ?array $data  Data to upload
     *
     * @return array
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function upload(?array $data): array
    {
        return (new Cwmuploadscript())->upload($data);
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function getMigrationPatterns(): array
    {
        return [
            'type'     => 'direct',
            'label'    => 'Direct Link',
            'patterns' => [],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.1.0
     */
    public function transformMigrationParams(
        array $params,
        string $mediacode,
        string $filename,
        string $avContent,
        string $combined,
        array $legacyServerParams = []
    ): array {
        return [
            'filename'  => $filename,
            'player'    => '0',
            'mediacode' => '',
            'special'   => $params['special'] ?? '_blank',
        ];
    }

    /**
     * Render Fields for the general view.
     *
     * @param   object  $media_form  Media files form
     * @param bool      $new         If media is new
     *
     * @return string
     *
     * @since 10.1.0
     */
    public function renderGeneral(object $media_form, bool $new): string
    {
        $html = '';

        foreach ($media_form->getFieldset('general') as $field) {
            if ($new) {
                $s_name = $field->fieldname;

                if (isset($media_form->s_params[$s_name])) {
                    $field->setValue($media_form->s_params[$s_name]);
                }
            }

            $html .= $field->renderField();
        }

        return $html;
    }

    /**
     * Render Layout and fields
     *
     * @param   object  $media_form  Media files form
     * @param bool      $new         If the media is new
     *
     * @return string
     *
     * @since 10.1.0
     */
    public function render(object $media_form, bool $new): string
    {
        $html = HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('JBS_ADDON_MEDIA_OPTIONS_LABEL'));
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
    }

    /**
     * Render inline player HTML (responsive iframe/embed).
     * Override in each platform addon.
     *
     * @param   string    $url          The raw URL/filename
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   int       $mediaId      The media file ID (for play tracking)
     *
     * @return  string  Complete player HTML, or empty to fall back to CWMHtml5Inline
     *
     * @since 10.1.0
     */
    public function renderInlinePlayer(string $url, Registry $mediaParams, int $mediaId): string
    {
        // For Direct Link addon, we just want to output a simple link,
        // bypassing the automatic audio/video tag generation of CWMHtml5Inline.
        $target     = $mediaParams->get('special', '_blank');
        $targetAttr = $target ? ' target="' . $target . '"' : '';
        $text       = $mediaParams->get('media_button_text', Text::_('JBS_MED_DOWNLOAD'));

        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $targetAttr . '>' . $text . '</a>';
    }

    /**
     * Detect metadata for a direct link (noop).
     *
     * @param   Registry    $params      Media params
     * @param   object      $server      Server object
     * @param   string      $set_path    Server path prefix
     * @param   Registry    $path        Server params
     * @param   Cwmpodcast  $jbspodcast  Podcast helper
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[\Override]
    public function detectMetadata(Registry $params, object $server, string $set_path, Registry $path, Cwmpodcast $jbspodcast): void
    {
        // Direct links do not support automatic metadata detection.
        // Users must enter size/duration manually if needed.
    }

}
