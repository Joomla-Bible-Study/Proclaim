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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * class for Podcasts
 *
 * @since  10.0.0
 */
class PodcastsField extends ListField
{
    /**
     * Flag to work with nested tag field
     *
     * @var    bool
     * @since  3.1
     */
    public $isNested = null;
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'Podcasts';
    /**
     * com_tags parameters
     *
     * @var    \Joomla\Registry\Registry
     * @since  3.1
     */
    protected $comParams = null;

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.list';

    /**
     * Method to get the field input for a tag field.
     *
     * @return  string  The field input.
     *
     * @since   3.1
     */
    protected function getInput(): string
    {
        $data = $this->getLayoutData();

        if (!\is_array($this->value) && !empty($this->value)) {
            if ($this->value instanceof TagsHelper) {
                if (empty($this->value->tags)) {
                    $this->value = [];
                } else {
                    $this->value = $this->value->tags;
                }
            }

            // String in format 2,5,4
            if (\is_string($this->value)) {
                $this->value = explode(',', $this->value);
            }

            // Integer is given
            if (\is_int($this->value)) {
                $this->value = [$this->value];
            }

            $data['value'] = $this->value;
        }

        return $this->getRenderer($this->layout)->render($data);
    }


    /**
     * Returns an array of tags.
     *
     * @return  array
     *
     * @throws \Exception
     * @since   3.1
     */
    protected function getOptions(): array
    {
        $published = (string)$this->element['published'] ?: [0, 1];
        $app       = Factory::getApplication();
        $language  = null;
        $options   = [];

        // This limit is only used with isRemoteSearch
        $prefillLimit = 30;

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.title'),
                ]
            )
            ->from($db->quoteName('#__bsms_podcast', 'a'));

        // Limit Options in multilanguage
        if ($app->isClient('site') && Multilanguage::isEnabled()) {
            if (ComponentHelper::getParams('com_tags')->get('tag_list_language_filter') === 'current_language') {
                $language = [$app->getLanguage()->getTag(), '*'];
            }
        } elseif (!empty($this->element['language'])) {
            // Filter language
            if (str_contains($this->element['language'], ',')) {
                $language = explode(',', $this->element['language']);
            } else {
                $language = [$this->element['language']];
            }
        }

        if ($language) {
            $query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
        }

        // Filter on the published state
        if (is_numeric($published)) {
            $published = (int)$published;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif (\is_array($published)) {
            $published = ArrayHelper::toInteger($published);
            $query->whereIn($db->quoteName('a.published'), $published);
        }

        $query->order($db->quoteName('a.title') . ' ASC');

        // Only execute the query if we need more tags not already loaded by the $preQuery query
        // Get the options.
        $db->setQuery($query);

        try {
            $options = array_merge($options, $db->loadObjectList());
        } catch (\RuntimeException $e) {
            return [];
        }

        return $options;
    }
}
