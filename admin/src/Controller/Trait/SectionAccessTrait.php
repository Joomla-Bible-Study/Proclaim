<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Controller\Trait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Answers create and edit against `com_proclaim.<section>` rather than the component.
 *
 * `FormController::allowAdd()` and `allowEdit()` ask `com_proclaim`, so a section
 * rule hid a list's buttons while the record stayed reachable and saveable by URL.
 *
 * Usage: `use SectionAccessTrait;` in a FormController subclass and set
 * `protected string $aclSection = 'teacher';`
 *
 * @since  10.5.8
 */
trait SectionAccessTrait
{
    /**
     * The access.xml section this controller's records belong to.
     * Must be set by the using class.
     *
     * @var    string
     * @since  10.5.8
     */
    // protected string $aclSection = 'teacher';

    /**
     * The asset create and edit are authorised against.
     *
     * The record's own asset when it has one — those are parented to the section
     * now, so its rules are inherited and an item rule refines within them
     * rather than escaping them. A deny on the section still wins, because
     * `Rule::mergeIdentity()` merges root-first and an explicit deny survives
     * every later merge.
     *
     * ⚠️ Only when `asset_id > 0`. Joomla resolves an unknown
     * `com_proclaim.teacher.5` to everything before the **first** dot, so a
     * row-less record would fall back to `com_proclaim` and skip the section.
     *
     * @param   int  $recordId  The record being edited, 0 when creating
     *
     * @return  string
     *
     * @since   10.5.8
     */
    protected function sectionAsset(int $recordId = 0): string
    {
        if (!isset($this->aclSection)) {
            return 'com_proclaim';
        }

        $section = 'com_proclaim.' . $this->aclSection;

        return $recordId > 0 && $this->recordHasAsset($recordId) ? $section . '.' . $recordId : $section;
    }

    /**
     * Whether a record carries an `#__assets` row of its own.
     *
     * Most do not: Proclaim strips an item asset again when its rules are empty,
     * so one exists only where per-record permissions were actually set.
     *
     * @param   int  $recordId  The record primary key
     *
     * @return  bool
     *
     * @since   10.5.8
     */
    private function recordHasAsset(int $recordId): bool
    {
        if (!isset($this->accessTable)) {
            return false;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('asset_id'))
            ->from($db->quoteName($this->accessTable))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $recordId, ParameterType::INTEGER);

        return (int) $db->setQuery($query)->loadResult() > 0;
    }

    /**
     * Whether the user may create a record in this section.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  bool
     *
     * @throws  \Exception
     * @since   10.5.8
     */
    protected function allowAdd($data = []): bool
    {
        return Factory::getApplication()->getIdentity()->authorise('core.create', $this->sectionAsset());
    }

    /**
     * Whether the user may edit records in this section.
     *
     * Does not consider `core.edit.own`, matching what `FormController` did
     * before: only the asset changes here, not the question.
     *
     * @return  bool
     *
     * @throws  \Exception
     * @since   10.5.8
     */
    protected function allowSectionEdit(int $recordId = 0): bool
    {
        return Factory::getApplication()->getIdentity()->authorise('core.edit', $this->sectionAsset($recordId));
    }
}
