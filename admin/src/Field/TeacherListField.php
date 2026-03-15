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
 * Teachers List Form Field class for the Proclaim component
 *
 * On the frontend, only teachers associated with published, access-filtered
 * messages are shown, cross-filtered by other active filters.
 * On the backend, all teachers are listed.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TeacherListField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 9.0.0
     */
    protected $type = 'TeacherList';

    /**
     * Set up the field, switching to fancy-select layout when searchable="true".
     *
     * @param   \SimpleXMLElement  $element  The XML element
     * @param   mixed              $value    The field value
     * @param   string             $group    The field group
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    #[\Override]
    public function setup(\SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result && (string) $this->element['searchable'] === 'true') {
            $this->layout = 'joomla.form.field.list-fancy-select';
        }

        return $result;
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since 9.0.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select('DISTINCT ' . $db->quoteName('t.id') . ', ' . $db->quoteName('t.teachername'))
            ->from($db->quoteName('#__bsms_teachers', 't'));

        if ($app->isClient('site')) {
            $user   = $app->getIdentity();
            $groups = $user->getAuthorisedViewLevels();

            $query->join(
                'INNER',
                $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                . $db->quoteName('stj.teacher_id') . ' = ' . $db->quoteName('t.id')
            )
                ->join(
                    'INNER',
                    $db->quoteName('#__bsms_studies', 's') . ' ON '
                    . $db->quoteName('s.id') . ' = ' . $db->quoteName('stj.study_id')
                )
                ->whereIn($db->quoteName('s.published'), [1, 2])
                ->whereIn($db->quoteName('s.access'), $groups);

            CwmfilterHelper::applyCrossFilters($query, 'teacher');
        }

        $query->order($db->quoteName('t.teachername'));
        $db->setQuery($query);
        $teachers = $db->loadObjectList();
        $options  = [];

        if ($teachers) {
            foreach ($teachers as $teacher) {
                $options[] = HTMLHelper::_('select.option', $teacher->id, $teacher->teachername);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
