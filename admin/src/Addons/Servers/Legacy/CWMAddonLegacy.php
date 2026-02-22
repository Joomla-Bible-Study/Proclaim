<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Legacy;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\Cwmuploadscript;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Class CWMAddonLegacy
 *
 * @package     Proclaim.Admin
 * @since       9.0.0
 * @deprecated  10.1.0  Legacy servers will be removed in 11.0.0.
 *              Use the Server Migration tool in Admin Center to migrate
 *              media files to core server addons (YouTube, Vimeo, Local, etc.).
 */
class CWMAddonLegacy extends CWMAddon
{
    /**
     * Name of Add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $name = 'Legacy';

    /**
     * Description of add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $description = 'Legacy Server that we brought over from 8.x.x version of proclaim';

    /**
     * Upload
     *
     * @param ?array $data  Data to upload
     *
     * @return array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function upload(?array $data): array
    {
        return (new Cwmuploadscript())->upload($data);
    }

    /**
     * Render Fields for the general view.
     *
     * @param   object  $media_form  Media files form
     * @param bool      $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
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
     * @since 9.1.3
     */
    public function render(object $media_form, bool $new): string
    {
        $html = HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('JBS_ADDON_MEDIA_OPTIONS_LABEL'));
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
    }
}
