<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;

/**
 * Class JBSMAddonYouTube
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CWMAddonYoutube extends CWMAddon
{
    /**
     * Name of Add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $name = 'Youtube';

    /**
     * Description of add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $description = 'Used for YouTube server access';

    /**
     * Upload
     *
     * @param array|Input $data  Data to upload
     *
     * @return array
     *
     * @since 9.0.0
     */
    public function upload(\Joomla\Input\Input|array $data): mixed
    {
        // Holds for nothing
        return $data;
    }

    /**
     * Render Fields for general view.
     *
     * @param   object  $media_form  Medea files form
     * @param bool $new         If media is new
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
            foreach ($fields as $field) :
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
     * @param   object  $media_form  Medea files form
     * @param bool $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function render($media_form, bool $new): string
    {
        $html = '';
        $html .= HtmlHelper::_('uitab.addTab', 'myTab', 'options', Text::_('Options'));

        $html .= '<div class="row-fluid">';

        foreach ($media_form->getFieldsets('params') as $name => $fieldset) {
            if ($name !== 'general') {
                $html .= '<div class="col-6">';

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
        $html .= HtmlHelper::_('uitab.endTab');

        return $html;
    }
}
