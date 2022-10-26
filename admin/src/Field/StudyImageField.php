<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Administrator\Field;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;


/**
 * Location List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class StudyImageField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Studyimage';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		if ($this->form->getValue('id'))
		{
			$images = Folder::files(JPATH_SITE.'/media/com_proclaim/images/stockimages');
		}
		else
		{
			$images = null;
		}

		$options = array();

		if ($images)
		{
			foreach ($images as $key=>$value)
			{
				$image = HTMLHelper::_('image','com_proclaim/stockimages/'.$value, 'alt text',null, true);
				$options[]       = HTMLHelper::_('select.option', $value, $image
				);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
