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

use Joomla\CMS\Component\ComponentHelper;
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
class VirtuemartField extends ListField
{
    /**
     * The field type.
     *
     * @var         string
     *
     * @since 7.0.4
     */
    protected $type = 'Virtuemart';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array An array of JHtml options.
     *
     * @since 1.5
     */
    protected function getOptions(): array
    {
        $params = ComponentHelper::getParams('com_languages');

        // Use default joomla
        $siteLang = $params->get('site', 'en-GB');
        $lang = strtolower(str_replace('-', '_', $siteLang));
        define('VMLANG', $lang);

        // Check to see if component installed
        jimport('joomla.filesystem.folder');

        if (
            !Folder::exists(
                JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart'
            )
        ) {
            return [Text::_('JBS_CMN_VIRTUEMART_NOT_INSTALLED')];
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('v.virtuemart_product_id, v.product_name');
        $query->from('#__virtuemart_products_' . VMLANG . ' AS v');
        $query->select('p.product_sku');
        $query->join('LEFT', '#__virtuemart_products as p ON v.virtuemart_product_id = p.virtuemart_product_id');
        $query->order('v.virtuemart_product_id DESC');
        $db->setQuery((string)$query);
        $products = $db->loadObjectList();
        $options = array();

        if ($products) {
            foreach ($products as $product) {
                $options[] = HTMLHelper::_(
                    'select.option',
                    $product->virtuemart_product_id,
                    $product->product_name .
                    ' (' . $product->product_sku . ')'
                );
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
