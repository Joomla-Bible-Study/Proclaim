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
use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Year List Form Field class for the Proclaim component
 *
 * On the frontend, only years with published, access-filtered messages
 * are shown. On the backend, all years with studies are listed.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class YearListField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 9.0.0
     */
    protected $type = 'YearList';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site')) {
            return array_merge(parent::getOptions(), CwmproclaimHelper::getStudyYears());
        }

        // Frontend: only years from published, accessible messages
        $user   = $app->getIdentity();
        $groups = $user->getAuthorisedViewLevels();
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $query  = $db->getQuery(true);

        $query->select(
            'DISTINCT YEAR(' . $db->quoteName('s.studydate') . ') AS ' . $db->quoteName('value')
            . ', YEAR(' . $db->quoteName('s.studydate') . ') AS ' . $db->quoteName('text')
        )
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->whereIn($db->quoteName('s.published'), [1, 2])
            ->whereIn($db->quoteName('s.access'), $groups)
            ->order($db->quoteName('value') . ' DESC');

        CwmfilterHelper::applyCrossFilters($query, 'year');

        $db->setQuery($query);
        $years   = $db->loadObjectList() ?: [];
        $options = [];

        foreach ($years as $year) {
            $options[] = HTMLHelper::_('select.option', $year->value, $year->text);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
