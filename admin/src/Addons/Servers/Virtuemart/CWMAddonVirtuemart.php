<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Virtuemart;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;

/**
 * VirtueMart Server Addon
 *
 * Provides integration with VirtueMart e-commerce for serving
 * digital media products (audio/video downloads and streams).
 *
 * @since 10.1.0
 */
class CWMAddonVirtuemart extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.1.0
     */
    protected $name = 'Virtuemart';

    /**
     * Addon description
     *
     * @var string
     * @since 10.1.0
     */
    protected $description = 'Used for VirtueMart digital media downloads';

    /**
     * Check if a URL is a VirtueMart URL
     *
     * @param   string  $url  The URL to check
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function isVirtuemartUrl(string $url): bool
    {
        return preg_match('/com_virtuemart|virtuemart.*download/i', $url) === 1;
    }

    /**
     * Render general fieldset fields
     *
     * @param   object  $media_form  Media files form
     * @param   bool    $new         If media is new
     *
     * @return  string  Rendered HTML
     *
     * @since   10.1.0
     */
    public function renderGeneral(object $media_form, bool $new): string
    {
        $html = '';

        foreach ($media_form->getFieldset('general') as $field) {
            if ($new && isset($media_form->s_params[$field->fieldname])) {
                $field->setValue($media_form->s_params[$field->fieldname]);
            }

            $html .= $field->renderField();
        }

        return $html;
    }

    /**
     * Render full tab with addTab/endTab wrappers
     *
     * @param   object  $media_form  Media files form
     * @param   bool    $new         If media is new
     *
     * @return  string  Rendered HTML
     *
     * @since   10.1.0
     */
    public function render(object $media_form, bool $new): string
    {
        $html = '<div class="tab-pane" id="virtuemart">';
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= '</div>';

        return $html;
    }

    /**
     * Upload method (not supported for VirtueMart URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.1.0
     */
    protected function upload(?array $data): mixed
    {
        return false;
    }
}
