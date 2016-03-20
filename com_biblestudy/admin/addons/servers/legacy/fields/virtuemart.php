<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Import the list field type
jimport('joomla.form.helper');

JFormHelper::loadFieldClass('list');

/**
 * Virtuemart Category List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.4
 */
class JFormFieldVirtuemart extends JFormFieldList
{

	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'Virtuemart';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 */
	protected function getOptions()
	{
		$params = JComponentHelper::getParams('com_languages');

		// Use default joomla
		$siteLang = $params->get('site', 'en-GB');
		$lang     = strtolower(strtr($siteLang, '-', '_'));
		define('VMLANG', $lang);

		// Check to see if component installed
		jimport('joomla.filesystem.folder');

		if (!JFolder::exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart'))
		{
			return JText::_('JBS_CMN_VIRTUEMART_NOT_INSTALLED');
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('v.virtuemart_product_id, v.product_name');
		$query->from('#__virtuemart_products_' . VMLANG . ' AS v');
		$query->select('p.product_sku');
		$query->join('LEFT', '#__virtuemart_products as p ON v.virtuemart_product_id = p.virtuemart_product_id');
		$query->order('v.virtuemart_product_id DESC');
		$db->setQuery((string) $query);
		$products = $db->loadObjectList();
		$options  = array();

		if ($products)
		{
			foreach ($products as $product)
			{
				$options[] = JHtml::_('select.option', $product->virtuemart_product_id, $product->product_name . ' (' . $product->product_sku . ')');
			}
		}
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
