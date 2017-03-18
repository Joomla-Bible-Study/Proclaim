<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JFormHelper::loadFieldClass('list');

/**
 * Mime Type List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JFormFieldMimetype extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Mimetype';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$MediaHelper = new JBSMMedia;
		$mimetypes = $MediaHelper->getMimetypes();

		$options = array();

		foreach ($mimetypes as $key => $message)
		{
			$options[] = JHtml::_('select.option', $message, $key);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
