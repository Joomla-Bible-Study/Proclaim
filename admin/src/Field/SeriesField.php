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

use CWM\Component\Proclaim\Administrator\Helper\CwmfilterHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Series List Form Field class for the Proclaim component
 *
 * On the frontend, only series that contain published, access-filtered messages
 * are shown, and the series itself must be accessible to the user.
 * On the backend, all published series are listed.
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class SeriesField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     * @since    7.0.4
     */
    protected $type = 'Series';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of JHtml options.
     *
     * @since    7.0.4
     */
    #[\Override]
    protected function getOptions(): array
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select('DISTINCT ' . $db->quoteName('se.id') . ', ' . $db->quoteName('se.series_text'))
            ->from($db->quoteName('#__bsms_series', 'se'))
            ->whereIn($db->quoteName('se.published'), [1, 2]);

        if ($app->isClient('site')) {
            // Frontend: only series with published/archived, accessible messages + series access check
            $user   = $app->getIdentity();
            $groups = $user->getAuthorisedViewLevels();

            $query->join(
                'INNER',
                $db->quoteName('#__bsms_studies', 's') . ' ON '
                . $db->quoteName('s.series_id') . ' = ' . $db->quoteName('se.id')
            )
                ->whereIn($db->quoteName('s.published'), [1, 2])
                ->whereIn($db->quoteName('s.access'), $groups)
                ->whereIn($db->quoteName('se.access'), $groups);

            CwmfilterHelper::applyCrossFilters($query, 'series');
        }

        $query->order($db->quoteName('se.series_text'));
        $db->setQuery($query);
        $rows    = $db->loadObjectList();
        $options = [];

        if ($rows) {
            foreach ($rows as $row) {
                $options[] = HTMLHelper::_('select.option', $row->id, $row->series_text);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
