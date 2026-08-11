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

/**
 * Answers create and edit against `com_proclaim.<section>` rather than the component.
 *
 * `FormController::allowAdd()` and `allowEdit()` ask `com_proclaim`, so a section
 * rule hid a list's buttons while the record stayed reachable and saveable by URL.
 *
 * Usage: `use SectionAccessTrait;` in a FormController subclass and set
 * `protected string $aclSection = 'teacher';`
 *
 * @since  __DEPLOY_VERSION__
 */
trait SectionAccessTrait
{
    /**
     * The access.xml section this controller's records belong to.
     * Must be set by the using class.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    // protected string $aclSection = 'teacher';

    /**
     * The asset create and edit are authorised against.
     *
     * Always the section, never `com_proclaim.<section>.<id>`. Item assets are
     * parented to the component, so asking one lets any record that happens to
     * carry an `#__assets` row escape the section rule entirely. Records with an
     * explicit item grant are therefore governed by the section too.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function sectionAsset(): string
    {
        return isset($this->aclSection) ? 'com_proclaim.' . $this->aclSection : 'com_proclaim';
    }

    /**
     * Whether the user may create a record in this section.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  bool
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    protected function allowSectionEdit(): bool
    {
        return Factory::getApplication()->getIdentity()->authorise('core.edit', $this->sectionAsset());
    }
}
