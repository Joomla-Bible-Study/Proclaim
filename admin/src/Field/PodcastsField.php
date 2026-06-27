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

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Podcasts List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    10.0.0
 */
class PodcastsField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.0.0
     */
    protected $type = 'Podcasts';

    /**
     * Set up the field, switching to the fancy-select (Choices.js) layout when
     * searchable="true". Gives a searchable, tag-style multi-select instead of
     * the native ctrl-click listbox.
     *
     * @param   \SimpleXMLElement  $element  The XML element
     * @param   mixed              $value    The field value
     * @param   string             $group    The field group
     *
     * @return  bool
     *
     * @since   10.3.3
     */
    #[\Override]
    public function setup(\SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result && (string) $this->element['searchable'] === 'true') {
            $this->layout = 'joomla.form.field.list-fancy-select';

            // Ensure the Choices.js dropdown is not clipped by parent containers
            // (rules live in topics-field.css).
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wa->getRegistry()->addExtensionRegistryFile('com_proclaim');
            $wa->useStyle('com_proclaim.topics-field');
        }

        return $result;
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since 10.0.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['id', 'title']))
            ->from($db->quoteName('#__bsms_podcast'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('title') . ' ASC');

        // Filter by location for non-admin users (graceful — column may not exist)
        $user    = Factory::getApplication()->getIdentity();
        $columns = $db->getTableColumns('#__bsms_podcast');

        if (!$user->authorise('core.admin')) {
            if (CwmlocationHelper::isEnabled() && isset($columns['location_id'])) {
                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible)) {
                    $inClause = implode(',', array_map('intval', $accessible));
                    $query->extendWhere(
                        'AND',
                        [
                            $db->quoteName('location_id') . ' IS NULL',
                            $db->quoteName('location_id') . ' IN (' . $inClause . ')',
                        ],
                        'OR'
                    );
                } else {
                    $query->where($db->quoteName('location_id') . ' IS NULL');
                }
            }

            // Standard Joomla access-level filter
            $query->whereIn($db->quoteName('access'), $user->getAuthorisedViewLevels());
        }

        $db->setQuery($query);
        $podcasts = $db->loadObjectList();
        $options  = [];

        if ($podcasts) {
            foreach ($podcasts as $podcast) {
                $options[] = HTMLHelper::_('select.option', $podcast->id, $podcast->title);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
