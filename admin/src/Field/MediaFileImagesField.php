<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
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
use Joomla\Database\DatabaseInterface;
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
     * The params that together describe how a media file is displayed.
     *
     * Matches the set CwmadminModel::changeMediaImages() writes, so an option
     * value round-trips through either consumer without losing a field.
     *
     * @var  string[]
     *
     * @since __DEPLOY_VERSION__
     */
    public const DISPLAY_KEYS = [
        'media_use_button_icon',
        'media_button_type',
        'media_button_text',
        'media_button_color',
        'media_icon_type',
        'media_custom_icon',
        'media_image',
    ];

    /**
     * Method to get a list of options for a list input.
     *
     * @return   array  An array of HTMLHelper options.
     *
     * @since 7.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        return array_merge(parent::getOptions(), self::buildDisplayOptions());
    }

    /**
     * Build one option per distinct display configuration in use.
     *
     * Shared with Cwmhtml::mediaType(), which offers the same choices as a
     * batch action, so the list a batch can apply and the list this field
     * offers cannot drift apart.
     *
     * @param   bool  $withCounts  Append the site-wide number of media files
     *                             using each configuration to its label. Off
     *                             for the batch widget, where a global count
     *                             next to a selection of rows misleads.
     *
     * @return  object[]  HTMLHelper select options; value is a JSON blob of the
     *                    display params, text is a human-readable label.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function buildDisplayOptions(bool $withCounts = true): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();
        $query->select($db->quoteName('params'));
        $query->from($db->quoteName('#__bsms_mediafiles'));
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
                            $button             = self::getButton($media);
                            $media->media_image = Text::_('JBS_MED_BUTTON') . ': ' . $button . ' - ' . Text::_(
                                'JBS_MED_TEXT'
                            ) .
                                ': ' . $media->params->get('media_button_text');
                            $options[]          = HTMLHelper::_(
                                'select.option',
                                self::buildOptionValueJson($media->params),
                                $media->media_image
                            );
                            break;
                        case 2:
                            $button             = self::getButton($media);
                            $icon               = self::getIcon($media);
                            $media->media_image = Text::_('JBS_MED_BUTTON') . ': ' . $button . ' - ' . Text::_(
                                'JBS_MED_ICON'
                            ) . ': ' . $icon;
                            $options[]          = HTMLHelper::_(
                                'select.option',
                                self::buildOptionValueJson($media->params),
                                $media->media_image
                            );
                            break;
                        case 3:
                            $icon               = self::getIcon($media);
                            $media->media_image = Text::_('JBS_MED_ICON') . ': ' . $icon;
                            $options[]          = HTMLHelper::_(
                                'select.option',
                                self::buildOptionValueJson($media->params),
                                $media->media_image
                            );
                            break;
                    }
                } else {
                    // media_image is unset on media files that predate the
                    // display settings, so cast before measuring it.
                    $image              = (string) ($media->params->get('media_image') ?? '');
                    $slash              = strrpos($image, '/');
                    $media->media_image = Text::_('JBS_MED_IMAGE') . ': '
                        . ($slash === false ? $image : substr($image, $slash + 1));
                    $options[]          = HTMLHelper::_(
                        'select.option',
                        self::buildOptionValueJson($media->params, $image),
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
            if (!\array_key_exists($k, $tmp)) {
                unset($options[$k]);
            }
        }

        if ($withCounts) {
            foreach ($options as $k => $v) {
                foreach ($count as $key => $value) {
                    if ($key == $v->text) {
                        $options[$k]->text = $v->text . ' (' . $value . ')';
                    }
                }
            }
        }

        return array_values($options);
    }

    /**
     * Build the option value: a JSON blob of the media params this option
     * represents, re-applied to the target field on selection.
     *
     * Every key CwmadminModel::changeMediaImages() writes is included, so
     * applying an option reproduces the configuration its label describes.
     * Leaving media_button_color or media_custom_icon out would keep the
     * target's existing colour or custom icon and produce something that does
     * not match the option the administrator picked.
     *
     * @param   Registry  $params      The media file's params.
     * @param   string    $mediaImage  The media_image value to embed (empty for button/icon options).
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function buildOptionValueJson(Registry $params, string $mediaImage = ''): string
    {
        $value = [];

        foreach (self::DISPLAY_KEYS as $key) {
            $value[$key] = (string) $params->get($key);
        }

        // Button/icon options carry no image; only the image mode sets one.
        $value['media_image'] = $mediaImage;

        return json_encode($value) ?: '{}';
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
    public static function getButton(object $media): ?string
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
    public static function getIcon(object $media): string
    {
        $MediaHelper = new Cwmmedia();
        $mimetypes   = $MediaHelper->getIcons();

        $iconType = (string) ($media->params->get('media_icon_type') ?? '');

        if ($iconType !== '1' && !str_starts_with($iconType, 'fa') && $iconType !== '') {
            // An icon type with no entry in the map would otherwise raise an
            // undefined-key warning and render an empty label.
            return isset($mimetypes[$iconType]) ? Text::_($mimetypes[$iconType]) : $iconType;
        }

        // media_custom_icon is unset on any media file using a Font Awesome
        // icon rather than a custom one, and the declared return type is
        // string -- returning the null straight through fataled the whole
        // Image Tools screen for those rows.
        return (string) ($media->params->get('media_custom_icon') ?? '');
    }
}
