<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
JFormHelper::loadFieldClass('list');

/**
 * Icons List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class JFormFieldIcontype extends ListField
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'IconType';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   9.1.3
	 */
	protected function getInput()
	{
		$data = $this->getLayoutData();

		$convert = [
			'fa fa-play'          => 'fas fa-play',
			'fa fa-youtube'       => 'fab fa-youtube',
			'fa fa-video-camera'  => 'fas fa-video',
			'fa fa fa-television' => 'far fa-tv',
			'fa fa-file'          => 'fas fa-file',
			'fa fa-file-pdf'      => 'fas fa-file-pdf',
			'fa fa-vimeo'         => 'fab fa-vimeo'
		];

		if (isset($convert[$this->value]))
		{
			$this->value = $convert[$this->value];
		}

		$data['options'] = (array) $this->getOptions();

		return $this->getRenderer($this->layout)->render($data);
	}

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
		$icontypes = $MediaHelper->getIcons();

		$options = array();

		foreach ($icontypes as $key => $message)
		{
			$key = JText::_($key);
			$options[] = JHtml::_('select.option', $message, $key);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
