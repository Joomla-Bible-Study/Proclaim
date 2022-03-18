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
use CWM\Component\Proclaim\Site\Helper\CWMMedia;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;



/**
 * Mime Type List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class mimetypeField extends ListField
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
		$MediaHelper = new CWMMedia;
		$mimetypes = $MediaHelper->getMimetypes();

		$options = array();

		foreach ($mimetypes as $key => $message)
		{
			$options[] = HTMLHelper::_('select.option', $message, $key);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
