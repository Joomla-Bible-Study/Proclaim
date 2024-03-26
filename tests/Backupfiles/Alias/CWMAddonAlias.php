<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
namespace CWM\Component\Proclaim\Administrator\Addons\Alias;

\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Amazons3\S3;
use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Input\Input;

/**
 * Class Cwmalias
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CWMAlias extends CWMAddon
{
    public $name = 'alias';

    /**
     * Construct
     *
     * @param   array  $options  Array of Options
     *
     * @since 10.0.0
     */
    protected function __construct($config = array())
    {
        $options['key']    = (isset($options['key'])) ? $options['key'] : '';
        $options['secret'] = (isset($options['secret'])) ? $options['secret'] : '';

        // Include the S3 class
        Loader::register('S3', dirname(__FILE__) . '/S3.class.php');

        $this->connection = new S3($options['key'], $options['secret']);
    }

    /**
     * Upload
     *
     * @param array|Input $data     ?
     *
     * @return void
     * @since 10.0.0
     */
    protected function upload(Input|array $data): mixed
    {
        // TODO: Implement upload() method.
    }

    /**
     * Test funciotn
     *
     * @return string
     * @since 10.0.0
     */
    public function test()
    {
        return "hello from amazon";
    }

    protected function renderGeneral($media_form, bool $new): string
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

    protected function render($media_form, bool $new): string
    {
        $html = HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('Options'));

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
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
    }
}
