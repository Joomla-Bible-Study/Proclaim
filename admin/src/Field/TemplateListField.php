<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2016 (C) CWM Team All rights reserved
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
 * Teachers List Form Field class for the Proclaim component
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
    protected $type = 'Templates';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since 9.0.0
     */
    protected function getOptions(): array
    {
        $options = [];

        if (!self::$templates) {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('id,title');
            $query->from('#__bsms_templates');
            $query->where('published = 1');
            $query->order('text ASC');
            $db->setQuery((string)$query);
            $messages = $db->loadObjectList();

            foreach ($messages as $message) {
                $options[] = HTMLHelper::_('select.option', $message->id, $message->title);
            }

            self::$templates = array_merge(parent::getOptions(), $options);
        }

        return self::$templates;
    }
}
