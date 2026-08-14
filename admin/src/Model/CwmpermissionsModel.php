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
 * One section per request: all 17 grids on one form exceeds PHP's
 * `max_input_vars`, and the excess is discarded silently.
 *
 * @since  10.5.8
 */
class CwmpermissionsModel extends FormModel
{
    /**
     * Resolve the section being edited, falling back to the first declared one.
     *
     * ⚠️ The value reaches `RulesField` as an xpath fragment, so it must be
     * validated against access.xml before the field renders.
     *
     * @return  string  A declared section name, or an empty string when none exist
     *
     * @since   10.5.8
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
     * @since   10.5.8
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

        // ⚠️ An empty asset_field makes RulesField silently fall back to the
        // component asset, showing and saving com_proclaim's own rules.
        $form->setValue('asset_id', null, Cwmassets::sectionId($section));

        return $form;
    }

    /**
     * Data for the form.
     *
     * @return  array
     *
     * @throws  \Exception
     * @since   10.5.8
     */
    protected function loadFormData(): array
    {
        $section = $this->getSection();

        return $section === '' ? [] : ['asset_id' => Cwmassets::sectionId($section)];
    }

    /**
     * Write the posted grid to the section's asset.
     *
     * Refuses without `core.admin` rather than discarding the values, following
     * com_config's ComponentModel.
     *
     * @param   array  $data  Form data, already filtered through the form
     *
     * @return  bool  True on success
     *
     * @throws  \RuntimeException  When rules are posted without core.admin
     * @since   10.5.8
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
