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
use CWM\Library\Scripture\Helper\ScriptureHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Book List Form Field class for the Proclaim component
 *
 * On the frontend, only books used by published, access-filtered messages
 * are shown. On the backend, all books used in any study are listed.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BookListField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'BookList';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of JHtml options.
     *
     * @throws \Exception
     * @since 7.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site')) {
            return array_merge(parent::getOptions(), CwmproclaimHelper::getStudyBooks());
        }

        // Frontend: only books used by published, accessible messages
        $user   = $app->getIdentity();
        $groups = $user->getAuthorisedViewLevels();
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $query  = $db->createQuery();

        // Books come from the junction, so a study with more than two references
        // offers all of them rather than the two the flat columns could hold
        // . The studies keep the alias `s`: applyCrossFilters() below
        // writes s.series_id and s.id, so the alias is part of that helper's
        // contract.
        $query->select('DISTINCT ' . $db->quoteName('sr.booknumber', 'value'))
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->join(
                'INNER',
                $db->quoteName('#__bsms_study_scriptures', 'sr') . ' ON '
                . $db->quoteName('sr.study_id') . ' = ' . $db->quoteName('s.id')
            )
            ->where($db->quoteName('sr.booknumber') . ' > 0')
            ->whereIn($db->quoteName('s.published'), [1, 2])
            ->whereIn($db->quoteName('s.access'), $groups)
            ->order($db->quoteName('sr.booknumber') . ' ASC');

        CwmfilterHelper::applyCrossFilters(
            $query,
            'book',
            CwmfilterHelper::contextFromForm($this->form)
        );

        $db->setQuery($query);
        $books   = $db->loadObjectList() ?: [];
        $options = [];

        foreach ($books as $book) {
            $name = ScriptureHelper::getBookName((int) $book->value);

            // A number the library cannot name would render as a blank entry.
            if ($name !== '') {
                $options[] = HTMLHelper::_('select.option', $book->value, $name);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
