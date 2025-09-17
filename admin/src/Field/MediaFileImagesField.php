<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Location List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class MediaFileImagesField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'MediaFileImages';

    /**
     * Method to get a list of options for a list input.
     *
     * @return   array  An array of HTMLHelper options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__bsms_mediafiles');
        $db->setQuery((string)$query);
        $mediafiles = $db->loadObjectList();
        $options    = [];

        if ($mediafiles) {
            foreach ($mediafiles as $media) {
                $reg = new Registry();
                $reg->loadString($media->params);
                $media->params = $reg;

                if ($media->params->get('media_use_button_icon') >= 1) {
                    switch ($media->params->get('media_use_button_icon')) {
                        case 1:
                            $button             = $this->getButton($media);
                            $media->media_image = Text::_('JBS_MED_BUTTON') . ': ' . $button . ' - ' . Text::_(
                                'JBS_MED_TEXT'
                            ) .
                                ': ' . $media->params->get('media_button_text');
                            $options[]          = HTMLHelper::_(
                                'select.option',
                                '{"media_use_button_icon":"' .
                                $media->params->get('media_use_button_icon') .
                                '","media_button_type":"' . $media->params->get(
                                    'media_button_type'
                                ) . '","media_button_text":"' .
                                $media->params->get(
                                    'media_button_text'
                                ) . '","media_icon_type":"' . $media->params->get('media_icon_type') .
                                '","media_icon_text_size":"' . $media->params->get('media_icon_text_size') .
                                '","media_image":""}',
                                $media->media_image
                            );
                            break;
                        case 2:
                            $button             = $this->getButton($media);
                            $icon               = $this->getIcon($media);
                            $media->media_image = Text::_('JBS_MED_BUTTON') . ': ' . $button . ' - ' . Text::_(
                                'JBS_MED_ICON'
                            ) . ': ' . $icon;
                            $options[]          = HTMLHelper::_(
                                'select.option',
                                '{"media_use_button_icon":"' .
                                $media->params->get('media_use_button_icon') .
                                '","media_button_type":"' . $media->params->get(
                                    'media_button_type'
                                ) . '","media_button_text":"' .
                                $media->params->get(
                                    'media_button_text'
                                ) . '","media_icon_type":"' . $media->params->get('media_icon_type') .
                                '","media_icon_text_size":"' . $media->params->get('media_icon_text_size') .
                                '","media_image":""}',
                                $media->media_image
                            );
                            break;
                        case 3:
                            $icon               = $this->getIcon($media);
                            $media->media_image = Text::_('JBS_MED_ICON') . ': ' . $icon;
                            $options[]          = HTMLHelper::_(
                                'select.option',
                                '{"media_use_button_icon":"' .
                                $media->params->get('media_use_button_icon') .
                                '","media_button_type":"' . $media->params->get(
                                    'media_button_type'
                                ) . '","media_button_text":"' .
                                $media->params->get(
                                    'media_button_text'
                                ) . '","media_icon_type":"' . $media->params->get('media_icon_type') .
                                '","media_icon_text_size":"' . $media->params->get('media_icon_text_size') .
                                '","media_image":""}',
                                $media->media_image
                            );
                            break;
                    }
                } else {
                    $image              = $media->params->get('media_image');
                    $totalcount         = strlen($image);
                    $slash              = strrpos($image, '/');
                    $imagecount         = $totalcount - $slash;
                    $media->media_image = Text::_('JBS_MED_IMAGE') . ': ' . substr($image, $slash + 1, $imagecount);
                    $options[]          = HTMLHelper::_(
                        'select.option',
                        '{"media_use_button_icon":"' . $media->params->get('media_use_button_icon') .
                        '","media_button_type":"' . $media->params->get(
                            'media_button_type'
                        ) . '","media_button_text":"' .
                        $media->params->get('media_button_text') . '","media_icon_type":"' . $media->params->get(
                            'media_icon_type'
                        ) .
                        '","media_icon_text_size":"' . $media->params->get(
                            'media_icon_text_size'
                        ) . '","media_image":"' .
                        $media->params->get('media_image') . '"}',
                        $media->media_image
                    );
                }
            }
        }

        $tmp = [];

        foreach ($options as $k => $v) {
            $tmp[$k] = $v->text;
        }

        // Determine the total records for each image/button/incon
        $count = array_count_values($tmp);

        $tmp = array_unique($tmp);

        // Remove the duplicates from original array
        foreach ($options as $k => $v) {
            if (!array_key_exists($k, $tmp)) {
                unset($options[$k]);
            }
        }

        // Add the number of records from $count to the text of the drop down
        foreach ($options as $k => $v) {
            foreach ($count as $key => $value) {
                if ($key == $v->text) {
                    $options[$k]->text = $v->text . ' (' . $value . ')';
                }
            }
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get Button
     *
     * @param   Object  $media  Media table.
     *
     * @return string|null
     *
     * @since 7.0
     */
    public function getButton(object $media): ?string
    {
        $button = null;

        switch ($media->params->get('media_button_type')) {
            case 'btn-link':
                $button = Text::_('JBS_MED_NO_COLOR');
                break;
            case 'btn-primary':
                $button = Text::_('JBS_MED_PRIMARY');
                break;
            case 'btn-success':
                $button = Text::_('JBS_MED_SUCCESS');
                break;
            case 'btn-info':
                $button = Text::_('JBS_MED_INFO');
                break;
            case 'btn-warning':
                $button = Text::_('JBS_MED_WARNING');
                break;
            case 'btn-danger':
                $button = Text::_('JBS_MED_DANGER');
                break;
        }

        if ($media->params->get('media_button_color')) {
            $button = $media->params->get('media_button_color');
        }

        return $button;
    }

    /**
     * Get Icon
     *
     * @param   Object  $media  Media Table
     *
     * @return string
     *
     * @since 7.0
     */
    public function getIcon(object $media): string
    {
        $MediaHelper = new Cwmmedia();
        $mimetypes   = $MediaHelper->getIcons();

        if (
            $media->params->get('media_icon_type') !== '1'
            && !str_starts_with($media->params->get('media_icon_type'), 'fa')
            && !empty($media->params->get('media_icon_type'))
        ) {
            return Text::_($mimetypes[$media->params->get('media_icon_type')]) ?? "";
        }

        return $media->params->get('media_custom_icon');
    }
}
