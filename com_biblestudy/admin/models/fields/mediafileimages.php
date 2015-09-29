<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JFormHelper::loadFieldClass('list');

/**
 * Location List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JFormFieldMediafileImages extends JFormFieldList
{

	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'Mediafileimages';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 */
	protected function getOptions()
	{

			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('a.id, a.params');
			$query->from('#__bsms_mediafiles as a');
			$db->setQuery((string) $query);
			$mediafiles = $db->loadObjectList();


		$options = array();

		if ($mediafiles)
		{
			foreach ($mediafiles as $media)
			{
				$reg = new Registry;
				$reg->loadString($media->params);
				$media->params = $reg;
				$image = $media->params->get('media_image');
				$totalcount = strlen($image);
				$slash = strrpos($image,'/');
				$imagecount = $totalcount - $slash;
				$media->media_image = substr($image,$slash + 1,$imagecount);
				$options[]       = JHtml::_('select.option', $media->id, $media->media_image
				);
			}
		}

		$tmp = array();
		foreach($options as $k => $v)
			$tmp[$k] = $v->text;

// Find duplicates in temporary array
		$tmp = array_unique($tmp);

// Remove the duplicates from original array
		foreach($options as $k => $v)
		{
			if (!array_key_exists($k, $tmp))
				unset($options[$k]);
		}
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
