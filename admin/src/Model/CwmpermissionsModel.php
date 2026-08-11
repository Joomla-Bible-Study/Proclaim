<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Asset;

/**
 * Permissions for one `com_proclaim.<section>` asset.
 *
 * Deliberately one section per request. The earlier design put every section's
 * grid on the Administration screen, which posted 1217 fields against PHP's
 * default `max_input_vars` of 1000 -- `$_POST` was truncated at request
 * startup, Joomla's `task` field went with it, and the request fell through to
 * `display` without ever reaching a controller. Nothing saved, and nothing said
 * so. See #1653.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmpermissionsModel extends FormModel
{
    /**
     * Resolve the section being edited, refusing anything access.xml does not declare.
     *
     * The value reaches `RulesField` as an xpath fragment
     * (`/access/section[@name="<section>"]/`), so it is validated here rather
     * than relying on `Cwmassets::sectionId()` to refuse it later -- the field
     * renders long before that would matter.
     *
     * @return  string  A declared section name, or an empty string when none exist
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getSection(): string
    {
        $sections = Cwmassets::declaredSections();

        if ($sections === []) {
            return '';
        }

        $requested = (string) Factory::getApplication()->getInput()->getCmd('section', '');

        return \in_array($requested, $sections, true) ? $requested : $sections[0];
    }

    /**
     * Method to get the form.
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data.
     *
     * @return  Form|false  A Form object on success, false on failure
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function getForm($data = [], $loadData = true): Form|false
    {
        $form = $this->loadForm('com_proclaim.permissions', 'permissions', ['control' => 'jform', 'load_data' => $loadData]);

        if (!$form) {
            return false;
        }

        $section = $this->getSection();

        if ($section === '') {
            return $form;
        }

        $form->setFieldAttribute('rules', 'section', $section);

        // Bind the asset id explicitly. RulesField resolves it with
        // $form->getValue($assetField), and an empty value makes it fall back
        // to the component asset -- the grid would then show, and save,
        // com_proclaim's own rules under a section label.
        $form->setValue('asset_id', null, Cwmassets::sectionId($section));

        return $form;
    }

    /**
     * Data for the form.
     *
     * @return  array
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    protected function loadFormData(): array
    {
        $section = $this->getSection();

        return $section === '' ? [] : ['asset_id' => Cwmassets::sectionId($section)];
    }

    /**
     * Write the posted grid to the section's asset.
     *
     * Follows com_config's ComponentModel: build a Rules object from the grid,
     * store it, and refuse outright without `core.admin`. The screen only
     * renders for users who hold it, so a post that arrives without it is a
     * stale form or a forged request -- neither should be answered by silently
     * discarding the values.
     *
     * @param   array  $data  Posted form data
     *
     * @return  bool  True on success
     *
     * @throws  \RuntimeException  When rules are posted without core.admin
     * @since   __DEPLOY_VERSION__
     */
    public function save($data): bool
    {
        if (!isset($data['rules']) || !\is_array($data['rules'])) {
            $this->setError(Text::_('JBS_ADM_PERMISSIONS_NOTHING_POSTED'));

            return false;
        }

        if (!$this->getCurrentUser()->authorise('core.admin', 'com_proclaim')) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
        }

        $section = $this->getSection();

        if ($section === '') {
            $this->setError(Text::_('JBS_ADM_PERMISSIONS_NO_SECTIONS'));

            return false;
        }

        $assetId = Cwmassets::sectionId($section);

        if ($assetId < 1) {
            $this->setError(Text::sprintf('JBS_ADM_PERMISSIONS_NO_ASSET', $section));

            return false;
        }

        $asset = new Asset($this->getDatabase());

        if (!$asset->load($assetId)) {
            $this->setError(Text::sprintf('JBS_ADM_PERMISSIONS_NO_ASSET', $section));

            return false;
        }

        $asset->rules = (string) new Rules($data['rules']);

        if (!$asset->check() || !$asset->store()) {
            $this->setError($asset->getError());

            return false;
        }

        return true;
    }
}
