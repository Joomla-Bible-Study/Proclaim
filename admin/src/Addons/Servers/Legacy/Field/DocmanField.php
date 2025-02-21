<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Legacy\Field;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

use function defined;

/**
 * Virtuemart Category List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class DocmanField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 9.0.0
     */
    protected $type = 'Docman';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     *
     * @since 9.0.0
     */
    protected function getOptions(): array
    {
        if (
            !Folder::exists(JPATH_ADMINISTRATOR . '/components/com_docman')
        ) {
            return [Text::_('JBS_CMN_DOCMAN_NOT_INSTALLED')];
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('dm.docman_document_id, dm.title');
        $query->from('#__docman_documents AS dm');
        $query->order('dm.docman_document_id DESC');
        $db->setQuery((string)$query);
        $docs    = $db->loadObjectList();
        $options = array();

        if ($docs) {
            $options[] = HTMLHelper::_('select.option', '-1', Text::_('JBS_MED_DOCMAN_SELECT'));

            foreach ($docs as $doc) {
                $options[] = HTMLHelper::_('select.option', $doc->id, $doc->title);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
