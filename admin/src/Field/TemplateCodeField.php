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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Template Code List Form Field class for the Proclaim component
 *
 * This field fetches all template codes in a single query and caches them,
 * then filters by the specified template type. This reduces database queries
 * when multiple template code fields are used on the same form.
 *
 * Usage in XML:
 * <field name="sermontemplate" type="TemplateCode" templateType="2"
 *        label="JBS_TPL_SERMON_TEMPLATE" description="JBS_TPL_SERMONS_TEMPLATE_DESC">
 *     <option value="0">JBS_CMN_USE_DEFAULT</option>
 * </field>
 *
 * Template types:
 * - 2: Sermon templates
 * - 3: Teachers list templates
 * - 4: Teacher templates
 * - 5: Series list templates
 * - 6: Series detail templates
 *
 * @package  Proclaim.Admin
 * @since    10.0.0
 */
class TemplateCodeField extends ListField
{
    /**
     * The field type.
     *
     * @var    string
     * @since  10.0.0
     */
    protected $type = 'TemplateCode';

    /**
     * Cached template codes grouped by type.
     * Static so it persists across multiple field instances.
     *
     * @var    array|null
     * @since  10.0.0
     */
    protected static ?array $cachedTemplateCodes = null;

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since   10.0.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        // Get the template type from the field definition
        $templateType = (int) $this->element['templateType'];

        // Load and cache all template codes if not already cached
        if (self::$cachedTemplateCodes === null) {
            $this->loadTemplateCodes();
        }

        $options = [];

        // Get options for the specified type
        if (isset(self::$cachedTemplateCodes[$templateType])) {
            foreach (self::$cachedTemplateCodes[$templateType] as $template) {
                $options[] = HTMLHelper::_('select.option', $template->filename, $template->filename);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Load all template codes from the database and cache them grouped by type.
     *
     * @return  void
     *
     * @since   10.0.0
     */
    protected function loadTemplateCodes(): void
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'type', 'filename']))
            ->from($db->quoteName('#__bsms_templatecode'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('type') . ' IN (2, 3, 4, 5, 6)')
            ->order($db->quoteName('filename') . ' ASC');

        $db->setQuery($query);
        $results = $db->loadObjectList();

        // Initialize cache with empty arrays for each type
        self::$cachedTemplateCodes = [
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            6 => [],
        ];

        // Group results by type
        if ($results) {
            foreach ($results as $template) {
                $type = (int) $template->type;
                if (isset(self::$cachedTemplateCodes[$type])) {
                    self::$cachedTemplateCodes[$type][] = $template;
                }
            }
        }
    }

    /**
     * Clear the cached template codes.
     * Useful when template codes are modified and need to be refreshed.
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public static function clearCache(): void
    {
        self::$cachedTemplateCodes = null;
    }
}
