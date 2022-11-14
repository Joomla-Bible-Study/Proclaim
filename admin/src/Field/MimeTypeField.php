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
class MimeTypeField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since 7.0
	 */
	protected $type = 'MimeType';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array   An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions(): array
	{
		$MediaHelper = new CWMMedia;
		$mimetypes   = $MediaHelper->getMimetypes();

		$options = array();

		foreach ($mimetypes as $key => $message)
		{
			$options[] = HTMLHelper::_('select.option', $message, $key);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
