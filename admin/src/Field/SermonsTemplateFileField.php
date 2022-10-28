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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use JFormHelper;
use Joomla\CMS\Filesystem\Folder;


defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');
/**
 * Message Type List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class SermonsTemplateFileField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'SermonsTemplateFile';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$folder = Folder::files(JPATH_SITE.'/components/com_proclaim/tmpl/cwmsermons');

		foreach ($folder as $key=>$value)
		{
			if ($value == 'default.php'){unset($folder[$key]);}
			if ($value == 'default_easy.php'){unset($folder[$key]);}
			if ($value == 'default_main.php'){unset($folder[$key]);}
			if ($value == 'default_formfooter.php'){unset($folder[$key]);}
			if ($value == 'default_formheader.php'){unset($folder[$key]);}
			if ($value == 'default.xml'){unset($folder[$key]);}
		}
		$folder = str_replace('.php','', $folder);
		$folder = str_replace('default_','', $folder);
		$options  = array();

		if ($folder)
		{
			foreach ($folder as $file)
			{
				$options[] = HtmlHelper::_('select.option', $file, $file);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
