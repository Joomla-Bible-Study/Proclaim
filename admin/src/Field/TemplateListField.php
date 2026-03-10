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
use Joomla\Database\DatabaseInterface;

/**
 * Template selector dropdown for the Proclaim component.
 *
 * When only one published template exists the field renders as a hidden
 * input (the sole template is auto-selected) so it doesn't clutter the UI.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TemplateListField extends ListField
{
    /** @var  array Template Table
     *
     * @since 9.0.13
     */
    public static array $templates = [];

    /**
     * The field type.
     *
     * @var  string
     *
     * @since 9.0.0
     */
    protected $type = 'TemplateList';

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
        if (self::$templates) {
            return self::$templates;
        }

        $options = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'title']))
            ->from($db->quoteName('#__bsms_templates'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('title') . ' ASC');

        $db->setQuery($query);
        $templates = $db->loadObjectList();

        if ($templates) {
            foreach ($templates as $template) {
                $options[] = HTMLHelper::_('select.option', $template->id, $template->title);
            }
        }

        self::$templates = array_merge(parent::getOptions(), $options);

        return self::$templates;
    }

    /**
     * Check whether only one published template exists.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    private function isSingleTemplate(): bool
    {
        $options     = $this->getOptions();
        $realOptions = array_filter($options, static fn ($o) => (string) $o->value !== '');

        return \count($realOptions) <= 1;
    }

    /**
     * Render the complete field row.  When only one published template
     * exists the user has no meaningful choice, so we emit a bare hidden
     * input — no label, no description, no wrapper markup.
     *
     * @param   array  $options  Options for the field rendering.
     *
     * @return  string  The field HTML.
     *
     * @since   10.1.0
     */
    #[\Override]
    public function renderField($options = []): string
    {
        if ($this->isSingleTemplate()) {
            $realOptions = array_filter(
                $this->getOptions(),
                static fn ($o) => (string) $o->value !== ''
            );
            $val = $this->value ?: ($realOptions ? reset($realOptions)->value : '');

            return '<input type="hidden"'
                . ' name="' . $this->name . '"'
                . ' id="' . $this->id . '"'
                . ' value="' . htmlspecialchars((string) $val, ENT_COMPAT, 'UTF-8') . '" />';
        }

        return parent::renderField($options);
    }
}
