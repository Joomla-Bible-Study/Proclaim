<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Local;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\Cwmuploadscript;
use Exception;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Class JBSMAddonLocal
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CWMAddonLocal extends CWMAddon
{
    /**
     * Name of Add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $name = 'local';

    /**
     * Description of add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $description = 'Used for local server files';

    /**
     * Upload
     *
     * @param ?array $data  Data to upload
     *
     * @return array
     *
     * @throws Exception
     * @since 9.0.0
     */
    public function upload(?array $data): array
    {
        return (new Cwmuploadscript())->upload($data);
    }

    /**
     * Render Fields for general view.
     *
     * @param object  $media_form  Medea files form
     * @param bool    $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function renderGeneral($media_form, bool $new): string
    {
        $html   = '';
        $fields = $media_form->getFieldset('general');

        if ($fields) {
            foreach ($media_form->getFieldset('general') as $field) :
                $html .= '<div class="control-group">';
                $html .= '<div class="control-label">';
                $html .= $field->label;
                $html .= '</div>';
                $html .= '<div class="controls">';

                // Way to set defaults on new media
                if ($new) {
                    $s_name = $field->fieldname;

                    if (isset($media_form->s_params[$s_name])) {
                        $field->setValue($media_form->s_params[$s_name]);
                    }
                }

                $html .= $field->input;
                $html .= '</div>';
                $html .= '</div>';
            endforeach;
        }

        return $html;
    }

    /**
     * Render Layout and fields
     *
     * @param object  $media_form  Medea files form
     * @param bool    $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function render($media_form, bool $new): string
    {
        $html = HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('Options'));

        $html .= '<div class="row-fluid">';

        foreach ($media_form->getFieldsets('params') as $name => $fieldset) {
            if ($name !== 'general') {
                $html .= '<div class="span6">';

                foreach ($media_form->getFieldset($name) as $field) :
                    $html .= '<div class="control-group">';
                    $html .= '<div class="control-label">';
                    $html .= $field->label;
                    $html .= '</div>';
                    $html .= '<div class="controls">';

                    // Way to set defaults on new media
                    if ($new) {
                        $s_name = $field->fieldname;

                        if (isset($media_form->s_params[$s_name])) {
                            $field->setValue($media_form->s_params[$s_name]);
                        }
                    }

                    $html .= $field->input;
                    $html .= '</div>';
                    $html .= '</div>';
                endforeach;

                $html .= '</div>';
            }
        }

        $html .= '</div>';
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
    }
}
